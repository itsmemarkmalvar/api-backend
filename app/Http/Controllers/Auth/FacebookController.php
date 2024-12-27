<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FacebookController extends Controller
{
    public function handleFacebookCallback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'userData' => 'required|array',
                'userData.id' => 'required|string',
                'userData.email' => 'required|email',
                'userData.name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user exists
            $user = User::where('email', $request->userData['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $request->userData['name'],
                    'email' => $request->userData['email'],
                    'password' => Hash::make(Str::random(24)),
                    'facebook_id' => $request->userData['id'],
                    'profile_photo' => $request->userData['picture']['data']['url'] ?? null,
                ]);
            } else {
                // Update existing user's Facebook ID if not set
                if (!$user->facebook_id) {
                    $user->facebook_id = $request->userData['id'];
                    $user->save();
                }
            }

            // Generate token
            $token = $user->createToken('facebook-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 