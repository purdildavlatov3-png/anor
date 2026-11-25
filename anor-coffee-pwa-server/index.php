<?php
require_once __DIR__ . '/bootstrap.php';
$token = preg_match('/^[a-f0-9]{64}$/', $_GET['t'] ?? '') ? $_GET['t'] : '';
?><!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#171715"><meta name="description" content="Карта лояльности Анор кофе">
<link rel="manifest" href="/manifest.webmanifest"><link rel="apple-touch-icon" href="/assets/icon-192.png">
<title>Анор кофе — карта лояльности</title><link rel="stylesheet" href="/assets/app.css">
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head><body>
<main class="app">
<header class="top"><div class="brand"><div class="logo">А</div><div><b>АНОР КОФЕ</b><small>КАРТА ЛОЯЛЬНОСТИ</small></div></div><span class="pill">ANOR CLUB</span></header>
<section class="hero"><span class="eyebrow">ВАШ КОФЕ. ВАШИ БОНУСЫ.</span><h1 id="headline">Каждый десятый<br>напиток — в подарок.</h1><p id="intro">Зарегистрируйтесь и собирайте штампы за покупки в «Анор кофе».</p></section>
<section id="registerPanel" class="panel">
  <h2>Ваша карта начинается здесь</h2><p class="muted">Создайте карту за несколько секунд. Номер телефона нужен, чтобы мы могли найти вашу карту при потере ссылки.</p>
  <form id="registerForm">
    <label>Ваше имя<input name="name" autocomplete="name" minlength="2" maxlength="120" placeholder="Например, Алина" required></label>
    <label>Номер телефона<input name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+7 900 000-00-00" required></label>
    <label class="consent"><input type="checkbox" required><span>Согласен(на) с <a href="/privacy.html" target="_blank">политикой обработки персональных данных</a> и даю согласие на обработку данных для работы программы лояльности.</span></label>
    <button class="primary" type="submit">Создать карту</button><div id="registerError" class="error" role="alert"></div>
  </form>
</section>
<section id="memberPanel" class="hidden">
  <div class="loyalty-card">
    <div class="card-top"><span>☕ АНор КОФЕ</span><small>DIGITAL CLUB</small></div>
    <div class="card-main"><div class="card-title">10 штампов =<br>напиток в подарок</div><p id="memberGreeting">Ваша карта участника</p></div>
    <div id="stamps" class="stamps"></div>
    <div class="card-bottom"><span>MEMBER CARD</span><b id="memberCode"></b></div>
  </div>
  <div class="progress"><div><b>Ваш прогресс</b><strong id="progressCount">0 / 10</strong></div><div class="track"><i id="progressBar"></i></div><p id="progressHint">Осталось 10 штампов до подарка.</p></div>
  <div class="qr-panel"><div class="qr" id="memberQr"></div><div><b>Ваш персональный QR-код</b><p>Покажите его бариста после покупки, чтобы получить штамп.</p><small>Карта привязана к вашему профилю</small></div></div>
  <button id="installBtn" class="primary install" type="button">Добавить Анор кофе на экран</button>
  <p class="install-help" id="installHelp">Иконка появится на главном экране телефона. Отдельное приложение устанавливать не нужно.</p>
  <button id="shareBtn" class="ghost" type="button">Поделиться ссылкой на карту</button>
  <div id="rewardBanner" class="reward hidden">🎉 У вас накоплено 10 штампов! Покажите карту сотруднику, чтобы получить подарок.</div>
</section>
<footer>АНОР КОФЕ · КРАСНОДАР</footer>
</main>
<div id="toast" class="toast" role="status"></div>
<script>
(() => {
const token = <?=json_encode($token)?>;
const API='/api';
let member=null, deferredPrompt=null, cardUrl=location.href;
const $=id=>document.getElementById(id);
const showError=(msg)=>{$('registerError').textContent=msg};
function toast(msg){$('toast').textContent=msg;$('toast').classList.add('show');setTimeout(()=>$('toast').classList.remove('show'),2600)}
function showMember(m,qrUrl){
 member=m;cardUrl=qrUrl||location.href;
 $('registerPanel').classList.add('hidden');$('memberPanel').classList.remove('hidden');
 $('memberGreeting').textContent='Приятно познакомиться, '+m.name+'!';
 $('memberCode').textContent='ANOR · '+m.publicId.slice(0,8);
 const stamps=$('stamps');stamps.innerHTML='';
 for(let i=0;i<10;i++){const s=document.createElement('span');s.className='stamp '+(i<m.stamps?'on':'');s.textContent=i<m.stamps?'✓':String(i+1);stamps.appendChild(s)}
 $('progressCount').textContent=m.stamps+' / 10';
 $('progressBar').style.width=(m.stamps*10)+'%';
 $('progressHint').textContent=m.rewardReady?'Подарок уже доступен — покажите карту бариста.':'Осталось '+(10-m.stamps)+' штампов до подарка.';
 $('rewardBanner').classList.toggle('hidden',!m.rewardReady);
 $('memberQr').innerHTML='';
 if(window.QRCode)new QRCode($('memberQr'),{text:cardUrl,width:116,height:116,colorDark:'#171715',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.M});
 else $('memberQr').textContent='QR временно недоступен';
}
async function loadCard(){
 if(!token)return;
 try{const r=await fetch(API+'/me.php?t='+encodeURIComponent(token));const d=await r.json();if(!r.ok)throw Error(d.error||'Карта не найдена');showMember(d.member,location.href)}
 catch(e){$('intro').textContent=e.message+' Зарегистрируйтесь, чтобы создать новую карту.'}
}
$('registerForm').addEventListener('submit',async e=>{
 e.preventDefault();showError('');
 const fd=new FormData(e.currentTarget);
 const btn=e.currentTarget.querySelector('button');btn.disabled=true;btn.textContent='Создаём карту…';
 try{
  const r=await fetch(API+'/register.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:fd.get('name'),phone:fd.get('phone')})});
  const d=await r.json();if(!r.ok)throw Error(d.error||'Ошибка регистрации');
  const url=new URL(d.qrUrl);history.replaceState({},'',url.pathname+url.search);showMember(d.member,d.qrUrl);toast('Карта создана!');
 }catch(err){showError(err.message)}finally{btn.disabled=false;btn.textContent='Создать карту'}
});
window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();deferredPrompt=e;$('installBtn').classList.remove('hidden');});
$('installBtn').addEventListener('click',async()=>{
 if(deferredPrompt){deferredPrompt.prompt();const choice=await deferredPrompt.userChoice;deferredPrompt=null;if(choice.outcome==='accepted')toast('Анор кофе добавлен на экран');return}
 $('installHelp').textContent=/iphone|ipad|ipod/i.test(navigator.userAgent)
 ? 'На iPhone: нажмите «Поделиться» в Safari → «На экран Домой» → «Добавить».'
 : 'Откройте меню браузера (⋮) и выберите «Установить приложение» или «Добавить на главный экран».';
});
$('shareBtn').addEventListener('click',async()=>{
 try{if(navigator.share)await navigator.share({title:'Анор кофе — моя карта',url:cardUrl});else if(navigator.clipboard){await navigator.clipboard.writeText(cardUrl);toast('Ссылка скопирована')}else toast('Скопируйте ссылку из адресной строки')}
 catch(e){if(e.name!=='AbortError')toast('Не удалось поделиться ссылкой')}
});
if('serviceWorker' in navigator && location.protocol.startsWith('http'))navigator.serviceWorker.register('/sw.js').catch(console.error);
loadCard();
})();
</script></body></html>
