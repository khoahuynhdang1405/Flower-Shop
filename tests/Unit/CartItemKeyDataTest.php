<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Cart\CartItemKeyData;
use App\Enums\CartItemType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CartItemKeyDataTest extends TestCase
{
    /**
     * @return array<string, array{string, CartItemType, int, ?int}>
     */
    public static function keyProvider(): array
    {
        return [
            'hoa có size' => ['flower-3-7', CartItemType::Flower, 3, 7],
            'hoa không size' => ['flower-3', CartItemType::Flower, 3, null],
            'quà kèm' => ['addon-12', CartItemType::Addon, 12, null],
        ];
    }

    #[DataProvider('keyProvider')]
    public function test_key_round_trips(string $key, CartItemType $type, int $id, ?int $sizeId): void
    {
        $item = CartItemKeyData::fromString($key);

        $this->assertSame($type, $item->type);
        $this->assertSame($id, $item->id);
        $this->assertSame($sizeId, $item->sizeId);
        $this->assertSame($key, $item->toString());
    }

    public function test_malformed_key_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CartItemKeyData::fromString('addon-1-2');
    }
}
