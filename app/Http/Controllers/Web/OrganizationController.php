<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->organization;
        return view('organization.settings', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = auth()->user()->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'currency' => 'required|string|max:10',
            'country' => 'nullable|string|max:100',
            'notifications_email_enabled' => 'nullable|boolean',
            'notifications_email_alias' => 'nullable|email|max:255',
        ]);

        // Fix boolean checkbox
        $validated['notifications_email_enabled'] = $request->has('notifications_email_enabled');

        $organization->update($validated);

        return back()->with('success', 'Configuración del negocio actualizada correctamente.');
    }
}
