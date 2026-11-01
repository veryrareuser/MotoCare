<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SparepartCatalogController extends Controller
{
    /**
     * Browse the sparepart catalog. Online ordering is out of scope.
     */
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', Sparepart::class);

        $search = $request->string('search')->trim()->toString();

        return view('customer.spareparts.index', [
            'spareparts' => Sparepart::query()
                ->active()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('sku', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('name')
                ->get(),
            'search' => $search,
        ]);
    }
}
