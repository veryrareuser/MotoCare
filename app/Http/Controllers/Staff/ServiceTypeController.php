<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTypeRequest;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceTypeController extends Controller
{
    /**
     * Display the service catalog.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', ServiceType::class);

        return view('staff.services.index', [
            'serviceTypes' => ServiceType::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for adding a service.
     */
    public function create(): View
    {
        Gate::authorize('create', ServiceType::class);

        return view('staff.services.create');
    }

    /**
     * Store a new service type.
     */
    public function store(ServiceTypeRequest $request): RedirectResponse
    {
        ServiceType::query()->create($request->validated());

        return redirect()
            ->route('staff.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a service.
     */
    public function edit(ServiceType $serviceType): View
    {
        Gate::authorize('update', $serviceType);

        return view('staff.services.edit', [
            'serviceType' => $serviceType,
        ]);
    }

    /**
     * Update the service type.
     */
    public function update(ServiceTypeRequest $request, ServiceType $serviceType): RedirectResponse
    {
        $serviceType->update($request->validated());

        return redirect()
            ->route('staff.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    /**
     * Retire the service type without deleting its history.
     */
    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        Gate::authorize('delete', $serviceType);

        $serviceType->delete();

        return redirect()
            ->route('staff.services.index')
            ->with('success', 'Layanan berhasil dinonaktifkan.');
    }
}
