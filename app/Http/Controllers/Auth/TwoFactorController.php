<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        // Per-user+IP lockout (mirrors LoginRequest::ensureIsNotRateLimited).
        // The route-level throttle alone only limits one IP; this also stops
        // an attacker who spreads guesses across IPs at a single victim.
        $throttleKey = 'two-factor:' . $userId . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout($request));
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'code' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = \App\Models\User::findOrFail($userId);

        $code   = preg_replace('/\s+/', '', $request->code);
        $secret = Crypt::decryptString($user->two_factor_secret);

        // Check TOTP code (30-s window ±1)
        $valid = $this->google2fa->verifyKey($secret, $code, 1);

        // Also check recovery codes (stored hashed — never compared/logged in plaintext)
        if (! $valid && $user->two_factor_recovery_codes) {
            $hashedCodes = $user->two_factor_recovery_codes;
            $matchedHash = collect($hashedCodes)->first(fn ($hash) => Hash::check($code, $hash));

            if ($matchedHash !== null) {
                $valid = true;
                // Burn the recovery code (one-time use)
                $user->update([
                    'two_factor_recovery_codes' => array_values(array_filter($hashedCodes, fn ($h) => $h !== $matchedHash)),
                ]);
            }
        }

        if (! $valid) {
            RateLimiter::hit($throttleKey);
            return back()->withErrors(['code' => 'Invalid code. Please check your authenticator and try again.']);
        }

        RateLimiter::clear($throttleKey);

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

        // Generate 8 recovery codes. Only the hashed form is ever persisted —
        // the plaintext codes exist only long enough to be shown to the user
        // once, via a one-time session flash.
        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)))
            ->toArray();

        auth()->user()->update([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_enabled'        => true,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => array_map(fn ($code) => Hash::make($code), $recoveryCodes),
        ]);

        session()->forget('2fa_setup_secret');

        return redirect()->route('2fa.recovery-codes')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function showRecoveryCodes()
    {
        // Recovery codes are stored hashed and cannot be redisplayed later —
        // only show them immediately after generation, via the session flash.
        $codes = session('recovery_codes');

        if (! $codes) {
            return redirect()->route('2fa.security')
                ->with('error', 'Recovery codes can only be viewed once, right after they are generated. Disable and re-enable 2FA to generate a new set.');
        }

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
