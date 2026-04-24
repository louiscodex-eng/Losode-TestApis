<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\RegisterDTO;
use App\DTOs\LoginDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthService
{
    public function registerUser(RegisterDTO $dto)
    {
        try {
           
        //if the request body is missing any of the required fields, the controller's validation will catch it before reaching this service method. So we can safely assume all required fields are present here.
        if (!$dto->name || !$dto->email || !$dto->password || !$dto->role) {
            throw new Exception("Missing required registration fields.");
        }

            // Check if user already exists (extra safety)
            if (User::where('email', $dto->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['This email address is already registered.'],
                ]);
            }


            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'role' => $dto->role 
            ]);

            if (!$user) {
                throw new Exception("Could not create user record, some credentials are missing.");
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                // 'access_token' => $token,
                // 'token_type' => 'Bearer',
                'user' => $user
            ];
        } catch (Exception $e) {
            Log::error("Registration Service Error: " . $e->getMessage());
            throw $e; 
        }
    }

    public function loginUser(LoginDTO $dto)
    {
        try {
            $user = User::where('email', $dto->email)->first();

            if (!$user || !Hash::check($dto->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'],
                ]);
            }

            // Standard SQL bulk delete (works perfectly on Supabase)
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ];
        } catch (Exception $e) {
            Log::error("Login Service Error: " . $e->getMessage());
            // Keeping your detailed exception for debugging
            throw new Exception("Auth failed at ". $e->getMessage());
        }
    }
}