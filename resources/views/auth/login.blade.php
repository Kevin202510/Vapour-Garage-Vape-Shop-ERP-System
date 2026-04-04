<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VapeHub ERP — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=IBM+Plex+Mono:wght@400;500&family=Epilogue:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream font-sans" x-data>

<div class="min-h-screen grid grid-cols-[1fr_480px]">

    {{-- Left decorative panel --}}
    <div class="bg-navy relative flex flex-col items-center justify-center overflow-hidden">
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-terra opacity-10 rounded-full"></div>
        <div class="absolute top-10 right-10 w-40 h-40 bg-gold opacity-[0.07] rounded-full"></div>
        <div class="absolute top-1/3 left-1/4 w-60 h-60 bg-terra opacity-[0.05] rounded-full blur-3xl"></div>

        <div class="relative z-10 text-center px-12">
            <p class="font-mono text-[10px] tracking-[0.24em] uppercase text-white/30 mb-3">Operations Platform</p>
            <h1 class="font-serif text-[52px] font-black text-white leading-[0.9] tracking-tight mb-6">
                Vape<span class="text-terra-light">Hub</span><br>
                <em class="text-white/50 font-normal text-[36px]">ERP</em>
            </h1>
            <p class="text-white/40 text-[13px] font-light max-w-xs leading-relaxed">
                Unified operations platform for all 4 branches. Inventory, sales, HR, and reporting — all in one place.
            </p>
            <div class="flex items-center justify-center gap-3 mt-10">
                @foreach(['QC','MKT','PSG','BGC'] as $branch)
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-ok" style="animation:pulse 2s infinite;animation-delay:{{ $loop->index * 0.3 }}s"></div>
                        <span class="font-mono text-[8px] text-white/25 tracking-widest">{{ $branch }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right login panel --}}
    <div class="bg-paper flex flex-col items-center justify-center px-12">
        <div class="w-full max-w-sm">
            <p class="font-mono text-[9px] tracking-[0.2em] uppercase text-terra mb-2">Welcome back</p>
            <h2 class="font-serif text-[28px] font-bold text-ink mb-1">Sign in</h2>
            <p class="text-[13px] text-ink4 font-light mb-8">Enter your credentials to continue.</p>

            @if ($errors->any())
                <div class="mb-5 px-4 py-3 rounded-lg bg-danger/5 border border-danger/20 text-danger text-[12.5px]">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 px-4 py-3 rounded-lg bg-ok/5 border border-ok/20 text-ok text-[12.5px]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-cream border border-cream3 rounded-md px-3.5 py-2.5 text-[13px] font-sans text-ink
                                  placeholder-ink4 outline-none focus:border-terra focus:ring-1 focus:ring-terra/20 transition-colors
                                  @error('email') border-danger @enderror"
                           placeholder="admin@vapehub.ph">
                </div>

                <div>
                    <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full bg-cream border border-cream3 rounded-md px-3.5 py-2.5 text-[13px] font-sans text-ink
                                  placeholder-ink4 outline-none focus:border-terra focus:ring-1 focus:ring-terra/20 transition-colors"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-cream3 text-terra focus:ring-terra/30">
                        <span class="text-[12px] text-ink3">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-[12px] text-terra hover:underline">Forgot password?</a>
                    @endif
                </div>

                <button type="submit"
                        class="w-full py-3 bg-navy text-white font-serif text-[15px] font-bold rounded-md mt-2
                               hover:bg-navy-dark hover:-translate-y-px transition-all shadow-sm">
                    Sign In to ERP
                </button>
            </form>

            <p class="text-center font-mono text-[10px] text-ink4 mt-8">
                VapeHub ERP v2.1.0 · {{ now()->format('Y') }}
            </p>
        </div>
    </div>
</div>

</body>
</html>