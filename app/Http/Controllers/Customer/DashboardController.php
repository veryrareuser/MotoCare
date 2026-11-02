<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ServiceOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use App\Models\ServiceOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $motorcycleCount = $user->motorcycles()->count();

        $activeServiceOrders = ServiceOrder::query()
            ->where('customer_id', $user->getKey())
            ->active()
            ->with(['motorcycle', 'services', 'invoice'])
            ->orderByDesc('checked_in_at')
            ->orderByDesc('id')
            ->get();

        $dueReminderQuery = MaintenanceReminder::query()
            ->whereHas('motorcycle', fn (Builder $query) => $query->where('user_id', $user->getKey()))
            ->openDue(CarbonImmutable::now(config('app.display_timezone'))->startOfDay());

        $dueReminderCount = (clone $dueReminderQuery)->count();

        $dueReminders = $dueReminderQuery
            ->with('motorcycle')
            ->orderBy('due_date')
            ->orderBy('id')
            ->limit(3)
            ->get();

        $recentHistory = ServiceOrder::query()
            ->where('customer_id', $user->getKey())
            ->whereIn('status', [ServiceOrderStatus::Completed, ServiceOrderStatus::PickedUp])
            ->with(['motorcycle', 'services', 'invoice'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $activeCount = $activeServiceOrders->count();

        return view('customer.dashboard', [
            'metrics' => [
                [
                    'label' => 'Motor Terdaftar',
                    'value' => (string) $motorcycleCount,
                    'hint' => $motorcycleCount.' dari '.Motorcycle::MAX_PER_CUSTOMER.' slot terpakai',
                    'icon' => 'motorcycle',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Servis Aktif',
                    'value' => (string) $activeCount,
                    'hint' => $activeCount === 0 ? 'Tidak ada servis berjalan' : 'Sedang dikerjakan workshop',
                    'icon' => 'wrench',
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'Pengingat Jatuh Tempo',
                    'value' => (string) $dueReminderCount,
                    'hint' => $dueReminderCount === 0 ? 'Tidak ada pengingat jatuh tempo' : 'Perlu dijadwalkan servis',
                    'icon' => 'bell',
                    'tone' => 'warm',
                ],
            ],
            'activeServiceOrder' => $activeServiceOrders->first(),
            'dueReminders' => $dueReminders,
            'recentHistory' => $recentHistory,
        ]);
    }
}
