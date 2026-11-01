<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompatibilityRequest;
use App\Models\MotorcycleCompatibility;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SparepartCompatibilityController extends Controller
{
    /**
     * Record a compatible motorcycle model for the sparepart.
     */
    public function store(CompatibilityRequest $request, Sparepart $sparepart): RedirectResponse
    {
        $sparepart->compatibilities()->create($request->validated());

        return redirect()
            ->route('staff.spareparts.show', $sparepart)
            ->with('success', 'Kompatibilitas motor berhasil ditambahkan.');
    }

    /**
     * Remove a compatibility record from the sparepart.
     */
    public function destroy(Sparepart $sparepart, MotorcycleCompatibility $compatibility): RedirectResponse
    {
        Gate::authorize('update', $sparepart);

        abort_unless($compatibility->sparepart_id === $sparepart->getKey(), 404);

        $compatibility->delete();

        return redirect()
            ->route('staff.spareparts.show', $sparepart)
            ->with('success', 'Kompatibilitas motor dihapus.');
    }
}
