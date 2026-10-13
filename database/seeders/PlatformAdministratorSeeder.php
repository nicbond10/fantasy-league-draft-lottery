<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use App\Support\NormalizedEmail;
use Illuminate\Database\Seeder;

final class PlatformAdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('platform.administrator.name');
        $email = config('platform.administrator.email');
        $password = config('platform.administrator.password');

        if (! is_string($name) || ! is_string($email) || ! is_string($password)
            || $name === '' || $email === '' || $password === '') {
            $this->command->warn('Platform administrator was not seeded; configure PLATFORM_ADMIN_NAME, PLATFORM_ADMIN_EMAIL, and PLATFORM_ADMIN_PASSWORD.');

            return;
        }

        $administrator = User::query()->firstOrNew(['email' => NormalizedEmail::from($email)]);
        $administrator->forceFill([
            'name' => $name,
            'email_verified_at' => $administrator->email_verified_at ?? now(),
            'is_platform_admin' => true,
            'status' => UserStatus::Active,
        ]);

        if (! $administrator->exists) {
            $administrator->password = $password;
        }

        $administrator->save();
    }
}
