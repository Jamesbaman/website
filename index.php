<?php
session_start();
$stage = $_SESSION['stage'] ?? 0;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>This Is Not A Website</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=IBM+Plex+Serif:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
<style>
  :root {
    --paper: #dcdde3;
    --ink: #15151a;
    --blue: #3a46ff;
    --red: #e8341c;
    --title: 'Archivo Black', 'Arial Black', sans-serif;
    --voice: 'IBM Plex Serif', Georgia, serif;
  }
  * { box-sizing: border-box; }
  html, body { height: 100%; margin: 0; }
  body {
    background: var(--paper); color: var(--ink);
    font-family: var(--voice); font-size: 1.15rem; line-height: 1.6;
    display: grid; grid-template-rows: auto 1fr auto; overflow-x: hidden;
  }
  #chrome {
    min-height: 3.25rem; display: flex; align-items: center; justify-content: space-between;
    border-bottom: 3px solid var(--ink); padding: .35rem 1rem; font-family: var(--title);
    text-transform: uppercase; letter-spacing: .03em; font-size: .78rem;
  }
  #chrome strong { color: var(--blue); }
  #chrome button { font-size: .72rem; padding: .25rem .55rem; background: transparent; color: var(--ink); border-width: 2px; }
  #chrome button.on { background: var(--ink); color: var(--paper); }
  main { display: grid; place-items: center; padding: 2rem 1.25rem; }
  #stage { width: min(46rem, 100%); text-align: center; position: relative; }
  footer { padding: 1.25rem; min-height: 8.5rem; display: grid; place-items: center; }
  #narrator { max-width: 36rem; margin: 0; font-style: italic; text-align: center; min-height: 3.5em; }
  #narrator::before { content: "\201C"; } #narrator::after { content: "\201D"; }
  :focus-visible { outline: 3px solid var(--blue); outline-offset: 3px; }

  /* stage 0: the title you are supposed to break */
  .title {
    font-family: var(--title); font-size: clamp(2.6rem, 11vw, 6.5rem); line-height: .95;
    margin: 0; cursor: pointer; user-select: none; text-transform: uppercase;
  }
  .title span { display: inline-block; transition: transform .8s cubic-bezier(.5,0,1,.6), opacity .8s; }
  .title.shake { animation: shake .18s; }
  .title.broken span { transform: translateY(120vh) rotate(var(--r)); opacity: 0; }
  @keyframes shake { 25% { transform: translateX(-8px) rotate(-1deg); } 75% { transform: translateX(8px) rotate(1deg); } }

  button, input {
    font: inherit; border: 3px solid var(--ink); background: transparent; color: var(--ink);
    padding: .6rem 1.4rem; border-radius: 0;
  }
  button { cursor: pointer; font-family: var(--title); text-transform: uppercase; background: var(--blue); color: #fff; border-color: var(--blue); }
  button:hover { background: var(--ink); border-color: var(--ink); }
  #go { margin-top: 2.5rem; animation: pop .35s; }
  @keyframes pop { from { transform: scale(.3); } }

  /* stage 1: the bar that never finishes */
  .bar { position: relative; height: 3rem; border: 3px solid var(--ink); margin: 1.5rem 0 .75rem; touch-action: none; }
  .bar i { position: absolute; inset: 0 auto 0 0; width: 99%; background: var(--ink); transition: width .2s; }
  .bar b { position: absolute; top: -.6rem; bottom: -.6rem; left: 99%; width: 1.4rem; margin-left: -.7rem;
           background: var(--blue); cursor: grab; }
  .label { font-family: var(--title); font-size: 1.6rem; margin: 0; }

  /* stage 2: password */
  form { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
  input { width: 14rem; text-align: center; }

  /* stage 3: the button that runs away */
  #nope { background: var(--red); border-color: var(--red); font-size: 1.6rem; padding: 1rem 2rem; position: relative; transition: transform .15s; }
  #nope:hover { background: var(--red); border-color: var(--ink); }

  .agreement { max-width: 30rem; margin: 1.5rem auto 0; text-align: left; border: 3px solid var(--ink); padding: .6rem 1rem; }
  .agreement label { display: block; cursor: pointer; padding: .35rem 0; }
  .agreement input { width: auto; margin-right: .55rem; accent-color: var(--blue); }

  .achievements { display: flex; justify-content: center; flex-wrap: wrap; gap: .5rem; margin: 1rem 0; }
  .badge { border: 2px solid var(--ink); padding: .25rem .65rem; font-family: var(--title); font-size: .72rem; background: #fff4; }

  .stats { font-size: 1.05rem; margin: 1.5rem 0; }
  @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
</style>
</head>
<body>
<!-- Dev note, please ignore: the password is "nogame" -->
<header id="chrome">
  <strong>THIS IS NOT A WEBSITE</strong>
  <button id="sound" type="button" aria-pressed="false">Sound: off</button>
</header>
<main><div id="stage" aria-live="polite"></div></main>
<footer><p id="narrator"></p></footer>

<script>
const lines = {
  0: ["Hello, visitor. I have bad news.", "There is no website."],
  1: ["Oh. You broke the title.", "That is vandalism, and I am disappointed.", "Fine. Continue. This is still not a website."],
  2: ["Access denied. There is a password, and I will not tell you what it is.", "Whatever you do, do not inspect the source."],
  3: ["You are persistent. I respect that a little.", "Do not press the red button. It is not part of this. Because this is not a website."],
  4: ["Almost finished. Please accept absolutely nothing.", "Your consent is unnecessary and probably not legal."],
  5: ["Congratulations. You did not use a website.", "Here is proof of your excellent inaction."]
};
let stage = <?= (int)$stage ?>;
let clicks = 0;
const $stage = document.getElementById('stage');
const $nar = document.getElementById('narrator');
document.addEventListener('pointerdown', () => clicks++);
let audio = null, soundOn = false;
const soundButton = document.getElementById('sound');
soundButton.onclick = () => {
  soundOn = !soundOn;
  soundButton.classList.toggle('on', soundOn);
  soundButton.setAttribute('aria-pressed', String(soundOn));
  soundButton.textContent = `Sound: ${soundOn ? 'on' : 'off'}`;
  if (soundOn) blip(440, .04);
};
function blip(freq, duration = .08) {
  if (!soundOn) return;
  audio ||= new (window.AudioContext || window.webkitAudioContext)();
  const o = audio.createOscillator(), g = audio.createGain();
  o.frequency.value = freq; o.type = 'square';
  g.gain.setValueAtTime(.025, audio.currentTime);
  g.gain.exponentialRampToValueAtTime(.001, audio.currentTime + duration);
  o.connect(g); g.connect(audio.destination); o.start(); o.stop(audio.currentTime + duration);
}

/* ---------- narrator: types one line, then the next ---------- */
let talkId = 0;
function say(list, i = 0) {
  const id = ++talkId;
  const text = list[i];
  $nar.textContent = '';
  let n = 0;
  (function tick() {
    if (id !== talkId) return;
    $nar.textContent = text.slice(0, ++n);
    if (n < text.length) setTimeout(tick, 28);
    else if (i + 1 < list.length) setTimeout(() => id === talkId && say(list, i + 1), 1700);
  })();
}

/* ---------- server calls ---------- */
async function api(body) {
  const r = await fetch('api.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  return r.json();
}
async function advance(extra) {
  const res = await api({ action: 'advance', clicks, ...extra });
  clicks = 0;
  if (res.ok) { stage = res.stage; render(res); }
  return res;
}

/* ---------- stages ---------- */
function render(info) {
  $stage.innerHTML = '';
  say(lines[stage]);
  [s0, s1, s2, s3, s4, s5][stage](info);
}

function s0() {
  const h = document.createElement('h1');
  h.className = 'title'; h.setAttribute('role', 'button'); h.tabIndex = 0;
  h.innerHTML = 'This is not a website'.split('').map(c =>
    c === ' ' ? ' ' : `<span style="--r:${Math.round(Math.random() * 90 - 45)}deg">${c}</span>`).join('');
  $stage.append(h);
  const taunts = ["Please stop clicking that.", "It is a title. It has feelings.", "I am serious.", "Last warning."];
  let hits = 0;
  const hit = () => {
    if (hits >= 5) return;
    hits++;
    h.classList.remove('shake'); void h.offsetWidth; h.classList.add('shake');
    if (hits < 5) { say([taunts[hits - 1]]); return; }
    h.classList.add('broken');
    say(["...You actually did it."]);
    setTimeout(() => {
      const b = document.createElement('button');
      b.id = 'go'; b.textContent = 'Start';
      b.onclick = () => advance({ hits });
      $stage.append(b); b.focus();
    }, 900);
  };
  h.onclick = hit;
  h.onkeydown = e => (e.key === 'Enter' || e.key === ' ') && (e.preventDefault(), hit());
}

function s1() {
  $stage.innerHTML = `<p class="label">Loading the website that is not here: <span id="pc">99</span>%</p>
    <div class="bar" id="bar"><i id="fill"></i><b id="grip" tabindex="0" role="slider" aria-valuenow="99"></b></div>`;
  const bar = $stage.querySelector('#bar'), grip = $stage.querySelector('#grip'),
        fill = $stage.querySelector('#fill'), pc = $stage.querySelector('#pc');
  let down = false;
  const set = x => {
    const r = bar.getBoundingClientRect();
    const p = Math.max(0, Math.min(1, (x - r.left) / r.width));
    grip.style.left = fill.style.width = (p * 100) + '%';
    pc.textContent = Math.round(p * 100);
    if (p >= 0.999) { down = false; advance({ dragged: true }); }
  };
  grip.onpointerdown = e => { down = true; grip.setPointerCapture(e.pointerId); };
  grip.onpointermove = e => down && set(e.clientX);
  grip.onpointerup = () => down = false;
  grip.onkeydown = e => {
    if (e.key !== 'ArrowRight') return;
    const cur = parseFloat(grip.style.left || '99') / 100, r = bar.getBoundingClientRect();
    set(r.left + Math.min(1, cur + 0.005) * r.width);
  };
}

function s2(info) {
  $stage.innerHTML = `<p class="label">Enter password</p>
    <form id="f"><input id="pw" type="text" autocomplete="off" aria-label="Password"><button>Enter</button></form>`;
  const f = $stage.querySelector('#f'), pw = $stage.querySelector('#pw');
  pw.focus();
  f.onsubmit = async e => {
    e.preventDefault();
    const res = await advance({ password: pw.value });
    if (!res.ok) {
      pw.value = '';
      say(res.wrong >= 3
        ? ["Wrong again. Fine: it is hidden where developers leave notes.", "Not that you should look."]
        : ["Wrong. Of course it is wrong."]);
    }
  };
}

function s3() {
  $stage.innerHTML = `<button id="nope">Do not press</button>`;
  const b = $stage.querySelector('#nope');
  let dodges = 0;
  b.addEventListener('pointerenter', e => {
    if (e.pointerType !== 'mouse' || dodges >= 6) return;
    dodges++;
    const dx = (Math.random() - .5) * Math.min(innerWidth * .6, 420);
    const dy = (Math.random() - .5) * 220;
    b.style.transform = `translate(${dx}px, ${dy}px)`;
    if (dodges === 6) say(["...Fine. I am tired. Press it."]);
  });
  b.onclick = () => advance({ pressed: true });
}

function s4() {
  $stage.innerHTML = `<p class="label">Terms of not using anything</p>
    <div class="agreement">
      <label><input type="checkbox" id="disclaimer"> I do not agree to anything</label>
      <label><input type="checkbox" id="cookies"> This not-website must reject cookies</label>
      <label><input type="checkbox" id="identity"> I confirm this is not a website</label>
    </div>
    <button id="finish" style="margin-top:1.25rem">Submit absence</button>`;
  const boxes = [...$stage.querySelectorAll('input')];
  $stage.querySelector('#finish').onclick = async () => {
    const all = boxes.every(b => b.checked);
    if (!all) {
      say(["All three boxes. This is not a legally binding inconvenience."]);
      return;
    }
    await advance({
      disclaimed: true,
      noCookies: true,
      notAWebsite: true
    });
  };
}

async function s5(info) {
  const d = info || await api({});
  const mins = Math.floor(d.seconds / 60), secs = d.seconds % 60;
  $stage.innerHTML = `<h1 class="title" style="cursor:default">Not a website</h1>
    <div class="achievements">
      <span class="badge">TITLE BREAKER</span>
      <span class="badge">NO-COOKIE HERO</span>
      <span class="badge">BUTTON RESISTER</span>
    </div>
    <p class="stats">Time not played: ${mins}m ${secs}s<br>
    Clicks not made: ${d.clicks}<br>Wrong passwords: ${d.wrong}</p>
    <button id="again">Use again</button>`;
  $stage.querySelector('#again').onclick = async () => {
    const r = await api({ action: 'reset' });
    stage = r.stage; say(["Again? There is still no website."]); render(r);
  };
}

render();
</script>
</body>
</html>
