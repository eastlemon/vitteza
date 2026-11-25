<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access matrix for record creation and administrative sections.
 * (fix: "no role can create records" — the CreateAction
 * button was missing on List pages; logists/rating must
 * be admin-only; the site root goes straight to /admin)
 *
 * Each scenario is a separate test method: the AuthenticateSession middleware
 * stores the password hash in the session and logs out when the actingAs user
 * changes within a single method.
 *
 * For each resource checks:
 *   - Resource::canCreate() for admin / a logist with manage-competencies /
 *     a logist with view-only competencies;
 *   - HTTP GET on List pages: 200 and the presence of a /create link for those allowed
 *     to; no link and 403 on /create for those not allowed.
 *
 * Also: the dashboard no longer has AccountWidget ("Welcome"),
 * but has the user menu with logout (fi-user-menu).
 */
class CreateAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role, array $competencies): User
    {
        return User::create([
            'name' => 'Test '.$email,
            'email' => $email,
            'username' => explode('@', $email)[0],
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
            'competencies' => $competencies,
        ]);
    }

    /** [label, resourceClass, listPath, can the manage-logist create?] */
    private function resources(): array
    {
        return [
            'trips'      => [\App\Filament\Resources\TripResource::class, '/admin/trips', true],
            'drivers'   => [\App\Filament\Resources\DriverResource::class, '/admin/drivers', true],
            'customers'  => [\App\Filament\Resources\CustomerResource::class, '/admin/customers', true],
            'cargo-templates'      => [\App\Filament\Resources\CargoTemplateResource::class, '/admin/cargo-templates', true],
            'employees' => [\App\Filament\Resources\EmployeeResource::class, '/admin/employees', false],
        ];
    }

    public function test_admin_sees_create_button_everywhere(): void
    {
        $admin = $this->makeUser('admin@test.ru', 'admin', []);
        $this->actingAs($admin);

        foreach ($this->resources() as $label => [$resourceClass, $listPath]) {
            $this->assertTrue($resourceClass::canCreate(), "$label: admin canCreate");

            $resp = $this->get($listPath);
            $this->assertSame(200, $resp->status(), "$label: admin GET $listPath = {$resp->status()}");
            $this->assertStringContainsString(
                $listPath.'/create',
                $resp->getContent(),
                "$label: no /create link on the admin List page",
            );

            $respCreate = $this->get($listPath.'/create');
            $this->assertSame(200, $respCreate->status(), "$label: admin GET {$listPath}/create = {$respCreate->status()}");
        }
    }

    public function test_manage_logist_sees_create_button_where_allowed(): void
    {
        $manage = $this->makeUser('manage@test.ru', 'user', [
            'trips.view', 'trips.manage', 'drivers.view', 'drivers.manage',
            'customers.view', 'customers.manage', 'cargo.view', 'cargo.manage',
        ]);
        $this->actingAs($manage);

        foreach ($this->resources() as $label => [$resourceClass, $listPath, $manageMayCreate]) {
            $this->assertSame($manageMayCreate, $resourceClass::canCreate(), "$label: manage canCreate");

            $resp = $this->get($listPath);
            if ($manageMayCreate) {
                $this->assertSame(200, $resp->status(), "$label: manage GET $listPath");
                $this->assertStringContainsString(
                    $listPath.'/create',
                    $resp->getContent(),
                    "$label: the manage-logist must see the create button",
                );
                $respCreate = $this->get($listPath.'/create');
                $this->assertSame(200, $respCreate->status(), "$label: manage GET {$listPath}/create");
            } else {
                // "Employees" — an administrative section
                $this->assertSame(403, $resp->status(), "$label: manage GET $listPath must be 403");
            }
        }
    }

    public function test_viewer_sees_no_create_button(): void
    {
        $viewer = $this->makeUser('viewer@test.ru', 'user', [
            'trips.view', 'customers.view', 'drivers.view', 'cargo.view',
        ]);
        $this->actingAs($viewer);

        foreach ($this->resources() as $label => [$resourceClass, $listPath]) {
            $this->assertFalse($resourceClass::canCreate(), "$label: viewer canCreate must be false");

            $resp = $this->get($listPath);
            if ($resp->status() === 200) {
                $this->assertStringNotContainsString(
                    $listPath.'/create',
                    $resp->getContent(),
                    "$label: the viewer must not see the create button",
                );

                $respCreate = $this->get($listPath.'/create');
                $this->assertSame(403, $respCreate->status(), "$label: viewer GET create must be 403");
            } else {
                $this->assertSame(403, $resp->status(), "$label: viewer GET $listPath — 200 or 403, got {$resp->status()}");
            }
        }
    }

    public function test_dashboard_has_no_account_widget(): void
    {
        $admin = $this->makeUser('admin@test.ru', 'admin', []);

        $resp = $this->actingAs($admin)->get('/admin');
        $this->assertSame(200, $resp->status());

        $content = $resp->getContent();
        $this->assertStringNotContainsString('Добро пожаловать', $content, 'AccountWidget must be removed from the dashboard');
        $this->assertStringNotContainsString('fi-account-widget', $content, 'The AccountWidget CSS class must not be present');

        // The user menu with logout stays in the topbar. We check without
        // tying to the locale: 'logout' is an action/URL name, not a translation.
        $this->assertStringContainsString('fi-user-menu', $content, 'The topbar user menu must remain');
        $this->assertStringContainsString('logout', $content, 'The logout action must remain available');
    }

    /**
     * "Logists" and "Rating" are administrative sections: a regular
     * (non-admin) user must not see them even in the menu.
     */
    public function test_admin_can_access_logists_and_rating(): void
    {
        $admin = $this->makeUser('admin@test.ru', 'admin', []);
        $resp = $this->actingAs($admin)->get('/admin/logists');
        $this->assertSame(200, $resp->status(), 'admin GET /admin/logists');
        $resp = $this->actingAs($admin)->get('/admin/rating');
        $this->assertSame(200, $resp->status(), 'admin GET /admin/rating');
    }

    public function test_logists_and_rating_are_forbidden_for_non_admin(): void
    {
        // A logist with the widest competencies — but NOT an admin
        $logist = $this->makeUser('logist@test.ru', 'user', [
            'trips.view', 'trips.view_all', 'trips.manage', 'finance.view',
            'logists.view', 'drivers.view', 'customers.view', 'cargo.view',
        ]);
        $this->actingAs($logist);

        foreach (['/admin/logists', '/admin/rating'] as $path) {
            $resp = $this->get($path);
            $this->assertSame(403, $resp->status(), "logist GET $path must be 403, got {$resp->status()}");
        }

        // And there must be no sections in the sidebar
        $resp = $this->get('/admin');
        $this->assertSame(200, $resp->status());
        $this->assertStringNotContainsString('href="/admin/logists"', $resp->getContent(), 'the "Logists" link must not be in the logist menu');
        $this->assertStringNotContainsString('href="/admin/rating"', $resp->getContent(), 'the "Rating" link must not be in the logist menu');
    }

    /** The site root redirects to the admin panel (a guest — to the login form). */
    public function test_root_redirects_to_admin(): void
    {
        $resp = $this->get('/');
        $this->assertSame(301, $resp->status(), 'GET / must return 301');
        $this->assertSame('/admin', $resp->headers->get('Location'), 'the redirect must go to /admin');

        // A logged-in admin also goes to /admin (already inside the panel)
        $admin = $this->makeUser('admin@test.ru', 'admin', []);
        $resp = $this->actingAs($admin)->get('/');
        $this->assertSame(301, $resp->status());
        $this->assertSame('/admin', $resp->headers->get('Location'));
    }
}
