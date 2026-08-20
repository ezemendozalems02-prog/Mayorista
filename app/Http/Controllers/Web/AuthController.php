<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && !Auth::user()->is_demo) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Clear demo state if it exists
            $request->session()->forget('is_demo_session');
            $cookie = cookie()->forget('active_demo_user');
            
            return redirect()->route('dashboard')->withCookie($cookie);
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no corresponden a nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        // El registro publico solo tiene sentido en modo SaaS (cada quien
        // crea su propia organizacion). En single_license (este despliegue,
        // una sola empresa) no debe existir una puerta para que cualquiera
        // se cree una cuenta nueva.
        abort_if(config('platform.mode') === 'single_license', 404);

        if (Auth::check() && !Auth::user()->is_demo) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        abort_if(config('platform.mode') === 'single_license', 404);

        $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create Organization
        $organization = \App\Models\Organization::create([
            'name' => $request->organization_name,
            'slug' => \Illuminate\Support\Str::slug($request->organization_name) . '-' . \Illuminate\Support\Str::random(5),
            'trial_ends_at' => now()->addDays(14),
            'subscription_status' => 'trial',
        ]);

        // Create User (Admin)
        $user = \App\Models\User::create([
            'organization_id' => $organization->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'owner', // Fixed as owner for the first user
        ]);

        Auth::login($user);

        // Clear demo state if it exists
        $request->session()->forget('is_demo_session');
        $cookie = cookie()->forget('active_demo_user');

        return redirect()->route('dashboard')->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Ensure demo cookie is also forgotten on manual logout
        $cookie = cookie()->forget('active_demo_user');
        
        return redirect()->route('login')->withCookie($cookie);
    }
}
