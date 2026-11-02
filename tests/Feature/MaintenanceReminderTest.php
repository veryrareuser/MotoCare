<?php

namespace Tests\Feature;

use App\Enums\MaintenanceReminderStatus;
use App\Enums\UserRole;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_reminder_with_a_due_date(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);

        $response = $this->actingAs($admin)->post('/staff/reminders', [
            'motorcycle_id' => $motorcycle->id,
            'title' => 'Servis rutin berikutnya',
            'description' => 'Cek rem dan ban.',
            'due_date' => now()->addDays(14)->toDateString(),
            'due_odometer' => null,
        ]);

        $reminder = MaintenanceReminder::query()->firstOrFail();

        $response
            ->assertRedirect('/staff/reminders/'.$reminder->id)
            ->assertSessionHasNoErrors();

        $this->assertSame($admin->id, $reminder->created_by);
        $this->assertSame($motorcycle->id, $reminder->motorcycle_id);
        $this->assertSame(MaintenanceReminderStatus::Scheduled, $reminder->status);
        $this->assertSame('Servis rutin berikutnya', $reminder->title);
        $this->assertNull($reminder->due_odometer);
        $this->assertNull($reminder->sent_at);
    }

    public function test_admin_can_create_a_reminder_with_a_due_odometer(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);

        $this->actingAs($admin)->post('/staff/reminders', [
            'motorcycle_id' => $motorcycle->id,
            'title' => 'Servis 16.000 km',
            'due_date' => null,
            'due_odometer' => 16000,
        ])->assertSessionHasNoErrors();

        $reminder = MaintenanceReminder::query()->firstOrFail();

        $this->assertNull($reminder->due_date);
        $this->assertSame(16000, $reminder->due_odometer);
    }

    public function test_a_reminder_requires_a_due_date_or_a_due_odometer(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);

        $this->actingAs($admin)
            ->from('/staff/reminders/create')
            ->post('/staff/reminders', [
                'motorcycle_id' => $motorcycle->id,
                'title' => 'Pengingat tanpa patokan',
                'due_date' => null,
                'due_odometer' => null,
            ])
            ->assertRedirect('/staff/reminders/create')
            ->assertSessionHasErrors(['due_date', 'due_odometer']);

        $this->assertSame(0, MaintenanceReminder::query()->count());
    }

    public function test_due_odometer_cannot_be_below_the_motorcycle_reading(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);

        $this->actingAs($admin)
            ->from('/staff/reminders/create')
            ->post('/staff/reminders', [
                'motorcycle_id' => $motorcycle->id,
                'title' => 'Terlalu mundur',
                'due_date' => null,
                'due_odometer' => 11999,
            ])
            ->assertRedirect('/staff/reminders/create')
            ->assertSessionHasErrors('due_odometer');

        $this->assertSame(0, MaintenanceReminder::query()->count());
    }

    public function test_admin_can_update_a_scheduled_reminder(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $reminder = $this->reminderFor(User::factory()->create(), [
            'created_by' => $admin->id,
            'title' => 'Judul lama',
        ]);

        $this->actingAs($admin)
            ->patch('/staff/reminders/'.$reminder->id, [
                'motorcycle_id' => $reminder->motorcycle_id,
                'title' => 'Judul baru',
                'description' => 'Diperbarui.',
                'due_date' => now()->addDays(21)->toDateString(),
                'due_odometer' => 20000,
            ])
            ->assertRedirect('/staff/reminders/'.$reminder->id)
            ->assertSessionHasNoErrors();

        $reminder->refresh();

        $this->assertSame('Judul baru', $reminder->title);
        $this->assertSame(20000, $reminder->due_odometer);
        $this->assertSame('Diperbarui.', $reminder->description);
    }

    public function test_a_sent_reminder_cannot_be_edited(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $reminder = $this->reminderFor(User::factory()->create(), [
            'created_by' => $admin->id,
            'status' => MaintenanceReminderStatus::Sent,
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch('/staff/reminders/'.$reminder->id, [
                'motorcycle_id' => $reminder->motorcycle_id,
                'title' => 'Tidak boleh berubah',
                'due_date' => now()->addDays(7)->toDateString(),
            ])
            ->assertForbidden();

        $this->assertNotSame('Tidak boleh berubah', $reminder->fresh()->title);
    }

    public function test_admin_can_cancel_and_complete_open_reminders(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $scheduled = $this->reminderFor(User::factory()->create(), [
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch('/staff/reminders/'.$scheduled->id.'/cancel')
            ->assertRedirect('/staff/reminders/'.$scheduled->id);

        $this->assertSame(MaintenanceReminderStatus::Cancelled, $scheduled->fresh()->status);

        $sent = $this->reminderFor(User::factory()->create(), [
            'created_by' => $admin->id,
            'status' => MaintenanceReminderStatus::Sent,
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch('/staff/reminders/'.$sent->id.'/complete')
            ->assertRedirect('/staff/reminders/'.$sent->id);

        $this->assertSame(MaintenanceReminderStatus::Completed, $sent->fresh()->status);
    }

    public function test_customer_only_sees_their_own_reminders(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $own = $this->reminderFor($customer, ['title' => 'Pengingat motor sendiri']);
        $other = $this->reminderFor($otherCustomer, ['title' => 'Pengingat motor orang lain']);

        $this->actingAs($customer)
            ->get('/reminders')
            ->assertOk()
            ->assertSee('Pengingat motor sendiri')
            ->assertDontSee('Pengingat motor orang lain');

        $this->actingAs($customer)
            ->get('/reminders/'.$own->id)
            ->assertOk()
            ->assertSee('Pengingat motor sendiri');

        $this->actingAs($customer)
            ->get('/reminders/'.$other->id)
            ->assertForbidden();
    }

    public function test_customer_reminder_detail_shows_the_due_condition(): void
    {
        $customer = User::factory()->create();
        $reminder = $this->reminderFor($customer, [
            'title' => 'Servis besar',
            'due_date' => now()->addDays(10)->toDateString(),
            'due_odometer' => 18000,
        ]);

        $this->actingAs($customer)
            ->get('/reminders/'.$reminder->id)
            ->assertOk()
            ->assertSee('Servis besar')
            ->assertSee('18.000 km');
    }

    public function test_owner_can_view_reminders_but_cannot_manage_them(): void
    {
        $owner = User::factory()->role(UserRole::Owner)->create();
        $reminder = $this->reminderFor(User::factory()->create());

        $this->actingAs($owner)
            ->get('/staff/reminders')
            ->assertOk()
            ->assertSee($reminder->title);

        $this->actingAs($owner)
            ->get('/staff/reminders/'.$reminder->id)
            ->assertOk();

        $this->actingAs($owner)
            ->get('/staff/reminders/create')
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch('/staff/reminders/'.$reminder->id.'/complete')
            ->assertForbidden();
    }

    public function test_non_admin_staff_cannot_access_reminders(): void
    {
        $reminder = $this->reminderFor(User::factory()->create());

        foreach ([UserRole::Mechanic, UserRole::Cashier, UserRole::InventoryStaff] as $role) {
            $staff = User::factory()->role($role)->create();

            $this->actingAs($staff)
                ->get('/staff/reminders')
                ->assertForbidden();

            $this->actingAs($staff)
                ->get('/staff/reminders/'.$reminder->id)
                ->assertForbidden();
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function reminderFor(User $customer, array $attributes = []): MaintenanceReminder
    {
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);

        return MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->create($attributes);
    }
}
