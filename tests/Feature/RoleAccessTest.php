<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/staff/dashboard')->assertRedirect('/login');
    }

    public function test_customer_can_access_customer_dashboard(): void
    {
        $customer = User::factory()->role(UserRole::Customer)->create();

        $this->actingAs($customer)->get('/dashboard')->assertOk();
    }

    public function test_customer_cannot_access_staff_dashboard(): void
    {
        $customer = User::factory()->role(UserRole::Customer)->create();

        $this->actingAs($customer)->get('/staff/dashboard')->assertForbidden();
    }

    #[DataProvider('staffRoles')]
    public function test_staff_can_access_staff_dashboard(UserRole $role): void
    {
        $staff = User::factory()->role($role)->create();

        $this->actingAs($staff)->get('/staff/dashboard')->assertOk();
    }

    #[DataProvider('staffRoles')]
    public function test_staff_cannot_access_customer_dashboard(UserRole $role): void
    {
        $staff = User::factory()->role($role)->create();

        $this->actingAs($staff)->get('/dashboard')->assertForbidden();
    }

    public function test_inactive_user_cannot_access_any_dashboard(): void
    {
        $user = User::factory()->role(UserRole::Admin)->inactive()->create();

        $this->actingAs($user)->get('/staff/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_staff_visiting_login_is_redirected_to_staff_dashboard(): void
    {
        $staff = User::factory()->role(UserRole::Mechanic)->create();

        $this->actingAs($staff)->get('/login')->assertRedirect(route('staff.dashboard'));
    }

    public function test_staff_login_screen_can_be_rendered(): void
    {
        $this->get('/login/staff')
            ->assertOk()
            ->assertSee('Masuk Portal Staf');
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function staffRoles(): array
    {
        return [
            'admin' => [UserRole::Admin],
            'mechanic' => [UserRole::Mechanic],
            'inventory' => [UserRole::InventoryStaff],
            'cashier' => [UserRole::Cashier],
            'owner' => [UserRole::Owner],
        ];
    }
}
