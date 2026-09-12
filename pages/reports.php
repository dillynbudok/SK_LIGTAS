<?php
require_once __DIR__ . '/../api/config.php';

$sent = isset($_GET['sent']);
$error = '';
$selectedCategory = trim($_GET['category'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['contact'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    $latitude = is_numeric($latitude) && $latitude >= -90 && $latitude <= 90
        ? (float)$latitude
        : null;

    $longitude = is_numeric($longitude) && $longitude >= -180 && $longitude <= 180
        ? (float)$longitude
        : null;

    if ($category === '' || $phone === '' || $description === '') {
        $error = 'Please complete the required fields.';
    } elseif ($latitude !== null && $longitude !== null) {
        require_once __DIR__ . '/../api/location_service.php';

        $verify = sk_resolve_location($latitude, $longitude);

        if (!is_array($verify) || empty($verify['success']) || empty($verify['inside'])) {
            $error = $verify['message']
                ?? 'The GPS location could not be verified as inside Santa Cruz, Ilocos Sur.';
        } else {
            $location = trim((string)($verify['address'] ?? $location));
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            'INSERT INTO reports
            (category,name,contact,location,latitude,longitude,description)
            VALUES (?,?,?,?,?,?,?)'
        );

        if (!$stmt) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param(
                'ssssdds',
                $category,
                $name,
                $phone,
                $location,
                $latitude,
                $longitude,
                $description
            );

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: reports.php?sent=1');
                exit;
            }

            $error = 'Unable to save the report: ' . $stmt->error;
            $stmt->close();
        }
    }

    $selectedCategory = $category;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Report Emergency - SK LIGTAS</title>

<link
    rel="stylesheet"
    href="../assets/style.css"
>

<style>

:root {
    --sk-blue: #0754c7;
    --sk-dark-blue: #073b91;
    --sk-light-blue: #eaf2ff;
    --sk-red: #ed2638;
    --sk-yellow: #f4c21f;
    --sk-bg: #f4f7fb;
    --sk-text: #152238;
    --sk-border: #dce5ef;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: var(--sk-bg);
    color: var(--sk-text);
    font-family: Arial, Helvetica, sans-serif;
}

.report-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
    background: linear-gradient(
        110deg,
        #062b69 0 70%,
        #0b4fae 70% 100%
    ) !important;
    color: #fff !important;
    border-bottom: 4px solid #f4c21f !important;
    min-height: 72px;
    padding: 0 28px !important;
}

.report-header .brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-header .brand strong,
.report-header .brand span {
    color: #fff !important;
}

.report-header .back-link {
    color: #fff !important;
    width: 38px;
    height: 38px;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 10px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.08);
    margin-right: 4px;
    text-decoration: none;
}

.report-header .shield {
    width: 46px;
    height: 46px;
    min-width: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff !important;
    border-radius: 10px !important;
    overflow: hidden;
}

.report-header .shield img {
    display: block;
    width: 38px;
    height: 38px;
    max-width: 38px;
    max-height: 38px;
    object-fit: contain;
}

.report-header .report-back {
    margin-left: 0 !important;
    padding: 0 !important;
    background: rgba(255,255,255,.08) !important;
    border: 1px solid rgba(255,255,255,.35) !important;
}

.report-header .report-back:hover {
    background: rgba(255,255,255,.2) !important;
}

.report-main {
    width: min(900px, 92%);
    margin: 30px auto 50px;
}

.report-page-card {
    background: #fff;
    border: 1px solid var(--sk-border);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 7px 24px rgba(19,50,90,.08);
}

.report-card-top {
    position: relative;
    padding: 28px 30px;
    background: linear-gradient(
        135deg,
        var(--sk-dark-blue),
        var(--sk-blue)
    );
    color: #fff;
    border-bottom: 4px solid var(--sk-yellow);
    overflow: hidden;
}

.report-card-top::after {
    content: "";
    position: absolute;
    right: -80px;
    bottom: -100px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    border: 45px solid rgba(255,255,255,.06);
}

.report-icon {
    position: relative;
    z-index: 2;
    width: 52px;
    height: 52px;
    display: grid;
    place-items: center;
    margin-bottom: 13px;
    border-radius: 14px;
    background: var(--sk-red);
    border: 3px solid var(--sk-yellow);
    font-size: 24px;
}

.report-card-top h1 {
    position: relative;
    z-index: 2;
    margin: 0;
    color: #fff;
    font-size: 30px;
    font-weight: 900;
}

.report-card-top p {
    position: relative;
    z-index: 2;
    margin: 7px 0 0;
    color: rgba(255,255,255,.9);
    font-size: 13px;
    line-height: 1.5;
}

.report-form {
    padding: 28px 30px;
}

.report-success {
    padding: 13px 15px;
    margin-bottom: 18px;
    background: #eaf8ef;
    border: 1px solid #c9ead6;
    border-left: 5px solid #19a366;
    border-radius: 10px;
    color: #176b35;
    font-size: 13px;
    font-weight: 700;
}

.report-error {
    padding: 13px 15px;
    margin-bottom: 18px;
    background: #ffe9ec;
    border: 1px solid #ffd0d5;
    border-left: 5px solid var(--sk-red);
    border-radius: 10px;
    color: #b42331;
    font-size: 13px;
    font-weight: 700;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    min-width: 0;
}

.report-form label {
    display: block;
    margin: 17px 0 7px;
    color: #12315d;
    font-size: 12px;
    font-weight: 800;
}

.form-row .form-group label {
    margin-top: 0;
}

.report-form input,
.report-form select,
.report-form textarea {
    width: 100%;
    padding: 12px 13px;
    background: #fbfcfe;
    border: 1px solid #d5dfeb;
    border-radius: 10px;
    color: var(--sk-text);
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition: .2s;
}

.report-form input:focus,
.report-form select:focus,
.report-form textarea:focus {
    background: #fff;
    border-color: var(--sk-blue);
    box-shadow: 0 0 0 3px rgba(7,84,199,.10);
}

.report-form textarea {
    min-height: 140px;
    resize: vertical;
}

.location-row {
    display: flex;
    gap: 9px;
}

.location-row input {
    flex: 1;
    min-width: 0;
}

.gps-button {
    min-height: 46px;
    padding: 0 18px;
    border: none;
    border-radius: 10px;
    background: var(--sk-blue);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
    transition: .2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.gps-button:hover {
    background: var(--sk-dark-blue);
}

.gps-button:disabled {
    opacity: .7;
    cursor: wait;
}

.gps-status {
    margin-top: 7px;
    color: #667085;
    font-size: 11px;
}

.submit-report {
    width: 100%;
    margin-top: 22px;
    padding: 14px 18px;
    background: var(--sk-red);
    color: #fff;
    border: 3px solid var(--sk-yellow);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    transition: .2s;
}

.submit-report:hover {
    background: #c91420;
    transform: translateY(-2px);
}

.call-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 16px;
    padding: 18px 20px;
    background: linear-gradient(
        135deg,
        var(--sk-dark-blue),
        var(--sk-blue)
    );
    border-top: 4px solid var(--sk-yellow);
    border-radius: 16px;
    color: #fff;
    box-shadow: 0 7px 20px rgba(19,50,90,.10);
}

.call-box b {
    display: block;
    font-size: 14px;
}

.call-box small {
    display: block;
    margin-top: 4px;
    color: rgba(255,255,255,.8);
    font-size: 10px;
}

.call-box a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 17px;
    background: var(--sk-red);
    color: #fff;
    border: 2px solid var(--sk-yellow);
    border-radius: 10px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 900;
    white-space: nowrap;
}

.call-box a:hover {
    background: #c91420;
}

@media (max-width: 600px) {

    .report-header {
        padding: 0 16px !important;
    }

    .report-header .shield {
        width: 42px;
        height: 42px;
        min-width: 42px;
    }

    .report-header .shield img {
        width: 34px;
        height: 34px;
        max-width: 34px;
        max-height: 34px;
    }

    .report-brand-text span {
        display: none;
    }

    .report-main {
        width: 94%;
        margin-top: 20px;
    }

    .report-card-top {
        padding: 23px 20px;
    }

    .report-card-top h1 {
        font-size: 25px;
    }

    .report-form {
        padding: 20px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .location-row {
        flex-direction: column;
        gap: 10px;
    }

    .gps-button {
        width: 100%;
        min-height: 46px;
        padding: 12px 16px;
        font-size: 13px;
    }

    .call-box {
        flex-direction: column;
        align-items: stretch;
    }

    .call-box a {
        width: 100%;
    }
}

</style>

</head>

<body>

<header class="header report-header">

    <div class="brand">

        <a
            href="../index.php"
            class="back-link report-back"
            aria-label="Back to home"
        >
            ←
        </a>

        <div class="shield">

            <img
                src="../asset/sk-logo.png"
                alt="SK LIGTAS Logo"
            >

        </div>

        <div class="report-brand-text">

            <strong>SK LIGTAS</strong>

            <span>Report Emergency</span>

        </div>

    </div>

</header>

<main class="report-main">

<section class="report-page-card">

<div class="report-card-top">

    <div class="report-icon">
        🚨
    </div>

    <h1>
        Report an Emergency
    </h1>

    <p>
        Send your emergency details to authorized responders.
    </p>

</div>

<div class="report-form">

<?php if ($sent): ?>

<div class="report-success">
    ✓ Report submitted successfully.
</div>

<?php endif; ?>

<?php if ($error): ?>

<div class="report-error">
    ⚠ <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form
    method="POST"
    action="reports.php"
>

<div class="form-row">

    <div class="form-group">

        <label>
            Emergency Category *
        </label>

        <select
            name="category"
            required
        >

            <option value="">
                Select category
            </option>

            <option
                value="Medical Emergency"
                <?= $selectedCategory === 'Medical Emergency' ? 'selected' : '' ?>
            >
                Medical Emergency
            </option>

            <option
                value="Fire"
                <?= $selectedCategory === 'Fire' ? 'selected' : '' ?>
            >
                Fire
            </option>

            <option
                value="Police"
                <?= $selectedCategory === 'Police' ? 'selected' : '' ?>
            >
                Police
            </option>

            <option
                value="Rescue"
                <?= $selectedCategory === 'Rescue' ? 'selected' : '' ?>
            >
                Rescue
            </option>

            <option
                value="Accident"
                <?= $selectedCategory === 'Accident' ? 'selected' : '' ?>
            >
                Accident
            </option>

            <option
                value="Flood / Disaster"
                <?= $selectedCategory === 'Flood / Disaster' ? 'selected' : '' ?>
            >
                Flood / Disaster
            </option>

            <option
                value="Other Emergency"
                <?= $selectedCategory === 'Other Emergency' ? 'selected' : '' ?>
            >
                Other Emergency
            </option>

            <option
                value="Community Concern"
                <?= $selectedCategory === 'Community Concern' ? 'selected' : '' ?>
            >
                Community Concern
            </option>

        </select>

    </div>

    <div class="form-group">

        <label>
            Name
        </label>

        <input
            type="text"
            name="name"
            placeholder="Your name (optional)"
            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
        >

    </div>

</div>

<div class="form-row">

    <div class="form-group">

        <label>
            Contact Number *
        </label>

        <input
            type="tel"
            name="contact"
            placeholder="09XXXXXXXXX"
            required
            value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>"
        >

    </div>

    <div class="form-group">

        <label>
            Location
        </label>

        <div class="location-row">

            <input
                type="text"
                name="location"
                id="reportLocation"
                placeholder="Street / House Number, Barangay, Santa Cruz, Ilocos Sur"
                value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
            >

            <button
                type="button"
                class="gps-button"
                id="gpsButton"
                onclick="getLocation()"
            >
                📍 GPS
            </button>

        </div>

        <div
            class="gps-status"
            id="gpsStatus"
        ></div>

    </div>

</div>

<input
    type="hidden"
    name="latitude"
    id="latitude"
    value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>"
>

<input
    type="hidden"
    name="longitude"
    id="longitude"
    value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>"
>

<label>
    Description *
</label>

<textarea
    name="description"
    placeholder="Describe what happened..."
    required
><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

<button
    type="submit"
    class="submit-report"
>
    SUBMIT EMERGENCY REPORT
</button>

</form>

</div>

</section>

<div class="call-box">

<div>

    <b>
        Immediate danger?
    </b>

    <small>
        For life-threatening emergencies, call 911.
    </small>

</div>

<a href="tel:911">
    ☎ Call 911
</a>

</div>

</main>

<script>

function reverseGeocode(latitude, longitude) {

    const controller =
        new AbortController();

    const timer =
        setTimeout(function() {
            controller.abort();
        }, 10000);

    const url =
        '../api/location.php?lat=' +
        encodeURIComponent(latitude) +
        '&lon=' +
        encodeURIComponent(longitude);

    return fetch(
        url,
        {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store',
            signal: controller.signal
        }
    )
    .then(function(response) {

        if (!response.ok) {
            throw new Error(
                'Location verification failed.'
            );
        }

        return response.json();

    })
    .finally(function() {

        clearTimeout(timer);

    });
}

function resetGpsButton() {

    const button =
        document.getElementById('gpsButton');

    if (button) {

        button.disabled = false;

        button.textContent =
            '📍 GPS';
    }
}

function getLocation() {

    if (!navigator.geolocation) {

        alert(
            'GPS is not supported by this browser.'
        );

        return;
    }

    const input =
        document.getElementById('reportLocation');

    const latitudeInput =
        document.getElementById('latitude');

    const longitudeInput =
        document.getElementById('longitude');

    const gpsButton =
        document.getElementById('gpsButton');

    const gpsStatus =
        document.getElementById('gpsStatus');

    if (gpsButton) {

        gpsButton.disabled = true;

        gpsButton.textContent =
            '📍 Locating...';
    }

    if (gpsStatus) {

        gpsStatus.textContent =
            'Getting your current GPS location...';
    }

    if (input) {

        input.value =
            'Getting GPS location...';
    }

    if (latitudeInput) {
        latitudeInput.value = '';
    }

    if (longitudeInput) {
        longitudeInput.value = '';
    }

    navigator.geolocation.getCurrentPosition(

        function(position) {

            const latitude =
                Number(position.coords.latitude);

            const longitude =
                Number(position.coords.longitude);

            const accuracy =
                Number(position.coords.accuracy || 0);

            if (latitudeInput) {
                latitudeInput.value =
                    latitude;
            }

            if (longitudeInput) {
                longitudeInput.value =
                    longitude;
            }

            if (input) {

                input.value =
                    'Finding actual address...';
            }

            if (gpsStatus) {

                gpsStatus.textContent =
                    'GPS detected. Finding street and barangay...';
            }

            reverseGeocode(
                latitude,
                longitude
            )
            .then(function(data) {

                if (
                    data.inside_service_area === false ||
                    (
                        data.inside === false &&
                        data.inside_service_area !== true
                    )
                ) {

                    throw new Error(
                        data.message ||
                        'Your GPS location is outside the SK LIGTAS service area.'
                    );
                }

                let address = '';

                if (
                    data.address &&
                    String(data.address).trim() !== ''
                ) {

                    address =
                        String(data.address).trim();

                } else {

                    const parts = [];

                    let street = '';

                    if (
                        data.house_number &&
                        data.road
                    ) {

                        street =
                            String(data.house_number).trim() +
                            ' ' +
                            String(data.road).trim();

                    } else if (data.road) {

                        street =
                            String(data.road).trim();

                    } else if (
                        data.street
                    ) {

                        street =
                            String(data.street).trim();
                    }

                    if (street) {
                        parts.push(street);
                    }

                    if (data.barangay) {
                        parts.push(
                            String(data.barangay).trim()
                        );
                    }

                    parts.push('Santa Cruz');
                    parts.push('Ilocos Sur');

                    address =
                        parts
                            .filter(Boolean)
                            .join(', ');
                }

                if (!address) {

                    throw new Error(
                        'The actual street address could not be determined.'
                    );
                }

                if (input) {

                    input.value =
                        address;
                }

                if (gpsStatus) {

                    gpsStatus.textContent =
                        accuracy > 0
                            ? 'GPS location verified. GPS accuracy: approximately ±' +
                              Math.round(accuracy) +
                              ' meters.'
                            : 'GPS location verified successfully.';
                }

                resetGpsButton();

            })
            .catch(function(error) {

                if (input) {
                    input.value = '';
                }

                if (gpsStatus) {

                    gpsStatus.textContent =
                        error.message ||
                        'Unable to determine your address.';
                }

                resetGpsButton();

            });

        },

        function(error) {

            let message =
                'Unable to get your GPS location.';

            if (error.code === 1) {

                message =
                    'Location permission was denied. Please allow location access.';

            } else if (error.code === 2) {

                message =
                    'Your location could not be determined.';

            } else if (error.code === 3) {

                message =
                    'GPS request timed out. Please try again.';
            }

            const gpsStatus =
                document.getElementById('gpsStatus');

            if (gpsStatus) {
                gpsStatus.textContent =
                    message;
            }

            resetGpsButton();

        },

        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

window.addEventListener(
    'DOMContentLoaded',
    function() {

        const address =
            sessionStorage.getItem(
                'sk_ligtas_address'
            );

        const latitude =
            sessionStorage.getItem(
                'sk_ligtas_latitude'
            );

        const longitude =
            sessionStorage.getItem(
                'sk_ligtas_longitude'
            );

        if (
            address ||
            latitude ||
            longitude
        ) {

            const locationInput =
                document.getElementById(
                    'reportLocation'
                );

            const latitudeInput =
                document.getElementById(
                    'latitude'
                );

            const longitudeInput =
                document.getElementById(
                    'longitude'
                );

            if (locationInput && address) {

                locationInput.value =
                    address;
            }

            if (latitudeInput) {

                latitudeInput.value =
                    latitude || '';
            }

            if (longitudeInput) {

                longitudeInput.value =
                    longitude || '';
            }

            sessionStorage.removeItem(
                'sk_ligtas_address'
            );

            sessionStorage.removeItem(
                'sk_ligtas_latitude'
            );

            sessionStorage.removeItem(
                'sk_ligtas_longitude'
            );

            sessionStorage.removeItem(
                'sk_ligtas_barangay'
            );
        }
    }
);

</script>

</body>
</html>