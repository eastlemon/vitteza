<?php

namespace Database\Seeders;

use App\Models\MailDomain;
use Illuminate\Database\Seeder;

/**
 * Создаёт почтовый домен vitteza.ru (ТЗ §7.3).
 * Запуск: php artisan db:seed --class=MailDomainSeeder --force
 */
class MailDomainSeeder extends Seeder
{
    public function run(): void
    {
        MailDomain::updateOrCreate(
            ['domain' => 'vitteza.ru'],
            ['quota_mb' => 2048, 'is_active' => true],
        );
    }
}
