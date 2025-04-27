<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        try {
            // Ensure we load roles and permissions properly
            $user->load('roles.permissions');
            
            // For API requests
            if ($request->wantsJson()) {
                // Regenerate session to prevent session fixation attacks
                $request->session()->regenerate();
                
                // Clear any old session data that might conflict
                $request->session()->forget('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
                
                // Log successful login for debugging
                Log::info('User logged in successfully: ' . $user->email);
                
                return new JsonResponse([
                    'user' => $user,
                    'success' => true
                ]);
            }
            
            // For web requests
            return redirect()->intended($this->redirectPath());
        } catch (\Exception $e) {
            Log::error('Authentication error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return new JsonResponse([
                    'message' => 'Authentication failed',
                    'error' => $e->getMessage(),
                    'success' => false
                ], 500);
            }
            return redirect()->back()->withErrors(['email' => 'Authentication failed']);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Get user info for logging before logout
        $user = $request->user();
        $userId = $user ? $user->id : 'unknown';
        $userEmail = $user ? $user->email : 'unknown';
        
        // Standard Laravel logout
        $this->guard()->logout();
        
        // Clear and invalidate the session
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear any potential cache that might affect future logins
        Cache::forget('user.' . $userId);
        
        // Log the successful logout
        Log::info("User logged out successfully: {$userEmail} (ID: {$userId})");
        
        // Return JSON response for API requests
        if ($request->wantsJson()) {
            return new JsonResponse([
                'message' => 'Successfully logged out',
                'success' => true,
                'timestamp' => now()->timestamp // Add timestamp for cache busting
            ], 200);
        }
        
        // Redirect for web requests
        return $this->loggedOut($request) ?: redirect('/');
    }
}
