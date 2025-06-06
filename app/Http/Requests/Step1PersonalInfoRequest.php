<?php

namespace App\Http\Requests;

use App\Enums\CountryCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Step1PersonalInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'honorific' => ['nullable', Rule::in(['Mr.', 'Mrs.', 'Miss', 'Ms.', 'Dr.', 'Prof.', 'Hon.'])],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => 'required|date',
            'email' => ['required', 'email:rfc,dns'],
            'nationality' => ['required', Rule::enum(CountryCode::class)],
            'phone_number' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:png|max:2048',
        ];
    }
}
