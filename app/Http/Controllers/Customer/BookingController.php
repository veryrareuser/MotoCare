<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Booking\CancelBooking;
use App\Actions\Booking\CreateBooking;
use App\Actions\Booking\EnsureBookingSlots;
use App\Actions\Booking\RescheduleBooking;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\RescheduleBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Motorcycle;
use App\Models\ServiceType;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display the customer's bookings.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        $bookings = Booking::query()
            ->forCustomer($request->user())
            ->with(['motorcycle', 'slot', 'services.serviceType'])
            ->get();

        $upcoming = $bookings
            ->filter(fn (Booking $booking): bool => in_array(
                $booking->status,
                [BookingStatus::Confirmed, BookingStatus::CheckedIn],
                strict: true,
            ))
            ->sortBy('scheduled_at')
            ->values();

        $history = $bookings
            ->reject(fn (Booking $booking): bool => in_array(
                $booking->status,
                [BookingStatus::Confirmed, BookingStatus::CheckedIn],
                strict: true,
            ))
            ->sortByDesc('scheduled_at')
            ->values();

        return view('customer.bookings.index', [
            'upcoming' => $upcoming,
            'history' => $history,
        ]);
    }

    /**
     * Show the booking form with motorcycles, services, and open slots.
     */
    public function create(Request $request, EnsureBookingSlots $ensureBookingSlots): View
    {
        Gate::authorize('create', Booking::class);

        return view('customer.bookings.create', [
            'motorcycles' => $request->user()->motorcycles()
                ->orderBy('brand')
                ->orderBy('model')
                ->get(),
            'serviceTypes' => ServiceType::query()
                ->active()
                ->orderBy('name')
                ->get(),
            'slotGroups' => $this->availableSlotGroups($ensureBookingSlots),
        ]);
    }

    /**
     * Confirm the booking and issue its queue number.
     */
    public function store(StoreBookingRequest $request, CreateBooking $createBooking): RedirectResponse
    {
        $booking = $createBooking->handle(
            $request->user(),
            Motorcycle::query()->findOrFail($request->integer('motorcycle_id')),
            BookingSlot::query()->findOrFail($request->integer('booking_slot_id')),
            $request->array('service_type_ids'),
            $request->string('customer_note')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking '.$booking->queue_number.' berhasil dikonfirmasi.');
    }

    /**
     * Show the booking with its status history and change options.
     */
    public function show(Booking $booking, EnsureBookingSlots $ensureBookingSlots): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['motorcycle', 'slot', 'services.serviceType', 'statusLogs.changedBy']);

        return view('customer.bookings.show', [
            'booking' => $booking,
            'slotGroups' => $booking->canBeChanged()
                ? $this->availableSlotGroups($ensureBookingSlots, $booking)
                : collect(),
        ]);
    }

    /**
     * Move the booking to another slot.
     */
    public function reschedule(
        RescheduleBookingRequest $request,
        Booking $booking,
        RescheduleBooking $rescheduleBooking,
    ): RedirectResponse {
        $rescheduleBooking->handle(
            $booking,
            BookingSlot::query()->findOrFail($request->integer('booking_slot_id')),
            $request->user(),
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Jadwal booking berhasil diperbarui.');
    }

    /**
     * Cancel the booking before check-in.
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
            ->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Group bookable slots by local date for the slot picker.
     *
     * @return Collection<int, array{date: CarbonImmutable, slots: Collection<int, BookingSlot>}>
     */
    private function availableSlotGroups(
        EnsureBookingSlots $ensureBookingSlots,
        ?Booking $booking = null,
    ): Collection {
        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();
        $lastDay = $today->addDays((int) config('booking.booking_window_days'));

        return $ensureBookingSlots
            ->handleRange($today, $lastDay)
            ->filter(fn (BookingSlot $slot): bool => $slot->is_active
                && ! $slot->isFull()
                && ! $slot->isInThePast()
                && ($booking === null || $slot->getKey() !== $booking->booking_slot_id))
            ->groupBy(fn (BookingSlot $slot): string => $slot->localStart()->toDateString())
            ->map(fn (Collection $slots, string $date): array => [
                'date' => CarbonImmutable::parse($date, config('app.display_timezone')),
                'slots' => $slots->values(),
            ])
            ->values();
    }
}
