<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vapour Garage Main — Reset Password</title>
<link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Exo+2:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full" style="background:#060608;font-family:'Exo 2',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh">

<div style="position:fixed;inset:0;background:linear-gradient(rgba(124,34,212,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(124,34,212,0.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none"></div>
<div style="position:fixed;top:-20%;right:-10%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(124,34,212,0.18) 0%,transparent 65%);pointer-events:none"></div>
<div style="position:fixed;bottom:-10%;left:-5%;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(57,255,20,0.05) 0%,transparent 65%);pointer-events:none"></div>

<div style="position:relative;z-index:10;width:100%;max-width:420px;padding:20px">

    {{-- Brand --}}
    <div style="text-align:center;margin-bottom:32px">
        <a href="{{ route('login') }}" style="text-decoration:none">
            <div style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.08em;background:linear-gradient(90deg,#9b3df0,#39ff14);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px">
                VAPOUR GARAGE
            </div>
            <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;color:rgba(57,255,20,0.4);text-transform:uppercase">
                ERP — Staff Access
            </div>
        </a>
    </div>

    {{-- Card --}}
    <div style="background:#0d0b14;border:1px solid rgba(124,34,212,0.3);border-radius:8px;overflow:hidden">
        <div style="height:2px;background:linear-gradient(90deg,#5b16a0,#39ff14,#5b16a0)"></div>
        <div style="padding:32px">

            {{-- Key icon --}}
            <div style="width:52px;height:52px;border-radius:12px;background:rgba(124,34,212,0.12);border:1px solid rgba(124,34,212,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <svg style="width:24px;height:24px;color:#9b3df0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
            </div>

            <h2 style="font-family:'Russo One',sans-serif;font-size:20px;letter-spacing:0.05em;color:#e8e4f0;text-transform:uppercase;text-align:center;margin-bottom:8px">
                Reset Password
            </h2>
            <p style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490;text-align:center;margin-bottom:28px;line-height:1.6;letter-spacing:0.04em">
                Enter your email and we'll send you a<br>link to reset your password.
            </p>

            {{-- Success status --}}
            @if(session('status'))
            <div style="margin-bottom:20px;padding:12px 16px;border-radius:4px;background:rgba(57,255,20,0.08);border:1px solid rgba(57,255,20,0.3);font-size:12px;color:#39ff14;font-family:'Share Tech Mono',monospace;text-align:center;line-height:1.6">
                {{ session('status') }}
            </div>
            @endif

            {{-- Error --}}
            @if($errors->any())
            <div style="margin-bottom:20px;padding:12px 16px;border-radius:4px;background:rgba(184,50,50,0.1);border:1px solid rgba(184,50,50,0.3);font-size:12px;color:#e05050;font-family:'Share Tech Mono',monospace;text-align:center">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf

                <div>
                    <label style="display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(124,34,212,0.7);margin-bottom:8px">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="your@email.com"
                           style="width:100%;background:#060608;border:1px solid rgba(124,34,212,0.3);border-radius:4px;padding:11px 14px;font-family:'Exo 2',sans-serif;font-size:13px;color:#e8e4f0;outline:none;caret-color:#39ff14;transition:all 0.2s"
                           onfocus="this.style.borderColor='rgba(124,34,212,0.7)';this.style.boxShadow='0 0 12px rgba(124,34,212,0.2)'"
                           onblur="this.style.borderColor='rgba(124,34,212,0.3)';this.style.boxShadow='none'">
                </div>

                <button type="submit"
                        style="width:100%;padding:13px;background:#7c22d4;color:#fff;font-family:'Russo One',sans-serif;font-size:14px;letter-spacing:0.08em;text-transform:uppercase;border:none;border-radius:4px;cursor:pointer;box-shadow:0 0 24px rgba(124,34,212,0.4);transition:all 0.2s;clip-path:polygon(0 0,calc(100% - 6px) 0,100% 6px,100% 100%,6px 100%,0 calc(100% - 6px))"
                        onmouseover="this.style.background='#9b3df0';this.style.boxShadow='0 0 40px rgba(124,34,212,0.7)'"
                        onmouseout="this.style.background='#7c22d4';this.style.boxShadow='0 0 24px rgba(124,34,212,0.4)'">
                    Send Reset Link →
                </button>
            </form>

            <div style="margin-top:24px;text-align:center">
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
