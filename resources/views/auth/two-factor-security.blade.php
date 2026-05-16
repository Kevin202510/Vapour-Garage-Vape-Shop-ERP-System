<x-app-layout title="Security Settings">

    <x-page-header overline="Account" title="Security Settings"
                   subtitle="Manage two-factor authentication for your account.">
    </x-page-header>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-5" />
    @endif

    <div class="max-w-xl">

        {{-- 2FA Status Card --}}
        <x-card title="Two-Factor Authentication">
            @if(auth()->user()->two_factor_enabled && auth()->user()->two_factor_confirmed_at)

                {{-- Enabled state --}}
                <div class="flex items-center gap-3 px-4 py-3 bg-ok/5 border border-ok/20 rounded-lg mb-5">
                    <svg class="w-5 h-5 text-ok flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <polyline points="9 12 11 14 15 10"/>
                    </svg>
                    <div>
                        <p class="text-[13px] font-semibold text-ok">Two-factor authentication is enabled</p>
                        <p class="font-mono text-[9px] text-ink4 mt-0.5">
                            Enabled {{ auth()->user()->two_factor_confirmed_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <p class="text-[12.5px] text-ink3 mb-5 leading-relaxed">
                    Your account is protected. Every sign-in requires a code from your authenticator app
                    in addition to your password.
                </p>

                <div class="flex flex-col gap-3">
                    <x-btn :href="route('2fa.recovery-codes')" variant="ghost" size="sm">
                        View Recovery Codes
                    </x-btn>

                    <div class="border-t border-cream3 pt-4">
                        <p class="text-[12px] text-ink4 mb-3">To disable 2FA, confirm your current password:</p>
                        <form method="POST" action="{{ route('2fa.disable') }}" class="flex gap-2">
                            @csrf
                            <input type="password" name="password" required
                                   placeholder="Current password"
                                   class="erp-input flex-1">
                            <x-btn type="submit" variant="danger" size="sm"
                                   onclick="return confirm('Disable two-factor authentication? Your account will be less secure.')">
                                Disable 2FA
                            </x-btn>
                        </form>
                        @error('password')
                            <p class="erp-field-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            @else

                {{-- Disabled state --}}
                <div class="flex items-center gap-3 px-4 py-3 bg-warn/5 border border-warn/20 rounded-lg mb-5">
                    <svg class="w-5 h-5 text-warn flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <div>
                        <p class="text-[13px] font-semibold text-warn">Two-factor authentication is not enabled</p>
                        <p class="font-mono text-[9px] text-ink4 mt-0.5">Your account is protected by password only.</p>
                    </div>
                </div>

                <p class="text-[12.5px] text-ink3 mb-5 leading-relaxed">
                    Two-factor authentication adds an extra layer of security. After enabling it, signing in will require
                    both your password and a time-based code from your authenticator app.
                </p>

                <div class="flex flex-col gap-3 px-4 py-3 bg-cream rounded-lg border border-cream3 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center font-mono text-[11px] text-purple font-bold flex-shrink-0">1</div>
                        <p class="text-[12px] text-ink3">Download <strong class="text-ink">Google Authenticator</strong>, <strong class="text-ink">Authy</strong>, or any TOTP app</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center font-mono text-[11px] text-purple font-bold flex-shrink-0">2</div>
                        <p class="text-[12px] text-ink3">Scan the QR code shown on the setup page</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center font-mono text-[11px] text-purple font-bold flex-shrink-0">3</div>
                        <p class="text-[12px] text-ink3">Enter the 6-digit code to confirm and save recovery codes</p>
                    </div>
                </div>

                <x-btn :href="route('2fa.setup')" variant="primary">
                    Enable Two-Factor Authentication →
                </x-btn>

            @endif
        </x-card>

    </div>

</x-app-layout>
