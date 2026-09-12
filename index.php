<?php
require_once "api/config.php";

$quickImageDir = __DIR__ . "/uploads/buttons/";
$quickImageUrl = "uploads/buttons/";
$quickButtons = [
    "hospitals" => ["Nearest Hospitals", "Find Medical Help", "green", "pages/hospitals.php", "fa-solid fa-hospital"],
    "incident" => ["Incident Map", "View Advisories", "orange", "pages/alerts.php", "fa-solid fa-map"],
    "evacuation" => ["Evacuation Centers", "Safe Location", "pink", "pages/evacuation.php", "fa-solid fa-house"],
    "request" => ["Request Help", "Report an Emergency", "red", "pages/reports.php", "fa-solid fa-phone"],
    "firstaid" => ["First Aid", "Emergency Guide", "yellow", "pages/firstaid.php", "fa-solid fa-kit-medical"],
    "alerts" => ["Disaster Alerts", "Latest Advisors", "cyan", "pages/alerts.php", "fa-solid fa-triangle-exclamation"],
    "contacts" => ["Emergency Contacts", "Hotlines & Numbers", "purple", "pages/emergency.php", "fa-solid fa-phone"]
];

foreach ($quickButtons as $key => &$button) {
    $button["image"] = null;
    foreach (["jpg","jpeg","png","webp"] as $ext) {
        $candidate = $quickImageDir . $key . "." . $ext;
        if (is_file($candidate)) {
            $button["image"] = $quickImageUrl . $key . "." . $ext;
            break;
        }
    }
}
unset($button);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK LIGTAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<header class="top-header">
    <div class="brand">
        <div class="logo"><img src="assets/sk-official-logo.png" alt="SK LIGTAS Logo"></div>
        <div>
            <h1>SK LIGTAS</h1>
            <p>Emergency & Community Assistance Portal</p>
            <small>Poblacion Este, Sta. Cruz, Ilocos Sur</small>
        </div>
    </div>

    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="pages/emergency.php">Hotlines</a>
        <a href="pages/alerts.php">Alerts</a>
        <a href="pages/hospitals.php">Hospitals</a>
        <a href="pages/evacuation.php">Evacuation</a>
        <a href="pages/reports.php">Reports</a>
        <a href="pages/firstaid.php">First Aid</a>
    </nav>

    <a href="pages/reports.php" class="location-btn" id="myLocationButton" onclick="return goToMyLocation(event)">
        <i class="fa-solid fa-location-dot"></i> My Location
    </a>
</header>

<main>

<section class="hero">
    <div class="hero-content">
        <h2>Isang Tap. Isang Tawag.</h2>
        <h3>Agarang Tulong.</h3>
        <p>SK LIGTAS – Your Digital Partner for a Safer Sta. Cruz</p>
    </div>

    <a href="tel:911" class="sos-call">
        <i class="fa-solid fa-phone"></i>
        EMERGENCY – CALL 911
    </a>
</section>

<section class="quick-grid">
<?php foreach ($quickButtons as $button): ?>
    <a href="<?= htmlspecialchars($button[3], ENT_QUOTES, "UTF-8") ?>" class="quick-card <?= htmlspecialchars($button[2], ENT_QUOTES, "UTF-8") ?>">
        <?php if ($button["image"]): ?>
            <span class="quick-card-image-wrap"><img class="quick-card-image" src="<?= htmlspecialchars($button["image"], ENT_QUOTES, "UTF-8") ?>" alt=""></span>
        <?php else: ?>
            <span class="quick-card-icon"><i class="<?= htmlspecialchars($button[4], ENT_QUOTES, "UTF-8") ?>"></i></span>
        <?php endif; ?>
        <strong><?= htmlspecialchars($button[0], ENT_QUOTES, "UTF-8") ?></strong>
        <span><?= htmlspecialchars($button[1], ENT_QUOTES, "UTF-8") ?></span>
    </a>
<?php endforeach; ?>
</section>

<section class="dashboard-grid">

    <div class="panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-phone"></i> Emergency Contacts</span>
            <a href="pages/emergency.php">View All</a>
        </div>

        <div class="tabs">
            <span class="selected">Barangay</span>
            <span>Municipal</span>
            <span>Provincial</span>
            <span>National</span>
        </div>

        <h3>Barangay Poblacion Este</h3>
        <p class="location">
            <i class="fa-solid fa-location-dot"></i>
            Sta. Cruz, Ilocos Sur
        </p>

        <div class="contact-row">
            <div>
                <b>Punong Barangay</b>
                <small>0917-123-4567</small>
            </div>
            <a href="tel:09171234567">📞</a>
        </div>

        <div class="contact-row">
            <div>
                <b>Barangay Secretary</b>
                <small>0918-123-4567</small>
            </div>
            <a href="tel:09181234567">📞</a>
        </div>

        <div class="contact-row">
            <div>
                <b>Barangay Tanod</b>
                <small>0999-123-4567</small>
            </div>
            <a href="tel:09991234567">📞</a>
        </div>

        <div class="contact-row">
            <div>
                <b>Barangay Health Worker</b>
                <small>0906-123-4567</small>
            </div>
            <a href="tel:09061234567">📞</a>
        </div>

        <a class="view-button" href="pages/emergency.php">
            View All Contacts →
        </a>
    </div>


    <div class="panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-hospital"></i> Nearest Hospitals</span>
            <a href="pages/hospitals.php">View All</a>
        </div>

        <div class="hospital">
            <div class="hospital-icon">🏥</div>
            <div class="hospital-info">
                <b>Sta. Cruz Municipal Hospital</b>
                <small>📍 Sta. Cruz, Ilocos Sur</small>
                <small>📞 (077) 123-4567</small>
            </div>
            <strong>0.5 km</strong>
        </div>

        <div class="hospital">
            <div class="hospital-icon">🏥</div>
            <div class="hospital-info">
                <b>Ilocos Sur Medical Center</b>
                <small>📍 Candon City, Ilocos Sur</small>
                <small>📞 (077) 632-1234</small>
            </div>
            <strong>12.4 km</strong>
        </div>

        <div class="hospital">
            <div class="hospital-icon">🏥</div>
            <div class="hospital-info">
                <b>Rural Health Unit (RHU)</b>
                <small>📍 Sta. Cruz, Ilocos Sur</small>
                <small>📞 (077) 123-5678</small>
            </div>
            <strong>1.2 km</strong>
        </div>
    </div>


    <div class="panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-cloud"></i> Disaster Alerts</span>
            <a href="pages/alerts.php">View All</a>
        </div>

        <div class="alert danger">
            <b>⚠️ Heavy Rainfall Advisory</b>
            <p>Possible flooding in low-lying areas.</p>
            <small>Stay alert and take necessary precautions.</small>
        </div>

        <div class="alert warning">
            <b>⚠️ Typhoon Update</b>
            <p>Typhoon outside PAR may affect Northern Luzon.</p>
            <small>Keep monitoring.</small>
        </div>

        <div class="alert info">
            <b>ℹ️ Class Suspension</b>
            <p>No class on August 30, 2026.</p>
            <small>Sta. Cruz</small>
        </div>
    </div>


    <div class="panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-house"></i> Evacuation Centers</span>
            <a href="pages/evacuation.php">View All</a>
        </div>

        <div class="evacuation">
            <b>Sta. Cruz Covered Court</b>
            <small>📍 Poblacion Este, Sta. Cruz</small>
            <span class="open">Open</span>
        </div>

        <div class="evacuation">
            <b>Sta. Cruz Elementary School</b>
            <small>📍 Poblacion Este, Sta. Cruz</small>
            <span class="open">Open</span>
        </div>

        <div class="evacuation">
            <b>Sta. Cruz National High School</b>
            <small>📍 Poblacion Este, Sta. Cruz</small>
            <span class="open">Open</span>
        </div>
    </div>


    <div class="panel call-panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-phone"></i> What Do I Call?</span>
        </div>

        <div class="call-grid">
            <a href="tel:911">🔥 Fire / Smoke<br><b>Call 911</b></a>
            <a href="tel:911">➕ Medical Emergency<br><b>Call 911</b></a>
            <a href="tel:911">👮 Crime / Police<br><b>Call 911</b></a>
            <a href="tel:911">🌊 Flood / Disaster<br><b>Call 911</b></a>
            <a href="tel:911">🚑 Accident<br><b>Call 911</b></a>
            <a href="pages/reports.php">👥 Missing Person<br><b>Report</b></a>
        </div>
    </div>


    <div class="panel">
        <div class="panel-title">
            <span><i class="fa-solid fa-book-medical"></i> Emergency First Aid Guide</span>
            <a href="pages/firstaid.php">View All</a>
        </div>

        <a href="pages/firstaid.php" class="aid-row">❤️ CPR – Cardiopulmonary Resuscitation →</a>
        <a href="pages/firstaid.php" class="aid-row">🫁 Choking – What To Do →</a>
        <a href="pages/firstaid.php" class="aid-row">🩸 Severe Bleeding – How To Stop →</a>
        <a href="pages/firstaid.php" class="aid-row">🔥 Burns – Immediate Care →</a>
        <a href="pages/firstaid.php" class="aid-row">🦴 Fractures – Basic Treatment →</a>
        <a href="pages/firstaid.php" class="aid-row">🌡️ Heat Stroke – Prevention & Care →</a>
    </div>

</section>


<section class="report-banner">
    <div>
        <h2>📢 Report an Emergency or Community Concern</h2>
        <p>Help us keep our barangay safe and informed. Send a report with photos and location.</p>
    </div>

    <a href="pages/reports.php">Submit a Report</a>
</section>

</main>

<footer>
    <div>🛡️ <b>SK LIGTAS</b> – Poblacion Este, Sta. Cruz, Ilocos Sur</div>
    <div>© 2026 SK LIGTAS. All Rights Reserved.</div>
</footer>

<script src="assets/app.js"></script>


<script>
const SK_LIGTAS_BARANGAYS = [
"Amarao","Babayoan","Bacsayan","Banay","Bayugao Este","Bayugao Oeste","Besalan","Bugbuga","Calaoaan","Camanggaan","Candalican","Capariaan","Casilagan","Coscosnong","Daligan","Dili","Gabor Norte","Gabor Sur","Lalong","Lantag","Las-ud","Mambog","Mantanas","Nagtengnga","Padaoil","Paratong","Pattiqui","Pidpid","Pilar","Pinipin","Poblacion Este","Poblacion Norte","Poblacion Sur","Poblacion Weste","Quinfermin","Quinsoriano","Sagat","San Antonio","San Jose","San Pedro","Saoat","Sevilla","Sidaoen","Suyo","Tampugo","Turod","Villa Garcia","Villa Hermosa","Villa Laurencia"
];

function normalizeLocationName(value){
 return String(value||'').toLowerCase().replace(/\./g,'').replace(/[-_]/g,' ').replace(/\s+/g,' ').trim();
}

function officialBarangay(value){
 const n=normalizeLocationName(value);
 return SK_LIGTAS_BARANGAYS.find(b=>normalizeLocationName(b)===n)||'';
}

async function lookupLocation(latitude,longitude){
 const response=await fetch('api/location.php?lat='+encodeURIComponent(latitude)+'&lon='+encodeURIComponent(longitude),{headers:{'Accept':'application/json'}});
 if(!response.ok) throw new Error('Location lookup failed');
 const data=await response.json();
 if(!data.success) throw new Error(data.message||'Location could not be verified');
 return data;
}

async function goToMyLocation(event){
 event.preventDefault();
 if(!navigator.geolocation){alert('GPS is not supported by this browser.');return false;}
 const button=document.getElementById('myLocationButton');
 const original=button?button.innerHTML:'';
 if(button){button.innerHTML='<i class="fa-solid fa-location-dot"></i> Checking location...';button.style.pointerEvents='none';}
 navigator.geolocation.getCurrentPosition(async position=>{
   const latitude=position.coords.latitude;
   const longitude=position.coords.longitude;
   try{
     const data=await lookupLocation(latitude,longitude);
     if(!data.inside_service_area){
       sessionStorage.removeItem('sk_ligtas_latitude');
       sessionStorage.removeItem('sk_ligtas_longitude');
       sessionStorage.removeItem('sk_ligtas_address');
       alert('Your current location is outside the SK LIGTAS service area of Santa Cruz, Ilocos Sur.');
       return;
     }
     sessionStorage.setItem('sk_ligtas_latitude',String(latitude));
     sessionStorage.setItem('sk_ligtas_longitude',String(longitude));
     sessionStorage.setItem('sk_ligtas_address',data.address);
     sessionStorage.setItem('sk_ligtas_barangay',data.barangay);
     window.location.href='pages/reports.php';
   }catch(error){
     alert(error.message||'Unable to verify your location. Please try again.');
   }finally{
     if(button){button.innerHTML=original;button.style.pointerEvents='';}
   }
 },()=>{
   alert('Unable to get your location. Please allow location permission and try again.');
   if(button){button.innerHTML=original;button.style.pointerEvents='';}
 },{enableHighAccuracy:true,timeout:20000,maximumAge:0});
 return false;
}
</script>

</body>
</html>