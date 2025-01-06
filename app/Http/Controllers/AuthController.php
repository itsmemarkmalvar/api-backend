<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number ?? null,
            ]);

            // Send verification email
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Registration successful. Please check your email for verification.',
                'user' => $user,
                'requires_verification' => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Please verify your email first.',
                    'requires_verification' => true
                ], 403);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        \Log::info('User data being fetched:', [
            'user' => $request->user()
        ]);
        
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            \Log::info('Profile update request:', [
                'all_data' => $request->all(),
                'user_id' => $user->id
            ]);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string|max:11',
                'birthday' => 'sometimes|date|before_or_equal:today',
                'gender' => 'sometimes|in:male,female',
                'nationality' => 'sometimes|string|max:100',
                'street_address' => 'sometimes|string|max:255',
                'city' => 'sometimes|string|max:100',
                'province' => 'sometimes|string|max:100',
                'postal_code' => 'sometimes|string|size:4'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Filter out null values to prevent overwriting existing data with null
            $validatedData = array_filter($validator->validated(), function ($value) {
                return $value !== null;
            });

            \Log::info('Validated data:', [
                'data' => $validatedData
            ]);

            $user->update($validatedData);

            \Log::info('User updated:', [
                'user' => $user->toArray()
            ]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile update failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|different:current_password',
                'new_password_confirmation' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            // Store IDs for logging before deletion
            $userId = $user->id;
            $userEmail = $user->email;
            $babyId = $user->baby ? $user->baby->id : null;
            
            // Verify password before deletion
            $validator = Validator::make($request->all(), [
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify the password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Password is incorrect'
                ], 422);
            }

            \Log::info('Starting account deletion process for user:', [
                'user_id' => $userId,
                'email' => $userEmail
            ]);

            try {
                // Delete tokens first
                $user->tokens()->delete();
                \Log::info('User tokens deleted', ['user_id' => $userId]);

                // Delete baby and related records if they exist
                if ($babyId) {
                    \Log::info('Deleting baby and related records', ['baby_id' => $babyId]);
                    
                    // Delete related records in order
                    \DB::table('growth_records')->where('baby_id', $babyId)->delete();
                    \DB::table('milestones')->where('baby_id', $babyId)->delete();
                    \DB::table('sleep_logs')->where('baby_id', $babyId)->delete();
                    \DB::table('feeding_logs')->where('baby_id', $babyId)->delete();
                    \DB::table('health_records')->where('baby_id', $babyId)->delete();
                    \DB::table('vaccinations')->where('baby_id', $babyId)->delete();
                    \DB::table('vaccination_logs')->where('baby_id', $babyId)->delete();
                    
                    // Delete the baby record
                    \DB::table('babies')->where('id', $babyId)->delete();
                    \Log::info('Baby and related records deleted', ['baby_id' => $babyId]);
                }

                // Finally delete the user
                \DB::table('users')->where('id', $userId)->delete();
                \Log::info('User deleted', ['user_id' => $userId]);

                \Log::info('Account deletion completed successfully', [
                    'user_id' => $userId,
                    'baby_id' => $babyId
                ]);

                return response()->json([
                    'message' => 'Account deleted successfully'
                ]);
            } catch (\Exception $e) {
                \Log::error('Error during account deletion:', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'message' => 'Failed to delete account',
                    'error' => 'An unexpected error occurred. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Account deletion failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to delete account',
                'error' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 400);
        }

        try {
            $user->sendEmailVerificationNotification();
            \Log::info('Verification email sent to: ' . $user->email);

            return response()->json([
                'message' => 'Verification email sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send verification email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkVerification($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'verified' => false
            ], 404);
        }

        return response()->json([
            'verified' => !is_null($user->email_verified_at)
        ]);
    }

    public function verifyEmail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
            
            // Generate token after verification
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'message' => 'Email has been verified.',
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Invalid verification link.'
        ], 400);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users'
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link has been sent to your email'
                ]);
            } else {
                return response()->json([
                    'message' => 'Unable to send reset link'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send reset link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password has been reset successfully'
                ]);
            } else {
                return response()->json([
                    'message' => 'Invalid reset token'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 