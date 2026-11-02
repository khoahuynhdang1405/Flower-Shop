<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sản phẩm mẫu theo docs/designs/admin/03-san-pham-danh-gia.png, kèm giá từng size.
 */
class ProductSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'bouquet' => 'Bó hoa',
        'vase' => 'Hoa cắm bình',
        'basket' => 'Giỏ & hộp hoa',
    ];

    /**
     * Giá size mặc định (Vừa, hoặc size duy nhất) là base_price.
     *
     * @var list<array{name: string, category: string, stock: int, is_active: bool, sizes: array<string, int>, default: string}>
     */
    private const PRODUCTS = [
        ['name' => 'Nắng trong veo', 'category' => 'bouquet', 'stock' => 18, 'is_active' => true, 'sizes' => ['Nhỏ' => 450000, 'Vừa' => 550000, 'Lớn' => 750000], 'default' => 'Vừa'],
        ['name' => 'Lời thì thầm', 'category' => 'bouquet', 'stock' => 12, 'is_active' => true, 'sizes' => ['Nhỏ' => 590000, 'Vừa' => 690000, 'Lớn' => 890000], 'default' => 'Vừa'],
        ['name' => 'Thu dịu dàng', 'category' => 'vase', 'stock' => 3, 'is_active' => true, 'sizes' => ['Vừa' => 890000, 'Lớn' => 1190000], 'default' => 'Vừa'],
        ['name' => 'Bình yên xanh', 'category' => 'basket', 'stock' => 10, 'is_active' => true, 'sizes' => ['Vừa' => 750000, 'Lớn' => 950000], 'default' => 'Vừa'],
        ['name' => 'Một ngày bình dị', 'category' => 'vase', 'stock' => 8, 'is_active' => true, 'sizes' => ['Nhỏ' => 490000, 'Vừa' => 590000, 'Lớn' => 790000], 'default' => 'Vừa'],
        ['name' => 'Hồng phấn ban mai', 'category' => 'bouquet', 'stock' => 2, 'is_active' => true, 'sizes' => ['Nhỏ' => 420000, 'Vừa' => 520000], 'default' => 'Nhỏ'],
        ['name' => 'Ấm áp mùa đông', 'category' => 'basket', 'stock' => 0, 'is_active' => true, 'sizes' => ['Vừa' => 980000], 'default' => 'Vừa'],
        ['name' => 'Cúc họa mi tháng mười', 'category' => 'bouquet', 'stock' => 15, 'is_active' => false, 'sizes' => ['Nhỏ' => 380000, 'Vừa' => 480000], 'default' => 'Nhỏ'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = collect(self::CATEGORIES)->map(fn (string $name): Category => Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'type' => CategoryType::FlowerType],
        ));

        foreach (self::PRODUCTS as $data) {
            $product = Product::updateOrCreate(['slug' => Str::slug($data['name'])], [
                'category_id' => $categories[$data['category']]->id,
                'name' => $data['name'],
                'base_price' => $data['sizes'][$data['default']],
                'stock' => $data['stock'],
                'is_active' => $data['is_active'],
            ]);

            foreach ($data['sizes'] as $sizeName => $price) {
                $product->sizes()->updateOrCreate(['size_name' => $sizeName], [
                    'price' => $price,
                    'is_default' => $sizeName === $data['default'],
                ]);
            }
        }
    }
}
