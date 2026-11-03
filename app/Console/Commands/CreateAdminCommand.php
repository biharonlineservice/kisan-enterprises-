<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Securely create an Admin account from the server shell (password is typed hidden,
 * never stored in code, .env or shell history).
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'kisan:create-admin {--name=} {--email=} {--mobile=}';

    protected $description = 'Create an Admin user account for Kisan Enterprises ERP';

    public function handle(): int
    {
        if (! Role::where('name', Role::ADMIN)->exists()) {
            $this->call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        }

        $name = $this->option('name') ?: $this->ask('Full name');
        $email = $this->option('email') ?: $this->ask('Email address');
        $mobile = $this->option('mobile') ?: $this->ask('Mobile number (optional, 10 digits)', '');
        $password = $this->secret('Password (min 8 chars, letters and numbers)');
        $confirm = $this->secret('Confirm password');

        $validator = Validator::make(
            compact('name', 'email', 'mobile', 'password') + ['password_confirmation' => $confirm],
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'mobile' => ['nullable', 'regex:/^[6-9]\d{9}$/', 'unique:users,mobile'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile ?: null,
            'designation' => 'Administrator',
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'password' => $password,
            'is_active' => true,
        ]);

        AuditLogger::log('user.created', $user, [], ['email' => $email, 'role' => 'Admin'], 'Admin created via console', $user->id);

        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
