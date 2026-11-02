<?php

namespace Tests\Feature;

use App\Actions\Inventory\NotifyLowStockSpareparts;
use App\Actions\Reminders\NotifyDueMaintenanceReminders;
use App\Enums\MaintenanceReminderStatus;
use App\Enums\UserRole;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\Sparepart;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\MaintenanceReminderDue;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-02 07:00:00', config('app.display_timezone')));
    }

    public function test_due_date_reminder_notifies_the_customer_and_marks_the_reminder_sent(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);
        $admin = User::factory()->role(UserRole::Admin)->create();
        $reminder = MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->create([
                'created_by' => $admin->id,
                'title' => 'Servis rutin',
                'due_date' => now()->toDateString(),
                'due_odometer' => null,
                'status' => MaintenanceReminderStatus::Scheduled,
            ]);

        $sent = app(NotifyDueMaintenanceReminders::class)->handle();

        $this->assertSame(1, $sent);

        $reminder->refresh();

        $this->assertSame(MaintenanceReminderStatus::Sent, $reminder->status);
        $this->assertNotNull($reminder->sent_at);

        $notification = $customer->notifications()->firstOrFail();

        $this->assertSame(MaintenanceReminderDue::class, $notification->type);
        $this->assertSame('reminder', $notification->data['kind']);
        $this->assertSame('maintenance_reminder', $notification->data['related_type']);
        $this->assertSame($reminder->id, $notification->data['related_id']);
        $this->assertStringContainsString('Servis rutin', $notification->data['message']);
        $this->assertSame(route('reminders.show', $reminder), $notification->data['url']);
        $this->assertNull($notification->read_at);
    }

    public function test_due_odometer_reminder_is_sent_and_future_reminders_are_left_alone(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create([
            'user_id' => $customer->id,
            'odometer_current' => 12000,
        ]);
        $admin = User::factory()->role(UserRole::Admin)->create();

        $dueByOdometer = MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->dueByOdometer(12000)
            ->create(['created_by' => $admin->id]);

        $notYetDue = MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->create([
                'created_by' => $admin->id,
                'due_date' => now()->addDays(10)->toDateString(),
                'due_odometer' => 20000,
            ]);

        $sent = app(NotifyDueMaintenanceReminders::class)->handle();

        $this->assertSame(1, $sent);
        $this->assertSame(MaintenanceReminderStatus::Sent, $dueByOdometer->fresh()->status);
        $this->assertSame(MaintenanceReminderStatus::Scheduled, $notYetDue->fresh()->status);
        $this->assertSame(1, $customer->notifications()->count());
    }

    public function test_running_the_due_reminder_action_twice_does_not_duplicate_notifications(): void
    {
        $customer = User::factory()->create();
        $motorcycle = Motorcycle::factory()->create(['user_id' => $customer->id]);
        $admin = User::factory()->role(UserRole::Admin)->create();
        MaintenanceReminder::factory()
            ->forMotorcycle($motorcycle)
            ->dueByDate()
            ->create(['created_by' => $admin->id]);

        $action = app(NotifyDueMaintenanceReminders::class);

        $this->assertSame(1, $action->handle());
        $this->assertSame(0, $action->handle());
        $this->assertSame(1, $customer->notifications()->count());
    }

    public function test_low_stock_alerts_notify_active_admins_and_inventory_staff_only(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $inventory = User::factory()->role(UserRole::InventoryStaff)->create();
        $inactiveInventory = User::factory()->role(UserRole::InventoryStaff)->inactive()->create();
        $mechanic = User::factory()->role(UserRole::Mechanic)->create();
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $owner = User::factory()->role(UserRole::Owner)->create();
        $customer = User::factory()->create();

        $lowStock = Sparepart::factory()->lowStock()->create();
        Sparepart::factory()->create(['stock_quantity' => 30, 'minimum_stock' => 5]);
        Sparepart::factory()->lowStock()->inactive()->create();

        $sent = app(NotifyLowStockSpareparts::class)->handle();

        $this->assertSame(2, $sent);
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $inventory->notifications()->count());
        $this->assertSame(0, $inactiveInventory->notifications()->count());
        $this->assertSame(0, $mechanic->notifications()->count());
        $this->assertSame(0, $cashier->notifications()->count());
        $this->assertSame(0, $owner->notifications()->count());
        $this->assertSame(0, $customer->notifications()->count());

        $notification = $admin->notifications()->firstOrFail();

        $this->assertSame(LowStockAlert::class, $notification->type);
        $this->assertSame('low_stock', $notification->data['kind']);
        $this->assertSame('sparepart', $notification->data['related_type']);
        $this->assertSame($lowStock->id, $notification->data['related_id']);
        $this->assertStringContainsString($lowStock->name, $notification->data['message']);
    }

    public function test_low_stock_alerts_are_limited_to_once_per_user_and_day(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        Sparepart::factory()->lowStock()->create();

        $action = app(NotifyLowStockSpareparts::class);

        $this->assertSame(1, $action->handle());
        $this->assertSame(0, $action->handle());

        $this->travelTo(CarbonImmutable::parse('2026-11-03 07:05:00', config('app.display_timezone')));

        $this->assertSame(1, $action->handle());
        $this->assertSame(2, $admin->notifications()->count());
    }

    public function test_customer_can_read_their_own_notifications(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $unread = $this->notificationFor($customer, ['title' => 'Pengingat servis']);
        $this->notificationFor($customer, ['title' => 'Stok rendah'], readAt: now());
        $otherNotification = $this->notificationFor($otherCustomer, ['title' => 'Milik orang lain']);

        $this->actingAs($customer)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Pengingat servis')
            ->assertSee('Stok rendah')
            ->assertDontSee('Milik orang lain');

        $unreadResponse = $this->actingAs($customer)
            ->get('/notifications?filter=unread')
            ->assertOk()
            ->assertSee('Pengingat servis');

        $unreadPage = $unreadResponse->viewData('notifications');

        $this->assertSame(1, $unreadPage->total());
        $this->assertSame([$unread->id], $unreadPage->getCollection()->pluck('id')->all());

        $this->actingAs($customer)
            ->patch('/notifications/'.$unread->id)
            ->assertRedirect(route('service-history.index'));

        $this->assertNotNull($unread->fresh()->read_at);

        $this->actingAs($customer)
            ->patch('/notifications/'.$otherNotification->id)
            ->assertForbidden();
    }

    public function test_mark_all_read_only_affects_the_current_user(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $this->notificationFor($customer, ['title' => 'Pertama']);
        $this->notificationFor($customer, ['title' => 'Kedua']);
        $other = $this->notificationFor($otherCustomer, ['title' => 'Lain']);

        $this->actingAs($customer)
            ->patch('/notifications/read-all')
            ->assertRedirect('/notifications');

        $this->assertSame(0, $customer->unreadNotifications()->count());
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_guest_cannot_open_notifications(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notificationFor(
        User $user,
        array $data = [],
        ?DateTimeInterface $readAt = null,
    ): DatabaseNotification {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => MaintenanceReminderDue::class,
            'data' => [
                'kind' => 'reminder',
                'title' => 'Notifikasi',
                'message' => 'Pesan notifikasi.',
                'related_type' => 'maintenance_reminder',
                'related_id' => 1,
                'url' => route('service-history.index'),
                ...$data,
            ],
            'read_at' => $readAt,
        ]);
    }
}
