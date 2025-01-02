<?php

namespace App\Http\Controllers;

use App\Models\DoctorVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DoctorVisitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'date|required_with:end_date',
                'end_date' => 'date|required_with:start_date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = DoctorVisit::where('baby_id', $request->baby->id)
                ->orderBy('visit_date', 'desc');

            if ($request->has(['start_date', 'end_date'])) {
                $query->whereBetween('visit_date', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            $visits = $query->paginate(20);
            return response()->json($visits);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch doctor visits'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visit_date' => 'required|date',
                'doctor_name' => 'required|string|max:255',
                'clinic_location' => 'nullable|string|max:255',
                'reason_for_visit' => 'required|string',
                'diagnosis' => 'nullable|string',
                'prescription' => 'nullable|string',
                'notes' => 'nullable|string',
                'follow_up_instructions' => 'nullable|string',
                'next_visit_date' => 'nullable|date|after:visit_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $visit = new DoctorVisit($request->all());
            $visit->baby_id = $request->baby->id;
            $visit->save();

            return response()->json($visit, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create doctor visit'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $visit = DoctorVisit::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            return response()->json($visit);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Doctor visit not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visit_date' => 'required|date',
                'doctor_name' => 'required|string|max:255',
                'clinic_location' => 'nullable|string|max:255',
                'reason_for_visit' => 'required|string',
                'diagnosis' => 'nullable|string',
                'prescription' => 'nullable|string',
                'notes' => 'nullable|string',
                'follow_up_instructions' => 'nullable|string',
                'next_visit_date' => 'nullable|date|after:visit_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $visit = DoctorVisit::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $visit->update($request->all());

            return response()->json($visit);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update doctor visit'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $visit = DoctorVisit::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $visit->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete doctor visit'], 500);
        }
    }
} 