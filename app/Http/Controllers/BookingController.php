<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // READ — get user's own bookings
    public function index(Request $request)
    {
        $bookings = Booking::with('court')
            ->where('user_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($bookings);
    }

    // READ — single booking
    public function show($id)
    {
        $booking = Booking::with(['court', 'user'])->findOrFail($id);
        return response()->json($booking);
    }

    // CREATE — make a booking
    public function store(Request $request)
    {
        $request->validate([
            'court_id'     => 'required|exists:courts,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
        ]);

        // Check if court is already booked at that time
        $conflict = Booking::where('court_id', $request->court_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Court is already booked at this time.'
            ], 409);
        }

        // Calculate price
        $court    = Court::findOrFail($request->court_id);
        $hours    = (strtotime($request->end_time) - strtotime($request->start_time)) / 3600;
        $price    = $court->price_per_hour * $hours;

        $booking = Booking::create([
            'user_id'      => $request->user()->id,
            'court_id'     => $request->court_id,
            'booking_date' => $request->booking_date,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
            'status'       => 'confirmed',
            'total_price'  => $price,
        ]);

        return response()->json($booking->load('court'), 201);
    }

    // UPDATE — edit booking
    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'booking_date' => 'sometimes|date|after_or_equal:today',
            'start_time'   => 'sometimes|date_format:H:i',
            'end_time'     => 'sometimes|date_format:H:i',
        ]);

        $booking->update($request->only([
            'booking_date', 'start_time', 'end_time', 'status'
        ]));

        return response()->json($booking->load('court'));
    }

    // DELETE — cancel booking
    public function destroy(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled successfully']);
    }

    // READ ALL — admin view all bookings
    public function allBookings()
    {
        $bookings = Booking::with(['court', 'user'])
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json($bookings);
    }
}