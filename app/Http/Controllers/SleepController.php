<?php

namespace App\Http\Controllers;

use App\Models\SleepLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class SleepController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'date|required_with:end_date',
                'end_date' => 'date|required_with:start_date|after_or_equal:start_date',
                'is_nap' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Debug information
            \Log::info('Sleep logs request:', [
                'baby_id' => $request->baby->id ?? 'not set',
                'filters' => $request->all()
            ]);

            if (!$request->baby) {
                return response()->json(['error' => 'Baby not found in request'], 400);
            }

            $query = SleepLog::forBaby($request->baby->id)
                ->orderBy('start_time', 'desc');

            if ($request->has(['start_date', 'end_date'])) {
                $query->inDateRange($request->start_date, $request->end_date);
            }

            if ($request->has('is_nap')) {
                $query = $request->is_nap ? $query->napsOnly() : $query->nightSleepOnly();
            }

            $result = $query->paginate(20);

            // Debug information
            \Log::info('Sleep logs response:', [
                'count' => $result->count(),
                'total' => $result->total()
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error in SleepController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'error' => 'Failed to fetch sleep logs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'quality' => 'nullable|in:poor,fair,good,excellent',
            'location' => 'nullable|in:crib,bed,stroller,car,other',
            'is_nap' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sleepLog = new SleepLog($request->all());
        $sleepLog->baby_id = $request->baby->id;
        $sleepLog->save();

        return response()->json($sleepLog, 201);
    }

    public function show(Request $request, $id)
    {
        $sleepLog = SleepLog::forBaby($request->baby->id)->findOrFail($id);
        return response()->json($sleepLog);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'sometimes|required|date',
            'end_time' => 'nullable|date|after:start_time',
            'quality' => 'nullable|in:poor,fair,good,excellent',
            'location' => 'nullable|in:crib,bed,stroller,car,other',
            'is_nap' => 'sometimes|required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sleepLog = SleepLog::forBaby($request->baby->id)->findOrFail($id);
        $sleepLog->update($request->all());

        return response()->json($sleepLog);
    }

    public function destroy(Request $request, $id)
    {
        $sleepLog = SleepLog::forBaby($request->baby->id)->findOrFail($id);
        $sleepLog->delete();

        return response()->json(null, 204);
    }

    public function stats(Request $request)
    {
        try {
            // Log incoming request data
            \Log::info('Sleep stats request received:', [
                'all_params' => $request->all(),
                'query_params' => $request->query(),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
            ]);

            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                \Log::warning('Sleep stats validation failed:', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (!$request->baby) {
                return response()->json(['error' => 'Baby not found in request'], 400);
            }

            $logs = SleepLog::forBaby($request->baby->id)
                ->inDateRange($request->start_date, $request->end_date)
                ->get();

            $stats = [
                'total_sleep_minutes' => $logs->sum('duration_minutes'),
                'average_sleep_minutes_per_day' => $logs->avg('duration_minutes'),
                'naps' => [
                    'count' => $logs->where('is_nap', true)->count(),
                    'total_minutes' => $logs->where('is_nap', true)->sum('duration_minutes'),
                    'average_duration' => $logs->where('is_nap', true)->avg('duration_minutes')
                ],
                'night_sleep' => [
                    'count' => $logs->where('is_nap', false)->count(),
                    'total_minutes' => $logs->where('is_nap', false)->sum('duration_minutes'),
                    'average_duration' => $logs->where('is_nap', false)->avg('duration_minutes')
                ],
                'quality_distribution' => $logs->groupBy('quality')->map->count(),
                'location_distribution' => $logs->groupBy('location')->map->count()
            ];

            \Log::info('Sleep stats response:', ['stats' => $stats]);

            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Error in SleepController@stats: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'error' => 'Failed to fetch sleep statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 