<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Ensures a valid API token exists in the session for any authenticated
 * web user. Allows proceeding without token on first request to avoid
 * locking users out during token initialization.
 */
class EnsureApiToken
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check() && empty(session('api_token'))) {
            // Token is missing but user is session-authenticated
            Log::info('EnsureApiToken: User has no token, storing request info', [
                'user_id'   => Auth::user()->id,
                'email'     => Auth::user()->email,
                'path'      => $request->path(),
            ]);

            // Check if this is the first request after login by looking at session age
            $loginTime = session('_login_time');
            $now = now()->timestamp;
            
            if (!$loginTime) {
                // First request after login, mark it
                session(['_login_time' => $now]);
                Log::info('EnsureApiToken: Marking first login attempt', ['time' => $now]);
                // Allow this first request to proceed without token
                return $next($request);
            }

            $secondsSinceLogin = $now - $loginTime;
            
            // If less than 5 seconds since login, allow proceeding (token is still being issued)
            if ($secondsSinceLogin < 5) {
                Log::info('EnsureApiToken: Recent login, allowing without token', [
                    'seconds_since_login' => $secondsSinceLogin,
                ]);
                return $next($request);
            }

            // Token is genuinely missing after 5+ seconds - force re-login
            Log::warning('EnsureApiToken: Token missing after 5+ seconds, forcing logout', [
                'user_email'           => Auth::user()->email,
                'seconds_since_login'  => $secondsSinceLogin,
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir, silakan login kembali.');
        }

        return $next($request);
    }
}
