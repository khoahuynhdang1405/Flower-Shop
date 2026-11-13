<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\DTOs\Order\PlaceOrderData;
use App\Enums\PaymentMethod;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Form "Gửi hoa, gửi yêu thương". Tiền và các món không gửi lên mà tính lại từ giỏ ở PlaceOrderAction.
 */
class PlaceOrderRequest extends FormRequest
{
    /** Phương thức khách chọn được ở trang thanh toán. */
    private const PAYMENT_METHODS = [PaymentMethod::Cod, PaymentMethod::Vnpay, PaymentMethod::Momo];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect(['sender_phone', 'receiver_phone'])
            ->mapWithKeys(fn (string $field): array => [
                $field => is_string($this->input($field)) ? preg_replace('/[\s.\-]/', '', $this->input($field)) : $this->input($field),
            ])
            ->all());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $today = now()->startOfDay();

        return [
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'regex:'.RegisterRequest::PHONE_PATTERN],
            'sender_email' => ['required', 'email', 'max:255'],
            'is_gift' => ['required', Rule::in(['0', '1'])],
            'receiver_name' => ['required_if:is_gift,1', 'nullable', 'string', 'max:255'],
            'receiver_phone' => ['required_if:is_gift,1', 'nullable', 'string', 'regex:'.RegisterRequest::PHONE_PATTERN],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_district' => ['required', Rule::in(config('shop.districts'))],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'delivery_date' => [
                'required', 'date_format:Y-m-d',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$today->copy()->addDays(config('shop.delivery_max_days_ahead'))->toDateString(),
            ],
            'time_slot' => ['required', Rule::in(config('shop.delivery_time_slots'))],
            'card_sender' => ['nullable', 'string', 'max:100'],
            'card_receiver' => ['nullable', 'string', 'max:100'],
            'card_message' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)->only(self::PAYMENT_METHODS)],
        ];
    }

    /**
     * Giao trong hôm nay thì khung giờ phải chưa bắt đầu.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['delivery_date', 'time_slot']) || $this->input('delivery_date') !== now()->toDateString()) {
                    return;
                }

                $slotStart = Carbon::createFromFormat('Y-m-d H:i', $this->input('delivery_date').' '.strtok((string) $this->input('time_slot'), '-'));

                if ($slotStart->isPast()) {
                    $validator->errors()->add('time_slot', __('order.errors.time_slot_passed'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sender_phone.regex' => __('auth.register.phone_invalid'),
            'receiver_phone.regex' => __('auth.register.phone_invalid'),
            'delivery_date.required' => __('checkout.schedule.date_required'),
            'time_slot.required' => __('order.errors.time_slot_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sender_name' => __('checkout.sender.name'),
            'sender_phone' => __('checkout.sender.phone'),
            'sender_email' => __('checkout.sender.email'),
            'receiver_name' => __('checkout.receiver.name'),
            'receiver_phone' => __('checkout.receiver.phone'),
            'shipping_address' => __('checkout.receiver.address'),
            'shipping_district' => __('checkout.receiver.district'),
            'delivery_notes' => __('checkout.receiver.notes'),
            'delivery_date' => __('checkout.schedule.date'),
            'time_slot' => __('checkout.schedule.time_slot'),
            'card_sender' => __('checkout.card.sender'),
            'card_receiver' => __('checkout.card.receiver'),
            'card_message' => __('checkout.card.message'),
            'payment_method' => __('checkout.payment.title'),
        ];
    }

    public function toData(): PlaceOrderData
    {
        return new PlaceOrderData(
            userId: $this->user()?->id,
            senderName: $this->string('sender_name')->trim()->toString(),
            senderPhone: $this->string('sender_phone')->toString(),
            senderEmail: $this->string('sender_email')->trim()->toString(),
            isGift: $this->input('is_gift') === '1',
            receiverName: $this->string('receiver_name')->trim()->toString(),
            receiverPhone: $this->string('receiver_phone')->toString(),
            shippingAddress: $this->string('shipping_address')->trim()->toString(),
            shippingDistrict: $this->string('shipping_district')->toString(),
            deliveryDate: $this->string('delivery_date')->toString(),
            timeSlot: $this->string('time_slot')->toString(),
            cardSender: $this->filled('card_sender') ? $this->string('card_sender')->trim()->toString() : null,
            cardReceiver: $this->filled('card_receiver') ? $this->string('card_receiver')->trim()->toString() : null,
            cardMessage: $this->filled('card_message') ? $this->string('card_message')->trim()->toString() : null,
            deliveryNotes: $this->filled('delivery_notes') ? $this->string('delivery_notes')->trim()->toString() : null,
            paymentMethod: $this->enum('payment_method', PaymentMethod::class),
        );
    }
}
