<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the authenticated user's in-app notifications.
     */
    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString();

        $notifications = $request->user()
            ->notifications()
            ->when($filter === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'totalCount' => $request->user()->notifications()->count(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read and open its related page.
     */
    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
                && (int) $notification->notifiable_id === (int) $request->user()->getKey(),
            403,
        );

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;
        $baseUrl = url('/');

        if (is_string($url) && str_starts_with($url, $baseUrl.'/')) {
            return redirect()->to($url);
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Mark every unread notification as read.
     */
    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
