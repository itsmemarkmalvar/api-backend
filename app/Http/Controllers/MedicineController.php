<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $baby = $request->baby;
            $medicines = Medicine::where('baby_id', $baby->id)
                ->with(['schedules' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($medicines);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch medicines'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'instructions' => 'nullable|string',
                'side_effects' => 'nullable|string',
                'form' => 'required|string',
                'strength' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = new Medicine($request->all());
            $medicine->baby_id = $baby->id;
            $medicine->save();

            return response()->json($medicine, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create medicine'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)
                ->with(['schedules' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->findOrFail($id);

            return response()->json($medicine);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'instructions' => 'nullable|string',
                'side_effects' => 'nullable|string',
                'form' => 'required|string',
                'strength' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'boolean',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($id);
            $medicine->update($request->all());

            return response()->json($medicine);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update medicine'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($id);
            $medicine->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete medicine'], 500);
        }
    }
} 