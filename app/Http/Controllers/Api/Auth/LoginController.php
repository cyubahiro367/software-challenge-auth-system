<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\LoginService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __construct(protected LoginService $service) {}

    public function login(LoginRequest $request)
    {
        try {
            $response = $this->service->login($request);

            return $this->successMessage('Login successful.', $response);
        } catch (\Throwable $th) {
            Log::error('Login', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to loggin. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }
}
