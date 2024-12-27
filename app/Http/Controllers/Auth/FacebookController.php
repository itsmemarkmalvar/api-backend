<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    public function handleFacebookCallback(Request $request)
    {
        try {
            Log::info('Facebook login attempt', ['request' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'user_data' => 'required|array',
                'user_data.id' => 'required|string',
                'user_data.email' => 'required|email',
                'user_data.name' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('Facebook validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify token with Facebook
            $fbResponse = file_get_contents(
                "https://graph.facebook.com/debug_token?" .
                "input_token={$request->access_token}&" .
                "access_token=" . env('FACEBOOK_APP_ID') . "|" . env('FACEBOOK_APP_SECRET')
            );
            
            $tokenInfo = json_decode($fbResponse);
            if (!$tokenInfo || !$tokenInfo->data->is_valid) {
                Log::error('Invalid Facebook token', ['token_info' => $tokenInfo]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Facebook token'
                ], 401);
            }

            // Check if user exists
            $user = User::where('email', $request->user_data['email'])
                       ->orWhere('facebook_id', $request->user_data['id'])
                       ->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $request->user_data['name'],
                    'email' => $request->user_data['email'],
                    'password' => Hash::make(Str::random(24)),
                    'facebook_id' => $request->user_data['id'],
                    'profile_photo' => $request->user_data['picture']['data']['url'] ?? null,
                ]);
                Log::info('New user created via Facebook', ['user_id' => $user->id]);
            } else {
                // Update existing user's Facebook info
                $user->facebook_id = $request->user_data['id'];
                if (isset($request->user_data['picture']['data']['url'])) {
                    $user->profile_photo = $request->user_data['picture']['data']['url'];
                }
                $user->save();
                Log::info('Existing user updated via Facebook', ['user_id' => $user->id]);
            }

            // Generate token
            $token = $user->createToken('facebook-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Facebook authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 