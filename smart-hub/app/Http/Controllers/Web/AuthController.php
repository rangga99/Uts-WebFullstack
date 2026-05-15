<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'member.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 1. Authenticate via session (Laravel Auth)
        if (! Auth::attempt([
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ], true)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah, atau akun tidak aktif.'])
                ->withInput();
        }

        $request->session()->regenerate();

        // 2. Issue a Sanctum token
        $tokenIssued = static::issueApiToken($request->email, $request->password);
        
        Log::info('AuthController: Login successful, token issued: ' . ($tokenIssued ? 'yes' : 'no'), [
            'email' => $request->email,
            'role'  => Auth::user()->role,
        ]);

        return redirect()
            ->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'member.dashboard')
            ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
    }

    /**
     * POST /login-with-token
     * Authenticate user using an existing API token and create a web session.
     * This allows users to convert an API token to a web session.
     */
    public function loginWithToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // 1. Validate the token by calling API /auth/me
        try {
            $apiResponse = Http::timeout(5)
                ->withToken($request->token)
                ->get(config('app.url') . '/api/v1/auth/me');

            if (!$apiResponse->successful()) {
                return back()
                    ->withErrors(['token' => 'Token tidak valid atau telah kadaluarsa.'])
                    ->withInput();
            }

            $userData = $apiResponse->json('data');
            
            if (!$userData || !isset($userData['email'])) {
                return back()
                    ->withErrors(['token' => 'Gagal mengambil data pengguna dari token.'])
                    ->withInput();
            }

            // 2. Find or create user session
            $user = User::where('email', $userData['email'])->first();
            
            if (!$user) {
                return back()
                    ->withErrors(['token' => 'Pengguna tidak ditemukan.'])
                    ->withInput();
            }

            // 3. Authenticate via session
            Auth::login($user, true);
            $request->session()->regenerate();

            // 4. Store token in session
            session(['api_token' => $request->token]);

            return redirect()
                ->route($user->isAdmin() ? 'admin.dashboard' : 'member.dashboard')
                ->with('success', 'Selamat datang, ' . $user->name . '!');

        } catch (\Exception $e) {
            Log::error('AuthController: Exception with token login: ' . $e->getMessage());
            return back()
                ->withErrors(['token' => 'Gagal melakukan login dengan token.'])
                ->withInput();
        }
    }

    /**
     * Call the API login endpoint to get a Sanctum token and persist it
     * in the session. This must complete quickly to avoid blocking login.
     */
    public static function issueApiToken(string $email, string $password): bool
    {
        try {
            $user = User::where('email', $email)
                ->where('is_active', true)
                ->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                Log::warning('AuthController: API token issuance failed due to invalid credentials', ['email' => $email]);
                return false;
            }

            $user->tokens()->where('name', 'web-session')->delete();

            $tokenResult = $user->createToken(
                'web-session',
                $user->isAdmin()
                    ? ['admin', 'equipment:read', 'equipment:write', 'booking:read', 'booking:write']
                    : ['member', 'equipment:read', 'equipment:checkout', 'booking:read', 'booking:create'],
                now()->addDays(30)
            );

            session(['api_token' => $tokenResult->plainTextToken]);
            Log::info('AuthController: API token stored in session');
            return true;

        } catch (\Exception $e) {
            Log::warning('AuthController: API token issuance exception: ' . $e->getMessage(), [
                'email' => $email,
            ]);
        }

        return false;
    }

    public function logout(Request $request)
    {
        $token = session('api_token');
        if ($token) {
            try {
                Http::timeout(5)
                    ->withToken($token)
                    ->post(config('app.url') . '/api/v1/auth/logout');
            } catch (\Exception $e) {
                // Ignore logout errors
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
