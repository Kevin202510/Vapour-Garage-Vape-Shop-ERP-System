<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'Shop' }} — Vapour Garage</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:ital,wght@0,300;0,400;0,500;0,600;0,800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --black:#060608;--dark:#0d0b14;--dark2:#130f1e;--dark3:#1a1428;
  --purple:#7c22d4;--purple2:#9b3df0;--purple3:#5b16a0;
  --green:#39ff14;--green2:#25cc0a;
  --white:#ffffff;--grey:#c8c4d4;--muted:#7a7490;
  --ok:#39ff14;--warn:#c9943a;--danger:#e05050;
}
html{scroll-behavior:smooth;overflow-x:hidden}
body{background:var(--black);color:var(--grey);font-family:'Exo 2',sans-serif;min-height:100vh}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(124,34,212,0.012) 3px,rgba(124,34,212,0.012) 4px)}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;background:linear-gradient(rgba(124,34,212,0.035) 1px,transparent 1px),linear-gradient(90deg,rgba(124,34,212,0.035) 1px,transparent 1px);background-size:48px 48px}

/* NAV */
.shop-nav{position:sticky;top:0;z-index:200;background:rgba(6,6,8,0.95);border-bottom:1px solid rgba(124,34,212,0.3);backdrop-filter:blur(16px);display:flex;align-items:center;justify-content:space-between;padding:0 40px;height:60px;flex-wrap:wrap}
.snav-brand{display:flex;flex-direction:column;text-decoration:none;white-space:nowrap}
.snav-brand-main{font-family:'Russo One',sans-serif;font-size:16px;letter-spacing:0.08em;background:linear-gradient(90deg,#9b3df0,#39ff14);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1}
.snav-brand-sub{font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.2em;color:rgba(57,255,20,0.5);text-transform:uppercase;margin-top:2px}
.snav-links{display:flex;align-items:center;gap:28px}
.snav-links a{font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.1em;color:var(--muted);text-decoration:none;text-transform:uppercase;transition:color 0.2s}
.snav-links a:hover,.snav-links a.active{color:var(--green)}
.snav-actions{display:flex;align-items:center;gap:14px}
.snav-right{display:flex;align-items:center;gap:14px}
.snav-cart{position:relative;display:flex;align-items:center;gap:7px;padding:8px 16px;background:var(--purple);color:#fff;font-family:'Russo One',sans-serif;font-size:12px;letter-spacing:0.06em;border-radius:3px;text-decoration:none;transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 5px) 0,100% 5px,100% 100%,5px 100%,0 calc(100% - 5px))}
.snav-cart:hover{background:var(--purple2);box-shadow:0 0 24px rgba(124,34,212,0.5)}
.cart-count{position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:var(--green);color:var(--black);font-family:'Share Tech Mono',monospace;font-size:9px;display:flex;align-items:center;justify-content:center;font-weight:bold;box-shadow:0 0 8px var(--green)}
.snav-login{font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.1em;color:var(--muted);text-decoration:none;text-transform:uppercase;transition:color 0.2s}
.snav-login:hover{color:var(--purple2)}

/* Hamburger (hidden on desktop) */
.snav-hamburger{display:none;align-items:center;justify-content:center;width:34px;height:34px;background:none;border:none;cursor:pointer;color:var(--muted)}
.snav-hamburger:hover{color:var(--green)}
.snav-hamburger span{display:flex;flex-direction:column;gap:4px}
.snav-hamburger .bar1,.snav-hamburger .bar2,.snav-hamburger .bar3{display:block;width:20px;height:2px;background:currentColor;border-radius:2px;transition:transform .25s,opacity .2s;transform-origin:center}
.snav-hamburger.is-open .bar1{transform:translateY(6px) rotate(45deg)}
.snav-hamburger.is-open .bar2{opacity:0}
.snav-hamburger.is-open .bar3{transform:translateY(-6px) rotate(-45deg)}

@media(max-width:767px){
    .shop-nav{padding:0 16px;height:auto;min-height:56px}
    .snav-brand-main{font-size:14px}
    .snav-actions{gap:8px}
    .snav-right{gap:8px}
    .snav-hamburger{display:flex}
    .snav-cart{padding:7px 12px;font-size:0}
    .snav-cart svg{margin:0}
    .snav-login{display:none}
    /* Links collapse into a full-width dropdown below the top row, with its
       own opaque background so — unlike the landing-page nav bug this
       mirrors the fix for — it never blends into page content behind it. */
    .snav-links{
        display:none;order:4;width:100%;flex-direction:column;align-items:stretch;
        gap:0;padding:6px 0 14px;margin-top:10px;
        border-top:1px solid rgba(124,34,212,0.15);
        background:rgba(6,6,8,0.98);backdrop-filter:blur(20px);
    }
    .snav-links.nav-open{display:flex}
    .snav-links a{padding:11px 4px;font-size:13px;border-bottom:1px solid rgba(124,34,212,0.08)}
    .snav-links a:last-child{border-bottom:none}
    .snav-login-mobile{display:block!important;text-align:center;margin-top:8px;padding:10px;border:1px solid rgba(124,34,212,0.3);border-radius:3px}

    /* Shop page header row (title + filter form) — the row itself didn't
       wrap, and the search/category/sort inputs had fixed 160-180px widths
       that don't shrink, so the filter form ran off the right edge of the
       screen instead of stacking under the title. */
    .shop-header-row{flex-wrap:wrap!important;row-gap:20px}
    .shop-filter-form{width:100%!important}
    .shop-filter-form>div{flex:1!important;min-width:130px}
    .shop-filter-form input.vinput,.shop-filter-form select.vinput{width:100%!important}
}

/* CONTENT WRAPPER */
.shop-wrap{position:relative;z-index:10;min-height:calc(100vh - 60px)}

/* FLASH */
.flash{padding:12px 20px;border-radius:4px;margin-bottom:20px;font-family:'Share Tech Mono',monospace;font-size:12px;letter-spacing:0.04em}
.flash-ok{background:rgba(57,255,20,0.08);border:1px solid rgba(57,255,20,0.3);color:var(--green)}
.flash-err{background:rgba(224,80,80,0.08);border:1px solid rgba(224,80,80,0.3);color:var(--danger)}

/* BTN styles */
.vbtn{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;font-family:'Russo One',sans-serif;font-size:13px;letter-spacing:0.06em;text-transform:uppercase;border:none;border-radius:3px;cursor:pointer;text-decoration:none;transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 5px) 0,100% 5px,100% 100%,5px 100%,0 calc(100% - 5px))}
.vbtn-primary{background:var(--purple);color:#fff;box-shadow:0 0 20px rgba(124,34,212,0.35)}
.vbtn-primary:hover{background:var(--purple2);box-shadow:0 0 36px rgba(124,34,212,0.6);transform:translateY(-1px)}
.vbtn-outline{background:transparent;color:var(--green);border:1px solid rgba(57,255,20,0.35)}
.vbtn-outline:hover{background:rgba(57,255,20,0.08);box-shadow:0 0 16px rgba(57,255,20,0.2)}
.vbtn-ghost{background:transparent;color:var(--muted);border:1px solid rgba(124,34,212,0.25)}
.vbtn-ghost:hover{border-color:rgba(124,34,212,0.5);color:var(--grey)}
.vbtn-sm{padding:7px 14px;font-size:11px}

/* FORM inputs */
.vinput{width:100%;background:#0d0b14;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:10px 14px;font-family:'Exo 2',sans-serif;font-size:13px;color:#e8e4f0;outline:none;caret-color:var(--green);transition:all 0.2s}
.vinput:focus{border-color:rgba(124,34,212,0.7);box-shadow:0 0 12px rgba(124,34,212,0.2)}
.vselect{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%237a7490' stroke-width='1.5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px;cursor:pointer}
.vlabel{display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:7px}

/* FOOTER */
.shop-footer{border-top:1px solid rgba(124,34,212,0.2);padding:32px 40px;display:flex;align-items:center;justify-content:space-between;margin-top:80px}
.sfooter-brand{font-family:'Russo One',sans-serif;font-size:14px;background:linear-gradient(90deg,#9b3df0,#39ff14);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.sfooter-legal{font-family:'Share Tech Mono',monospace;font-size:10px;color:rgba(255,255,255,0.15);letter-spacing:0.06em}
.sfooter-age{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--green);letter-spacing:0.1em;opacity:0.6}

/* Badges */
.vbadge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;text-transform:uppercase}
.vbadge-ok{background:rgba(57,255,20,0.1);color:var(--green);border:1px solid rgba(57,255,20,0.3)}
.vbadge-warn{background:rgba(201,148,58,0.1);color:var(--warn);border:1px solid rgba(201,148,58,0.3)}
.vbadge-danger{background:rgba(224,80,80,0.1);color:var(--danger);border:1px solid rgba(224,80,80,0.3)}
.vbadge-purple{background:rgba(124,34,212,0.15);color:var(--purple2);border:1px solid rgba(124,34,212,0.35)}
.vbadge-muted{background:rgba(255,255,255,0.06);color:var(--muted);border:1px solid rgba(255,255,255,0.1)}

/* SECTION HEADER */
.sec-tag{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.24em;text-transform:uppercase;color:var(--green);margin-bottom:10px;display:flex;align-items:center;gap:8px}
.sec-tag::before{content:'//';opacity:0.4}
.sec-title{font-family:'Russo One',sans-serif;font-size:clamp(28px,4vw,48px);letter-spacing:0.04em;color:#e8e4f0;line-height:0.95;text-transform:uppercase}
</style>

{{-- NAV --}}
<nav class="shop-nav" id="shop-nav">
    <a href="{{ route('landing') }}" class="snav-brand">
        <span class="snav-brand-main">VAPOUR GARAGE</span>
        <span class="snav-brand-sub">Est. 2023 · Online Shop</span>
    </a>

    <div class="snav-links" id="snav-links">
        <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.index') ? 'active' : '' }}">Shop</a>
        <a href="{{ route('shop.index', ['category' => 'dispods']) }}">Dispods</a>
        <a href="{{ route('shop.index', ['category' => 'ejuice']) }}">EJuice</a>
        <a href="{{ route('shop.index', ['category' => 'mods']) }}">Mods</a>
        <a href="{{ route('shop.track') }}">Track Order</a>
        <a href="{{ route('login') }}" class="snav-login-mobile" style="display:none">Staff Login</a>
    </div>

    {{-- Cart + hamburger grouped together so space-between treats them as
         one unit on the right, instead of pushing the hamburger away to the
         far edge with a gap between it and the cart. --}}
    <div class="snav-actions">
        <div class="snav-right">
            @php $cartCount = array_sum(session('vgm_cart', [])); @endphp
            <a href="{{ route('shop.cart') }}" class="snav-cart">
                <svg style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                CART
                @if($cartCount > 0)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>
            <a href="{{ route('login') }}" class="snav-login">Staff Login</a>
        </div>

        {{-- Hamburger (mobile only) --}}
        <button type="button" class="snav-hamburger" id="snav-hamburger" aria-label="Menu">
            <span>
                <span class="bar1"></span>
                <span class="bar2"></span>
                <span class="bar3"></span>
            </span>
        </button>
    </div>
</nav>

<script>
(function () {
    var nav       = document.getElementById('shop-nav');
    var hamburger = document.getElementById('snav-hamburger');
    var links     = document.getElementById('snav-links');
    if (!hamburger || !links) return;

    function closeMenu() {
        links.classList.remove('nav-open');
        hamburger.classList.remove('is-open');
    }

    hamburger.addEventListener('click', function () {
        var isOpen = links.classList.toggle('nav-open');
        hamburger.classList.toggle('is-open', isOpen);
    });

    links.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', function (e) {
        if (!links.contains(e.target) && !hamburger.contains(e.target)) closeMenu();
    });
})();
</script>

<div class="shop-wrap">
