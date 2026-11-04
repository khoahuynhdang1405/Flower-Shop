<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\DTOs\Product\CatalogFilterData;
use App\Enums\FlowerColor;
use App\Enums\PriceRange;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Bộ lọc trang "Tất cả hoa" (query string): ?type[]=bo-hoa&occasion[]=sinh-nhat&color[]=pink&price=500_800.
 * Slug không tồn tại chỉ cho ra danh sách rỗng nên không cần kiểm tra trong DB.
 */
class ListFlowersRequest extends FormRequest
{
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
            'type' => ['nullable', 'array'],
            'type.*' => ['string', 'max:255'],
            'occasion' => ['nullable', 'array'],
            'occasion.*' => ['string', 'max:255'],
            'color' => ['nullable', 'array'],
            'color.*' => [Rule::enum(FlowerColor::class)],
            'price' => ['nullable', Rule::enum(PriceRange::class)],
        ];
    }

    public function toData(): CatalogFilterData
    {
        return new CatalogFilterData(
            typeSlugs: array_values($this->array('type')),
            occasionSlugs: array_values($this->array('occasion')),
            priceRange: $this->enum('price', PriceRange::class),
            colors: array_map(fn (string $color): FlowerColor => FlowerColor::from($color), array_values($this->array('color'))),
        );
    }
}
