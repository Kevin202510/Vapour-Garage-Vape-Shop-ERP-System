<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vapour Garage — Dispods · EJuice · Mods · Wires · Est. 2023</title>
<meta name="description" content="Vapour Garage — NUEVA ECIJA's premier vape shop. Dispods, EJuice, Mods, Wires. Est. 2023.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:ital,wght@0,300;0,400;0,600;0,800;1,300;1,600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
  .justify-end {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --black:#060608;--dark:#0d0b14;--dark2:#130f1e;--dark3:#1a1428;
  --purple:#7c22d4;--purple2:#9b3df0;--purple3:#5b16a0;--dpurple:#2d0d52;
  --green:#39ff14;--green2:#5eff42;--green3:#25cc0a;
  --white:#ffffff;--grey:#c8c4d4;--muted:#7a7490;
  --display:'Russo One',sans-serif;
  --body:'Exo 2',sans-serif;
  --mono:'Share Tech Mono',monospace;
}
html{scroll-behavior:smooth;overflow-x:hidden}
body{background:var(--black);color:var(--white);font-family:var(--body);overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:9998;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,0.02) 3px,rgba(0,0,0,0.02) 4px)}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9997;opacity:0.025;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");background-size:120px}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:500;display:flex;align-items:center;justify-content:space-between;padding:18px 60px;transition:all 0.4s}
nav.scrolled{background:rgba(6,6,8,0.92);border-bottom:1px solid rgba(124,34,212,0.35);backdrop-filter:blur(20px)}
.nbrand{font-family:var(--display);font-size:17px;letter-spacing:0.08em;background:linear-gradient(90deg,var(--purple2),var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.nlinks{display:flex;align-items:center;gap:28px}
.nlinks a{font-family:var(--mono);font-size:11px;letter-spacing:0.12em;color:var(--muted);text-decoration:none;text-transform:uppercase;transition:color 0.2s}
.nlinks a:hover{color:var(--green)}
.ncta{padding:8px 20px;background:transparent;border:1px solid var(--purple);color:var(--white);font-family:var(--mono);font-size:11px;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;border-radius:3px;transition:all 0.2s;box-shadow:0 0 14px rgba(124,34,212,0.25)}
.ncta:hover{background:var(--purple);box-shadow:0 0 28px rgba(124,34,212,0.6)}

/* HERO */
.hero{min-height:100vh;display:flex;flex-direction:column;justify-content:center;padding:120px 60px 80px;position:relative;overflow:hidden}
.hgrid{position:absolute;inset:0;z-index:0;background:linear-gradient(rgba(124,34,212,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(124,34,212,0.06) 1px,transparent 1px);background-size:56px 56px;animation:gdrift 24s linear infinite}
@keyframes gdrift{0%{background-position:0 0}100%{background-position:56px 56px}}
.horb1{position:absolute;top:-15%;right:5%;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(124,34,212,0.22) 0%,transparent 65%);pointer-events:none;animation:opulse 7s ease-in-out infinite}
.horb2{position:absolute;bottom:-5%;left:25%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(57,255,20,0.08) 0%,transparent 65%);pointer-events:none;animation:opulse 5s ease-in-out infinite 2s}
@keyframes opulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.08);opacity:0.7}}
.hcorn1{position:absolute;top:88px;right:60px;width:180px;height:180px;border-top:1.5px solid rgba(57,255,20,0.25);border-right:1.5px solid rgba(57,255,20,0.25)}
.hcorn2{position:absolute;bottom:80px;left:60px;width:140px;height:140px;border-bottom:1.5px solid rgba(124,34,212,0.35);border-left:1.5px solid rgba(124,34,212,0.35)}

.hc{position:relative;z-index:1;max-width:820px}
.hest{display:inline-flex;align-items:center;gap:8px;padding:5px 14px;border:1px solid rgba(57,255,20,0.35);border-radius:2px;font-family:var(--mono);font-size:10px;letter-spacing:0.18em;color:var(--green);text-transform:uppercase;margin-bottom:28px;box-shadow:0 0 18px rgba(57,255,20,0.08);animation:fu 0.7s ease 0.1s both}
.hest-dot{width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:blink 1.4s step-end infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}

.htitle{font-family:var(--display);line-height:0.88;letter-spacing:0.04em;margin-bottom:22px;animation:fu 0.8s ease 0.25s both}
.ht1{display:block;font-size:clamp(70px,9.5vw,140px);color:var(--white);text-shadow:0 0 40px rgba(124,34,212,0.4)}
.ht2{display:block;font-size:clamp(78px,10.5vw,160px);background:linear-gradient(180deg,var(--purple2) 0%,var(--purple3) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;filter:drop-shadow(0 0 28px rgba(124,34,212,0.7))}
.ht3{display:block;font-size:clamp(86px,11.5vw,178px);color:var(--black);-webkit-text-stroke:2.5px var(--green);text-shadow:0 0 40px rgba(57,255,20,0.35),0 0 80px rgba(57,255,20,0.15);letter-spacing:0.06em;cursor:default}
.ht3:hover{animation:glitch 0.25s steps(2) infinite}
@keyframes glitch{0%{text-shadow:2px 0 0 var(--green),-2px 0 0 var(--purple2),0 0 40px rgba(57,255,20,0.4)}50%{text-shadow:-2px 0 0 var(--green),2px 0 0 var(--purple2),0 0 40px rgba(57,255,20,0.4)}}

.htag{font-family:var(--mono);font-size:12px;letter-spacing:0.2em;text-transform:uppercase;color:var(--muted);margin-bottom:40px;display:flex;align-items:center;gap:12px;animation:fu 0.7s ease 0.5s both}
.htag::before{content:'';display:inline-block;width:36px;height:1px;background:var(--purple)}

.hbtns{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:56px;animation:fu 0.7s ease 0.65s both}
.btn1{padding:14px 30px;background:var(--purple);color:var(--white);border:none;font-family:var(--display);font-size:14px;letter-spacing:0.05em;border-radius:3px;cursor:pointer;text-decoration:none;box-shadow:0 0 28px rgba(124,34,212,0.45);transition:all 0.2s;display:inline-flex;align-items:center;gap:8px;clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px))}
.btn1:hover{background:var(--purple2);box-shadow:0 0 48px rgba(124,34,212,0.75);transform:translateY(-2px)}
.btn2{padding:13px 26px;background:transparent;color:var(--green);border:1px solid var(--green);font-family:var(--mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;border-radius:3px;cursor:pointer;text-decoration:none;box-shadow:0 0 14px rgba(57,255,20,0.12);transition:all 0.2s;clip-path:polygon(8px 0,100% 0,100% calc(100% - 8px),calc(100% - 8px) 100%,0 100%,0 8px)}
.btn2:hover{background:rgba(57,255,20,0.08);box-shadow:0 0 28px rgba(57,255,20,0.28)}

.hbranches{display:flex;gap:0;padding-top:26px;border-top:1px solid rgba(124,34,212,0.2);animation:fu 0.7s ease 0.8s both}
.hbi{display:flex;align-items:center;gap:7px;padding:0 20px;border-right:1px solid rgba(124,34,212,0.15);font-family:var(--mono);font-size:10px;letter-spacing:0.06em}
.hbi:first-child{padding-left:0}.hbi:last-child{border-right:none}
.hdot{width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 7px var(--green);animation:opulse 2s infinite;flex-shrink:0}
.hbn{color:var(--grey)}

@keyframes fu{from{opacity:0;transform:translateY(36px)}to{opacity:1;transform:translateY(0)}}

/* MARQUEE */
.mwrap{overflow:hidden;padding:11px 0;background:var(--purple3);border-top:1px solid rgba(57,255,20,0.18);border-bottom:1px solid rgba(57,255,20,0.18);box-shadow:0 0 40px rgba(124,34,212,0.25)}
.mtrack{display:flex;animation:mq 16s linear infinite;white-space:nowrap}
.mi{font-family:var(--display);font-size:12px;letter-spacing:0.14em;color:var(--white);padding:0 22px;opacity:0.9;text-transform:uppercase}
.ms{color:var(--green);padding:0 4px;font-size:10px}
@keyframes mq{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* SECTIONS */
.sec{padding:96px 60px;position:relative}
.stag{font-family:var(--mono);font-size:9px;letter-spacing:0.24em;text-transform:uppercase;color:var(--green);margin-bottom:12px;display:flex;align-items:center;gap:8px}
.stag::before{content:'//';opacity:0.45}
.stitle{font-family:var(--display);font-size:clamp(40px,5vw,68px);line-height:0.93;letter-spacing:0.04em}

/* STATS */
.srow{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:rgba(124,34,212,0.18);border:1px solid rgba(124,34,212,0.18);margin-top:60px}
.sbox{background:var(--dark2);padding:44px 28px;text-align:center;transition:background 0.3s;position:relative;overflow:hidden}
.sbox::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--purple),var(--green),transparent);transform:scaleX(0);transition:transform 0.4s}
.sbox:hover{background:var(--dark3)}.sbox:hover::after{transform:scaleX(1)}
.snum{font-family:var(--display);font-size:60px;line-height:1;background:linear-gradient(135deg,var(--purple2),var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:6px}
.slbl{font-family:var(--mono);font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--muted)}

/* PRODUCTS */
.pbg{background:var(--dark2)}
.pgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px;background:rgba(124,34,212,0.12);margin-top:48px}
.pc{background:var(--dark);padding:30px 22px;border:1px solid transparent;position:relative;overflow:hidden;cursor:pointer;transition:all 0.25s}
.pc::before{content:'';position:absolute;top:0;left:0;right:0;height:2.5px;background:linear-gradient(90deg,var(--purple3),var(--green));transform:scaleX(0);transform-origin:left;transition:transform 0.3s}
.pc:hover{background:var(--dark3);border-color:rgba(124,34,212,0.35)}.pc:hover::before{transform:scaleX(1)}
.pcnum{font-family:var(--display);font-size:52px;color:rgba(255,255,255,0.04);position:absolute;top:14px;right:14px;line-height:1}
.pcat{font-family:var(--mono);font-size:8.5px;letter-spacing:0.2em;text-transform:uppercase;color:var(--purple2);margin-bottom:14px}
.pname{font-family:var(--display);font-size:20px;letter-spacing:0.04em;color:var(--white);margin-bottom:8px;line-height:1.1}
.pdesc{font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:20px;font-weight:300}
.pprice{font-family:var(--display);font-size:22px;color:var(--green);letter-spacing:0.02em}
.pprice span{font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:0.1em}
.ctl{position:absolute;top:16px;left:16px;width:24px;height:24px;border-top:1px solid rgba(57,255,20,0.25);border-left:1px solid rgba(57,255,20,0.25)}
.cbr{position:absolute;bottom:16px;right:16px;width:24px;height:24px;border-bottom:1px solid rgba(124,34,212,0.35);border-right:1px solid rgba(124,34,212,0.35)}

/* BRANCHES */
.bgrd{display:grid;grid-template-columns:1fr 1fr;gap:2px;background:rgba(124,34,212,0.12);margin-top:52px}
.bc{background:var(--dark);padding:38px;border:1px solid transparent;display:grid;grid-template-columns:auto 1fr;gap:20px;transition:all 0.25s;position:relative;overflow:hidden}
.bc::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--purple3),var(--green));transform:scaleX(0);transition:transform 0.3s}
.bc:hover{background:var(--dark2);border-color:rgba(124,34,212,0.28)}.bc:hover::after{transform:scaleX(1)}
.bcnum{font-family:var(--display);font-size:48px;color:rgba(124,34,212,0.12);line-height:1;align-self:flex-start;padding-top:2px}
.bcname{font-family:var(--display);font-size:18px;letter-spacing:0.04em;color:var(--white);margin-bottom:6px}
.bcaddr{font-family:var(--mono);font-size:11px;color:var(--muted);margin-bottom:14px;letter-spacing:0.04em;line-height:1.5}
.bchours{display:inline-flex;align-items:center;gap:6px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--green)}
.bchours::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);animation:opulse 2s infinite;flex-shrink:0}

/* LOYALTY */
.lbg{background:var(--dark2)}
.tgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px;background:rgba(124,34,212,0.12);margin-top:52px}
.tc{background:var(--dark);padding:34px 26px;border:1px solid transparent;position:relative;overflow:hidden;transition:all 0.3s}
.tc.plat{border-color:rgba(57,255,20,0.25);background:rgba(57,255,20,0.02)}
.tc:hover{background:var(--dark3)}
.tglow{position:absolute;top:0;left:0;right:0;height:70px;background:radial-gradient(ellipse at 50% 0%,rgba(124,34,212,0.28) 0%,transparent 70%);pointer-events:none}
.tc.plat .tglow{background:radial-gradient(ellipse at 50% 0%,rgba(57,255,20,0.18) 0%,transparent 70%)}
.tico{width:42px;height:42px;border-radius:3px;display:flex;align-items:center;justify-content:center;font-family:var(--display);font-size:18px;margin-bottom:18px;clip-path:polygon(0 0,calc(100% - 5px) 0,100% 5px,100% 100%,5px 100%,0 calc(100% - 5px))}
.tico.br{background:rgba(139,90,43,0.25);color:#c9a882;border:1px solid rgba(139,90,43,0.35)}
.tico.si{background:rgba(148,163,184,0.18);color:#94a3b8;border:1px solid rgba(148,163,184,0.28)}
.tico.go{background:rgba(201,148,58,0.18);color:#c9943a;border:1px solid rgba(201,148,58,0.32)}
.tico.pl{background:rgba(57,255,20,0.12);color:var(--green);border:1px solid rgba(57,255,20,0.35)}
.tname{font-family:var(--display);font-size:22px;letter-spacing:0.05em;margin-bottom:4px}
.tpts{font-family:var(--mono);font-size:9px;letter-spacing:0.12em;color:var(--muted);text-transform:uppercase;margin-bottom:18px}
.tperks{list-style:none;display:flex;flex-direction:column;gap:7px}
.tperks li{font-size:12.5px;color:var(--grey);display:flex;align-items:flex-start;gap:7px;line-height:1.4}
.tperks li::before{content:'›';color:var(--green);flex-shrink:0;font-family:var(--mono);margin-top:1px}

/* CTA */
.cta{padding:110px 60px;text-align:center;background:var(--dark);position:relative;overflow:hidden}
.ctabg{position:absolute;font-family:var(--display);font-size:260px;color:rgba(124,34,212,0.03);top:50%;left:50%;transform:translate(-50%,-50%);white-space:nowrap;pointer-events:none;letter-spacing:0.1em;line-height:1}
.ctatitle{font-family:var(--display);font-size:clamp(56px,8vw,108px);line-height:0.9;letter-spacing:0.04em;margin-bottom:18px;position:relative}
.cgreen{color:var(--black);-webkit-text-stroke:2.5px var(--green);text-shadow:0 0 50px rgba(57,255,20,0.45)}
.ctasub{font-family:var(--mono);font-size:12px;letter-spacing:0.14em;color:var(--muted);margin-bottom:44px;position:relative;text-transform:uppercase}
.ctabtns{display:flex;gap:14px;justify-content:center;position:relative}

/* AGE GATE */
.ag{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(6,6,8,0.97);backdrop-filter:blur(24px)}
.agi{max-width:400px;padding:0 24px;text-align:center;position:relative}
.agi::before{content:'';position:absolute;top:-80px;left:50%;transform:translateX(-50%);width:220px;height:220px;background:radial-gradient(circle,rgba(124,34,212,0.28) 0%,transparent 70%);pointer-events:none}
.agbrand{font-family:var(--display);font-size:28px;letter-spacing:0.08em;background:linear-gradient(90deg,var(--purple2),var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:28px}
.agtitle{font-family:var(--display);font-size:24px;color:var(--white);margin-bottom:10px;letter-spacing:0.03em}
.agsub{font-size:13px;color:var(--muted);margin-bottom:32px;line-height:1.6;font-weight:300}
.agbtns{display:flex;gap:12px;justify-content:center;margin-bottom:18px}
.agyes{padding:12px 34px;background:var(--purple);color:var(--white);border:none;font-family:var(--display);font-size:14px;cursor:pointer;border-radius:3px;box-shadow:0 0 22px rgba(124,34,212,0.45);transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 7px) 0,100% 7px,100% 100%,7px 100%,0 calc(100% - 7px))}
.agyes:hover{background:var(--purple2);box-shadow:0 0 38px rgba(124,34,212,0.75)}
.agno{padding:11px 22px;background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,0.1);font-family:var(--mono);font-size:11px;cursor:pointer;border-radius:3px;letter-spacing:0.08em;transition:all 0.2s}
.agno:hover{border-color:rgba(255,255,255,0.22);color:var(--grey)}
.aglegal{font-size:10.5px;color:rgba(255,255,255,0.14);line-height:1.6;font-family:var(--mono)}

/* FOOTER */
footer{background:var(--dark2);padding:56px 60px 28px;border-top:1px solid rgba(124,34,212,0.22)}
.fgrid{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px}
.flogo{font-family:var(--display);font-size:20px;letter-spacing:0.06em;background:linear-gradient(90deg,var(--purple2),var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:10px}
.fdesc{font-size:12.5px;color:var(--muted);line-height:1.7;max-width:210px;font-weight:300}
.fct{font-family:var(--mono);font-size:8.5px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(255,255,255,0.22);margin-bottom:14px}
.flinks{list-style:none;display:flex;flex-direction:column;gap:9px}
.flinks a{font-size:12.5px;color:var(--muted);text-decoration:none;transition:color 0.2s;font-family:var(--mono);letter-spacing:0.04em}
.flinks a:hover{color:var(--green)}
.fbot{display:flex;align-items:center;justify-content:space-between;padding-top:22px;border-top:1px solid rgba(124,34,212,0.18)}
.flegal{font-family:var(--mono);font-size:10px;color:rgba(255,255,255,0.15);letter-spacing:0.04em}
.fage{font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:0.12em;opacity:0.65}

/* Reveal */
.reveal{opacity:0;transform:translateY(24px);transition:opacity 0.65s ease,transform 0.65s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
.rd1{transition-delay:0.1s}.rd2{transition-delay:0.2s}.rd3{transition-delay:0.3s}.rd4{transition-delay:0.4s}
</style>
</head>
<body>

<div class="ag" id="ag">
  <div class="agi">
    <div class="agbrand">VAPOUR GARAGE</div>
    <h2 class="agtitle">Are you 18 or older?</h2>
    <p class="agsub">Vapour Garage sells nicotine &amp; tobacco products.<br>You must be of legal age to enter. Est. 2023.</p>
    <div class="agbtns">
      <button class="agyes" onclick="enter()">Yes, I'm 18+</button>
      <button class="agno" onclick="exit_()">Exit</button>
    </div>
    <p class="aglegal">By entering you confirm legal age in your region.<br>Compliant with Philippine Republic Act No. 11467.</p>
  </div>
</div>

<nav id="nav">
  <div class="nbrand">VAPOUR GARAGE</div>
  <div class="nlinks">
    <a href="#products">Products</a>
    <a href="#branches">Locations</a>
    <a href="#loyalty">Rewards</a>
    <a href="{{ route('login') }}" class="ncta">Staff Login</a>
  </div>
</nav>

<section class="hero">
  <div class="hgrid"></div>
  <div class="horb1"></div><div class="horb2"></div>
  <div class="hcorn1"></div><div class="hcorn2"></div>
  <div class="hc">
    <div class="hest"><span class="hest-dot"></span>EST. 2023 · NUEVA ECIJA PH</div>
    <h1 class="htitle">
      <span class="ht1">VAPOUR</span>
      <span class="ht2">GARAGE</span>
    </h1>
    <p class="htag">Dispods &nbsp;·&nbsp; EJuice &nbsp;·&nbsp; Mods &nbsp;·&nbsp; Wires</p>
    <div class="hbtns">
      <a href="#products" class="btn1">
        <svg style="width:13px;height:13px;fill:none;stroke:#fff;stroke-width:2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8"/></svg>
        Browse Products
      </a>
      <a href="#loyalty" class="btn2">Join Rewards Program</a>
    </div>
  </div>
  <div class="hbranches">
    @foreach($branches as $index => $branch)
          <div class="hbi">
              
              {{-- Animated dot --}}
              <span class="hdot" style="animation-delay: {{ $index * 0.3 }}s"></span>

              {{-- Branch name --}}
              <span class="hbn">
                  {{ str_replace('Vapour Garage - ', '', $branch['name']) }}
              </span>

              {{-- Status --}}
              <span class="ticker-status">
                {{ $branch['is_active'] == 1 && now()->hour >= 10 && now()->hour < 21 ? 'Open now' : 'Closed' }}
              </span>

          </div>
      @endforeach
  </div>
</section>

<div class="mwrap">
  <div class="mtrack">
    @foreach(range(0,1) as $_)
      <span class="mi">DISPODS</span><span class="ms">✦</span>
      <span class="mi">EJUICE</span><span class="ms">✦</span>
      <span class="mi">BOX MODS</span><span class="ms">✦</span>
      <span class="mi">WIRES</span><span class="ms">✦</span>
      <span class="mi">SALT NIC</span><span class="ms">✦</span>
      <span class="mi">COILS</span><span class="ms">✦</span>
      <span class="mi">BATTERIES</span><span class="ms">✦</span>
      <span class="mi">ACCESSORIES</span><span class="ms">✦</span>
      <span class="mi">LOYALTY REWARDS</span><span class="ms">✦</span>
    @endforeach
  </div>
</div>

<section class="sec">
  <div class="stag reveal">Numbers</div>
  <h2 class="stitle reveal" style="color:var(--white)">BY THE <span style="color:var(--green)">NUMBERS</span></h2>
  <div class="srow">
    <div class="sbox reveal rd1"><div class="snum">4+</div><div class="slbl">Locations</div></div>
    <div class="sbox reveal rd2"><div class="snum">200+</div><div class="slbl">Products</div></div>
    <div class="sbox reveal rd3"><div class="snum">10k+</div><div class="slbl">Members</div></div>
    <div class="sbox reveal rd4"><div class="snum">2023</div><div class="slbl">Est.</div></div>
  </div>
</section>

<section class="sec pbg" id="products">
  <div class="stag reveal">Catalogue</div>
  <h2 class="stitle reveal" style="color:var(--white)">WHAT WE <span style="color:var(--green)">CARRY</span></h2>
  <div class="pgrid">
    @foreach([
      ['01','Disposables','Dispods','Single-use 3000–8000 puff devices. Ready to vape, zero setup.','from ₱280'],
      ['02','Pod Systems','Pods','Refillable and closed pod kits for smooth, consistent hits.','from ₱1,200'],
      ['03','Box Mods','Mods','High-wattage regulated and unregulated mods for advanced vapers.','from ₱2,800'],
      ['04','Salt Nicotine','EJuice','30–50mg salt nic juices. Smooth, fast-absorbing, flavorful.','from ₱380'],
      ['05','Freebase','EJuice','Classic 0–6mg freebase e-liquids for big clouds and flavor.','from ₱500'],
      ['06','Coils & Pods','Replacements','Compatible coils and pods for all major device brands.','from ₱160'],
      ['07','Wire','Wires','Kanthal, NiChrome, SS316L, Clapton. All gauges available.','from ₱80'],
      ['08','Accessories','Extras','Drip tips, USB-C cables, cotton, battery wraps, and more.','from ₱50'],
    ] as [$n,$name,$cat,$desc,$price])
    <div class="pc reveal">
      <div class="ctlc"></div><div class="cbrc"></div>
      <div class="pcnum">{{ $n }}</div>
      <div class="pcat">{{ $cat }}</div>
      <div class="pname">{{ $name }}</div>
      <div class="pdesc">{{ $desc }}</div>
      <div class="pprice">{{ $price }} <span>/ item</span></div>
    </div>
    @endforeach
  </div>
  <div class="justify-end">
    <a href="{{ route('shop.index') }}" class="btn1">
    <svg style="width:13px;height:13px;fill:none;stroke:#fff;stroke-width:2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8"/></svg>
    Browse in Shop
  </a>
  </div>
</section>

<section class="sec" id="branches">
  <div class="stag reveal">Locations</div>
  <h2 class="stitle reveal" style="color:var(--white)">FIND YOUR <span style="color:var(--purple2)">BRANCH</span></h2>
  <div class="bgrd">
    @foreach($branches as $index => $branch)
    <div class="bc reveal">
      <div class="bcnum">{{ $index+1 }}</div>
      <div>
        <div class="bcname">{{ $branch->name }}</div>
        <div class="bcaddr">{{ $branch->city }}</div>
        <div class="bchours">10AM - 9PM Daily</div>
      </div>
    </div>
    @endforeach
  </div>
</section>

<section class="sec lbg" id="loyalty">
  <div class="stag reveal">Rewards</div>
  <h2 class="stitle reveal" style="color:var(--white)">EARN MORE. <span style="color:var(--green)">SAVE MORE.</span></h2>
  <p class="reveal" style="font-family:var(--mono);font-size:12px;color:var(--muted);margin-top:14px;letter-spacing:0.08em">
    1 point per ₱10 spent · Unlock exclusive perks as you level up
  </p>
  <div class="tgrid">
    @foreach([
      ['br','B','Bronze','0 – 999 pts',['Earn 1pt per ₱10','Birthday discount','Early sale access']],
      ['si','S','Silver','1,000 – 4,999',['5% off every order','Priority service','Monthly promos']],
      ['go','G','Gold','5,000 – 9,999',['10% off all items','Free coil monthly','VIP event access']],
      ['pl','P','Platinum','10,000+ pts',['15% off all items','Free device yearly','Exclusive drops first']],
    ] as [$cls,$icon,$name,$pts,$perks])
    <div class="tc {{ $cls === 'pl' ? 'plat' : '' }} reveal">
      <div class="tglow"></div>
      <div class="tico {{ $cls }}">{{ $icon }}</div>
      <div class="tname">{{ $name }}</div>
      <div class="tpts">{{ $pts }}</div>
      <ul class="tperks">
        @foreach($perks as $p)<li>{{ $p }}</li>@endforeach
      </ul>
    </div>
    @endforeach
  </div>
</section>

<section class="cta">
  <div class="ctabg">VGM</div>
  <h2 class="ctatitle reveal">VISIT US<br><span class="cgreen">TODAY</span></h2>
  <p class="ctasub reveal">4 locations across NUEVA ECIJA — always stocked, always ready</p>
  <div class="ctabtns reveal">
    <a href="#branches" class="btn1">Find a Branch</a>
    <a href="#loyalty" class="btn2">Join Rewards</a>
  </div>
</section>

<footer>
  <div class="fgrid">
    <div>
      <div class="flogo">VAPOUR GARAGE MAIN</div>
      <p class="fdesc">NUEVA ECIJA's premier vape destination. Dispods · EJuice · Mods · Wires. Est. 2023.</p>
    </div>
    <div>
      <p class="fct">Navigation</p>
      <ul class="flinks">
        <li><a href="#products">Products</a></li>
        <li><a href="#branches">Locations</a></li>
        <li><a href="#loyalty">Rewards</a></li>
        <li><a href="{{ route('login') }}">Staff Login</a></li>
      </ul>
    </div>
    <div>
      <p class="fct">Branches</p>
      <ul class="flinks">
        @foreach($branches as $index => $branch)
          <li><a href="#branches">{{ $branch->city }}</a></li>
        @endforeach
      </ul>
    </div>
    <div>
      <p class="fct">Legal</p>
      <ul class="flinks">
        <li><a href="#">Age Policy (18+)</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">RA 11467 Compliance</a></li>
        <li><a href="#">Terms of Use</a></li>
      </ul>
    </div>
  </div>
  <div class="fbot">
    <p class="flegal">© {{ date('Y') }} Vapour Garage Main. All rights reserved. 18+ only.</p>
    <p class="fage">// NICOTINE IS ADDICTIVE — 18+ ONLY</p>
  </div>
</footer>

<script>
function enter(){sessionStorage.setItem('vgm','1');document.getElementById('ag').style.display='none'}
function exit_(){document.body.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100vh;background:#060608;font-family:monospace;color:#7a7490;font-size:12px;letter-spacing:0.1em">YOU MUST BE 18+ TO VIEW THIS SITE</div>'}
if(sessionStorage.getItem('vgm'))document.getElementById('ag').style.display='none';
window.addEventListener('scroll',()=>document.getElementById('nav').classList.toggle('scrolled',scrollY>60));
const io=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting)x.target.classList.add('visible')}),{threshold:0.08});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
</script>
</body>
</html>
