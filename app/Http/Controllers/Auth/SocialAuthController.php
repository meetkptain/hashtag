<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Rediriger vers le provider social
     */
    public function redirect($provider)
    {
        // Valider le provider
        if (!in_array($provider, ['facebook', 'google', 'twitter', 'instagram'])) {
            return redirect('/login')->withErrors(['error' => 'Provider non supporté']);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Gérer le callback du provider
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            \Log::error("Social auth error for {$provider}: " . $e->getMessage());
            return redirect('/login')->withErrors([
                'error' => 'Erreur lors de l\'authentification avec ' . ucfirst($provider)
            ]);
        }

        // Chercher l'utilisateur par email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Utilisateur existe, on le connecte
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        // Nouvel utilisateur - créer compte + tenant
        return $this->createUserWithTenant($socialUser, $provider);
    }

    /**
     * Créer un nouvel utilisateur avec son tenant
     */
    protected function createUserWithTenant($socialUser, $provider)
    {
        try {
            DB::transaction(function () use ($socialUser, $provider, &$user) {
                // Créer le tenant
                $domain = $this->generateUniqueDomain($socialUser->getName());
                
                $tenant = Tenant::create([
                    'name' => $socialUser->getName() ?: 'User from ' . ucfirst($provider),
                    'domain' => $domain,
                    'email' => $socialUser->getEmail(),
                    'plan' => 'starter',
                    'trial_ends_at' => now()->addDays(14),
                    'active' => true,
                ]);

                // Créer la base de données tenant
                $dbName = $tenant->database;
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Exécuter les migrations tenant
                $tenant->switchDatabase();
                
                \Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                // Créer l'utilisateur
                $user = User::create([
                    'name' => $socialUser->getName() ?: 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(32)), // Mot de passe aléatoire
                    'email_verified_at' => now(), // Déjà vérifié par le provider
                    'role' => 'admin',
                    'tenant_id' => $tenant->id,
                ]);

                // Connecter l'utilisateur
                Auth::login($user);
            });

            return redirect('/dashboard')->with('success', 'Compte créé avec succès ! Bienvenue 🎉');

        } catch (\Exception $e) {
            \Log::error("Failed to create user with tenant from social: " . $e->getMessage());
            return redirect('/login')->withErrors([
                'error' => 'Erreur lors de la création du compte. Veuillez réessayer.'
            ]);
        }
    }

    /**
     * Générer un nom de domaine unique
     */
    protected function generateUniqueDomain($name)
    {
        $slug = Str::slug($name ?: 'user');
        $domain = $slug . '.hashmytag';

        // Si le domaine existe déjà, ajouter un nombre aléatoire
        $counter = 1;
        $originalDomain = $domain;

        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $originalDomain . '-' . $counter;
            $counter++;
        }

        return $domain;
    }
}

