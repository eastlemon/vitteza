<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Checks that all domain Filament resources and virtual pages
 * render without errors (HTTP 200) as the administrator.
 * Read-only GET requests against the production MySQL DB (no RefreshDatabase).
 */
class FilamentEntitiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // phpunit.xml forces sqlite :memory: (and pdo_sqlite is not installed).
        // Reconfigure the mysql connection to the production DB, reading .env manually.
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

        // PreventRequestForgery requires the request host to match APP_URL.
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

        // The dashboard actually renders the widget content (not an empty page)
        $dash = $this->actingAs($admin)->get($base . '/admin');
        $this->assertStringContainsString(__('dashboard.title'), $dash->getContent() ?? '');
        $this->assertStringContainsString(__('common.income_logist'), $dash->getContent() ?? '');
    }
}
