<?php

namespace App\Http\Requests;

use App\Enums\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Step2AddressRequest extends FormRequest
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
            'country_of_residence' => ['required', Rule::enum(Country::class)],
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'apartment_name' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
        ];
    }
}
