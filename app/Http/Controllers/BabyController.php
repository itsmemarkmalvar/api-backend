<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BabyController extends Controller
{
    public function __construct()
    {
        // Remove this line as it's causing the error
        // $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        // Add detailed logging
        \Log::info('Baby data store attempt', [
            'user_id' => Auth::id(),
            'auth_header' => $request->header('Authorization'),
            'request_data' => $request->all()
        ]);

        if (!Auth::id()) {
            \Log::error('No authenticated user found');
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'No valid authentication token found'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'birth_date' => 'required|date|before_or_equal:today',
                'height' => 'required|numeric|between:20,120',
                'weight' => 'required|numeric|between:1,30',
                'head_size' => 'required|numeric|between:20,60',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            $baby = Baby::create([
                'user_id' => Auth::id(),
                ...$validated
            ]);

            \Log::info('Baby created successfully', ['baby_id' => $baby->id]);

            return response()->json([
                'message' => 'Baby information saved successfully',
                'data' => $baby
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error saving baby data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error saving baby information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        try {
            \Log::info('Fetching baby data for user:', ['user_id' => Auth::id()]);
            
            $baby = Baby::where('user_id', Auth::id())->first();
            
            if (!$baby) {
                \Log::info('No baby found for user:', ['user_id' => Auth::id()]);
                return response()->json(['message' => 'Baby not found'], 404);
            }

            \Log::info('Baby data found:', ['baby' => $baby]);
            return response()->json(['data' => $baby]);
        } catch (\Exception $e) {
            \Log::error('Error fetching baby data:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['message' => 'Error fetching baby data'], 500);
        }
    }
} 