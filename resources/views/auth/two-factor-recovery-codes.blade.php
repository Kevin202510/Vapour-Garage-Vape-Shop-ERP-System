<x-app-layout title="2FA Recovery Codes">

    <x-page-header overline="Security · 2FA" title="Save Your Recovery Codes"
                   subtitle="These one-time codes let you access your account if you lose your authenticator device.">
        <x-slot:actions>
            <x-btn :href="route('2fa.security')" variant="primary" size="sm">Done →</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-xl">

        <div class="flex items-start gap-3 px-4 py-3 bg-danger/5 border border-danger/25 rounded-lg mb-6">
            <svg class="w-4 h-4 text-danger flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div>
                <p class="text-[13px] font-semibold text-ink mb-0.5">Save these codes now — they will not be shown again</p>
                <p class="text-[12px] text-ink3 leading-relaxed">
                    Each code can only be used once. Store them in a password manager, printed paper, or secure notes app.
                    If you lose both your authenticator device and these codes, you will be locked out of your account.
                </p>
            </div>
        </div>

        <x-card title="Recovery Codes">
            <div class="grid grid-cols-2 gap-2 mb-5">
                @foreach($codes as $code)
                <div class="font-mono text-[14px] text-ink font-semibold tracking-widest px-3 py-2.5 bg-cream border border-cream3 rounded-md text-center select-all">
                    {{ $code }}
                </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button onclick="copyAll()" id="copy-btn"
                        class="erp-btn erp-btn--ghost erp-btn--sm flex-1 justify-center">
                    Copy All Codes
                </button>
                <button onclick="printCodes()"
                        class="erp-btn erp-btn--ghost erp-btn--sm flex-1 justify-center">
                    Print
                </button>
            </div>

            <p class="font-mono text-[9px] text-ink4 tracking-wide mt-4 leading-relaxed">
                Two-factor authentication is now <strong>active</strong> on your account.
                You will be asked for a code from your authenticator app every time you sign in.
            </p>
        </x-card>

        <div class="mt-5 text-center">
            <x-btn :href="route('2fa.security')" variant="primary">Continue to Security Settings</x-btn>
        </div>
    </div>

    <script>
    const codes = @json($codes);
    function copyAll() {
        navigator.clipboard.writeText(codes.join('\n')).then(() => {
            const btn = document.getElementById('copy-btn');
            btn.textContent = '✓ Copied!';
            setTimeout(() => btn.textContent = 'Copy All Codes', 2000);
        });
    }
    function printCodes() {
        const w = window.open('', '_blank');
        w.document.write('<pre style="font-size:16px;padding:24px;font-family:monospace"><b>Vapour Garage ERP — Recovery Codes</b>\nGenerated: {{ now()->format("Y-m-d H:i") }}\n\n' + codes.join('\n') + '\n\n<i>Each code can be used once only. Keep these safe.</i></pre>');
        w.print();
    }
    </script>

</x-app-layout>
