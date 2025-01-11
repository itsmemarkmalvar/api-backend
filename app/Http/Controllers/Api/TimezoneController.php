<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\HandlesTimezones;

class TimezoneController extends Controller
{
    use HandlesTimezones;

    public function update(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string'
        ]);

        try {
            // Validate timezone
            if (!$this->validateTimezone($request->timezone)) {
                return response()->json([
                    'message' => 'Invalid timezone provided',
                    'timezone' => $request->timezone
                ], 422);
            }

            $user = auth()->user();
            $user->timezone = $request->timezone;
            $user->timezone_updated_at = now();
            $user->save();

            return response()->json([
                'message' => 'Timezone updated successfully',
                'timezone' => $user->timezone,
                'current_time' => $this->getCurrentTime($user->timezone)->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating timezone',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function current()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated user',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            \Log::info('User timezone request', [
                'user_id' => $user->id,
                'timezone' => $user->timezone
            ]);
            
            return response()->json([
                'timezone' => $user->timezone ?? 'Asia/Manila',
                'current_time' => $this->getCurrentTime($user->timezone)->format('Y-m-d H:i:s'),
                'utc_offset' => Carbon::now($user->timezone)->offsetHours
            ]);
        } catch (\Exception $e) {
            \Log::error('Timezone error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error getting timezone information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validate_timezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string'
        ]);

        try {
            $isValid = $this->validateTimezone($request->timezone);
            
            if ($isValid) {
                return response()->json([
                    'valid' => true,
                    'timezone' => $request->timezone,
                    'current_time' => Carbon::now($request->timezone)->format('Y-m-d H:i:s'),
                    'utc_offset' => Carbon::now($request->timezone)->offsetHours
                ]);
            }

            return response()->json([
                'valid' => false,
                'message' => 'Invalid timezone'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating timezone',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 