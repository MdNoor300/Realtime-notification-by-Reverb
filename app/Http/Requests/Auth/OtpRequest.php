<?php

namespace App\Http\Requests\Common;

use App\Traits\ApiValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
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
            "email" => "required|email",
            "action" => "required"
        ];
    }
}
