<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MedicineScheduleController extends Controller
{
    public function index(Request $request, $medicineId)
    {
        try {
            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            $schedules = $medicine->schedules()
                ->where('is_active', true)
                ->orderBy('time')
                ->get();

            return response()->json($schedules);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch schedules'], 500);
        }
    }

    public function store(Request $request, $medicineId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'time' => 'required|date_format:H:i',
                'dosage' => 'required|string',
                'frequency' => 'required|in:daily,weekly,monthly,as_needed',
                'days_of_week' => 'nullable|array',
                'days_of_week.*' => 'integer|between:1,7',
                'days_of_month' => 'nullable|array',
                'days_of_month.*' => 'integer|between:1,31',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            
            $schedule = new MedicineSchedule($request->all());
            $schedule->medicine_id = $medicine->id;
            $schedule->save();

            return response()->json($schedule, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create schedule'], 500);
        }
    }

    public function update(Request $request, $medicineId, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'time' => 'required|date_format:H:i',
                'dosage' => 'required|string',
                'frequency' => 'required|in:daily,weekly,monthly,as_needed',
                'days_of_week' => 'nullable|array',
                'days_of_week.*' => 'integer|between:1,7',
                'days_of_month' => 'nullable|array',
                'days_of_month.*' => 'integer|between:1,31',
                'is_active' => 'boolean',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            $schedule = $medicine->schedules()->findOrFail($id);
            $schedule->update($request->all());

            return response()->json($schedule);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update schedule'], 500);
        }
    }

    public function destroy(Request $request, $medicineId, $id)
    {
        try {
            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            $schedule = $medicine->schedules()->findOrFail($id);
            $schedule->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete schedule'], 500);
        }
    }

    public function getUpcoming(Request $request)
    {
        try {
            $baby = $request->baby;
            $now = Carbon::now();
            $endOfDay = Carbon::now()->endOfDay();

            $schedules = MedicineSchedule::whereHas('medicine', function ($query) use ($baby) {
                $query->where('baby_id', $baby->id)
                    ->where('is_active', true);
            })
            ->where('is_active', true)
            ->whereTime('time', '>=', $now->format('H:i:s'))
            ->whereTime('time', '<=', $endOfDay->format('H:i:s'))
            ->with('medicine')
            ->orderBy('time')
            ->get();

            return response()->json($schedules);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch upcoming schedules'], 500);
        }
    }
} 