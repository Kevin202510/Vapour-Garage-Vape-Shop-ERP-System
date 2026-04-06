<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vapour Garage Main — Staff Login</title>
<link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full" style="background:#060608;font-family:'Exo 2',sans-serif">

{{-- Grid bg --}}
<div style="position:fixed;inset:0;background:linear-gradient(rgba(124,34,212,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(124,34,212,0.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;z-index:0"></div>

{{-- Purple orb --}}
<div style="position:fixed;top:-20%;left:-10%;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(124,34,212,0.2) 0%,transparent 65%);pointer-events:none;z-index:0"></div>
<div style="position:fixed;bottom:-10%;right:-5%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(57,255,20,0.06) 0%,transparent 65%);pointer-events:none;z-index:0"></div>

<div style="position:relative;z-index:10;min-height:100vh;display:grid;grid-template-columns:1fr 480px">

    {{-- Left brand panel --}}
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px;position:relative;overflow:hidden">
        {{-- Corner brackets --}}
        <div style="position:absolute;top:40px;right:40px;width:60px;height:60px;border-top:1.5px solid rgba(57,255,20,0.2);border-right:1.5px solid rgba(57,255,20,0.2)"></div>
        <div style="position:absolute;bottom:40px;left:40px;width:60px;height:60px;border-bottom:1.5px solid rgba(124,34,212,0.3);border-left:1.5px solid rgba(124,34,212,0.3)"></div>

        <div style="text-align:center;max-width:400px">
            <div style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.24em;text-transform:uppercase;color:rgba(57,255,20,0.5);margin-bottom:28px;display:flex;align-items:center;justify-content:center;gap:10px">
                <span style="color:rgba(57,255,20,0.3)">//</span> EST. 2023 · METRO MANILA
            </div>

            <h1 style="font-family:'Russo One',sans-serif;line-height:0.9;letter-spacing:0.04em;margin-bottom:24px">
                <span style="display:block;font-size:56px;color:#e8e4f0">VAPOUR</span>
                <span style="display:block;font-size:64px;background:linear-gradient(180deg,#9b3df0,#5b16a0);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;filter:drop-shadow(0 0 24px rgba(124,34,212,0.8))">GARAGE</span>
                <span style="display:block;font-size:72px;color:#060608;-webkit-text-stroke:2.5px #39ff14;text-shadow:0 0 36px rgba(57,255,20,0.4)">MAIN</span>
            </h1>

            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#7a7490;margin-bottom:40px">
                Dispods · EJuice · Mods · Wires
            </p>

            {{-- Live branch dots --}}
            <div style="display:flex;justify-content:center;gap:28px">
                @foreach(['QC','MKT','PSG','BGC'] as $b)
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px">
                    <span style="width:5px;height:5px;border-radius:50%;background:#39ff14;box-shadow:0 0 8px #39ff14;display:inline-block;animation:pulse2 2s infinite;animation-delay:{{ $loop->index * 0.3 }}s"></span>
                    <span style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:0.14em;color:rgba(255,255,255,0.2)">{{ $b }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right login panel --}}
    <div style="background:#0d0b14;border-left:1px solid rgba(124,34,212,0.25);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 48px;position:relative;overflow:hidden">

        {{-- Top accent --}}
        <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>

        <div style="width:100%;max-width:340px">

            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <span style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.22em;color:rgba(57,255,20,0.5)">//</span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7)">STAFF ACCESS</span>
            </div>
            <h2 style="font-family:'Russo One',sans-serif;font-size:24px;letter-spacing:0.06em;color:#e8e4f0;text-transform:uppercase;margin-bottom:4px">Sign In</h2>
            <p style="font-size:12px;color:#7a7490;margin-bottom:32px;font-family:'Share Tech Mono',monospace;letter-spacing:0.04em">Enter credentials to access ERP</p>

            @if($errors->any())
            <div style="margin-bottom:20px;padding:10px 14px;border-radius:4px;background:rgba(184,50,50,0.1);border:1px solid rgba(184,50,50,0.3);font-size:12px;color:#e05050;font-family:'Share Tech Mono',monospace">
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('status'))
            <div style="margin-bottom:20px;padding:10px 14px;border-radius:4px;background:rgba(57,255,20,0.08);border:1px solid rgba(57,255,20,0.25);font-size:12px;color:#39ff14;font-family:'Share Tech Mono',monospace">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf

                <div>
                    <label style="display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:7px">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="admin@vapourgarage.ph"
                           style="width:100%;background:#060608;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:10px 14px;font-family:'Exo 2',sans-serif;font-size:13px;color:#e8e4f0;outline:none;caret-color:#39ff14;transition:all 0.2s"
                           onfocus="this.style.borderColor='rgba(124,34,212,0.7)';this.style.boxShadow='0 0 12px rgba(124,34,212,0.2)'"
                           onblur="this.style.borderColor='rgba(124,34,212,0.3)';this.style.boxShadow='none'">
                </div>

                <div>
                    <label style="display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:7px">
                        Password
                    </label>
                    <input type="password" name="password" required placeholder="••••••••"
                           style="width:100%;background:#060608;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:10px 14px;font-family:'Exo 2',sans-serif;font-size:13px;color:#e8e4f0;outline:none;caret-color:#39ff14;transition:all 0.2s"
                           onfocus="this.style.borderColor='rgba(124,34,212,0.7)';this.style.boxShadow='0 0 12px rgba(124,34,212,0.2)'"
                           onblur="this.style.borderColor='rgba(124,34,212,0.3)';this.style.boxShadow='none'">
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="remember"
                               style="width:14px;height:14px;accent-color:#7c22d4;cursor:pointer">
                        <span style="font-size:12px;color:#7a7490;font-family:'Share Tech Mono',monospace;letter-spacing:0.04em">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-family:'Share Tech Mono',monospace;font-size:11px;color:rgba(124,34,212,0.7);text-decoration:none;letter-spacing:0.06em;transition:color 0.2s"
                       onmouseover="this.style.color='#9b3df0'" onmouseout="this.style.color='rgba(124,34,212,0.7)'">
                        Forgot?
                    </a>
                    @endif
                </div>

                <button type="submit"
                        style="width:100%;padding:12px;background:#7c22d4;color:#fff;font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.08em;text-transform:uppercase;border:none;border-radius:4px;cursor:pointer;box-shadow:0 0 24px rgba(124,34,212,0.4);transition:all 0.2s;margin-top:4px;clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px))"
                        onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 40px rgba(124,34,212,0.7)';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='#7c22d4';this.style.boxShadow='0 0 24px rgba(124,34,212,0.4)';this.style.transform=''">
                    Access ERP →
                </button>
            </form>

            <p style="text-align:center;font-family:'Share Tech Mono',monospace;font-size:10px;color:rgba(255,255,255,0.12);margin-top:32px;letter-spacing:0.06em">
                VGM ERP v2.1 · {{ now()->format('Y') }}
            </p>
        </div>
    </div>
</div>

<style>
@keyframes pulse2 { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(0.8)} }
</style>
</body>
</html>
