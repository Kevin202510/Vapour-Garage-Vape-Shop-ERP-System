<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vapour Garage Main — Two-Factor Authentication</title>
<link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full" style="background:#060608;font-family:'Exo 2',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh">

<div style="position:fixed;inset:0;background:linear-gradient(rgba(124,34,212,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(124,34,212,0.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none"></div>
<div style="position:fixed;top:-20%;right:-10%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(124,34,212,0.18) 0%,transparent 65%);pointer-events:none"></div>

<div style="position:relative;z-index:10;width:100%;max-width:420px;padding:20px">

    {{-- Brand --}}
    <div style="text-align:center;margin-bottom:32px">
        <div style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.08em;background:linear-gradient(90deg,#9b3df0,#39ff14);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px">
            VAPOUR GARAGE
        </div>
        <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;color:rgba(57,255,20,0.4);text-transform:uppercase">
            Two-Factor Authentication
        </div>
    </div>

    {{-- Card --}}
    <div style="background:#0d0b14;border:1px solid rgba(124,34,212,0.3);border-radius:8px;overflow:hidden">
        <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
        <div style="padding:32px">

            {{-- Shield icon --}}
            <div style="width:52px;height:52px;border-radius:12px;background:rgba(124,34,212,0.12);border:1px solid rgba(124,34,212,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <svg style="width:26px;height:26px;color:#9b3df0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>

            <h2 style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.05em;color:#e8e4f0;text-transform:uppercase;text-align:center;margin-bottom:8px">
                Verify Identity
            </h2>
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490;text-align:center;margin-bottom:28px;line-height:1.6;letter-spacing:0.04em">
                Open your authenticator app and enter<br>the 6-digit code for Vapour Garage ERP.
            </p>

            @if($errors->has('code'))
            <div style="margin-bottom:20px;padding:10px 14px;border-radius:4px;background:rgba(184,50,50,0.1);border:1px solid rgba(184,50,50,0.3);font-size:12px;color:#e05050;font-family:'Share Tech Mono',monospace;text-align:center">
                {{ $errors->first('code') }}
            </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf

                <div>
                    <label style="display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:8px;text-align:center">
                        Authentication Code
                    </label>
                    <input type="text" name="code" required autofocus
                           maxlength="8" autocomplete="one-time-code"
                           placeholder="000 000"
                           style="width:100%;background:#060608;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:14px;font-family:'Share Tech Mono',monospace;font-size:28px;color:#e8e4f0;outline:none;caret-color:#39ff14;transition:all 0.2s;text-align:center;letter-spacing:0.3em"
                           onfocus="this.style.borderColor='rgba(124,34,212,0.7)';this.style.boxShadow='0 0 16px rgba(124,34,212,0.2)'"
                           onblur="this.style.borderColor='rgba(124,34,212,0.3)';this.style.boxShadow='none'">
                </div>

                <button type="submit"
                        style="width:100%;padding:13px;background:#7c22d4;color:#fff;font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.08em;text-transform:uppercase;border:none;border-radius:4px;cursor:pointer;box-shadow:0 0 24px rgba(124,34,212,0.4);transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px))"
                        onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 40px rgba(124,34,212,0.7)'"
                        onmouseout="this.style.background='#7c22d4';this.style.boxShadow='0 0 24px rgba(124,34,212,0.4)'">
                    Verify & Sign In →
                </button>
            </form>

            <div style="margin-top:20px;padding-top:20px;border-top:1px solid rgba(124,34,212,0.15);text-align:center">
                <p style="font-family:'Share Tech Mono',monospace;font-size:10px;color:#7a7490;margin-bottom:8px">
                    Lost your device? Use a recovery code.
                </p>
                <a href="{{ route('login') }}"
                   style="font-family:'Share Tech Mono',monospace;font-size:11px;color:rgba(124,34,212,0.6);text-decoration:none;letter-spacing:0.06em;transition:color 0.2s"
                   onmouseover="this.style.color='#9b3df0'" onmouseout="this.style.color='rgba(124,34,212,0.6)'">
                    ← Back to login
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
