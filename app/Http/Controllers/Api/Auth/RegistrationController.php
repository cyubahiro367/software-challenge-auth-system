<?php

namespace App\Http\Controllers\Api\Auth;

use App\Data\Step1PersonalInfoData;
use App\Data\Step2AddressData;
use App\Data\Step4PasswordData;
use App\Data\Step5ReviewData;
use App\Data\TwoFactorRequestData;
use App\Data\UploadProfilePictureDateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Step1PersonalInfoRequest;
use App\Http\Requests\Step2AddressRequest;
use App\Http\Requests\Step4PasswordRequest;
use App\Http\Requests\Step5ReviewRequest;
use App\Http\Requests\TwoFactorRequest;
use App\Http\Requests\UploadProfilePictureRequest;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function __construct(protected RegistrationService $service) {}

    /**
     * Start a new registration process
     */
    public function start(): JsonResponse
    {
        try {

            $response = $this->service->start();

            return $this->successMessage('Registration started successfully.', $response);

        } catch (\Throwable $th) {

            Log::error('Registration start failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start registration. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resume existing registration
     */
    public function resume(string $identifier): JsonResponse
    {
        try {
            $response = $this->service->resume($identifier);

            return $this->successMessage('Registration resumed successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Registration resume failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume registration. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 1: Personal Information
     */
    public function step1(Step1PersonalInfoRequest $request, string $identifier): JsonResponse
    {
        $data = Step1PersonalInfoData::from($request->validated());

        try {
            $response = $this->service->step1($identifier, $data);

            return $this->successMessage('Step 1 completed successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Registration step 1 failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete step 1. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 2: Address Details
     */
    public function step2(Step2AddressRequest $request, string $identifier): JsonResponse
    {
        $data = Step2AddressData::from($request->validated());

        try {
            $response = $this->service->step2($identifier, $data);

            return $this->successMessage('Step 2 completed successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Registration step 2 failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete step 2. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 3: Two-Factor Authentication Setup
     */
    public function step3(string $identifier): JsonResponse
    {
        try {

            $response = $this->service->step3($identifier);

            return $this->successMessage('2FA code sent to your email address.', $response);
        } catch (\Throwable $th) {
            Log::error('Registration step 3 failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send 2FA code. Please try again.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify 2FA Code
     */
    public function verify2FA(TwoFactorRequest $request, string $identifier): JsonResponse
    {
        $data = TwoFactorRequestData::from($request->validated());

        try {

            $response = $this->service->verify2FA($identifier, $data);

            return $this->successMessage('2FA verification successful.', $response);
        } catch (\Throwable $th) {
            Log::error('2FA verification failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify 2FA code. Please try again.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 4: Password Setup
     */
    public function step4(Step4PasswordRequest $request, string $identifier): JsonResponse
    {
        $data = Step4PasswordData::from($request->validated());

        try {
            $response = $this->service->step4($identifier, $data);

            return $this->successMessage('Password set successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Password set failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set password. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 5: Review & Confirm
     */
    public function step5(Step5ReviewRequest $request, string $identifier): JsonResponse
    {
        $data = Step5ReviewData::from($request->validated());

        try {
            $response = $this->service->step5($identifier, $data);

            return $this->successMessage('Registration review completed.', $response);
        } catch (\Throwable $th) {
            Log::error('Password set failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get review data. Please try again later.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete registration
     */
    public function complete(string $identifier): JsonResponse
    {
        try {
            $response = $this->service->complete($identifier);

            return $this->successMessage('Registration completed successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Registration set failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete registration.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(UploadProfilePictureRequest $request, string $identifier): JsonResponse
    {
        $data = UploadProfilePictureDateData::from($request->validated());

        try {
            $response = $this->service->uploadProfilePicture($identifier, $data);

            return $this->successMessage('Profile picture uploaded successfully.', $response);
        } catch (\Throwable $th) {
            Log::error('Profile picture uploaded failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to uploaded Profile picture.',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
