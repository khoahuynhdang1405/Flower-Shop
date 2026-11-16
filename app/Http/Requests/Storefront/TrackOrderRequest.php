<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form tra cứu đơn (?code=&phone=). Không có query thì chỉ hiện form, không validate.
 */
class TrackOrderRequest extends FormRequest
{
    /**
     * Sai định dạng thì về form trống (kèm lỗi + giá trị đã nhập), tránh quay lại đúng URL lỗi.
     */
    protected $redirectRoute = 'orders.track';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * SĐT bỏ khoảng trắng / chấm / gạch như lúc đặt hoa; mã đơn viết hoa, chấp nhận "lh8291", "LH-8291", "#LH-8291".
     */
    protected function prepareForValidation(): void
    {
        // merge() ghi vào query string của request GET: chưa tra cứu thì không thêm khóa nào.
        if (! $this->hasLookup()) {
            return;
        }

        $code = is_string($this->input('code')) ? mb_strtoupper(preg_replace('/\s+/', '', $this->input('code'))) : $this->input('code');

        if (is_string($code) && preg_match('/^#?LH-?(\d+)$/', $code, $matches) === 1) {
            $code = '#LH-'.str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        }

        $this->merge([
            'code' => $code,
            'phone' => is_string($this->input('phone')) ? preg_replace('/[\s.\-]/', '', $this->input('phone')) : $this->input('phone'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (! $this->hasLookup()) {
            return [];
        }

        return [
            'code' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'regex:'.RegisterRequest::PHONE_PATTERN],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('auth.register.phone_invalid'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => __('order.tracking.form.code'),
            'phone' => __('order.tracking.form.phone'),
        ];
    }

    public function hasLookup(): bool
    {
        return $this->query->has('code') || $this->query->has('phone');
    }

    public function orderCode(): string
    {
        return (string) $this->validated('code');
    }

    public function phone(): string
    {
        return (string) $this->validated('phone');
    }
}
