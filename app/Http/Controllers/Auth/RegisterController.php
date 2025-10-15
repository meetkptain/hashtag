<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'domain' => ['required', 'string', 'max:255', 'unique:tenants,domain'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                // Create tenant
                $tenant = Tenant::create([
                    'name' => $validated['name'],
                    'domain' => $validated['domain'],
                    'email' => $validated['email'],
                    'plan' => 'starter',
                    'trial_ends_at' => now()->addDays(14),
                    'active' => true,
                ]);

                // Create tenant database
                $dbName = $tenant->database;
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Switch to tenant database and run migrations
                $tenant->switchDatabase();
                
                \Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                // Create user
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'admin',
                ]);

                // Log in the user
                Auth::login($user);
            });

            return redirect('/dashboard')->with('success', 'Compte créé avec succès ! Bienvenue 🎉');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Erreur lors de la création du compte : ' . $e->getMessage()
            ])->withInput();
        }
    }
}

