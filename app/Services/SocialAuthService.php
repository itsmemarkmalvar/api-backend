<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Google_Client;
use Facebook\Facebook;
use Exception;

class SocialAuthService
{
    public function handleGoogleAuth($token)
    {
        try {
            $client = new Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($token);

            if (!$payload) {
                throw new Exception('Invalid token');
            }

            $user = User::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'google_id' => $payload['sub'],
                    'avatar' => $payload['picture'] ?? null,
                    'password' => Hash::make(Str::random(24))
                ]
            );

            return [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ];
        } catch (Exception $e) {
            throw new Exception('Google authentication failed: ' . $e->getMessage());
        }
    }

    public function handleFacebookAuth($token)
    {
        try {
            $fb = new Facebook([
                'app_id' => config('services.facebook.client_id'),
                'app_secret' => config('services.facebook.client_secret'),
                'default_graph_version' => 'v12.0',
            ]);

            $response = $fb->get('/me?fields=id,name,email,picture', $token);
            $fbUser = $response->getGraphUser();

            $user = User::updateOrCreate(
                ['email' => $fbUser->getEmail()],
                [
                    'name' => $fbUser->getName(),
                    'facebook_id' => $fbUser->getId(),
                    'avatar' => $fbUser->getPicture()->getUrl() ?? null,
                    'password' => Hash::make(Str::random(24))
                ]
            );

            return [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ];
        } catch (Exception $e) {
            throw new Exception('Facebook authentication failed: ' . $e->getMessage());
        }
    }
} 