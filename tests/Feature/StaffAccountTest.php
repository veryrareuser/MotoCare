<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_staff_accounts(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();

        $this->actingAs($admin)
            ->get('/staff/accounts')
            ->assertOk()
            ->assertSee($mechanic->name)
            ->assertSee($mechanic->email);
    }

    public function test_owner_can_view_staff_accounts_but_cannot_provision_them(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($owner)
            ->get('/staff/accounts')
            ->assertOk()
            ->assertDontSee('Tambah Staf');

        $this->actingAs($owner)
            ->get('/staff/accounts/create')
            ->assertForbidden();

        $this->actingAs($owner)
            ->post('/staff/accounts', [
                'name' => 'Akun Baru',
                'email' => 'akun.baru@motocare.test',
                'role' => UserRole::Mechanic->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_a_staff_account(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $response = $this->actingAs($admin)->post('/staff/accounts', [
            'name' => 'Mekanik Baru',
            'email' => 'mekanik.baru@motocare.test',
            'phone' => '081234567890',
            'role' => UserRole::Mechanic->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertRedirect('/staff/accounts')
            ->assertSessionHasNoErrors();

        $account = User::query()->firstWhere('email', 'mekanik.baru@motocare.test');

        $this->assertNotNull($account);
        $this->assertSame(UserRole::Mechanic, $account->role);
        $this->assertSame('081234567890', $account->phone);
        $this->assertTrue($account->is_active);
        $this->assertNotNull($account->email_verified_at);
        $this->assertTrue(Hash::check('password', $account->password));
    }

    public function test_admin_cannot_create_customer_or_owner_accounts(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        foreach ([UserRole::Customer, UserRole::Owner] as $role) {
            $this->actingAs($admin)
                ->post('/staff/accounts', [
                    'name' => 'Tidak Boleh',
                    'email' => $role->value.'@motocare.test',
                    'role' => $role->value,
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ])
                ->assertSessionHasErrors('role');
        }

        $this->assertDatabaseCount('users', 1);
    }

    public function test_staff_account_email_must_be_unique(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $existing = User::factory()->role(UserRole::Cashier)->create();

        $this->actingAs($admin)
            ->post('/staff/accounts', [
                'name' => 'Duplikat',
                'email' => $existing->email,
                'role' => UserRole::Mechanic->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)
            ->patch('/staff/accounts/'.$admin->id.'/deactivate')
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_the_owner_account(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $owner = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($admin)
            ->patch('/staff/accounts/'.$owner->id.'/deactivate')
            ->assertForbidden();

        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_admin_can_deactivate_and_reactivate_a_staff_account(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();

        $this->actingAs($admin)
            ->patch('/staff/accounts/'.$mechanic->id.'/deactivate')
            ->assertRedirect();

        $this->assertFalse($mechanic->fresh()->is_active);

        $this->actingAs($admin)
            ->patch('/staff/accounts/'.$mechanic->id.'/activate')
            ->assertRedirect();

        $this->assertTrue($mechanic->fresh()->is_active);
    }

    public function test_deactivated_staff_member_is_logged_out_on_their_next_request(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create(['is_active' => false]);

        $this->actingAs($mechanic)
            ->get('/staff/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_mechanic_cannot_manage_staff_accounts(): void
    {
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();

        $this->actingAs($mechanic)
            ->get('/staff/accounts')
            ->assertForbidden();
    }
}
