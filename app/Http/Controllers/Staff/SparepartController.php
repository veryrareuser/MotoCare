<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\SparepartRequest;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SparepartController extends Controller
{
    /**
     * Display the sparepart catalog with stock levels.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Sparepart::class);

        $search = $request->string('search')->trim()->toString();
        $onlyLowStock = $request->boolean('low_stock');

        $spareparts = Sparepart::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->when($onlyLowStock, fn ($query) => $query->lowStock())
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('staff.spareparts.index', [
            'spareparts' => $spareparts,
            'search' => $search,
            'onlyLowStock' => $onlyLowStock,
            'lowStockCount' => Sparepart::query()->lowStock()->count(),
        ]);
    }

    /**
     * Show the form for adding a sparepart.
     */
    public function create(): View
    {
        Gate::authorize('create', Sparepart::class);

        return view('staff.spareparts.create');
    }

    /**
     * Store a new sparepart with an opening stock of zero.
     */
    public function store(SparepartRequest $request): RedirectResponse
    {
        $sparepart = Sparepart::query()->create([
            ...$request->validated(),
            'stock_quantity' => 0,
        ]);

        return redirect()
            ->route('staff.spareparts.show', $sparepart)
            ->with('success', 'Sparepart berhasil ditambahkan. Catat stok masuk untuk mengisi saldo.');
    }

    /**
     * Show the sparepart with its stock ledger.
     */
    public function show(Sparepart $sparepart): View
    {
        Gate::authorize('view', $sparepart);

        return view('staff.spareparts.show', [
            'sparepart' => $sparepart,
            'compatibilities' => $sparepart->compatibilities()
                ->orderBy('brand')
                ->orderBy('model')
                ->get(),
            'movements' => $sparepart->movements()
                ->with('performedBy')
                ->latest('id')
                ->limit(50)
                ->get(),
        ]);
    }

    /**
     * Show the form for editing a sparepart.
     */
    public function edit(Sparepart $sparepart): View
    {
        Gate::authorize('update', $sparepart);

        return view('staff.spareparts.edit', [
            'sparepart' => $sparepart,
        ]);
    }

    /**
     * Update the sparepart catalog details.
     *
     * Stock is never edited here; it only changes through the ledger.
     */
    public function update(SparepartRequest $request, Sparepart $sparepart): RedirectResponse
    {
        $sparepart->update($request->validated());

        return redirect()
            ->route('staff.spareparts.show', $sparepart)
            ->with('success', 'Data sparepart berhasil diperbarui.');
    }

    /**
     * Retire the sparepart without deleting its stock history.
     */
    public function destroy(Sparepart $sparepart): RedirectResponse
    {
        Gate::authorize('delete', $sparepart);

        $sparepart->delete();

        return redirect()
            ->route('staff.spareparts.index')
            ->with('success', 'Sparepart berhasil dinonaktifkan.');
    }
}
