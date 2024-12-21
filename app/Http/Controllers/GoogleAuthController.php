<?php

namespace App\Http\Controllers;

use App\Services\SocialAuthService;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function handleGoogleSignIn(Request $request)
    {
        try {
            $result = $this->socialAuthService->handleGoogleAuth($request->token);

            return response()->json([
                'status' => 'success',
                'user' => $result['user'],
                'token' => $result['token']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }
} 