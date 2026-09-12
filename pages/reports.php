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
        ? (float)$latitude : null;
    $longitude = is_numeric($longitude) && $longitude >= -180 && $longitude <= 180
        ? (float)$longitude : null;

    if ($category === '' || $phone === '' || $description === '') {
        $error = 'Please complete the required fields.';
    } elseif ($latitude !== null && $longitude !== null) {
        require_once __DIR__ . '/../api/location_service.php';
        $verify = sk_resolve_location($latitude, $longitude);

        if (!is_array($verify) || empty($verify['success']) || empty($verify['inside'])) {
            $error = $verify['message'] ?? 'The GPS location could not be verified as inside Santa Cruz, Ilocos Sur.';
        } else {
            $location = trim((string)($verify['address'] ?? $location));
        }
    }

    // Save the report only after validation and, when supplied, GPS verification.
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

    background: #ffffff;

    border-bottom: 1px solid var(--sk-border);

    box-shadow:
        0 3px 14px rgba(10,35,70,.08);
}

.report-header-inner {
    width: 92%;
    max-width: 1100px;

    min-height: 70px;

    margin: auto;

    display: flex;
    align-items: center;

    justify-content: space-between;

    gap: 15px;
}

.report-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-logo {
    width: 48px;
    height: 48px;

    display: grid;
    place-items: center;

    overflow: hidden;

    border-radius: 13px;

    background: #ffffff;

    box-shadow:
        0 3px 10px rgba(10,35,70,.12);
}

.report-logo img {
    width: 100%;
    height: 100%;

    object-fit: contain;

    display: block;
}

.report-brand-text {
    display: flex;
    flex-direction: column;
}

.report-brand-text strong {
    color: var(--sk-dark-blue);

    font-size: 19px;

    line-height: 1.1;

    font-weight: 900;
}

.report-brand-text span {
    margin-top: 3px;

    color: #657185;

    font-size: 11px;
}

.report-brand-text small {
    margin-top: 3px;

    color: #8993a3;

    font-size: 10px;
}

.report-back {
    margin-left: auto;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 9px 14px;

    background: var(--sk-light-blue);
    color: var(--sk-blue);

    border: 1px solid #cbdcf5;
    border-radius: 9px;

    text-decoration: none;

    font-size: 12px;
    font-weight: 800;

    transition: .2s ease;
}

.report-back:hover {
    background: var(--sk-blue);
    color: #ffffff;
}

.report-main {
    width: min(900px, 92%);

    margin: 30px auto 50px;
}

.report-page-card {
    background: #ffffff;

    border: 1px solid var(--sk-border);

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 7px 24px rgba(19,50,90,.08);
}

.report-card-top {
    position: relative;

    padding: 28px 30px;

    background:
        linear-gradient(
            135deg,
            var(--sk-dark-blue),
            var(--sk-blue)
        );

    color: #ffffff;

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

    color: #ffffff;

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

.report-form label {
    display: block;

    margin: 17px 0 7px;

    color: #12315d;

    font-size: 12px;

    font-weight: 800;
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
    background: #ffffff;

    border-color: var(--sk-blue);

    box-shadow:
        0 0 0 3px rgba(7,84,199,.10);
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
}

.gps-button {
    padding: 0 16px;

    border: none;

    border-radius: 10px;

    background: var(--sk-blue);

    color: #ffffff;

    font-size: 12px;

    font-weight: 800;

    cursor: pointer;

    white-space: nowrap;

    transition: .2s;
}

.gps-button:hover {
    background: var(--sk-dark-blue);
}

.submit-report {
    width: 100%;

    margin-top: 22px;

    padding: 14px 18px;

    background: var(--sk-red);

    color: #ffffff;

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

    background:
        linear-gradient(
            135deg,
            var(--sk-dark-blue),
            var(--sk-blue)
        );

    border-top: 4px solid var(--sk-yellow);

    border-radius: 16px;

    color: #ffffff;

    box-shadow:
        0 7px 20px rgba(19,50,90,.10);
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

    color: #ffffff;

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

    .report-header-inner {
        width: 94%;
    }

    .report-brand-text span {
        display: none;
    }

    .report-brand-text strong {
        font-size: 17px;
    }

    .report-brand-text small {
        font-size: 9px;
    }

    .report-logo {
        width: 43px;
        height: 43px;
    }

    .report-back {
        padding: 8px 11px;
        font-size: 11px;
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

    .location-row {
        flex-direction: column;
    }

    .gps-button {
        padding: 11px;
    }

    .call-box {
        flex-direction: column;

        align-items: stretch;
    }

    .call-box a {
        width: 100%;
    }
}

.report-header{background:linear-gradient(110deg,#062b69 0 70%,#0b4fae 70% 100%)!important;color:#fff!important;border-bottom:4px solid #f4c21f!important;min-height:72px;padding:0 28px!important}
.report-header .brand{display:flex;align-items:center;gap:12px}
.report-header .brand strong,.report-header .brand span{color:#fff!important}
.report-header .back-link{color:#fff!important;width:38px;height:38px;border:1px solid rgba(255,255,255,.35);border-radius:10px;display:grid;place-items:center;background:rgba(255,255,255,.08);margin-right:4px}
.report-header .shield{background:#fff!important;border-radius:10px!important}
.report-header .report-back{margin-left:0!important;padding:0!important;background:rgba(255,255,255,.08)!important;border:1px solid rgba(255,255,255,.35)!important}
.report-header .report-back:hover{background:rgba(255,255,255,.2)!important}
</style>

</head>

<body>

<header class="header report-header">
    <div class="brand">
        <a href="../index.php" class="back-link report-back" aria-label="Back to home">←</a>
        <div class="shield"><img src="../asset/sk-logo.png" alt="SK LIGTAS Logo"></div>
        <div>
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

                <label>
                    Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Your name (optional)"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                >

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

                <label>
                    Location
                </label>

                <div class="location-row">

                    <input
                        type="text"
                        name="location"
                        id="reportLocation"
                        placeholder="Enter location"
                        value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                    >

                    <button
                        type="button"
                        class="gps-button"
                        onclick="getLocation()"
                    >
                        📍 GPS
                    </button>

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
async function lookupReportLocation(latitude,longitude){
 const response=await fetch('../api/location.php?lat='+encodeURIComponent(latitude)+'&lon='+encodeURIComponent(longitude),{headers:{'Accept':'application/json'},cache:'no-store'});
 if(!response.ok) throw new Error('Location verification failed');
 const data=await response.json();
 if(!data.inside_service_area) throw new Error(data.message||'Your GPS location is outside the SK LIGTAS service area.');
 return data;
}

function getLocation(){
 if(!navigator.geolocation){alert('GPS is not supported by this browser.');return;}
 const input=document.getElementById('reportLocation');
 const latitudeInput=document.getElementById('latitude');
 const longitudeInput=document.getElementById('longitude');
 if(input) input.value='Getting your most accurate GPS location...';
 if(latitudeInput) latitudeInput.value='';
 if(longitudeInput) longitudeInput.value='';

 let bestPosition=null;
 let watchId=null;
 let finished=false;
 let timer=null;
 let samples=0;

 const finish=async()=>{
   if(finished) return;
   finished=true;
   if(watchId!==null) navigator.geolocation.clearWatch(watchId);
   if(timer) clearTimeout(timer);
   if(!bestPosition){
     if(input) input.value='';
     alert('Unable to get an accurate GPS location. Please move near a window or outdoors and try again.');
     return;
   }

   const latitude=bestPosition.coords.latitude;
   const longitude=bestPosition.coords.longitude;
   const accuracy=Number(bestPosition.coords.accuracy||0);
   if(latitudeInput) latitudeInput.value=latitude;
   if(longitudeInput) longitudeInput.value=longitude;

   try{
     const data=await lookupReportLocation(latitude,longitude);
     if(input) input.value=data.address+(accuracy>0?' (GPS ±'+Math.round(accuracy)+' m)':'');
     alert('Exact GPS location found:\n'+data.address+'\nGPS accuracy: ±'+(accuracy>0?Math.round(accuracy)+' m':'unknown'));
   }catch(error){
     if(input) input.value='';
     alert(error.message||'The exact barangay could not be verified.');
   }
 };

 const consider=(position)=>{
   samples++;
   const accuracy=Number(position.coords.accuracy||Infinity);
   const current=bestPosition?Number(bestPosition.coords.accuracy||Infinity):Infinity;
   if(!bestPosition || accuracy<current) bestPosition=position;
   if(accuracy<=15 || samples>=5) finish();
 };

 watchId=navigator.geolocation.watchPosition(consider,()=>{},{
   enableHighAccuracy:true,
   timeout:15000,
   maximumAge:0
 });

 timer=setTimeout(finish,12000);
}

window.addEventListener('DOMContentLoaded',function(){
 const address=sessionStorage.getItem('sk_ligtas_address');
 const latitude=sessionStorage.getItem('sk_ligtas_latitude');
 const longitude=sessionStorage.getItem('sk_ligtas_longitude');
 if(address||latitude||longitude){
   const locationInput=document.getElementById('reportLocation');
   const latitudeInput=document.getElementById('latitude');
   const longitudeInput=document.getElementById('longitude');
   if(locationInput) locationInput.value=address||('GPS: '+Number(latitude).toFixed(6)+', '+Number(longitude).toFixed(6));
   if(latitudeInput) latitudeInput.value=latitude||'';
   if(longitudeInput) longitudeInput.value=longitude||'';
   sessionStorage.removeItem('sk_ligtas_address');
   sessionStorage.removeItem('sk_ligtas_latitude');
   sessionStorage.removeItem('sk_ligtas_longitude');
   sessionStorage.removeItem('sk_ligtas_barangay');
 }
});
</script>

</body>

</html>