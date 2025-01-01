<?php

namespace App\Http\Controllers;

use App\Models\FeedingLog;
use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FeedingController extends Controller
{
    public function index(Request $request)
    {
        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();
        
        $query = FeedingLog::where('baby_id', $baby->id)
            ->orderBy('start_time', 'desc');

        // Optional date filtering
        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_time', $date);
        }

        $logs = $query->paginate(20);
        
        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:breast,bottle,solid',
            'start_time' => 'required|date',
            'duration' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'breast_side' => 'nullable|required_if:type,breast|in:left,right,both',
            'food_type' => 'nullable|required_if:type,solid|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();

        $feedingLog = new FeedingLog($request->all());
        $feedingLog->baby_id = $baby->id;
        $feedingLog->save();

        return response()->json([
            'status' => 'success',
            'data' => $feedingLog
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();
        
        $feedingLog = FeedingLog::where('baby_id', $baby->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $feedingLog
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:breast,bottle,solid',
            'start_time' => 'sometimes|required|date',
            'duration' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'breast_side' => 'nullable|required_if:type,breast|in:left,right,both',
            'food_type' => 'nullable|required_if:type,solid|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();
        
        $feedingLog = FeedingLog::where('baby_id', $baby->id)
            ->where('id', $id)
            ->firstOrFail();

        $feedingLog->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $feedingLog
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();
        
        $feedingLog = FeedingLog::where('baby_id', $baby->id)
            ->where('id', $id)
            ->firstOrFail();

        $feedingLog->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Feeding log deleted successfully'
        ]);
    }

    public function stats(Request $request)
    {
        $baby = Baby::where('user_id', $request->user()->id)->firstOrFail();
        
        $date = $request->get('date', Carbon::today()->toDateString());
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        $stats = [
            'total_feedings' => FeedingLog::where('baby_id', $baby->id)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->count(),
            'by_type' => FeedingLog::where('baby_id', $baby->id)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
} 