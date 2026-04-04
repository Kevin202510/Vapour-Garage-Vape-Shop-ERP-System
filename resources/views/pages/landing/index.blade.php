<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VapeHub — Premium Vape Shop · Nueva Ecija</title>
<meta name="description" content="Nueva Ecija's finest vape shop. 4 locations, 200+ products, loyalty rewards. Disposables, pod systems, mods, and premium e-liquids.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300;1,9..144,700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0c0b09;
  --ink2:#1e1c18;
  --ink3:#3d3a32;
  --muted:#7a7568;
  --paper:#f5f0e8;
  --paper2:#ede7d9;
  --accent:#e8522a;
  --accent2:#f2854a;
  --gold:#c9943a;
  --white:#ffffff;
  --display:'Bebas Neue',sans-serif;
  --serif:'Fraunces',serif;
  --body:'DM Sans',sans-serif;
}
html{scroll-behavior:smooth;overflow-x:hidden}
body{background:var(--ink);color:var(--white);font-family:var(--body);font-size:15px;line-height:1.6;overflow-x:hidden}

/* ── Noise grain ── */
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:999;opacity:0.04;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  background-size:150px}

/* ── NAV ── */
nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:20px 60px;background:transparent;transition:background 0.3s,backdrop-filter 0.3s}
nav.scrolled{background:rgba(12,11,9,0.85);backdrop-filter:blur(16px)}
.nav-logo{font-family:var(--display);font-size:26px;letter-spacing:0.06em;color:var(--white)}
.nav-logo span{color:var(--accent)}
.nav-links{display:flex;align-items:center;gap:32px}
.nav-links a{font-size:13px;font-weight:400;color:rgba(255,255,255,0.6);text-decoration:none;letter-spacing:0.04em;transition:color 0.2s}
.nav-links a:hover{color:var(--white)}
.nav-cta{padding:9px 22px;background:var(--accent);color:var(--white);border:none;border-radius:6px;font-size:13px;font-weight:500;font-family:var(--body);cursor:pointer;text-decoration:none;transition:all 0.2s}
.nav-cta:hover{background:var(--accent2);transform:translateY(-1px)}

/* ── HERO ── */
.hero{min-height:100vh;display:flex;flex-direction:column;justify-content:flex-end;padding:0 60px 80px;position:relative;overflow:hidden}

/* Background layers */
.hero-bg{position:absolute;inset:0;z-index:0}
.hero-bg-img{width:100%;height:100%;object-fit:cover;opacity:0.25;filter:saturate(0.4) contrast(1.1)}
.hero-gradient{position:absolute;inset:0;background:linear-gradient(to top,var(--ink) 30%,rgba(12,11,9,0.4) 70%,rgba(12,11,9,0.2))}
.hero-gradient2{position:absolute;inset:0;background:radial-gradient(ellipse at 70% 50%,rgba(232,82,42,0.12) 0%,transparent 60%)}

/* Hero text */
.hero-content{position:relative;z-index:1;max-width:900px}
.hero-eyebrow{font-size:11px;font-family:var(--body);font-weight:500;letter-spacing:0.22em;text-transform:uppercase;color:var(--accent);margin-bottom:20px;display:flex;align-items:center;gap:10px}
.hero-eyebrow::before{content:'';display:inline-block;width:32px;height:1.5px;background:var(--accent)}
.hero-title{font-family:var(--display);font-size:clamp(72px,9vw,140px);line-height:0.92;letter-spacing:0.02em;margin-bottom:28px}
.hero-title .line1{display:block;color:var(--white)}
.hero-title .line2{display:block;color:var(--accent);-webkit-text-stroke:0px;letter-spacing:0.01em}
.hero-title .line3{display:block;color:rgba(255,255,255,0.2);font-size:0.72em}
.hero-sub{font-family:var(--serif);font-size:18px;font-weight:300;font-style:italic;color:rgba(255,255,255,0.55);max-width:480px;line-height:1.5;margin-bottom:44px}
.hero-actions{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.btn-primary{padding:15px 36px;background:var(--accent);color:var(--white);border:none;border-radius:6px;font-size:14px;font-weight:500;font-family:var(--body);cursor:pointer;text-decoration:none;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px}
.btn-primary:hover{background:var(--accent2);transform:translateY(-2px);box-shadow:0 8px 32px rgba(232,82,42,0.3)}
.btn-outline{padding:14px 32px;background:transparent;color:var(--white);border:1px solid rgba(255,255,255,0.25);border-radius:6px;font-size:14px;font-family:var(--body);cursor:pointer;text-decoration:none;transition:all 0.2s}
.btn-outline:hover{border-color:rgba(255,255,255,0.6);background:rgba(255,255,255,0.05)}

/* Live branch ticker */
.branch-ticker{position:relative;z-index:1;display:flex;gap:28px;margin-top:60px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.08)}
.ticker-branch{display:flex;align-items:center;gap:8px;font-size:12px;color:rgba(255,255,255,0.4);font-family:var(--body)}
.ticker-dot{width:6px;height:6px;border-radius:50%;background:#3a7a4a;box-shadow:0 0 8px #3a7a4a;flex-shrink:0;animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:0.4}}
.ticker-name{color:rgba(255,255,255,0.7);font-weight:500}

/* ── MARQUEE ── */
.marquee-wrap{overflow:hidden;background:var(--accent);padding:14px 0;border-top:none}
.marquee-track{display:flex;gap:0;animation:marquee 24s linear infinite;white-space:nowrap}
.marquee-item{font-family:var(--display);font-size:15px;letter-spacing:0.12em;color:var(--white);padding:0 32px;opacity:0.85}
.marquee-sep{color:rgba(255,255,255,0.4);padding:0 8px}
@keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── ABOUT / STATS ── */
.section{padding:100px 60px}
.section-label{font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:var(--accent);margin-bottom:16px;font-weight:500}

.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid rgba(255,255,255,0.08);border-radius:12px;overflow:hidden;margin-top:64px}
.stat-item{padding:48px 36px;border-right:1px solid rgba(255,255,255,0.08);position:relative;overflow:hidden;transition:background 0.3s}
.stat-item:last-child{border-right:none}
.stat-item:hover{background:rgba(255,255,255,0.02)}
.stat-num{font-family:var(--display);font-size:68px;line-height:1;color:var(--white);letter-spacing:-0.01em;margin-bottom:8px}
.stat-num span{color:var(--accent)}
.stat-label{font-size:12px;color:var(--muted);letter-spacing:0.06em;text-transform:uppercase;font-weight:500}
.stat-sub{font-family:var(--serif);font-size:13px;font-style:italic;color:rgba(255,255,255,0.3);margin-top:6px}

/* About split */
.about-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
.about-title{font-family:var(--display);font-size:clamp(48px,5vw,80px);line-height:0.95;letter-spacing:0.03em;margin-bottom:28px}
.about-title em{font-family:var(--serif);font-style:italic;color:var(--accent);font-size:0.9em}
.about-body{font-size:15px;color:rgba(255,255,255,0.55);line-height:1.8;margin-bottom:32px;font-weight:300}
.about-image-wrap{position:relative;border-radius:8px;overflow:hidden;aspect-ratio:4/5;background:var(--ink2)}
.about-image-placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px}
.about-image-placeholder svg{opacity:0.15}
.about-img-text{font-family:var(--display);font-size:80px;color:rgba(255,255,255,0.06);position:absolute;bottom:24px;left:24px;line-height:1}

/* ── PRODUCTS ── */
.products-section{background:var(--ink2);padding:100px 60px}
.products-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:48px}
.products-title{font-family:var(--display);font-size:clamp(48px,5vw,72px);line-height:0.95;letter-spacing:0.03em}
.products-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.product-card{background:var(--ink);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:28px;cursor:pointer;transition:all 0.25s;position:relative;overflow:hidden}
.product-card:hover{border-color:var(--accent);background:#111008;transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,0.4)}
.product-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--accent);transform:scaleX(0);transition:transform 0.25s;transform-origin:left}
.product-card:hover::before{transform:scaleX(1)}
.product-card-cat{font-size:10px;letter-spacing:0.16em;text-transform:uppercase;color:var(--muted);margin-bottom:14px;font-weight:500}
.product-card-name{font-family:var(--serif);font-size:18px;font-weight:700;color:var(--white);margin-bottom:8px;line-height:1.2}
.product-card-desc{font-size:12px;color:rgba(255,255,255,0.4);line-height:1.6;margin-bottom:20px;font-weight:300}
.product-card-price{font-family:var(--display);font-size:28px;color:var(--accent);letter-spacing:0.02em}
.product-card-price span{font-family:var(--body);font-size:12px;color:var(--muted);margin-left:4px;letter-spacing:normal}

/* ── BRANCHES ── */
.branches-section{padding:100px 60px}
.branches-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:56px}
.branch-card{border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:36px;display:flex;gap:24px;cursor:pointer;transition:all 0.2s;position:relative;overflow:hidden}
.branch-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--accent);transform:scaleX(0);transition:transform 0.3s;transform-origin:left}
.branch-card:hover{background:rgba(255,255,255,0.02);border-color:rgba(255,255,255,0.14)}
.branch-card:hover::after{transform:scaleX(1)}
.branch-num{font-family:var(--display);font-size:40px;color:rgba(255,255,255,0.06);flex-shrink:0;line-height:1;margin-top:4px}
.branch-info{flex:1}
.branch-name{font-family:var(--serif);font-size:20px;font-weight:700;color:var(--white);margin-bottom:4px}
.branch-addr{font-size:13px;color:var(--muted);margin-bottom:14px;line-height:1.5}
.branch-hours{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:500;letter-spacing:0.06em;padding:4px 10px;background:rgba(58,122,74,0.15);color:#6ab87a;border-radius:20px}
.branch-hours::before{content:'';width:5px;height:5px;border-radius:50%;background:#6ab87a;flex-shrink:0}

/* ── LOYALTY ── */
.loyalty-section{background:var(--paper);padding:100px 60px}
.loyalty-section *{color:var(--ink)}
.loyalty-header{text-align:center;margin-bottom:64px}
.loyalty-title{font-family:var(--display);font-size:clamp(52px,6vw,88px);line-height:0.95;letter-spacing:0.03em;margin-bottom:16px}
.loyalty-title em{color:var(--accent)}
.loyalty-sub{font-family:var(--serif);font-size:18px;font-style:italic;color:var(--muted);font-weight:300}
.tiers-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.tier-card{border-radius:10px;padding:32px 28px;position:relative;overflow:hidden;border:1px solid rgba(12,11,9,0.1)}
.tier-card.bronze{background:#f5f0e8;border-color:#c9a882}
.tier-card.silver{background:#e8eef2;border-color:#8fa3b0}
.tier-card.gold{background:#fdf4e0;border-color:#c9943a}
.tier-card.platinum{background:#1e1c18;border-color:rgba(255,255,255,0.1)}
.tier-card.platinum *{color:var(--white)!important}
.tier-badge{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--display);font-size:20px;margin-bottom:20px}
.tier-card.bronze .tier-badge{background:#c9a882;color:#fff}
.tier-card.silver .tier-badge{background:#8fa3b0;color:#fff}
.tier-card.gold   .tier-badge{background:#c9943a;color:#fff}
.tier-card.platinum .tier-badge{background:var(--accent);color:#fff}
.tier-name{font-family:var(--display);font-size:28px;letter-spacing:0.04em;margin-bottom:4px}
.tier-pts{font-size:11px;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:20px;font-weight:500}
.tier-perks{list-style:none;display:flex;flex-direction:column;gap:8px}
.tier-perks li{font-size:13px;display:flex;align-items:flex-start;gap:8px;line-height:1.4}
.tier-perks li::before{content:'→';color:var(--accent);flex-shrink:0;font-size:11px;margin-top:2px}

/* ── AGE GATE ── */
.age-gate{position:fixed;inset:0;background:rgba(12,11,9,0.98);z-index:9999;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:0;backdrop-filter:blur(24px)}
.age-gate-box{text-align:center;max-width:440px;padding:0 24px}
.age-gate-logo{font-family:var(--display);font-size:36px;letter-spacing:0.06em;color:var(--white);margin-bottom:40px}
.age-gate-logo span{color:var(--accent)}
.age-gate-title{font-family:var(--serif);font-size:28px;font-style:italic;font-weight:300;color:rgba(255,255,255,0.85);margin-bottom:12px}
.age-gate-sub{font-size:13px;color:var(--muted);margin-bottom:40px;line-height:1.6}
.age-gate-btns{display:flex;gap:12px;justify-content:center;margin-bottom:20px}
.age-yes{padding:14px 40px;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:500;font-family:var(--body);cursor:pointer;transition:all 0.2s}
.age-yes:hover{background:var(--accent2)}
.age-no{padding:13px 32px;background:transparent;color:rgba(255,255,255,0.4);border:1px solid rgba(255,255,255,0.15);border-radius:6px;font-size:14px;font-family:var(--body);cursor:pointer;transition:all 0.2s}
.age-no:hover{border-color:rgba(255,255,255,0.3)}
.age-legal{font-size:11px;color:rgba(255,255,255,0.2);line-height:1.5}

/* ── CTA BANNER ── */
.cta-section{padding:100px 60px;text-align:center;position:relative;overflow:hidden}
.cta-section::before{content:'VAPEHUB';position:absolute;font-family:var(--display);font-size:280px;color:rgba(255,255,255,0.02);top:50%;left:50%;transform:translate(-50%,-50%);white-space:nowrap;letter-spacing:0.05em;pointer-events:none}
.cta-title{font-family:var(--display);font-size:clamp(56px,7vw,100px);line-height:0.92;letter-spacing:0.03em;margin-bottom:24px;position:relative}
.cta-title em{font-family:var(--serif);font-style:italic;color:var(--accent);display:block}
.cta-sub{font-family:var(--serif);font-size:17px;font-style:italic;color:rgba(255,255,255,0.4);margin-bottom:48px;position:relative}
.cta-btns{display:flex;gap:14px;justify-content:center;position:relative}

/* ── FOOTER ── */
footer{background:var(--ink2);padding:56px 60px 32px;border-top:1px solid rgba(255,255,255,0.06)}
.footer-grid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px}
.footer-brand-logo{font-family:var(--display);font-size:28px;letter-spacing:0.06em;color:var(--white);margin-bottom:12px}
.footer-brand-logo span{color:var(--accent)}
.footer-brand-desc{font-size:13px;color:var(--muted);line-height:1.6;max-width:240px}
.footer-col-title{font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:16px;font-weight:500}
.footer-links{list-style:none;display:flex;flex-direction:column;gap:10px}
.footer-links a{font-size:13px;color:var(--muted);text-decoration:none;transition:color 0.2s}
.footer-links a:hover{color:var(--white)}
.footer-bottom{display:flex;align-items:center;justify-content:space-between;padding-top:24px;border-top:1px solid rgba(255,255,255,0.06)}
.footer-legal{font-size:11px;color:rgba(255,255,255,0.2)}
.footer-age{font-size:11px;color:var(--accent);font-weight:500;letter-spacing:0.06em}

/* ── Scroll animations ── */
.reveal{opacity:0;transform:translateY(32px);transition:opacity 0.7s ease,transform 0.7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
.reveal-delay-1{transition-delay:0.1s}
.reveal-delay-2{transition-delay:0.2s}
.reveal-delay-3{transition-delay:0.3s}
.reveal-delay-4{transition-delay:0.4s}

/* Hero animations */
@keyframes fadeUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.hero-eyebrow{animation:fadeUp 0.8s ease 0.2s both}
.hero-title{animation:fadeUp 0.8s ease 0.35s both}
.hero-sub{animation:fadeUp 0.8s ease 0.5s both}
.hero-actions{animation:fadeUp 0.8s ease 0.65s both}
.branch-ticker{animation:fadeUp 0.8s ease 0.8s both}
.branch-ticker {
    display: flex;
    gap: 28px;
    align-items: center;
    overflow-x: auto;
    padding: 10px 0;
}

.ticker-branch {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    white-space: nowrap;
}

.ticker-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #4ade80;
    animation: pulse 1.5s infinite;
}

.ticker-name {
    font-weight: 500;
    color: #ffffff;
}

.ticker-status {
    color: rgba(255,255,255,0.4);
    font-size: 11px;
}

@keyframes pulse {
    0%   { opacity: 0.4; transform: scale(0.8); }
    50%  { opacity: 1;   transform: scale(1.2); }
    100% { opacity: 0.4; transform: scale(0.8); }
}
</style>
</head>
<body>

<!-- Age Gate -->
<div class="age-gate" id="ageGate">
    <div class="age-gate-box">
        <div class="age-gate-logo">Vape<span>Hub</span></div>
        <p class="age-gate-title">Are you 18 years or older?</p>
        <p class="age-gate-sub">VapeHub sells nicotine and tobacco products. You must be of legal age to enter this site.</p>
        <div class="age-gate-btns">
            <button class="age-yes" onclick="enterSite()">Yes, I am 18+</button>
            <button class="age-no" onclick="document.body.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;color:#666\'>You must be 18+ to view this site.</div>'">No, I am under 18</button>
        </div>
        <p class="age-legal">By entering, you confirm you are of legal age to purchase tobacco/nicotine products in your region. VapeHub complies with Philippine Republic Act 11467.</p>
    </div>
</div>

<!-- Nav -->
<nav id="mainNav">
    <div class="nav-logo">Vape<span>Hub</span></div>
    <div class="nav-links">
        <a href="#products">Products</a>
        <a href="#branches">Locations</a>
        <a href="#loyalty">Loyalty</a>
        <a href="{{ route('login') }}">Staff Login</a>
        <a href="#branches" class="nav-cta">Find a Store</a>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg">
        <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1208 0%,#0c0b09 40%,#1a0d08 100%)"></div>
        <!-- Geometric accent -->
        <div style="position:absolute;top:-10%;right:-5%;width:60%;height:120%;background:radial-gradient(ellipse,rgba(232,82,42,0.08) 0%,transparent 65%);pointer-events:none"></div>
        <div style="position:absolute;bottom:10%;left:-10%;width:40%;height:60%;background:radial-gradient(ellipse,rgba(201,148,58,0.06) 0%,transparent 65%);pointer-events:none"></div>
        <!-- Geometric lines -->
        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:0.04" viewBox="0 0 1440 900" preserveAspectRatio="none">
            <line x1="0" y1="900" x2="800" y2="0" stroke="white" stroke-width="1"/>
            <line x1="200" y1="900" x2="1000" y2="0" stroke="white" stroke-width="0.5"/>
            <line x1="1440" y1="0" x2="600" y2="900" stroke="white" stroke-width="1"/>
            <circle cx="900" cy="300" r="200" fill="none" stroke="white" stroke-width="0.5"/>
        </svg>
    </div>

    <div class="hero-content">
        <div class="hero-eyebrow">Nueva Ecija's Premium Vape Destination</div>
        <h1 class="hero-title">
            <span class="line1">ELEVATE</span>
            <span class="line2">YOUR VAPE</span>
            <span class="line3">EXPERIENCE</span>
        </h1>
        <p class="hero-sub">200+ curated products. 4 branch locations. Exclusive loyalty rewards. The finest devices, e-liquids, and accessories — all in one place.</p>
        <div class="hero-actions">
            <a href="#products" class="btn-primary">
                <svg style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8"/></svg>
                Browse Products
            </a>
            <a href="#loyalty" class="btn-outline">Join Loyalty Program</a>
        </div>
    </div>

    <div class="branch-ticker">
        @foreach($branches as $index => $branch)
            <div class="ticker-branch">
                
                {{-- Animated dot --}}
                <span class="ticker-dot" style="animation-delay: {{ $index * 0.3 }}s"></span>

                {{-- Branch name --}}
                <span class="ticker-name">
                    {{ str_replace('Vapour Garage - ', '', $branch['name']) }}
                </span>

                {{-- Status --}}
                <span class="ticker-status">
                    · {{ $branch['is_active'] ? 'Open now' : 'Closed' }}
                </span>

            </div>
        @endforeach
    </div>
</section>

<!-- Marquee -->
<div class="marquee-wrap">
    <div class="marquee-track">
        @foreach(range(0,1) as $_)
            <span class="marquee-item">DISPOSABLES</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">POD SYSTEMS</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">BOX MODS</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">SALT NICOTINE</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">FREEBASE E-LIQUID</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">REPLACEMENT COILS</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">ACCESSORIES</span><span class="marquee-sep">✦</span>
            <span class="marquee-item">LOYALTY REWARDS</span><span class="marquee-sep">✦</span>
        @endforeach
    </div>
</div>

<!-- Stats -->
<section class="section">
    <div class="stats-grid reveal">
        <div class="stat-item">
            <div class="stat-num">4<span>+</span></div>
            <div class="stat-label">Locations</div>
            <div class="stat-sub">Across Nueva Ecija</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">200<span>+</span></div>
            <div class="stat-label">Products</div>
            <div class="stat-sub">Curated catalogue</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">10<span>k+</span></div>
            <div class="stat-label">Members</div>
            <div class="stat-sub">Loyalty program</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">5<span>★</span></div>
            <div class="stat-label">Rated</div>
            <div class="stat-sub">Customer satisfaction</div>
        </div>
    </div>
</section>

<!-- Products -->
<section class="products-section" id="products">
    <div class="products-header reveal">
        <div>
            <p class="section-label" style="color:var(--accent)">Our Catalogue</p>
            <h2 class="products-title">SHOP BY<br>CATEGORY</h2>
        </div>
        <a href="{{ route('login') }}" class="btn-outline" style="flex-shrink:0">Staff POS →</a>
    </div>
    <div class="products-grid">
        @foreach([
            ['Disposables',    'Single-use devices, 3000–5000 puffs. Perfect for on-the-go.',  'from ₱280', '0.1s'],
            ['Pod Systems',    'Rechargeable pod kits for smooth, satisfying hits.',             'from ₱1,200','0.2s'],
            ['Box Mods',       'High-wattage mods for the advanced vaper.',                      'from ₱2,800','0.3s'],
            ['Salt Nicotine',  'Smooth, fast-absorbing 30–50mg salt nic juices.',               'from ₱380', '0.4s'],
            ['Freebase Juice', 'Classic 0–6mg freebase juices for cloud chasers.',              'from ₱500', '0.1s'],
            ['Coils & Parts',  'Replacement coils, pods, and rebuild materials.',               'from ₱160', '0.2s'],
            ['Batteries',      '18650 and 21700 cells from trusted brands.',                    'from ₱450', '0.3s'],
            ['Accessories',    'Drip tips, cables, cotton, wire and more.',                     'from ₱80',  '0.4s'],
        ] as [$cat, $desc, $price, $delay])
        <div class="product-card reveal" style="transition-delay:{{ $delay }}">
            <div class="product-card-cat">{{ $cat }}</div>
            <div class="product-card-name">{{ $cat }}</div>
            <div class="product-card-desc">{{ $desc }}</div>
            <div class="product-card-price">{{ $price }} <span>/ item</span></div>
        </div>
        @endforeach
    </div>
</section>

<!-- Branches -->
<section class="branches-section" id="branches">
    <p class="section-label reveal">Our Locations</p>
    <h2 style="font-family:var(--display);font-size:clamp(52px,6vw,80px);line-height:0.95;letter-spacing:0.03em;margin-bottom:0" class="reveal">FIND YOUR<br>NEAREST <em style="font-family:var(--serif);font-style:italic;color:var(--accent)">Branch</em></h2>
    <div class="branches-grid">
        @foreach($branches as $index => $branch)
        <div class="branch-card reveal">
            <div class="branch-num">{{ $index+1 }}</div>
            <div class="branch-info">
                <div class="branch-name">{{ $branch->name }}</div>
                <div class="branch-addr">{{ $branch->city }}</div>
                <div class="branch-hours">10AM - 9PM Daily</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Loyalty -->
<section class="loyalty-section" id="loyalty">
    <div class="loyalty-header reveal">
        <p class="section-label">Rewards Program</p>
        <h2 class="loyalty-title">EARN MORE.<br><em>SAVE MORE.</em></h2>
        <p class="loyalty-sub">1 point for every ₱10 spent. Unlock exclusive perks as you climb tiers.</p>
    </div>
    <div class="tiers-grid">
        @foreach([
            ['bronze',  'Bronze',   '0 – 999 pts',    ['Earn 1pt / ₱10', 'Birthday discount', 'Early access sales']],
            ['silver',  'Silver',   '1,000 – 4,999',  ['5% off every purchase', 'Priority service', 'Monthly promo access']],
            ['gold',    'Gold',     '5,000 – 9,999',  ['10% off all items', 'Free coil monthly', 'VIP event invites']],
            ['platinum','Platinum', '10,000+ pts',    ['15% off all items', 'Free device yearly', 'Exclusive drops first']],
        ] as [$cls, $name, $pts, $perks])
        <div class="tier-card {{ $cls }} reveal">
            <div class="tier-badge">{{ strtoupper(substr($name,0,1)) }}</div>
            <div class="tier-name">{{ $name }}</div>
            <div class="tier-pts">{{ $pts }}</div>
            <ul class="tier-perks">
                @foreach($perks as $perk)
                    <li>{{ $perk }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2 class="cta-title reveal">READY TO<br><em>Get Started?</em></h2>
    <p class="cta-sub reveal">Visit any of our 4 branches across Nueva Ecija.</p>
    <div class="cta-btns reveal">
        <a href="#branches" class="btn-primary">Find a Store</a>
        <a href="#loyalty" class="btn-outline">Join Loyalty Program</a>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="footer-grid">
        <div>
            <div class="footer-brand-logo">Vape<span>Hub</span></div>
            <p class="footer-brand-desc">Nueva Ecija's premier vape destination. Quality products, expert staff, unmatched loyalty rewards.</p>
        </div>
        <div>
            <p class="footer-col-title">Quick Links</p>
            <ul class="footer-links">
                <li><a href="#products">Products</a></li>
                <li><a href="#branches">Locations</a></li>
                <li><a href="#loyalty">Loyalty Program</a></li>
                <li><a href="{{ route('login') }}">Staff Login</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-col-title">Locations</p>
            <ul class="footer-links">
                <li><a href="#branches">Quezon City</a></li>
                <li><a href="#branches">Makati</a></li>
                <li><a href="#branches">Pasig</a></li>
                <li><a href="#branches">BGC · Taguig</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-col-title">Legal</p>
            <ul class="footer-links">
                <li><a href="#">Age Policy</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Use</a></li>
                <li><a href="#">RA 11467 Compliance</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p class="footer-legal">© {{ date('Y') }} VapeHub Philippines. All rights reserved. For 18+ only.</p>
        <p class="footer-age">⚠ NICOTINE IS ADDICTIVE — 18+ ONLY</p>
    </div>
</footer>

<script>
// Age gate
function enterSite() {
    sessionStorage.setItem('ageVerified', '1');
    document.getElementById('ageGate').style.display = 'none';
}
if (sessionStorage.getItem('ageVerified')) {
    document.getElementById('ageGate').style.display = 'none';
}

// Scroll nav
window.addEventListener('scroll', () => {
    document.getElementById('mainNav').classList.toggle('scrolled', scrollY > 60);
});

// Reveal on scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
