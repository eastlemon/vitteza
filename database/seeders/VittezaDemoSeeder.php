<?php

namespace Database\Seeders;

use App\Enums\TripStatus;
use App\Models\CargoTemplate;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data for VITTEZA: logists, drivers, customers, cargo
 * templates and trips (some in the paid status, so the income trigger fires and
 * the current-month rating/dashboard are non-empty). Idempotent by trip count.
 */
class VittezaDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Trip::count() > 0) {
            return;
        }

        $logist1 = $this->logist('ivanov', 'Иванов Иван Иванович', ['trips.view', 'trips.manage', 'finance.view', 'logists.view', 'drivers.view', 'customers.view', 'cargo.view']);
        $logist2 = $this->logist('petrov', 'Петров Пётр Петрович', ['trips.view', 'trips.manage', 'finance.view']);

        $drivers = collect([
            Driver::firstOrCreate(['full_name' => 'Сидоров Алексей Михайлович'], ['plate_number' => 'В148ВО77', 'phone' => '+79161234567', 'capacity_t' => 20, 'volume_m3' => 82, 'body_params' => '13,6×2,45×2,7 тент', 'is_active' => true]),
            Driver::firstOrCreate(['full_name' => 'Кузнецов Дмитрий Сергеевич'], ['plate_number' => 'Е502КХ99', 'phone' => '+79167654321', 'capacity_t' => 5, 'volume_m3' => 36, 'body_params' => '7,2×2,2×2,2 тент', 'is_active' => true]),
            Driver::firstOrCreate(['full_name' => 'Морозов Олег Андреевич'], ['plate_number' => 'Р777ОА50', 'phone' => '+79150001122', 'capacity_t' => 24, 'volume_m3' => 90, 'body_params' => 'реф 13,6', 'is_active' => true]),
        ]);

        $cust1 = Customer::firstOrCreate(['name' => 'ООО «СтройМеталл»'], ['inn' => '7712345678', 'contact_person' => 'Григорьева А.В.', 'contact_phone' => '+74951234567', 'is_active' => true]);
        $cust2 = Customer::firstOrCreate(['name' => 'АО «ПромТех»'], ['inn' => '77019876543', 'contact_person' => 'Лебедев К.С.', 'contact_phone' => '+74953334455', 'is_active' => true]);
        $cust1->contracts()->firstOrCreate(['contract_no' => 'СМ-2026/01'], ['signed_at' => '2026-01-15', 'expires_at' => '2026-12-31', 'subject' => 'Перевозка металлопроката', 'is_active' => true]);
        $cust2->contracts()->firstOrCreate(['contract_no' => 'ПТ-2026/04'], ['signed_at' => '2026-04-01', 'expires_at' => '2027-03-31', 'subject' => 'Перевозка оборудования', 'is_active' => true]);

        $tpl = CargoTemplate::firstOrCreate(['name' => 'Металлопрокат (прокат стальной)'], ['cargo_class' => 'general', 'default_weight_t' => 19, 'default_volume_m3' => 60, 'default_packaging' => 'связки', 'is_active' => true]);
        CargoTemplate::firstOrCreate(['name' => 'Оборудование промышленное'], ['cargo_class' => 'oversized', 'default_weight_t' => 22, 'default_volume_m3' => 75, 'default_packaging' => 'ящики/поддоны', 'is_active' => true]);
        CargoTemplate::firstOrCreate(['name' => 'Продукты питания охлаждённые'], ['cargo_class' => 'perishable', 'default_weight_t' => 8, 'default_volume_m3' => 30, 'temperature_regime' => '+2…+6 °C', 'is_active' => true]);

        $month = Carbon::now()->startOfMonth();
        $mk = fn (int $day) => $month->copy()->addDays($day - 1)->toDateString();

        // Paid trips (the income trigger fires → income_entries for the current month)
        $this->trip($logist1, $drivers[0], $cust1, $mk(3), 'Москва — Санкт-Петербург', 70000, 'paid', 'СМ-2026/01');
        $this->trip($logist1, $drivers[0], $cust1, $mk(7), 'Москва — Казань', 65000, 'paid', 'СМ-2026/01');
        $this->trip($logist1, $drivers[2], $cust2, $mk(11), 'Екатеринбург — Челябинск', 58000, 'paid', 'ПТ-2026/04');
        $this->trip($logist2, $drivers[1], $cust2, $mk(5), 'Москва — Нижний Новгород', 48000, 'paid', 'ПТ-2026/04');
        $this->trip($logist2, $drivers[1], $cust1, $mk(9), 'Тверь — Москва', 35000, 'paid', 'СМ-2026/01');

        // Trips in other statuses
        $this->trip($logist1, $drivers[0], $cust2, $mk(14), 'Москва — Самара', 72000, 'in_progress', 'ПТ-2026/04');
        $this->trip($logist2, $drivers[2], $cust1, $mk(15), 'Москва — Ярославль', 30000, 'confirmed', 'СМ-2026/01');
        $this->trip($logist1, $drivers[0], $cust1, $mk(12), 'Москва — Тула', 22000, 'new', 'СМ-2026/01');
    }

    private function logist(string $username, string $name, array $competencies): User
    {
        return User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $username.'@vitteza.ru',
                'password' => env('APP_DEMO_PASSWORD', 'password'),
                'role' => 'user',
                'is_active' => true,
                'competencies' => $competencies,
            ],
        );
    }

    private function trip(User $logist, Driver $driver, Customer $cust, string $date, string $route, float $cost, string $status, string $contractNo): Trip
    {
        return Trip::create([
            'load_date' => $date,
            'logistic_id' => $logist->id,
            'driver_id' => $driver->id,
            'customer_id' => $cust->id,
            'route' => $route,
            'cost' => $cost,
            'status' => $status,
            'contract_no' => $contractNo,
            'payment_fact' => $status === 'paid' ? $cost : 0,
            'payment_date' => $status === 'paid' ? $date : null,
            'weight_t' => 19,
            'volume_m3' => 60,
        ]);
    }
}
