<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch ngang khách hay gõ trong số điện thoại (vd. "0901 234 567").
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? preg_replace('/[\s.\-]/', '', $this->phone) : $this->phone,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:'.RegisterRequest::PHONE_PATTERN],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'after:1900-01-01', 'before:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('account.profile.name_required'),
            'phone.required' => __('account.profile.phone_required'),
            'phone.regex' => __('auth.register.phone_invalid'),
            'birthday.date_format' => __('account.profile.birthday_invalid'),
            'birthday.after' => __('account.profile.birthday_invalid'),
            'birthday.before' => __('account.profile.birthday_invalid'),
        ];
    }

    /**
     * Gửi lỗi xong thì quay về đúng khối trên trang Tài khoản.
     */
    protected function getRedirectUrl(): string
    {
        return route('account').'#profile';
    }
}
