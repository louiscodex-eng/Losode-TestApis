<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\DTOs\RegisterDTO;
use App\DTOs\LoginDTO;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

   public function register(Request $request)
{
    try {
        // 1. Validation (Laravel throws a ValidationException automatically if this fails)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:vendor,customer', // Required role field
        ]);

        // 2. Map to DTO
        $dto = new RegisterDTO(
            $request->name, 
            $request->email, 
            $request->password,
            $request->role ?? 'customer' // default to 'customer' if role is not provided
        );

        // 3. Call Service
        $result = $this->authService->registerUser($dto);

        // 4. Return Success
        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => $result
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Specifically catch validation errors (like "email already taken")
        return response()->json([
            'status' => 'error',
            'message' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        // Catch everything else (Database connection issues, Supabase errors, etc.)
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

  public function login(Request $request)
{
    try {
        // 1. Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Map to DTO
        $dto = new LoginDTO(
            $request->email, 
            $request->password
        );

        // 3. Call Service
        $result = $this->authService->loginUser($dto);

        // 4. Return Success
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $result
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Catch invalid credentials or missing fields
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),  
        ], 422);

    } catch (\Exception $e) {
        // Catch Supabase connection issues or unexpected code errors
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}
}