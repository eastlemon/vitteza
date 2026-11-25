<?php

namespace Database\Seeders;

use App\Models\MailDomain;
use Illuminate\Database\Seeder;

/**
 * Creates the vitteza.ru mail domain (Spec §7.3).
 * Usage: php artisan db:seed --class=MailDomainSeeder --force
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
