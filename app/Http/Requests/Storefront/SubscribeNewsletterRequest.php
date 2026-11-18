<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form "Nhận một chút dịu dàng từ Lá" cuối Trang chủ. Lỗi nằm trong error bag riêng
 * và quay về đúng khối form (#newsletter).
 */
class SubscribeNewsletterRequest extends FormRequest
{
    /**
     * @var string
     */
    protected $errorBag = 'newsletter';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('newsletter.errors.required'),
            'email.email' => __('newsletter.errors.invalid'),
            'email.max' => __('newsletter.errors.invalid'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirector->getUrlGenerator()->previous().'#newsletter';
    }
}
