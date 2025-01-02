<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HealthRecordController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'date|required_with:end_date',
                'end_date' => 'date|required_with:start_date|after_or_equal:start_date',
                'category' => 'string|in:general,vaccination,medication,allergy,surgery,test_result,other',
                'is_ongoing' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = HealthRecord::where('baby_id', $request->baby->id)
                ->orderBy('record_date', 'desc');

            if ($request->has(['start_date', 'end_date'])) {
                $query->whereBetween('record_date', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('is_ongoing')) {
                $query->where('is_ongoing', $request->is_ongoing);
            }

            $records = $query->paginate(20);
            return response()->json($records);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch health records'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Creating health record with data:', [
                'request_data' => $request->all(),
                'user' => $request->user(),
                'baby' => $request->baby
            ]);

            $validator = Validator::make($request->all(), [
                'record_date' => 'required|date',
                'category' => 'required|string|in:general,vaccination,medication,allergy,surgery,test_result,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'nullable|string|in:mild,moderate,severe',
                'treatment' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_ongoing' => 'boolean',
                'resolved_at' => 'nullable|date|after_or_equal:record_date'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $record = new HealthRecord($request->all());
            $record->baby_id = $request->baby->id;
            $record->type = 'record';
            
            \Log::info('Saving health record:', [
                'record_data' => $record->toArray()
            ]);
            
            $record->save();

            return response()->json($record, 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create health record:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to create health record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $record = HealthRecord::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Health record not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'record_date' => 'required|date',
                'category' => 'required|string|in:general,vaccination,medication,allergy,surgery,test_result,other',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'nullable|string|in:mild,moderate,severe',
                'treatment' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_ongoing' => 'boolean',
                'resolved_at' => 'nullable|date|after_or_equal:record_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $record = HealthRecord::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $record->update($request->all());

            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update health record'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $record = HealthRecord::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $record->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete health record'], 500);
        }
    }
} 