<?php

namespace App\Http\Controllers\Staff;

use App\Enums\MaintenanceReminderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceReminderRequest;
use App\Models\MaintenanceReminder;
use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MaintenanceReminderController extends Controller
{
    /**
     * Display the manual maintenance reminders.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', MaintenanceReminder::class);

        $status = MaintenanceReminderStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('search')->trim()->toString();

        $reminders = MaintenanceReminder::query()
            ->with(['motorcycle.customer', 'createdBy'])
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('motorcycle', fn (Builder $query) => $query->where('license_plate', 'like', '%'.$search.'%'))
                        ->orWhereHas('motorcycle.customer', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 WHEN status = 'sent' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return view('staff.reminders.index', [
            'reminders' => $reminders,
            'status' => $status,
            'search' => $search,
            'counts' => MaintenanceReminder::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    /**
     * Show the form for creating a reminder.
     */
    public function create(): View
    {
        Gate::authorize('create', MaintenanceReminder::class);

        return view('staff.reminders.create', [
            'motorcycles' => $this->motorcycleOptions(),
        ]);
    }

    /**
     * Store a new manual reminder.
     */
    public function store(MaintenanceReminderRequest $request): RedirectResponse
    {
        $reminder = MaintenanceReminder::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->getKey(),
            'status' => MaintenanceReminderStatus::Scheduled,
        ]);

        return redirect()
            ->route('staff.reminders.show', $reminder)
            ->with('success', 'Pengingat servis berhasil dibuat.');
    }

    /**
     * Show a reminder and its due condition.
     */
    public function show(MaintenanceReminder $reminder): View
    {
        Gate::authorize('view', $reminder);

        $reminder->load(['motorcycle.customer', 'createdBy']);

        return view('staff.reminders.show', [
            'reminder' => $reminder,
        ]);
    }

    /**
     * Show the form for editing a scheduled reminder.
     */
    public function edit(MaintenanceReminder $reminder): View
    {
        Gate::authorize('update', $reminder);

        return view('staff.reminders.edit', [
            'reminder' => $reminder,
            'motorcycles' => $this->motorcycleOptions(),
        ]);
    }

    /**
     * Update a scheduled reminder.
     */
    public function update(MaintenanceReminderRequest $request, MaintenanceReminder $reminder): RedirectResponse
    {
        Gate::authorize('update', $reminder);

        $reminder->update($request->validated());

        return redirect()
            ->route('staff.reminders.show', $reminder)
            ->with('success', 'Pengingat servis berhasil diperbarui.');
    }

    /**
     * Cancel a reminder that no longer applies.
     */
    public function cancel(MaintenanceReminder $reminder): RedirectResponse
    {
        Gate::authorize('cancel', $reminder);

        $reminder->update([
            'status' => MaintenanceReminderStatus::Cancelled,
        ]);

        return redirect()
            ->route('staff.reminders.show', $reminder)
            ->with('success', 'Pengingat servis dibatalkan.');
    }

    /**
     * Mark a reminder as completed after the customer has serviced the motorcycle.
     */
    public function complete(MaintenanceReminder $reminder): RedirectResponse
    {
        Gate::authorize('complete', $reminder);

        $reminder->update([
            'status' => MaintenanceReminderStatus::Completed,
        ]);

        return redirect()
            ->route('staff.reminders.show', $reminder)
            ->with('success', 'Pengingat servis ditandai selesai.');
    }

    /**
     * @return Collection<int, Motorcycle>
     */
    private function motorcycleOptions(): Collection
    {
        return Motorcycle::query()
            ->with('customer')
            ->orderBy('license_plate')
            ->get();
    }
}
