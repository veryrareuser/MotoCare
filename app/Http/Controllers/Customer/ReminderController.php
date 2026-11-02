<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceReminder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReminderController extends Controller
{
    /**
     * Display the customer's maintenance reminders.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', MaintenanceReminder::class);

        $reminders = MaintenanceReminder::query()
            ->with('motorcycle')
            ->whereHas(
                'motorcycle',
                fn (Builder $query) => $query->where('user_id', $request->user()->getKey()),
            )
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 WHEN status = 'sent' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return view('customer.reminders.index', [
            'reminders' => $reminders,
        ]);
    }

    /**
     * Show one of the customer's reminders.
     */
    public function show(MaintenanceReminder $reminder): View
    {
        Gate::authorize('view', $reminder);

        $reminder->load(['motorcycle', 'createdBy']);

        return view('customer.reminders.show', [
            'reminder' => $reminder,
        ]);
    }
}
