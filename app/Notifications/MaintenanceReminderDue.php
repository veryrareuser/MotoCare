<?php

namespace App\Notifications;

use App\Enums\NotificationKind;
use App\Models\MaintenanceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceReminderDue extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceReminder $reminder,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $motorcycle = $this->reminder->motorcycle;
        $motorcycleName = $motorcycle?->displayName() ?? 'Motor Anda';
        $licensePlate = $motorcycle?->license_plate;

        return [
            'kind' => NotificationKind::Reminder->value,
            'title' => 'Pengingat servis jatuh tempo',
            'message' => 'Pengingat "'.$this->reminder->title.'" untuk '.$motorcycleName
                .($licensePlate !== null ? ' ('.$licensePlate.')' : '')
                .' sudah jatuh tempo: '.$this->reminder->dueSummary().'.',
            'related_type' => 'maintenance_reminder',
            'related_id' => $this->reminder->getKey(),
            'url' => route('reminders.show', $this->reminder),
        ];
    }
}
