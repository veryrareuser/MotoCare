<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Booking\CancelBooking;
use App\Actions\Booking\CheckInBooking;
use App\Actions\Booking\MarkBookingNoShow;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class BookingController extends Controller
{
    /**
     * Display the daily queue with optional status and text filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        $date = $this->resolveDate($request->query('date'));
        $status = BookingStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('search')->trim()->toString();

        $bookings = Booking::query()
            ->where('queue_date', $date->toDateString())
            ->with(['motorcycle', 'customer', 'slot', 'services.serviceType'])
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('queue_number', 'like', '%'.$search.'%')
                        ->orWhere('booking_number', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('motorcycle', fn (Builder $query) => $query->where('license_plate', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('queue_number')
            ->get();

        $counts = Booking::query()
            ->where('queue_date', $date->toDateString())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('staff.bookings.index', [
            'bookings' => $bookings,
            'date' => $date,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Show a booking with its queue and service details.
     */
    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['motorcycle', 'customer', 'slot', 'services.serviceType', 'statusLogs.changedBy']);

        return view('staff.bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Check the customer in when they arrive.
     */
    public function checkIn(
        Request $request,
        Booking $booking,
        CheckInBooking $checkInBooking,
    ): RedirectResponse {
        Gate::authorize('checkIn', $booking);

        $checkInBooking->handle($booking, $request->user());

        return redirect()
            ->route('staff.bookings.show', $booking)
            ->with('success', 'Booking '.$booking->queue_number.' berhasil check-in.');
    }

    /**
     * Cancel a booking on behalf of the customer.
     */
    public function cancel(
        CancelBookingRequest $request,
        Booking $booking,
        CancelBooking $cancelBooking,
    ): RedirectResponse {
        $cancelBooking->handle(
            $booking,
            $request->user(),
            $request->string('reason')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('staff.bookings.show', $booking)
            ->with('success', 'Booking '.$booking->queue_number.' dibatalkan.');
    }

    /**
     * Mark the customer as a no-show and release the slot.
     */
    public function noShow(
        Request $request,
        Booking $booking,
        MarkBookingNoShow $markBookingNoShow,
    ): RedirectResponse {
        Gate::authorize('markNoShow', $booking);

        $markBookingNoShow->handle(
            $booking,
            $request->user(),
            $request->string('reason')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('staff.bookings.show', $booking)
            ->with('success', 'Booking '.$booking->queue_number.' ditandai tidak hadir.');
    }

    /**
     * Resolve the requested queue date, falling back to today.
     */
    private function resolveDate(mixed $value): CarbonImmutable
    {
        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();

        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return $today;
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $value, config('app.display_timezone'));

            if ($date === null || $date->format('Y-m-d') !== $value) {
                return $today;
            }

            return $date->startOfDay();
        } catch (Throwable) {
            return $today;
        }
    }
}
