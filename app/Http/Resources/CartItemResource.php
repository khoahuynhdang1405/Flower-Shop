<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Cart\CartAddonLineData;
use App\DTOs\Cart\CartFlowerLineData;
use App\DTOs\Cart\CartItemKeyData;
use App\Enums\CartItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Một món trong giỏ (API). "id" dùng cho PATCH / DELETE /api/cart/items/{id}.
 *
 * @property CartFlowerLineData|CartAddonLineData $resource
 */
class CartItemResource extends JsonResource
{
    /**
     * @return array{id: string, type: string, name: string, size: ?string, unit_price: int, quantity: int, total: int, is_free: bool, image_url: ?string}
     */
    public function toArray(Request $request): array
    {
        $line = $this->resource;
        $isFlower = $line instanceof CartFlowerLineData;
        $key = $isFlower
            ? new CartItemKeyData(CartItemType::Flower, $line->productId, $line->sizeId)
            : new CartItemKeyData(CartItemType::Addon, $line->addonId);

        return [
            'id' => $key->toString(),
            'type' => $key->type->value,
            'name' => $line->name,
            'size' => $isFlower ? $line->sizeName : null,
            'unit_price' => $line->unitPrice,
            'quantity' => $line->quantity,
            'total' => $line->total(),
            'is_free' => ! $isFlower && $line->isFree,
            'image_url' => $line->imageUrl,
        ];
    }
}
