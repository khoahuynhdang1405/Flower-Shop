<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Tài khoản đã có mật khẩu thì bắt buộc nhập đúng mật khẩu hiện tại; tài khoản chỉ dùng Google thì bỏ qua.
            'current_password' => [
                $this->user()->password ? 'required' : 'nullable',
                'string',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
                'different:current_password',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => __('account.password.current_required'),
            'current_password.current_password' => __('account.password.current_wrong'),
            'password.required' => __('account.password.new_required'),
            'password.confirmed' => __('account.password.not_matched'),
            'password.different' => __('account.password.same_as_current'),
        ];
    }

    /**
     * Gửi lỗi xong thì quay về đúng khối trên trang Tài khoản.
     */
    protected function getRedirectUrl(): string
    {
        return route('account').'#password';
    }
}
