<?php
function sk_http_get($url, $headers = []) {
    $headers = array_merge([
        'User-Agent: SK-LIGTAS/1.0 emergency-location-service'
    ], $headers);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $status >= 200 && $status < 300) {
            return $body;
        }
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'header' => implode("\r\n", $headers)
        ]
    ]);

    $body = @file_get_contents($url, false, $context);
    return $body === false ? null : $body;
}

function sk_arcgis_point_query($serviceUrl, $latitude, $longitude, $where = '1=1', $fields = '*') {
    $params = [
        'where' => $where,
        'geometry' => json_encode([
            'x' => (float)$longitude,
            'y' => (float)$latitude,
            'spatialReference' => ['wkid' => 4326]
        ], JSON_UNESCAPED_SLASHES),
        'geometryType' => 'esriGeometryPoint',
        'inSR' => '4326',
        'spatialRel' => 'esriSpatialRelIntersects',
        'outFields' => $fields,
        'returnGeometry' => 'false',
        'outSR' => '4326',
        'f' => 'json'
    ];

    $url = $serviceUrl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $raw = sk_http_get($url);

    if (!$raw) {
        return null;
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || !empty($data['error'])) {
        return null;
    }

    return $data;
}

function sk_is_santa_cruz($attrs) {
    $cityCode = trim((string)($attrs['city_code'] ?? ''));
    $cityName = strtolower(trim((string)($attrs['city_name'] ?? '')));
    $province = strtolower(trim((string)($attrs['prov_name'] ?? '')));

    return $cityCode === '0102924000' || (
        $cityName === 'santa cruz' &&
        ($province === 'ilocos sur' || $province === 'ilocos sur province')
    );
}

function sk_nominatim_location($latitude, $longitude) {
    $url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2' .
        '&lat=' . rawurlencode((string)$latitude) .
        '&lon=' . rawurlencode((string)$longitude) .
        '&zoom=18&addressdetails=1';

    $raw = sk_http_get($url, [
        'Accept: application/json',
        'Accept-Language: en'
    ]);

    if (!$raw) {
        return null;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function sk_resolve_location($latitude, $longitude) {
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        return ['success' => false, 'inside' => false, 'message' => 'Invalid GPS coordinates.'];
    }

    $lat = (float)$latitude;
    $lon = (float)$longitude;

    if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        return ['success' => false, 'inside' => false, 'message' => 'Invalid GPS coordinates.'];
    }

    $municipal = sk_arcgis_point_query(
        'https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/Municipal/MapServer/0',
        $lat,
        $lon,
        "city_code='0102924000'",
        'city_name,prov_name,city_code,psgc_10d'
    );

    $municipalFeature = $municipal['features'][0] ?? null;
    $municipalAttrs = $municipalFeature['attributes'] ?? null;
    $municipalConfirmed = is_array($municipalAttrs) && sk_is_santa_cruz($municipalAttrs);

    $barangay = sk_arcgis_point_query(
        'https://ulap-nga.georisk.gov.ph/arcgis/rest/services/PSA/BarangayPopMF/MapServer/0',
        $lat,
        $lon,
        '1=1',
        'brgy_name,brgy_code,psgc_10d,city_name,prov_name,city_code'
    );

    $barangayAttrs = null;
    foreach (($barangay['features'] ?? []) as $feature) {
        $attrs = $feature['attributes'] ?? [];
        if (sk_is_santa_cruz($attrs)) {
            $barangayAttrs = $attrs;
            break;
        }
    }

    if (!$municipalConfirmed && !$barangayAttrs) {
        $nominatim = sk_nominatim_location($lat, $lon);
        $address = $nominatim['address'] ?? [];
        $fallbackCity = strtolower(trim((string)($address['municipality'] ?? $address['town'] ?? $address['city'] ?? '')));
        $fallbackProvince = strtolower(trim((string)($address['state'] ?? $address['province'] ?? '')));

        if ($fallbackCity === 'santa cruz' && ($fallbackProvince === 'ilocos sur' || $fallbackProvince === 'ilocos sur province')) {
            $road = trim((string)($address['road'] ?? ''));
            return [
                'success' => true,
                'inside' => true,
                'barangay' => '',
                'barangay_code' => '',
                'psgc_10d' => '0102924000',
                'municipality' => 'Santa Cruz',
                'province' => 'Ilocos Sur',
                'address' => $road !== '' ? $road . ', Santa Cruz, Ilocos Sur' : 'Santa Cruz, Ilocos Sur',
                'latitude' => $lat,
                'longitude' => $lon
            ];
        }

        return [
            'success' => true,
            'inside' => false,
            'message' => 'Your GPS location is outside the SK LIGTAS service area of Santa Cruz, Ilocos Sur.'
        ];
    }

    if ($barangayAttrs) {
        $barangayName = trim((string)($barangayAttrs['brgy_name'] ?? ''));
        $municipality = trim((string)($barangayAttrs['city_name'] ?? 'Santa Cruz'));
        $province = trim((string)($barangayAttrs['prov_name'] ?? 'Ilocos Sur'));

        $nominatim = sk_nominatim_location($lat, $lon);
        $road = trim((string)($nominatim['address']['road'] ?? ''));

        $parts = [];
        if ($road !== '') {
            $parts[] = $road;
        }
        if ($barangayName !== '') {
            $parts[] = $barangayName;
        }
        $parts[] = $municipality;
        $parts[] = $province;

        return [
            'success' => true,
            'inside' => true,
            'barangay' => $barangayName,
            'barangay_code' => (string)($barangayAttrs['brgy_code'] ?? ''),
            'psgc_10d' => (string)($barangayAttrs['psgc_10d'] ?? ''),
            'municipality' => $municipality,
            'province' => $province,
            'address' => implode(', ', $parts),
            'latitude' => $lat,
            'longitude' => $lon
        ];
    }

    return [
        'success' => true,
        'inside' => true,
        'barangay' => '',
        'barangay_code' => '',
        'psgc_10d' => '0102924000',
        'municipality' => 'Santa Cruz',
        'province' => 'Ilocos Sur',
        'address' => 'Santa Cruz, Ilocos Sur',
        'latitude' => $lat,
        'longitude' => $lon,
        'message' => 'Santa Cruz was verified, but the exact barangay could not be determined.'
    ];
}
