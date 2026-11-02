<?php

namespace App\Notifications;

use App\Enums\NotificationKind;
use App\Models\Sparepart;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Sparepart $sparepart,
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
        return [
            'kind' => NotificationKind::LowStock->value,
            'title' => 'Stok sparepart menipis',
            'message' => $this->sparepart->name.' ('.$this->sparepart->sku.') tersisa '
                .$this->sparepart->formattedStock().', batas minimum '
                .number_format($this->sparepart->minimum_stock, 0, ',', '.').' '
                .$this->sparepart->unit.'.',
            'related_type' => 'sparepart',
            'related_id' => $this->sparepart->getKey(),
            'url' => route('staff.spareparts.show', $this->sparepart),
        ];
    }
}
