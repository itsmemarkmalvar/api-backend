<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use App\Models\DevelopmentActivity;
use App\Models\DevelopmentTip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DevelopmentController extends Controller
{
    public function getActivities(Request $request)
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $category = $request->query('category', 'all');
            
            // Calculate baby's age in months
            $birthDate = Carbon::parse($baby->birth_date);
            $ageInMonths = $birthDate->diffInMonths(Carbon::now());

            $query = DevelopmentActivity::where('min_age_months', '<=', $ageInMonths)
                ->where('max_age_months', '>=', $ageInMonths);

            if ($category !== 'all') {
                $query->where('category', $category);
            }

            $activities = $query->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDevelopmentTips(Request $request)
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Calculate baby's age in months
            $birthDate = Carbon::parse($baby->birth_date);
            $ageInMonths = $birthDate->diffInMonths(Carbon::now());

            $tips = DevelopmentTip::where('min_age_months', '<=', $ageInMonths)
                ->where('max_age_months', '>=', $ageInMonths)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tips
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching development tips',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function trackActivity(Request $request)
    {
        try {
            $validated = $request->validate([
                'activity_id' => 'required|exists:development_activities,id',
                'completed_at' => 'required|date',
                'notes' => 'nullable|string|max:500'
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $activityProgress = $baby->activityProgress()->create([
                'activity_id' => $validated['activity_id'],
                'completed_at' => $validated['completed_at'],
                'notes' => $validated['notes'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity tracked successfully',
                'data' => $activityProgress
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 