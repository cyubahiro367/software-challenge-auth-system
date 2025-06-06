<?php

namespace App\Services;

use App\Data\Step1PersonalInfoData;
use App\Data\Step2AddressData;
use App\Data\Step4PasswordData;
use App\Data\TwoFactorRequestData;
use App\Data\UploadProfilePictureDateData;
use App\Enums\Country;
use App\Enums\CountryCode;
use App\Mail\TwoFactorCode;
use App\Models\TempRegistration;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ItemNotFoundException;

class RegistrationService
{
    public function start(): array
    {
        $tempRegistration = TempRegistration::create([]);

        return [
            'identifier' => $tempRegistration->unique_identifier,
            'current_step' => $tempRegistration->current_step,
        ];
    }

    /**
     * Resume existing registration
     */
    public function resume(string $identifier): array
    {
        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration), ItemNotFoundException::class, __('Registration session not found'));

        return [
            'identifier' => $tempRegistration->unique_identifier,
            'current_step' => $tempRegistration->current_step,
            'completed_steps' => [
                'step_1' => $tempRegistration->step_1_completed,
                'step_2' => $tempRegistration->step_2_completed,
                'step_3' => $tempRegistration->step_3_completed,
                'step_4' => $tempRegistration->step_4_completed,
                'step_5' => $tempRegistration->step_5_completed,
            ],
            'expires_at' => $tempRegistration->expires_at,
        ];
    }

    /**
     * Step 1: Personal Information
     */
    public function step1(string $identifier, Step1PersonalInfoData $dto): array
    {
        $data = $dto->toArray();

        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration), ItemNotFoundException::class, __('Registration session not found'));

        // Auto-set honorific title if not provided
        if (empty($data['honorific_title'])) {
            $data['honorific_title'] = $data['gender'] === 'male' ? 'Mr.' : 'Ms.';
        }

        // Get phone code from nationality using enums

        $countryEnum = Country::tryFrom($data['nationality']);

        if ($countryEnum && ! str_starts_with($data['phone_number'], '+')) {
            // Get numeric code from country enum
            $countryCodeEnum = CountryCode::tryFrom($data['nationality']);
            $phoneCode = $countryCodeEnum ? $countryCodeEnum->phoneCode() : '';
            $data['phone_number'] = $phoneCode . $data['phone_number'];
        }

        $tempRegistration->updateStepData(1, $data);

        return [
            'current_step' => $tempRegistration->current_step,
            'next_step' => 2,
        ];
    }

    /**
     * Step 2: Address Details
     */
    public function step2(string $identifier, Step2AddressData $dto): array
    {
        $data = $dto->toArray();

        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->canAccessStep(2), ItemNotFoundException::class, __('Cannot access step 2. Complete previous steps first.'));

        // Check if user is expatriate
        $step1Data = $tempRegistration->getStepData(1);
        $data['is_expatriate'] = $data['country_of_residence'] !== $step1Data['nationality'];

        $tempRegistration->updateStepData(2, $data);

        return [
            'current_step' => $tempRegistration->current_step,
            'next_step' => 3,
            'is_expatriate' => $data['is_expatriate'],
        ];
    }

    /**
     * Step 3: Two-Factor Authentication Setup
     */
    public function step3(string $identifier): array
    {
        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->canAccessStep(3), ItemNotFoundException::class, __('Cannot access step 3. Complete previous steps first.'));

        $step1Data = $tempRegistration->getStepData(1);
        $email = $step1Data['email'];

        // Generate 2FA code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        // Store 2FA data
        $data = [
            'two_factor_code' => $hashedCode,
            'two_factor_expires_at' => Carbon::now()->addMinutes(10),
            'email_sent_to' => $email,
        ];

        $tempRegistration->updateStepData(3, $data);

        Mail::to($email)->send(new TwoFactorCode($code));

        return [
            'email_sent_to' => $email,
            'expires_in_minutes' => 10,
        ];
    }

    /**
     * Step 4: Password Setup
     */
    public function step4(string $identifier, Step4PasswordData $dto): array
    {
        $data = $dto->toArray();

        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->canAccessStep(4), ItemNotFoundException::class, __('Cannot access step 4. Complete previous steps first.'));

        // Check if 2FA is verified
        $step3Data = $tempRegistration->getStepData(3);

        throw_if(! isset($step3Data['verified']) || ! $step3Data['verified'], ItemNotFoundException::class, __('Please verify your 2FA code first.'));

        $data['password'] = Hash::make($data['password']);

        $tempRegistration->updateStepData(4, $data);

        return [
            'current_step' => $tempRegistration->current_step,
            'next_step' => 5,
        ];
    }

    /**
     * Verify 2FA Code
     */
    public function verify2FA(string $identifier, TwoFactorRequestData $dto): array
    {
        $data = $dto->toArray();

        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->isStepCompleted(3), ItemNotFoundException::class, __('Invalid request.'));

        $step3Data = $tempRegistration->getStepData(3);
        $code = $data['code'];

        throw_if(Carbon::now()->isAfter($step3Data['two_factor_expires_at']), ItemNotFoundException::class, __('2FA code has expired. Please request a new one.'));

        throw_if(! Hash::check($code, $step3Data['two_factor_code']), ItemNotFoundException::class, __('Invalid 2FA code.'));

        // Mark as verified
        $step3Data['verified'] = true;
        $step3Data['verified_at'] = Carbon::now();
        $tempRegistration->updateStepData(3, $step3Data);

        return [
            'current_step' => $tempRegistration->current_step,
            'next_step' => 4,
        ];
    }

    /**
     * Step 5: Review & Confirm
     */
    public function step5(string $identifier): array
    {
        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->canAccessStep(5), ItemNotFoundException::class, __('Cannot access step 5. Complete previous steps first.'));

        $tempRegistration->updateStepData(5, ['confirmed' => true, 'confirmed_at' => Carbon::now()]);

        return [
            'ready_for_completion' => true,
            'all_data' => $this->prepareReviewData($tempRegistration),
        ];
    }

    /**
     * Complete registration
     */
    public function complete(string $identifier): array
    {
        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration) || ! $tempRegistration->isComplete(), ItemNotFoundException::class, __('Registration is not complete or session not found.'));

        try {
            $token = DB::transaction(function () use ($tempRegistration) {
                // Create user from temp registration data
                $user = $this->createUserFromTempRegistration($tempRegistration);

                // Generate API token
                $token = $user->createToken('registration')->plainTextToken;

                // Clean up temp registration
                $tempRegistration->delete();

                return $token;
            });

            return [
                'success' => true,
                'message' => 'Registration completed successfully',
                'data' => [
                    'token' => $token,
                ],
            ];
        } catch (\Exception $e) {
            throw new Exception(__('System error, contact support'));
        }
    }

    /**
     * Prepare review data (remove sensitive information)
     */
    private function prepareReviewData(TempRegistration $tempRegistration): array
    {
        $allData = $tempRegistration->getAllData();

        // Remove sensitive data for review
        if (isset($allData['step_3'])) {
            unset($allData['step_3']['two_factor_code']);
        }
        // Remove all of step 4 data
        unset($allData['step_4']);

        return $allData;
    }

    /**
     * Create user from temp registration
     */
    private function createUserFromTempRegistration(TempRegistration $tempRegistration): User
    {
        $step1 = $tempRegistration->getStepData(1);
        $step2 = $tempRegistration->getStepData(2);
        $step4 = $tempRegistration->getStepData(4);

        return User::create([
            'honorific_title' => $step1['honorific_title'],
            'first_name' => $step1['first_name'],
            'last_name' => $step1['last_name'],
            'gender' => $step1['gender'],
            'date_of_birth' => $step1['date_of_birth'],
            'email' => $step1['email'],
            'phone_number' => $step1['phone_number'],
            'nationality' => $step1['nationality'],
            'profile_picture' => $step1['profile_picture'] ?? null,
            'country_of_residence' => $step2['country_of_residence'],
            'city' => $step2['city'],
            'postal_code' => $step2['postal_code'],
            'apartment_name' => $step2['apartment_name'] ?? null,
            'room_number' => $step2['room_number'] ?? null,
            'is_expatriate' => $step2['is_expatriate'],
            'password' => $step4['password'],
            'email_verified_at' => Carbon::now(),
            'two_factor_verified' => true,
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(string $identifier, UploadProfilePictureDateData $dto): array
    {
        $data = $dto->toArray();
        $tempRegistration = TempRegistration::findByIdentifier($identifier);

        throw_if(is_null($tempRegistration), ItemNotFoundException::class, __('Registration session not found'));

        try {
            $file = $data['profile_picture'];
            $filename = $identifier . '_' . time() . '.png';
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            // Update step 1 data with profile picture
            $step1Data = $tempRegistration->getStepData(1);
            $step1Data['profile_picture'] = $path;
            $tempRegistration->updateStepData(1, $step1Data);

            return [
                'profile_picture_path' => $path,
                'profile_picture_url' => Storage::url($path)
            ];

        } catch (\Exception $e) {
            throw new Exception(__($e->getMessage()));
        }
    }
}
