<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Матрица прав на создание записей (фикс: «ни в одной роли нет возможности
 * создавать записи» — кнопка CreateAction отсутствовала на List-страницах).
 *
 * Каждый сценарий — отдельный тест-метод: middleware AuthenticateSession
 * хранит в сессии хэш пароля и разлогинивает при смене actingAs-пользователя
 * внутри одного метода.
 *
 * Проверяет по каждому ресурсу:
 *   - Resource::canCreate() для админа / логиста с manage-компетенциями /
 *     логиста только с view-компетенциями;
 *   - HTTP GET List-страницы: 200 и наличие ссылки на /create у тех, кому
 *     можно; отсутствие ссылки и 403 на /create у тех, кому нельзя.
 *
 * А также: на дашборде больше нет AccountWidget («Добро пожаловать»),
 * но есть юзер-меню с выходом (fi-user-menu).
 */
class CreateAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role, array $competencies): User
    {
        return User::create([
            'name' => 'Тест '.$email,
            'email' => $email,
            'username' => explode('@', $email)[0],
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
            'competencies' => $competencies,
        ]);
    }

    /** [label, resourceClass, listPath, manage-логисту можно создавать?] */
    private function resources(): array
    {
        return [
            'Рейсы'      => [\App\Filament\Resources\TripResource::class, '/admin/trips', true],
            'Водители'   => [\App\Filament\Resources\DriverResource::class, '/admin/drivers', true],
            'Заказчики'  => [\App\Filament\Resources\CustomerResource::class, '/admin/customers', true],
            'Грузы'      => [\App\Filament\Resources\CargoTemplateResource::class, '/admin/cargo-templates', true],
            'Сотрудники' => [\App\Filament\Resources\EmployeeResource::class, '/admin/employees', false],
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
                "$label: на List-странице админа нет ссылки на /create",
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
                    "$label: manage-логист должен видеть кнопку создания",
                );
                $respCreate = $this->get($listPath.'/create');
                $this->assertSame(200, $respCreate->status(), "$label: manage GET {$listPath}/create");
            } else {
                // «Сотрудники» — административный раздел
                $this->assertSame(403, $resp->status(), "$label: manage GET $listPath должен быть 403");
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
            $this->assertFalse($resourceClass::canCreate(), "$label: viewer canCreate должен быть false");

            $resp = $this->get($listPath);
            if ($resp->status() === 200) {
                $this->assertStringNotContainsString(
                    $listPath.'/create',
                    $resp->getContent(),
                    "$label: viewer не должен видеть кнопку создания",
                );

                $respCreate = $this->get($listPath.'/create');
                $this->assertSame(403, $respCreate->status(), "$label: viewer GET create должен быть 403");
            } else {
                $this->assertSame(403, $resp->status(), "$label: viewer GET $listPath — 200 или 403, получено {$resp->status()}");
            }
        }
    }

    public function test_dashboard_has_no_account_widget(): void
    {
        $admin = $this->makeUser('admin@test.ru', 'admin', []);

        $resp = $this->actingAs($admin)->get('/admin');
        $this->assertSame(200, $resp->status());

        $content = $resp->getContent();
        $this->assertStringNotContainsString('Добро пожаловать', $content, 'AccountWidget должен быть убран с дашборда');
        $this->assertStringNotContainsString('fi-account-widget', $content, 'CSS-класс AccountWidget не должен присутствовать');

        // Юзер-меню с выходом остаётся в топбаре. Проверяем без привязки
        // к локали: 'logout' — имя действия/URL, а не перевод.
        $this->assertStringContainsString('fi-user-menu', $content, 'Юзер-меню топбара должно остаться');
        $this->assertStringContainsString('logout', $content, 'Действие выхода должно остаться доступным');
    }
}
