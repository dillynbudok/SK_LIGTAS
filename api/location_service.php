<?php

if (!function_exists('sk_http_get')) {

    function sk_http_get($url, $timeout = 15)
    {
        if (function_exists('curl_init')) {

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => 'SK-LIGTAS/1.0 emergency-location-service',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                return $response;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'header' =>
                    "User-Agent: SK-LIGTAS/1.0 emergency-location-service\r\n" .
                    "Accept: application/json\r\n"
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        return $response !== false ? $response : null;
    }
}

if (!function_exists('sk_clean_location_name')) {

    function sk_clean_location_name($value)
    {
        $value = trim((string)$value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}

if (!function_exists('sk_reverse_nominatim')) {

    function sk_reverse_nominatim($latitude, $longitude)
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?' .
            http_build_query([
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'jsonv2',
                'zoom' => 18,
                'addressdetails' => 1,
                'layer' => 'address',
                'accept-language' => 'en'
            ]);

        $response = sk_http_get($url, 15);

        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return null;
        }

        return $data;
    }
}

if (!function_exists('sk_get_barangay')) {

    function sk_get_barangay($address)
    {
        $possible = [
            $address['barangay'] ?? '',
            $address['suburb'] ?? '',
            $address['neighbourhood'] ?? '',
            $address['quarter'] ?? '',
            $address['village'] ?? ''
        ];

        foreach ($possible as $value) {

            $value = sk_clean_location_name($value);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('sk_get_municipality')) {

    function sk_get_municipality($address)
    {
        $possible = [
            $address['municipality'] ?? '',
            $address['town'] ?? '',
            $address['city'] ?? '',
            $address['city_district'] ?? ''
        ];

        foreach ($possible as $value) {

            $value = sk_clean_location_name($value);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('sk_get_province')) {

    function sk_get_province($address)
    {
        $possible = [
            $address['province'] ?? '',
            $address['state'] ?? ''
        ];

        foreach ($possible as $value) {

            $value = sk_clean_location_name($value);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('sk_is_santa_cruz_ilocos_sur')) {

    function sk_is_santa_cruz_ilocos_sur($address, $displayName = '')
    {
        $values = [
            $address['municipality'] ?? '',
            $address['town'] ?? '',
            $address['city'] ?? '',
            $address['county'] ?? '',
            $address['state_district'] ?? '',
            $address['province'] ?? '',
            $address['state'] ?? '',
            $displayName
        ];

        $text = strtolower(
            sk_clean_location_name(
                implode(' ', $values)
            )
        );

        $hasSantaCruz =
            strpos($text, 'santa cruz') !== false;

        $hasIlocosSur =
            strpos($text, 'ilocos sur') !== false;

        return $hasSantaCruz && $hasIlocosSur;
    }
}

if (!function_exists('sk_build_exact_address')) {

    function sk_build_exact_address($address)
    {
        $houseNumber = sk_clean_location_name(
            $address['house_number'] ?? ''
        );

        $road = sk_clean_location_name(
            $address['road']
            ?? $address['street']
            ?? ''
        );

        $barangay = sk_get_barangay($address);

        if ($houseNumber !== '' && $road !== '') {

            $streetAddress =
                $houseNumber . ' ' . $road;

        } elseif ($road !== '') {

            $streetAddress = $road;

        } elseif ($houseNumber !== '') {

            $streetAddress = $houseNumber;

        } else {

            $streetAddress = '';
        }

        $parts = [];

        if ($streetAddress !== '') {
            $parts[] = $streetAddress;
        }

        if ($barangay !== '') {
            $parts[] = $barangay;
        }

        $parts[] = 'Santa Cruz';
        $parts[] = 'Ilocos Sur';

        return [
            'address' => implode(', ', $parts),
            'house_number' => $houseNumber,
            'road' => $road,
            'barangay' => $barangay
        ];
    }
}

if (!function_exists('sk_resolve_location')) {

    function sk_resolve_location($latitude, $longitude)
    {
        $latitude = (float)$latitude;
        $longitude = (float)$longitude;

        if (
            $latitude < -90 ||
            $latitude > 90 ||
            $longitude < -180 ||
            $longitude > 180
        ) {
            return [
                'success' => false,
                'inside' => false,
                'address' => '',
                'message' => 'Invalid GPS coordinates.'
            ];
        }

        $reverse = sk_reverse_nominatim(
            $latitude,
            $longitude
        );

        if (!is_array($reverse)) {

            return [
                'success' => false,
                'inside' => false,
                'address' => '',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'message' =>
                    'Unable to contact the address service. Please try GPS again.'
            ];
        }

        $addressData = $reverse['address'] ?? [];

        if (!is_array($addressData)) {
            $addressData = [];
        }

        $displayName = sk_clean_location_name(
            $reverse['display_name'] ?? ''
        );

        $municipality = sk_get_municipality(
            $addressData
        );

        $province = sk_get_province(
            $addressData
        );

        $barangay = sk_get_barangay(
            $addressData
        );

        $inside = false;

        if (
            stripos($displayName, 'Santa Cruz') !== false &&
            stripos($displayName, 'Ilocos Sur') !== false
        ) {
            $inside = true;
        }

        if (
            stripos($municipality, 'Santa Cruz') !== false &&
            stripos($province, 'Ilocos Sur') !== false
        ) {
            $inside = true;
        }

        if (!$inside) {

            $combined = strtolower(
                sk_clean_location_name(
                    json_encode(
                        $reverse,
                        JSON_UNESCAPED_UNICODE
                    )
                )
            );

            if (
                strpos($combined, 'santa cruz') !== false &&
                strpos($combined, 'ilocos sur') !== false
            ) {
                $inside = true;
            }
        }

        if (!$inside) {

            return [
                'success' => true,
                'inside' => false,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $displayName,
                'barangay' => $barangay,
                'municipality' => $municipality,
                'province' => $province,
                'message' =>
                    'The GPS location is outside Santa Cruz, Ilocos Sur.'
            ];
        }

        $exact = sk_build_exact_address(
            $addressData
        );

        $finalAddress = $exact['address'];

        if ($exact['road'] === '' && $exact['house_number'] === '') {

            return [
                'success' => false,
                'inside' => true,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => '',
                'barangay' => $barangay,
                'road' => '',
                'house_number' => '',
                'municipality' => 'Santa Cruz',
                'province' => 'Ilocos Sur',
                'message' =>
                    'Your location is inside Santa Cruz, but the exact street or house number could not be determined. Please enter your street or house number manually.'
            ];
        }

        return [
            'success' => true,
            'inside' => true,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $finalAddress,
            'barangay' => $exact['barangay'],
            'road' => $exact['road'],
            'house_number' => $exact['house_number'],
            'municipality' => 'Santa Cruz',
            'province' => 'Ilocos Sur',
            'message' =>
                'Location verified inside Santa Cruz, Ilocos Sur.'
        ];
    }
}