<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Создаёт первого администратора системы.
 * Запуск: php artisan db:seed --class=AdminUserSeeder --force
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
                'password' => env('APP_ADMIN_PASSWORD', 'password'),  // 'hashed'-каст bcrypt'нет
                'role' => 'admin',
                'is_active' => true,
                'competencies' => [],
            ]
        );
    }
}
