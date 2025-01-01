<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MedicineLogController extends Controller
{
    public function index(Request $request, $medicineId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            
            $logs = $medicine->logs()
                ->whereBetween('taken_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ])
                ->with('schedule')
                ->orderBy('taken_at', 'desc')
                ->get();

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch medicine logs'], 500);
        }
    }

    public function store(Request $request, $medicineId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_id' => 'nullable|exists:medicine_schedules,id',
                'taken_at' => 'required|date',
                'dosage_taken' => 'required|string',
                'skipped' => 'boolean',
                'skip_reason' => 'required_if:skipped,true|nullable|string',
                'notes' => 'nullable|string',
                'side_effects_noted' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);

            if ($request->schedule_id) {
                $schedule = MedicineSchedule::where('medicine_id', $medicineId)
                    ->findOrFail($request->schedule_id);
            }

            $log = new MedicineLog($request->all());
            $log->medicine_id = $medicine->id;
            $log->save();

            return response()->json($log, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create medicine log'], 500);
        }
    }

    public function update(Request $request, $medicineId, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'taken_at' => 'required|date',
                'dosage_taken' => 'required|string',
                'skipped' => 'boolean',
                'skip_reason' => 'required_if:skipped,true|nullable|string',
                'notes' => 'nullable|string',
                'side_effects_noted' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            $log = $medicine->logs()->findOrFail($id);
            $log->update($request->all());

            return response()->json($log);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update medicine log'], 500);
        }
    }

    public function destroy(Request $request, $medicineId, $id)
    {
        try {
            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            $log = $medicine->logs()->findOrFail($id);
            $log->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete medicine log'], 500);
        }
    }

    public function getStats(Request $request, $medicineId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $baby = $request->baby;
            $medicine = Medicine::where('baby_id', $baby->id)->findOrFail($medicineId);
            
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $stats = [
                'total_doses' => $medicine->logs()
                    ->whereBetween('taken_at', [$startDate, $endDate])
                    ->where('skipped', false)
                    ->count(),
                'skipped_doses' => $medicine->logs()
                    ->whereBetween('taken_at', [$startDate, $endDate])
                    ->where('skipped', true)
                    ->count(),
                'side_effects_reported' => $medicine->logs()
                    ->whereBetween('taken_at', [$startDate, $endDate])
                    ->whereNotNull('side_effects_noted')
                    ->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch medicine statistics'], 500);
        }
    }
} 