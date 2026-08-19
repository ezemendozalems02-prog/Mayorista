<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoController extends Controller
{
    public function start(Request $request)
    {
        // 0. Check if user is already logged in with a real account
        if (Auth::check() && !Auth::user()->is_demo) {
            return redirect()->route('dashboard')->with('info', 'Ya tienes una sesión activa en el modo real.');
        }

        // 1. Check if user already has an active demo session via Cookie
        $existingDemoUserId = $request->cookie('active_demo_user');
        
        if ($existingDemoUserId) {
            $existingUser = User::with('organization')
                ->where('id', $existingDemoUserId)
                ->where('is_demo', true)
                ->first();

            // Verify the user exists and the trial has not expired
            if ($existingUser && $existingUser->organization && now()->lt($existingUser->organization->trial_ends_at)) {
                
                Auth::login($existingUser);
                $request->session()->regenerate();
                $request->session()->put('is_demo_session', true);

                return redirect()->route('dashboard')
                    ->with('success', '¡Bienvenido de vuelta! Hemos recuperado tu entorno de demostración.');
            }
        }

        // 2. Generate unique identifiers for new demo
        $demoId = Str::random(8);
        $organizationName = "Demo Org " . $demoId;
        
        // 3. Create Demo Organization
        $organization = Organization::create([
            'name' => $organizationName,
            'slug' => Str::slug($organizationName) . '-' . Str::random(4),
            'trial_ends_at' => now()->addHours(1),
            'subscription_status' => 'trial',
            'plan' => 'pro',
            'is_demo' => true,
        ]);

        // 4. Create Demo User
        $user = User::create([
            'organization_id' => $organization->id,
            'name' => "Demo User " . $demoId,
            'email' => "demo_" . $demoId . "@mitoyamile.test",
            'password' => Hash::make(Str::random(16)),
            'role' => 'owner',
            'is_demo' => true,
        ]);

        // 5. Login the new demo user
        Auth::login($user);
        
        $request->session()->regenerate();
        $request->session()->put('is_demo_session', true);

        // 6. Redirect with an encrypted cookie that lasts 60 minutes (1 hour)
        $cookie = cookie('active_demo_user', $user->id, 60);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenido al Modo Demostración. Tienes acceso completo a las funciones Pro durante 1 hora.')
            ->withCookie($cookie);
    }
}
