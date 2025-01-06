<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token)
    {
        try {
            Log::info('Showing reset form', ['token' => $token, 'email' => $request->email]);
            
            // Check if token exists in the database
            $tokenExists = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->exists();

            if (!$tokenExists) {
                Log::warning('Invalid token or email', ['token' => $token, 'email' => $request->email]);
                return response()->json([
                    'message' => 'Invalid password reset token',
                    'error' => 'Token not found'
                ], 400);
            }

            return view('auth.reset-password', [
                'token' => $token,
                'email' => $request->email
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing reset form', [
                'error' => $e->getMessage(),
                'token' => $token,
                'email' => $request->email
            ]);
            
            return response()->json([
                'message' => 'Error processing password reset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reset(Request $request)
    {
        try {
            Log::info('Attempting password reset', ['email' => $request->email]);

            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            Log::info('Password reset status', ['status' => $status]);

            if ($request->wantsJson()) {
                return $status === Password::PASSWORD_RESET
                    ? response()->json(['message' => 'Password has been reset successfully'])
                    : response()->json(['error' => __($status)], 400);
            }

            if ($status === Password::PASSWORD_RESET) {
                return redirect('https://binibaby.com/login')->with('status', 'Password has been reset successfully');
            }

            return back()->withErrors(['email' => [__($status)]]);
        } catch (\Exception $e) {
            Log::error('Error resetting password', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Error resetting password',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['email' => ['Error resetting password. Please try again.']]);
        }
    }
} 