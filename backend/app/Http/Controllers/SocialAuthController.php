<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth consent screen
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google OAuth user data', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);

            // Check if user exists with this Google ID
            $user = User::where('provider', 'google')
                        ->where('provider_id', $googleUser->getId())
                        ->first();

            if (!$user) {
                // Check if user exists with same email (auto-link)
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    Log::info('Linking Google to existing user', ['user_id' => $user->user_id, 'email' => $user->email]);

                    // Link Google to existing account
                    $user->provider = 'google';
                    $user->provider_id = $googleUser->getId();
                    $user->provider_token = $googleUser->token;
                    $user->provider_refresh_token = $googleUser->refreshToken;
                    $user->provider_avatar_url = $googleUser->getAvatar();
                    $user->email_verified_at = now();
                    $user->save();
                } else {
                    Log::info('Creating new user from Google OAuth');

                    // Create new user
                    $user = User::create([
                        'full_name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'provider_token' => $googleUser->token,
                        'provider_refresh_token' => $googleUser->refreshToken,
                        'provider_avatar_url' => $googleUser->getAvatar(),
                        'email_verified_at' => now(),
                        'role' => 'customer',
                        'password' => bcrypt(Str::random(16)),
                    ]);

                    Log::info('New user created', ['user_id' => $user->user_id]);
                }
            } else {
                Log::info('Updating existing OAuth user', ['user_id' => $user->user_id]);

                // Update existing OAuth user tokens
                $user->provider_token = $googleUser->token;
                $user->provider_refresh_token = $googleUser->refreshToken;
                $user->provider_avatar_url = $googleUser->getAvatar();
                $user->save();
            }

            // Log in the user
            Auth::login($user);

            // Create Sanctum token for API authentication
            $token = $user->createToken('oauth-login')->plainTextToken;

            // Redirect to frontend with token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to("{$frontendUrl}/auth/callback?login=success&token={$token}");

        } catch (\Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to("{$frontendUrl}/?login=error&message=" . urlencode($e->getMessage()));
        }
    }
}
