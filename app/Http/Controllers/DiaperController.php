<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use App\Models\DiaperLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DiaperController extends Controller
{
    protected $baby;

    public function __construct()
    {
        $this->baby = null;
    }

    protected function getBaby()
    {
        if ($this->baby === null) {
            $this->baby = Baby::where('user_id', auth()->id())->first();
            
            if (!$this->baby) {
                abort(404, 'No baby profile found. Please create a baby profile first.');
            }
        }
        
        return $this->baby;
    }

    public function index()
    {
        try {
            $logs = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $logs]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching diaper logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|in:wet,dirty,both',
                'time' => 'required|date',
                'notes' => 'nullable|string',
                'color' => 'nullable|string',
                'consistency' => 'nullable|string',
                'rash_noticed' => 'nullable|boolean',
                'rash_description' => 'nullable|string'
            ]);

            $log = DiaperLog::create([
                'baby_id' => $this->getBaby()->id,
                ...$validated
            ]);

            return response()->json([
                'message' => 'Diaper log created successfully',
                'data' => $log
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating diaper log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $log = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->where('id', $id)
                ->first();

            if (!$log) {
                return response()->json(['message' => 'Diaper log not found'], 404);
            }

            return response()->json(['data' => $log]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching diaper log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $log = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->where('id', $id)
                ->first();

            if (!$log) {
                return response()->json(['message' => 'Diaper log not found'], 404);
            }

            $validated = $request->validate([
                'type' => 'required|string',
                'time' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            $log->update($validated);

            return response()->json([
                'message' => 'Diaper log updated successfully',
                'data' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating diaper log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $log = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->where('id', $id)
                ->first();

            if (!$log) {
                return response()->json(['message' => 'Diaper log not found'], 404);
            }

            $log->delete();

            return response()->json([
                'message' => 'Diaper log deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting diaper log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStats(Request $request)
    {
        try {
            // Get the requested date or default to today
            $date = $request->input('date', now()->format('Y-m-d'));
            $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();
            
            // Get today's logs
            $todayLogs = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->whereBetween('time', [$startOfDay, $endOfDay])
                ->get();

            // Get weekly data
            $startOfWeek = \Carbon\Carbon::parse($date)->startOfWeek();
            $endOfWeek = \Carbon\Carbon::parse($date)->endOfWeek();
            
            $weeklyLogs = DiaperLog::where('baby_id', $this->getBaby()->id)
                ->whereBetween('time', [$startOfWeek, $endOfWeek])
                ->get()
                ->groupBy(function($log) {
                    return \Carbon\Carbon::parse($log->time)->format('D');
                });

            // Calculate daily statistics
            $dailyStats = [
                'total' => $todayLogs->count(),
                'wet' => $todayLogs->where('type', 'wet')->count(),
                'dirty' => $todayLogs->where('type', 'dirty')->count()
            ];

            // Prepare weekly overview
            $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $weeklyOverview = collect($weekDays)->mapWithKeys(function($day) use ($weeklyLogs) {
                return [$day => $weeklyLogs->get($day, collect())->count()];
            });

            $stats = [
                'daily' => $dailyStats,
                'weekly' => $weeklyOverview,
                'recent_logs' => $todayLogs->take(5)->map(function($log) {
                    return [
                        'id' => $log->id,
                        'type' => $log->type,
                        'time' => $log->time,
                        'notes' => $log->notes
                    ];
                })
            ];

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching diaper statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 