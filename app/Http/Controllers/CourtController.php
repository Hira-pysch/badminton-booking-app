<?php
namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    // READ all courts
    public function index()
    {
        $courts = Court::all();
        return response()->json($courts);
    }

    // READ single court
    public function show($id)
    {
        $court = Court::findOrFail($id);
        return response()->json($court);
    }

    // CREATE court (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'type'           => 'required|in:mat,wood',
            'price_per_hour' => 'required|numeric',
        ]);

        $court = Court::create($request->all());
        return response()->json($court, 201);
    }

    // UPDATE court (admin only)
    public function update(Request $request, $id)
    {
        $court = Court::findOrFail($id);
        $court->update($request->all());
        return response()->json($court);
    }

    // DELETE court (admin only)
    public function destroy($id)
    {
        Court::findOrFail($id)->delete();
        return response()->json(['message' => 'Court deleted']);
    }
}