<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {domain} {name} {email} {--password=}';
    protected $description = 'Create a new tenant with admin user';

    public function handle(): int
    {
        $domain = $this->argument('domain');
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password') ?: 'password';

        // Check if tenant exists
        if (Tenant::where('domain', $domain)->exists()) {
            $this->error("Tenant with domain {$domain} already exists!");
            return Command::FAILURE;
        }

        $this->info("Creating tenant: {$name}");

        try {
            DB::transaction(function () use ($domain, $name, $email, $password) {
                // Create tenant
                $tenant = Tenant::create([
                    'name' => $name,
                    'domain' => $domain,
                    'email' => $email,
                    'plan' => 'starter',
                    'trial_ends_at' => now()->addDays(14),
                ]);

                $this->info("✓ Tenant created with ID: {$tenant->id}");
                $this->info("✓ Database: {$tenant->database}");
                $this->info("✓ API Key: {$tenant->api_key}");

                // Create tenant database
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenant->database}`");
                $this->info("✓ Database created");

                // Switch to tenant database and run migrations
                $tenant->switchDatabase();
                
                $this->call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                $this->info("✓ Migrations completed");

                // Create admin user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'admin',
                ]);

                $this->info("✓ Admin user created");
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->info("Tenant Details:");
                $this->line("Domain: {$domain}");
                $this->line("Email: {$email}");
                $this->line("Password: {$password}");
                $this->line("API Key: {$tenant->api_key}");
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            });

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to create tenant: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

