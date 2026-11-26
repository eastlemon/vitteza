<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the first system administrator.
 * Usage: php artisan db:seed --class=AdminUserSeeder --force
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vitteza.ru'],
            [
                'name' => 'Администратор VITTEZA',
                'username' => 'admin',
                'password' => env('APP_ADMIN_PASSWORD', 'password'),  // 'hashed' cast → bcrypt
                'role' => 'admin',
                'is_active' => true,
                'competencies' => [],
            ]
        );
    }
}
