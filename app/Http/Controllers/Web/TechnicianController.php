<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TechnicianController extends Controller
{
    public function index()
    {
        $orgId = auth()->user()->organization_id;
        $technicians = Technician::where('organization_id', $orgId)->latest()->get();
        return view('technicians.index', compact('technicians'));
    }

    public function show(Technician $technician)
    {
        return view('technicians.show', compact('technician'));
    }

    public function create()
    {
        return view('technicians.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
            ],
            'specialties' => 'nullable|string',
            'type' => 'required|in:owner,technician,seller,manager',
            'give_access' => 'nullable|boolean',
            'password' => 'required_if:give_access,1|nullable|string|min:8',
        ]);

        $orgId = auth()->user()->organization_id;
        $userId = null;

        if ($request->boolean('give_access')) {
            $user = User::create([
                'organization_id' => $orgId,
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'role' => $validated['type'], // Enum handles this if it matches
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        Technician::create([
            'organization_id' => $orgId,
            'user_id' => $userId,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'specialties' => $validated['specialties'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        return redirect()->route('technician.index')->with('success', 'Staff registrado correctamente.');
    }

    public function edit(Technician $technician)
    {
        return view('technicians.edit', compact('technician'));
    }

    public function update(Request $request, Technician $technician)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($technician->user_id)
            ],
            'specialties' => 'nullable|string',
            'type' => 'required|in:owner,technician,seller,manager',
            'is_active' => 'nullable|boolean',
            'give_access' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $isActive = $request->has('is_active');
        $userId = $technician->user_id;

        // Manage User account
        if ($request->boolean('give_access') && !$userId) {
            // Create new user
            $user = User::create([
                'organization_id' => auth()->user()->organization_id,
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password ?? '12345678'),
                'role' => $validated['type'],
                'is_active' => $isActive,
            ]);
            $userId = $user->id;
        } elseif ($userId) {
            // Update existing user
            $user = User::find($userId);
            if ($user) {
                $userData = [
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'role' => $validated['type'],
                    'is_active' => $isActive,
                ];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $user->update($userData);
            }
        }

        $technician->update([
            'user_id' => $userId,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'specialties' => $validated['specialties'],
            'type' => $validated['type'],
            'is_active' => $isActive,
        ]);

        return redirect()->route('technician.index')->with('success', 'Datos de staff actualizados.');
    }
}
