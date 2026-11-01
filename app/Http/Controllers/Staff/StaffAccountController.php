<?php

namespace App\Http\Controllers\Staff;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StaffAccountController extends Controller
{
    /**
     * Display the staff accounts.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('staff.accounts.index', [
            'accounts' => User::query()
                ->whereIn('role', UserRole::staffValues())
                ->orderBy('role')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for provisioning a staff account.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('staff.accounts.create', [
            'roles' => array_map(
                fn (string $role): UserRole => UserRole::from($role),
                UserRole::assignableStaffValues(),
            ),
        ]);
    }

    /**
     * Store a newly provisioned staff account.
     */
    public function store(StoreStaffAccountRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $account = User::create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        $account->markEmailAsVerified();

        return redirect()
            ->route('staff.accounts.index')
            ->with('success', 'Akun staf '.$account->name.' berhasil dibuat.');
    }

    /**
     * Reactivate a staff account.
     */
    public function activate(User $account): RedirectResponse
    {
        Gate::authorize('activate', $account);

        $account->update(['is_active' => true]);

        return back()->with('success', 'Akun '.$account->name.' diaktifkan.');
    }

    /**
     * Deactivate a staff account without deleting its history.
     */
    public function deactivate(User $account): RedirectResponse
    {
        Gate::authorize('deactivate', $account);

        $account->update(['is_active' => false]);

        return back()->with('success', 'Akun '.$account->name.' dinonaktifkan.');
    }
}
