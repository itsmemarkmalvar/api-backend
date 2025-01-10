<?php

namespace App\Http\Controllers;

use App\Models\Growth;
use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrowthController extends Controller
{
    public function store(Request $request)
    {
        try {
            \Log::info('Growth measurement store attempt', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'height' => 'required|numeric|between:20,200',
                'weight' => 'required|numeric|between:0,50',
                'head_size' => 'required|numeric|between:20,60',
                'date_recorded' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:500',
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                \Log::error('Baby not found for user:', ['user_id' => Auth::id()]);
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Create growth record
            $growth = Growth::create([
                'baby_id' => $baby->id,
                ...$validated
            ]);

            // Update baby's current measurements
            $baby->update([
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'head_size' => $validated['head_size'],
            ]);

            \Log::info('Growth record added successfully', [
                'growth_id' => $growth->id,
                'baby_id' => $baby->id,
                'measurements' => [
                    'height' => $validated['height'],
                    'weight' => $validated['weight'],
                    'head_size' => $validated['head_size'],
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Growth record added successfully',
                'data' => $growth
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error saving growth record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving growth record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $growthRecords = Growth::where('baby_id', $baby->id)
                ->orderBy('date_recorded', 'desc')
                ->get();

            return response()->json(['data' => $growthRecords]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching growth records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function charts()
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            
            if (!$baby) {
                \Log::error('Baby not found for user: ' . Auth::id());
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $growthData = Growth::where('baby_id', $baby->id)
                ->orderBy('date_recorded', 'asc')
                ->get()
                ->map(function ($record) {
                    return [
                        'date' => $record->date_recorded,
                        'height' => $record->height,
                        'weight' => $record->weight,
                        'head_size' => $record->head_size,
                        'notes' => $record->notes
                    ];
                });

            return response()->json(['data' => $growthData]);
        } catch (\Exception $e) {
            \Log::error('Error in growth charts: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error generating charts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPercentiles($babyId)
    {
        // Calculate percentiles based on WHO growth standards
        // Return percentile data for charts
    }

    public function getMilestones($babyId)
    {
        // Get growth milestones data
    }
} 