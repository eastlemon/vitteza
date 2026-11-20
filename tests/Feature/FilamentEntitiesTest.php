<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Проверяет, что все доменные Filament-ресурсы и виртуальные страницы
 * рендерятся без ошибок (HTTP 200) под администратором.
 * Read-only GET-запросы против боевой MySQL-базы (без RefreshDatabase).
 */
class FilamentEntitiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // phpunit.xml форсирует sqlite :memory: (а pdo_sqlite не установлен).
        // Перенастраиваем mysql-подключение на боевую БД, читая .env вручную.
        $env = [];
        if (is_file(base_path('.env'))) {
            foreach (file(base_path('.env')) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $v = trim($v);
                if (strlen($v) >= 2 && (($v[0] === '"' && $v[-1] === '"') || ($v[0] === "'" && $v[-1] === "'"))) {
                    $v = substr($v, 1, -1);
                }
                $env[$k] = $v;
            }
        }
        config([
            'database.default' => $env['DB_CONNECTION'] ?? 'mysql',
            'database.connections.mysql.host' => $env['DB_HOST'] ?? '127.0.0.1',
            'database.connections.mysql.port' => $env['DB_PORT'] ?? '3306',
            'database.connections.mysql.database' => $env['DB_DATABASE'] ?? '',
            'database.connections.mysql.username' => $env['DB_USERNAME'] ?? '',
            'database.connections.mysql.password' => $env['DB_PASSWORD'] ?? '',
        ]);
    }

    public function test_admin_can_view_all_entity_pages(): void
    {
        $this->withoutExceptionHandling();

        $admin = User::where('email', 'admin@vitteza.ru')->first();
        $this->assertNotNull($admin, 'admin user admin@vitteza.ru not found');

        // PreventRequestForgery требует, чтобы host запроса совпадал с APP_URL.
        $base = rtrim(config('app.url'), '/');

        $paths = [
            '/admin',
            '/admin/trips',
            '/admin/drivers',
            '/admin/customers',
            '/admin/cargo-templates',
            '/admin/logists',
            '/admin/rating',
            '/admin/settings',
            '/admin/trips/create',
            '/admin/drivers/create',
            '/admin/customers/create',
            '/admin/cargo-templates/create',
        ];

        foreach ($paths as $path) {
            $resp = $this->actingAs($admin)->get($base . $path);
            $this->assertSame(200, $resp->status(), "GET $path returned {$resp->status()}");
        }

        // Дашборд реально выводит контент виджета (не пустая страница)
        $dash = $this->actingAs($admin)->get($base . '/admin');
        $this->assertStringContainsString('Дашборд', $dash->getContent() ?? '');
        $this->assertStringContainsString('Доход логиста', $dash->getContent() ?? '');
    }
}
