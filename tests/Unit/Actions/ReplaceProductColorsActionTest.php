<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Product\ReplaceProductColorsAction;
use App\Enums\FlowerColor;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Mockery;
use Tests\TestCase;

class ReplaceProductColorsActionTest extends TestCase
{
    public function test_it_replaces_the_colors_once_each(): void
    {
        $product = new Product;
        $products = Mockery::mock(ProductRepositoryInterface::class);
        $products->shouldReceive('replaceColors')->once()->with($product, [FlowerColor::Pink, FlowerColor::Green]);

        (new ReplaceProductColorsAction($products))->execute($product, [FlowerColor::Pink, FlowerColor::Green, FlowerColor::Pink]);
    }
}
