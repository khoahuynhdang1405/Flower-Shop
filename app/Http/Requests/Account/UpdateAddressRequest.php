<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'default_shipping_address' => is_string($this->default_shipping_address) ? trim($this->default_shipping_address) : $this->default_shipping_address,
            'default_shipping_district' => is_string($this->default_shipping_district) ? trim($this->default_shipping_district) : $this->default_shipping_district,
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
            'default_shipping_address' => ['required', 'string', 'max:500'],
            'default_shipping_district' => ['required', 'string', Rule::in(config('shop.districts'))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'default_shipping_address.required' => __('account.address.street_required'),
            'default_shipping_district.required' => __('account.address.district_required'),
            'default_shipping_district.in' => __('account.address.district_required'),
        ];
    }

    /**
     * Gửi lỗi xong thì quay về đúng khối trên trang Tài khoản.
     */
    protected function getRedirectUrl(): string
    {
        return route('account').'#address';
    }
}
