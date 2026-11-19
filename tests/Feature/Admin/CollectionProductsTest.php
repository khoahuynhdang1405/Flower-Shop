<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bộ sưu tập › Sản phẩm: Theo mùa / Theo dịp thêm/bỏ qua pivot category_product, Kiểu hoa chỉ xem.
 */
class CollectionProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    public function test_page_lists_linked_products_and_candidates_to_add(): void
    {
        $autumn = Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu']);
        $bouquet = Category::factory()->flowerType()->create(['name' => 'Bó hoa']);
        $linked = Product::factory()->for($bouquet)->create(['name' => 'Thu dịu dàng', 'base_price' => 890000]);
        Product::factory()->for($bouquet)->create(['name' => 'Nắng trong veo']);
        Product::factory()->hidden()->create(['name' => 'Cúc họa mi tháng mười']);
        $autumn->linkedProducts()->attach($linked);

        $response = $this->get(route('admin.collections.products', $autumn))
            ->assertOk()
            ->assertSeeText('Sản phẩm trong “Dịu dàng mùa thu”')
            ->assertSeeText('Theo mùa · 1 sản phẩm')
            ->assertSeeTextInOrder(['Đang có trong bộ sưu tập', 'Thu dịu dàng', 'Bó hoa', '890.000', 'Đang bán', 'Bỏ khỏi bộ sưu tập'])
            ->assertSeeTextInOrder(['Thêm sản phẩm', 'Cúc họa mi tháng mười', 'Đang ẩn', 'Nắng trong veo'])
            ->assertSee('<span aria-current="page">Bộ sưu tập</span>', false);

        $this->assertSame(2, substr_count($response->getContent(), 'name="product_ids[]"'));
    }

    public function test_admin_adds_ticked_products_to_a_collection(): void
    {
        $birthday = Category::factory()->create(['name' => 'Một ngày sinh nhật']);
        $first = Product::factory()->create();
        $second = Product::factory()->create();

        $this->post(route('admin.collections.products.store', $birthday), ['product_ids' => [$first->id, $second->id]])
            ->assertRedirect(route('admin.collections.products', $birthday))
            ->assertSessionHas('status', 'Đã thêm 2 sản phẩm vào “Một ngày sinh nhật”.');

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $birthday->linkedProducts()->pluck('products.id')->all());
    }

    public function test_one_product_can_belong_to_several_collections(): void
    {
        $product = Product::factory()->create();
        $autumn = Category::factory()->season()->create();
        $birthday = Category::factory()->create();

        $this->post(route('admin.collections.products.store', $autumn), ['product_ids' => [$product->id]]);
        $this->post(route('admin.collections.products.store', $birthday), ['product_ids' => [$product->id]]);

        $this->assertDatabaseCount('category_product', 2);
    }

    public function test_adding_a_product_already_in_the_collection_does_not_duplicate_it(): void
    {
        $birthday = Category::factory()->create(['name' => 'Một ngày sinh nhật']);
        $product = Product::factory()->create();
        $birthday->linkedProducts()->attach($product);

        $this->post(route('admin.collections.products.store', $birthday), ['product_ids' => [$product->id]])
            ->assertSessionHas('status', 'Các sản phẩm này đã có trong “Một ngày sinh nhật” rồi.');

        $this->assertDatabaseCount('category_product', 1);
    }

    public function test_adding_requires_at_least_one_existing_product(): void
    {
        $birthday = Category::factory()->create();

        $this->from(route('admin.collections.products', $birthday))
            ->post(route('admin.collections.products.store', $birthday), [])
            ->assertRedirect(route('admin.collections.products', $birthday))
            ->assertSessionHasErrors(['product_ids' => 'Bạn tích chọn ít nhất một sản phẩm nhé.']);

        $this->post(route('admin.collections.products.store', $birthday), ['product_ids' => [999]])
            ->assertSessionHasErrors('product_ids.0');

        $this->assertDatabaseCount('category_product', 0);
    }

    public function test_admin_removes_a_product_but_keeps_it_in_the_shop(): void
    {
        $autumn = Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu']);
        $product = Product::factory()->create(['name' => 'Thu dịu dàng']);
        $autumn->linkedProducts()->attach($product);

        $this->from(route('admin.collections.products', $autumn))
            ->delete(route('admin.collections.products.destroy', [$autumn, $product]))
            ->assertRedirect(route('admin.collections.products', $autumn))
            ->assertSessionHas('status', 'Đã bỏ “Thu dịu dàng” khỏi “Dịu dàng mùa thu”.');

        $this->assertDatabaseCount('category_product', 0);
        $this->assertModelExists($product);
    }

    public function test_search_narrows_products_to_add(): void
    {
        $birthday = Category::factory()->create();
        Product::factory()->create(['name' => 'Hồng phấn ban mai']);
        Product::factory()->create(['name' => 'Bình yên xanh']);

        $this->get(route('admin.collections.products', ['collection' => $birthday, 'q' => 'hồng']))
            ->assertOk()
            ->assertSeeText('Hồng phấn ban mai')
            ->assertDontSeeText('Bình yên xanh');

        $this->get(route('admin.collections.products', ['collection' => $birthday, 'q' => 'không có']))
            ->assertSeeText('Không tìm thấy sản phẩm nào khớp “không có” mà chưa có trong bộ sưu tập.');
    }

    public function test_flower_type_collection_is_view_only(): void
    {
        $vase = Category::factory()->flowerType()->create(['name' => 'Hoa cắm bình']);
        Product::factory()->for($vase)->create(['name' => 'Một ngày bình dị']);
        $other = Product::factory()->create(['name' => 'Nắng trong veo']);

        $this->get(route('admin.collections.products', $vase))
            ->assertOk()
            ->assertSeeText('Một ngày bình dị')
            ->assertSeeText('Kiểu hoa là danh mục chính của từng sản phẩm nên chỉ xem ở đây.')
            ->assertDontSeeText('Nắng trong veo')
            ->assertDontSee('name="product_ids[]"', false)
            ->assertDontSeeText('Bỏ khỏi bộ sưu tập');

        $this->post(route('admin.collections.products.store', $vase), ['product_ids' => [$other->id]])->assertForbidden();
        $this->assertDatabaseCount('category_product', 0);
    }

    public function test_collection_cards_count_products_by_collection_type(): void
    {
        $bouquet = Category::factory()->flowerType()->create(['name' => 'Bó hoa']);
        $autumn = Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu']);
        $products = Product::factory()->for($bouquet)->count(3)->create();
        $autumn->linkedProducts()->attach($products->take(2)->pluck('id'));

        $this->get(route('admin.collections'))
            ->assertOk()
            ->assertSeeTextInOrder(['Dịu dàng mùa thu', '2 sản phẩm'])
            ->assertSeeTextInOrder(['Bó hoa', '3 sản phẩm'])
            ->assertSee(route('admin.collections.products', $autumn), false);
    }

    public function test_deleting_a_collection_or_product_removes_its_links(): void
    {
        $autumn = Category::factory()->season()->create();
        $birthday = Category::factory()->create();
        $product = Product::factory()->create();
        $autumn->linkedProducts()->attach($product);
        $birthday->linkedProducts()->attach($product);

        $this->delete(route('admin.collections.destroy', $autumn))->assertRedirect(route('admin.collections'));
        $this->assertDatabaseCount('category_product', 1);
        $this->assertModelExists($product);

        $product->delete();
        $this->assertDatabaseCount('category_product', 0);
    }

    public function test_guests_cannot_manage_collection_products(): void
    {
        $birthday = Category::factory()->create();
        auth('admin')->logout();

        $this->get(route('admin.collections.products', $birthday))->assertRedirect(route('admin.login'));
        $this->post(route('admin.collections.products.store', $birthday), ['product_ids' => [1]])->assertRedirect(route('admin.login'));
    }
}
