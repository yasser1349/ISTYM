<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Get company settings
     */
    public function getCompany()
    {
        $settings = CompanySettings::getSettings();
        return response()->json($settings);
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ice' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $settings = CompanySettings::getSettings();
        $settings->update($validated);

        return response()->json([
            'message' => 'Paramètres de l\'entreprise mis à jour avec succès',
            'settings' => $settings
        ]);
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $settings = CompanySettings::getSettings();

        // Delete old logo if exists
        if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
            Storage::disk('public')->delete($settings->logo);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');
        $settings->update(['logo' => $path]);

        return response()->json([
            'message' => 'Logo mis à jour avec succès',
            'logo' => Storage::url($path)
        ]);
    }

    /**
     * Get notification settings for current user
     */
    public function getNotifications()
    {
        $user = auth()->user();
        
        return response()->json([
            'email_alerts' => $user->email_alerts ?? true,
            'stock_alerts' => $user->stock_alerts ?? true,
            'order_alerts' => $user->order_alerts ?? true,
            'maintenance_alerts' => $user->maintenance_alerts ?? true,
            'push_notifications' => $user->push_notifications ?? false,
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_alerts' => 'boolean',
            'stock_alerts' => 'boolean',
            'order_alerts' => 'boolean',
            'maintenance_alerts' => 'boolean',
            'push_notifications' => 'boolean',
        ]);

        $user = auth()->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Paramètres de notification mis à jour avec succès',
        ]);
    }

    /**
     * Update user security settings (password)
     */
    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    }

    /**
     * Get all users (admin only)
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Update user status (admin only)
     */
    public function updateUserStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas désactiver votre propre compte'
            ], 422);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Statut utilisateur mis à jour',
            'user' => $user
        ]);
    }
}
