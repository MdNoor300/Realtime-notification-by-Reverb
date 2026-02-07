<?php

namespace App\Http\Requests\Auth;

use App\Traits\ApiValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    use ApiValidationTrait;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
           "name" => "required",
           "email" => "required|email",
           "phone" => "required",
           "password" => "required",
           "role" => "required",
           "otp" => "required"
        ];
    }
}
