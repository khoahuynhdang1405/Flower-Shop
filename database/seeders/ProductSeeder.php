<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Enums\FlowerColor;
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
     * Dịp gửi thương ở bộ lọc "Tất cả hoa" (pivot category_product).
     *
     * @var array<string, string>
     */
    private const OCCASIONS = [
        'birthday' => 'Sinh nhật',
        'love' => 'Tình yêu',
        'congrats' => 'Chúc mừng',
        'everyday' => 'Một ngày bình thường',
    ];

    /**
     * Giá size mặc định (Vừa, hoặc size duy nhất) là base_price. is_featured: hiện ở "Những bó hoa được thương nhất" (Trang chủ).
     *
     * @var list<array{name: string, is_featured?: bool, category: string, stock: int, is_active: bool, sizes: array<string, int>, default: string, occasions: list<string>, colors: list<string>}>
     */
    private const PRODUCTS = [
        ['name' => 'Nắng trong veo', 'is_featured' => true, 'category' => 'bouquet', 'stock' => 18, 'is_active' => true, 'sizes' => ['Nhỏ' => 450000, 'Vừa' => 550000, 'Lớn' => 750000], 'default' => 'Vừa', 'occasions' => ['birthday', 'everyday'], 'colors' => ['yellow', 'peach']],
        ['name' => 'Lời thì thầm', 'is_featured' => true, 'category' => 'bouquet', 'stock' => 12, 'is_active' => true, 'sizes' => ['Nhỏ' => 480000, 'Vừa' => 680000, 'Lớn' => 880000], 'default' => 'Vừa', 'occasions' => ['love', 'birthday'], 'colors' => ['pink']],
        ['name' => 'Thu dịu dàng', 'category' => 'vase', 'stock' => 3, 'is_active' => true, 'sizes' => ['Vừa' => 890000, 'Lớn' => 1190000], 'default' => 'Vừa', 'occasions' => ['everyday'], 'colors' => ['peach']],
        ['name' => 'Bình yên xanh', 'is_featured' => true, 'category' => 'basket', 'stock' => 10, 'is_active' => true, 'sizes' => ['Vừa' => 750000, 'Lớn' => 950000], 'default' => 'Vừa', 'occasions' => ['congrats'], 'colors' => ['green', 'white']],
        ['name' => 'Một ngày bình dị', 'is_featured' => true, 'category' => 'vase', 'stock' => 8, 'is_active' => true, 'sizes' => ['Nhỏ' => 490000, 'Vừa' => 590000, 'Lớn' => 790000], 'default' => 'Vừa', 'occasions' => ['everyday'], 'colors' => ['white', 'green']],
        ['name' => 'Hồng phấn ban mai', 'category' => 'bouquet', 'stock' => 2, 'is_active' => true, 'sizes' => ['Nhỏ' => 420000, 'Vừa' => 520000], 'default' => 'Nhỏ', 'occasions' => ['love'], 'colors' => ['pink']],
        ['name' => 'Ấm áp mùa đông', 'category' => 'basket', 'stock' => 0, 'is_active' => true, 'sizes' => ['Vừa' => 980000], 'default' => 'Vừa', 'occasions' => ['congrats'], 'colors' => ['red']],
        ['name' => 'Cúc họa mi tháng mười', 'category' => 'bouquet', 'stock' => 15, 'is_active' => false, 'sizes' => ['Nhỏ' => 380000, 'Vừa' => 480000], 'default' => 'Nhỏ', 'occasions' => ['birthday'], 'colors' => ['white']],
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
        $occasions = collect(self::OCCASIONS)->map(fn (string $name): Category => Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'type' => CategoryType::Occasion],
        ));

        foreach (self::PRODUCTS as $data) {
            $product = Product::updateOrCreate(['slug' => Str::slug($data['name'])], [
                'category_id' => $categories[$data['category']]->id,
                'name' => $data['name'],
                'base_price' => $data['sizes'][$data['default']],
                'stock' => $data['stock'],
                'is_active' => $data['is_active'],
                'is_featured' => $data['is_featured'] ?? false,
            ]);

            foreach ($data['sizes'] as $sizeName => $price) {
                $product->sizes()->updateOrCreate(['size_name' => $sizeName], [
                    'price' => $price,
                    'is_default' => $sizeName === $data['default'],
                ]);
            }

            $product->occasions()->sync($occasions->only($data['occasions'])->pluck('id'));
            $product->colors()->delete();
            $product->colors()->createMany(array_map(fn (string $color): array => ['color' => FlowerColor::from($color)], $data['colors']));
        }
    }
}
