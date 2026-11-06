<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Khách nhập mã ưu đãi ở giỏ: mã phải tồn tại và đang bật thì mới lưu (viết hoa, bỏ khoảng trắng).
 * Giá trị tối thiểu không chặn ở đây — giỏ hiện lời nhắc và tự áp mã khi khách chọn thêm đủ.
 * TODO: điều kiện "đơn đầu tiên" chưa có trong DB (docs/design-vs-db-gaps.md #8).
 */
final class ApplyPromotionCodeAction
{
    public function __construct(
        private PromotionRepositoryInterface $promotions,
        private CartRepositoryInterface $cart,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(string $code): void
    {
        $promotion = $this->promotions->findActiveByCode(mb_strtoupper(trim($code)))
            ?? throw ValidationException::withMessages(['code' => __('cart.promotion.invalid')]);

        $this->cart->putPromotionCode($promotion->code);
    }
}
