<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Motorcycle\CreateMotorcycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\MotorcycleRequest;
use App\Models\Motorcycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MotorcycleController extends Controller
{
    /**
     * Display the customer's motorcycles.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Motorcycle::class);

        return view('customer.motorcycles.index', [
            'motorcycles' => $request->user()->motorcycles()->latest()->get(),
            'limit' => Motorcycle::MAX_PER_CUSTOMER,
        ]);
    }

    /**
     * Show the form for registering a motorcycle.
     */
    public function create(): View
    {
        Gate::authorize('create', Motorcycle::class);

        return view('customer.motorcycles.create', [
            'limit' => Motorcycle::MAX_PER_CUSTOMER,
        ]);
    }

    /**
     * Register a motorcycle for the authenticated customer.
     */
    public function store(MotorcycleRequest $request, CreateMotorcycle $createMotorcycle): RedirectResponse
    {
        $createMotorcycle->handle($request->user(), $request->validated());

        return redirect()
            ->route('motorcycles.index')
            ->with('success', 'Motor berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the motorcycle.
     */
    public function edit(Motorcycle $motorcycle): View
    {
        Gate::authorize('update', $motorcycle);

        return view('customer.motorcycles.edit', [
            'motorcycle' => $motorcycle,
        ]);
    }

    /**
     * Update the motorcycle.
     */
    public function update(MotorcycleRequest $request, Motorcycle $motorcycle): RedirectResponse
    {
        $motorcycle->update($request->validated());

        return redirect()
            ->route('motorcycles.index')
            ->with('success', 'Data motor berhasil diperbarui.');
    }

    /**
     * Soft delete the motorcycle.
     */
    public function destroy(Motorcycle $motorcycle): RedirectResponse
    {
        Gate::authorize('delete', $motorcycle);

        $motorcycle->delete();

        return redirect()
            ->route('motorcycles.index')
            ->with('success', 'Motor berhasil dihapus.');
    }
}
