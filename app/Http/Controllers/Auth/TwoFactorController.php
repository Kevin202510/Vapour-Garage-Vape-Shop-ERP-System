<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // ── Challenge (OTP entry after login) ────────────────────────────────────

    public function showChallenge()
    {
        if (! session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);

        $code   = preg_replace('/\s+/', '', $request->code);
        $secret = Crypt::decryptString($user->two_factor_secret);

        // Check TOTP code (30-s window ±1)
        $valid = $this->google2fa->verifyKey($secret, $code, 1);

        // Also check recovery codes
        if (! $valid && $user->two_factor_recovery_codes) {
            $codes = $user->two_factor_recovery_codes;
            if (in_array($code, $codes)) {
                $valid = true;
                // Burn the recovery code (one-time use)
                $user->update([
                    'two_factor_recovery_codes' => array_values(array_filter($codes, fn ($c) => $c !== $code)),
                ]);
            }
        }

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Please check your authenticator and try again.']);
        }

        session()->forget('2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    // ── Setup (enable 2FA from settings) ─────────────────────────────────────

    public function showSetup()
    {
        $user   = auth()->user();
        $secret = $this->google2fa->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        $qrUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'Vapour Garage ERP'),
            $user->email,
            $secret
        );

        return view('auth.two-factor-setup', compact('secret', 'qrUrl'));
    }

    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $secret = session('2fa_setup_secret');
        if (! $secret) {
            return redirect()->route('2fa.setup');
        }

        $code  = preg_replace('/\s+/', '', $request->code);
        $valid = $this->google2fa->verifyKey($secret, $code, 1);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Make sure your authenticator is set up correctly.']);
        }

        // Generate 8 recovery codes
        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)))
            ->toArray();

        auth()->user()->update([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_enabled'        => true,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        session()->forget('2fa_setup_secret');

        return redirect()->route('2fa.recovery-codes')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function showRecoveryCodes()
    {
        $codes = session('recovery_codes') ?? auth()->user()->two_factor_recovery_codes ?? [];
        return view('auth.two-factor-recovery-codes', compact('codes'));
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        auth()->user()->update([
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);

        return redirect()->route('2fa.security')
            ->with('success', 'Two-factor authentication disabled.');
    }

    public function security()
    {
        return view('auth.two-factor-security');
    }
}
