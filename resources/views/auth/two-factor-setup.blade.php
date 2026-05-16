<x-app-layout title="Enable Two-Factor Authentication">

    <x-page-header overline="Security" title="Enable Two-Factor Authentication"
                   subtitle="Scan the QR code with your authenticator app, then verify with a code.">
        <x-slot:actions>
            <x-btn :href="route('2fa.security')" variant="ghost" size="sm">← Cancel</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-2xl">

        @if($errors->has('code'))
            <x-alert type="error" :message="$errors->first('code')" class="mb-5" />
        @endif

        <div class="grid grid-cols-2 gap-6">

            {{-- Step 1: Scan QR --}}
            <x-card title="Step 1 — Scan QR Code">
                <p class="text-[12.5px] text-ink3 mb-5 leading-relaxed">
                    Open <strong class="text-ink">Google Authenticator</strong>, <strong class="text-ink">Authy</strong>,
                    or any TOTP app and scan this QR code to add your account.
                </p>

                {{-- QR code rendered via JS --}}
                <div class="flex justify-center mb-4">
                    <div id="qrcode" class="p-3 bg-white rounded-lg border border-cream3 inline-block"></div>
                </div>

                <div class="px-3 py-2.5 bg-cream rounded-md border border-cream3">
                    <p class="erp-label mb-1">Can't scan? Enter this key manually:</p>
                    <p class="font-mono text-[13px] font-semibold text-purple tracking-widest select-all text-center">
                        {{ $secret }}
                    </p>
                </div>
            </x-card>

            {{-- Step 2: Verify --}}
            <x-card title="Step 2 — Verify & Enable">
                <p class="text-[12.5px] text-ink3 mb-5 leading-relaxed">
                    After scanning, your authenticator app will show a <strong class="text-ink">6-digit rotating code</strong>.
                    Enter it below to confirm the setup.
                </p>

                <form method="POST" action="{{ route('2fa.setup.confirm') }}" class="flex flex-col gap-4">
                    @csrf

                    <div>
                        <label class="erp-label mb-2 block">6-Digit Code from Authenticator</label>
                        <input type="text" name="code" required autofocus
                               maxlength="6" autocomplete="one-time-code"
                               placeholder="000000"
                               class="erp-input font-mono text-[22px] text-center tracking-[0.4em]">
                    </div>

                    <x-btn type="submit" variant="primary" class="w-full justify-center">
                        Verify & Enable 2FA
                    </x-btn>
                </form>

                <div class="mt-5 pt-4 border-t border-cream3">
                    <p class="font-mono text-[9px] text-ink4 tracking-wide leading-relaxed">
                        After enabling, you'll receive <strong>8 recovery codes</strong>.
                        Save them somewhere safe — they're the only way to access your account
                        if you lose your authenticator device.
                    </p>
                </div>
            </x-card>
        </div>
    </div>

    {{-- QR code generator (JS library) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: "{{ $qrUrl }}",
            width: 180,
            height: 180,
            colorDark: "#1c1830",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M,
        });
    </script>

</x-app-layout>
