<?php

namespace Tests\Feature\Admin;

use App\Enums\CategoryType;
use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    public function test_index_lists_collections_with_stats(): void
    {
        Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu', 'slug' => 'diu-dang-mua-thu']);
        Category::factory()->hidden()->create(['name' => 'Ấm áp mùa đông']);

        $this->get(route('admin.collections'))
            ->assertOk()
            ->assertSeeText('Dịu dàng mùa thu')
            ->assertSeeText('Ấm áp mùa đông')
            ->assertSeeText('1 đang hiển thị · 1 đang ẩn')
            ->assertSee(route('admin.collections.edit', Category::firstWhere('slug', 'diu-dang-mua-thu')), false)
            ->assertSee('<span aria-current="page">Bộ sưu tập</span>', false);
    }

    public function test_index_shows_empty_state(): void
    {
        $this->get(route('admin.collections'))->assertOk()->assertSeeText('Tiệm chưa có bộ sưu tập nào');
    }

    public function test_admin_can_create_collection_and_slug_is_generated(): void
    {
        $this->get(route('admin.collections.create'))->assertOk()->assertDontSee('name="slug"', false);

        $this->post(route('admin.collections.store'), [
            'name' => '  Dịu dàng mùa thu ',
            'slug' => 'khong-dung-toi',
            'type' => 'season',
            'description' => 'Hồng phấn, cam đào và những sắc thu dịu êm.',
            'is_active' => '1',
        ])
            ->assertRedirect(route('admin.collections'))
            ->assertSessionHas('status', 'Đã thêm bộ sưu tập “Dịu dàng mùa thu”.');

        $category = Category::sole();
        $this->assertSame('Dịu dàng mùa thu', $category->name);
        $this->assertSame('diu-dang-mua-thu', $category->slug);
        $this->assertSame(CategoryType::Season, $category->type);
        $this->assertTrue($category->is_active);
    }

    public function test_unchecked_visibility_saves_as_hidden(): void
    {
        $this->post(route('admin.collections.store'), ['name' => 'Ấm áp mùa đông', 'type' => 'season']);

        $this->assertFalse(Category::sole()->is_active);
    }

    public function test_create_validates_name_type_and_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'mot-ngay-sinh-nhat']);

        $this->post(route('admin.collections.store'), ['name' => '', 'type' => 'khong-co'])
            ->assertSessionHasErrors([
                'name' => __('admin.collections.form.name_required'),
                'type' => __('admin.collections.form.type_required'),
            ]);

        $this->post(route('admin.collections.store'), ['name' => 'Một ngày sinh nhật', 'type' => 'occasion'])
            ->assertSessionHasErrors(['name' => __('admin.collections.form.slug_taken')])
            ->assertSessionDoesntHaveErrors('slug');

        $this->assertSame(1, Category::count());
    }

    public function test_admin_can_update_collection_and_slug_follows_name(): void
    {
        $category = Category::factory()->create(['name' => 'Sinh nhật', 'slug' => 'sinh-nhat']);

        $this->get(route('admin.collections.edit', $category))->assertOk()->assertSee('value="Sinh nhật"', false);

        $this->put(route('admin.collections.update', $category), [
            'name' => 'Sinh nhật',
            'type' => 'occasion',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->put(route('admin.collections.update', $category), [
            'name' => 'Một ngày sinh nhật',
            'type' => 'occasion',
            'is_active' => '1',
        ])->assertRedirect(route('admin.collections'))->assertSessionHasNoErrors();

        $this->assertSame('Một ngày sinh nhật', $category->refresh()->name);
        $this->assertSame('mot-ngay-sinh-nhat', $category->slug);
    }

    public function test_admin_can_delete_empty_collection(): void
    {
        $category = Category::factory()->create();

        $this->delete(route('admin.collections.destroy', $category))
            ->assertRedirect(route('admin.collections'))
            ->assertSessionHas('status');

        $this->assertModelMissing($category);
    }

    public function test_collection_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create(['name' => 'Dịu dàng mùa thu']);
        DB::table('products')->insert([
            'category_id' => $category->id,
            'name' => 'Lời thì thầm',
            'slug' => 'loi-thi-tham',
            'base_price' => 690000,
        ]);

        $this->from(route('admin.collections'))
            ->delete(route('admin.collections.destroy', $category))
            ->assertRedirect(route('admin.collections'))
            ->assertSessionHas('error', __('admin.collections.flash.has_products', ['name' => 'Dịu dàng mùa thu']));

        $this->assertModelExists($category);
        $this->assertSame(1, DB::table('products')->count());
    }
}
