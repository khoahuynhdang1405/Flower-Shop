<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Customer\UpdateCustomerData;
use App\Enums\CustomerGroup;
use App\Http\Requests\Auth\RegisterRequest;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin sửa thông tin khách: cùng luật tên / SĐT / ngày sinh như trang Tài khoản, thêm nhóm khách.
 */
class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch ngang trong số điện thoại (vd. "0901 234 567").
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
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
            // Khách đăng ký bằng Google có thể chưa có SĐT; admin không bắt buộc nhập để đổi nhóm.
            'phone' => ['nullable', 'string', 'regex:'.RegisterRequest::PHONE_PATTERN],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'after:1900-01-01', 'before:today'],
            'customer_group' => ['required', Rule::enum(CustomerGroup::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('account.profile.name_required'),
            'phone.regex' => __('auth.register.phone_invalid'),
            'birthday.date_format' => __('account.profile.birthday_invalid'),
            'birthday.after' => __('account.profile.birthday_invalid'),
            'birthday.before' => __('account.profile.birthday_invalid'),
            'customer_group.required' => __('admin.customers.form.group_required'),
            'customer_group.enum' => __('admin.customers.form.group_required'),
        ];
    }

    public function toData(): UpdateCustomerData
    {
        $birthday = $this->validated('birthday');

        return new UpdateCustomerData(
            name: $this->validated('name'),
            phone: $this->validated('phone') ?: null,
            birthday: $birthday ? CarbonImmutable::createFromFormat('Y-m-d', $birthday)->startOfDay() : null,
            group: CustomerGroup::from($this->validated('customer_group')),
        );
    }
}
