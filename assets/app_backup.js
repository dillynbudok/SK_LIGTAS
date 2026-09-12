function call911(){window.location.href='tel:911';}
function openReport(){window.location.href='pages/reports.php';}
function scrollToSection(id){const e=document.getElementById(id);if(e)e.scrollIntoView({behavior:'smooth'});}
function getLocation(){
 const loc=document.getElementById('reportLocation'); const lat=document.getElementById('latitude'); const lng=document.getElementById('longitude');
 if(!navigator.geolocation){alert('GPS is not supported on this device.');return;}
 navigator.geolocation.getCurrentPosition(p=>{if(loc)loc.value=`${p.coords.latitude}, ${p.coords.longitude}`;if(lat)lat.value=p.coords.latitude;if(lng)lng.value=p.coords.longitude;},()=>alert('Unable to get your location. Please enter it manually.'));
}
async function loadData(){try{const r=await fetch('api/data.php');const d=await r.json();
 const c=document.getElementById('contacts');if(c)c.innerHTML=d.contacts.map(x=>`<div class="contact-row"><div class="row-main">${x.logo ? `<img class="contact-logo" src="uploads/logos/${encodeURIComponent(x.logo.split('/').pop())}" alt="${esc(x.name)} logo" onerror="this.remove()">` : '<div class="row-icon">☎</div>'}<div><b>${esc(x.name)}</b><small>${esc(x.position||'')}<br>${esc(x.phone)}</small></div></div><a class="call-btn" href="tel:${esc(x.phone)}">☎</a></div>`).join('');
 const a=document.getElementById('alertsList');if(a)a.innerHTML=d.alerts.map(x=>`<div class="alert ${esc(x.type)}"><b>⚠ ${esc(x.title)}</b><p>${esc(x.message)}</p><small>${esc(x.created_at)}</small></div>`).join('');
 const h=document.getElementById('hospitalsList');if(h)h.innerHTML=d.hospitals.map(x=>`<div class="hospital-row"><div class="row-main"><div class="row-icon">🏥</div><div><b>${esc(x.name)}</b><small>${esc(x.location)}<br>${esc(x.phone||'')}</small></div></div><a class="call-btn" href="tel:${esc(x.phone||'')}">☎</a></div>`).join('');
 const e=document.getElementById('evacuationList');if(e)e.innerHTML=d.evacuation.map(x=>`<div class="evac-row"><div class="row-main"><div class="row-icon">🏠</div><div><b>${esc(x.name)}</b><small>${esc(x.location)}<br>Capacity: ${esc(x.capacity)}</small></div></div><span class="status ${x.status.toLowerCase()}">${esc(x.status)}</span></div>`).join('');
 const f=document.getElementById('firstaidList');if(f)f.innerHTML=d.first_aid.map(x=>`<details class="aid-row"><summary><div class="row-main"><div class="row-icon">🩺</div><div><b>${esc(x.title)}</b><small>Tap to view first aid instructions</small></div></div><span class="aid-arrow">⌄</span></summary><div class="aid-content">${esc(x.description).replace(/\n/g,'<br>')}</div></details>`).join('');
}catch(e){console.error(e)}}
function esc(v){return String(v??'').replace(/[&<>'"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[m]));}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',loadData);else loadData();
