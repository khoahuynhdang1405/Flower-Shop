<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Số di động Việt Nam: 0 hoặc +84, đầu số 3/5/7/8/9, tổng 10 số.
     */
    public const PHONE_PATTERN = '/^(0|\+84)(3|5|7|8|9)\d{8}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch ngang khách hay gõ trong số điện thoại.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'phone' => is_string($this->phone) ? preg_replace('/[\s.\-]/', '', $this->phone) : $this->phone,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'terms' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('auth.register.phone_invalid'),
            'terms.accepted' => __('auth.register.terms_required'),
        ];
    }
}
