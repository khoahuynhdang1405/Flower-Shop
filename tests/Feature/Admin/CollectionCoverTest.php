<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ảnh chính bộ sưu tập (categories.cover_image): tải lên ở Admin, hiện ở thẻ Admin, thẻ dịp Trang chủ và trang Hoa theo mùa.
 */
class CollectionCoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        Storage::fake('public');
    }

    public function test_admin_creates_a_collection_with_a_main_image(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->post(route('admin.collections.store'), [
            'name' => 'Sinh nhật',
            'type' => 'occasion',
            'is_active' => '1',
            'cover_image' => UploadedFile::fake()->create('bia.jpg', 300, 'image/jpeg'),
        ])->assertRedirect(route('admin.collections'));

        $category = Category::sole();
        $this->assertStringStartsWith('collections/', $category->cover_image);
        Storage::disk('public')->assertExists($category->cover_image);

        $this->get(route('admin.collections'))->assertSee($category->cover_url, false);
        $this->get(route('admin.collections.edit', $category))
            ->assertOk()
            ->assertSee($category->cover_url, false)
            ->assertSee('name="remove_cover"', false)
            ->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_admin_replaces_and_removes_the_main_image(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');
        Storage::disk('public')->put('collections/cu.jpg', 'x');
        $category = Category::factory()->create(['name' => 'Sinh nhật', 'cover_image' => 'collections/cu.jpg']);
        $form = ['name' => 'Sinh nhật', 'type' => 'occasion', 'is_active' => '1'];

        $this->put(route('admin.collections.update', $category), [...$form, 'cover_image' => UploadedFile::fake()->create('moi.webp', 200, 'image/webp')])
            ->assertRedirect(route('admin.collections'));
        $newCover = $category->fresh()->cover_image;
        $this->assertNotSame('collections/cu.jpg', $newCover);
        Storage::disk('public')->assertMissing('collections/cu.jpg');

        $this->put(route('admin.collections.update', $category), [...$form, 'remove_cover' => '1'])->assertRedirect(route('admin.collections'));
        $this->assertNull($category->fresh()->cover_image);
        Storage::disk('public')->assertMissing($newCover);
    }

    public function test_main_image_must_be_a_small_jpg_png_or_webp(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->from(route('admin.collections.create'))
            ->post(route('admin.collections.store'), [
                'name' => 'Sinh nhật',
                'type' => 'occasion',
                'cover_image' => UploadedFile::fake()->create('bia.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['cover_image' => 'Bạn chọn ảnh JPG, PNG hoặc WebP nhé.']);

        $this->post(route('admin.collections.store'), [
            'name' => 'Sinh nhật',
            'type' => 'occasion',
            'cover_image' => UploadedFile::fake()->create('to.jpg', 5000, 'image/jpeg'),
        ])->assertSessionHasErrors(['cover_image' => 'Ảnh không quá 4 MB.']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_deleting_a_collection_removes_its_main_image(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');
        Storage::disk('public')->put('collections/cu.jpg', 'x');
        $category = Category::factory()->create(['cover_image' => 'collections/cu.jpg']);

        $this->delete(route('admin.collections.destroy', $category))->assertRedirect(route('admin.collections'));

        $this->assertModelMissing($category);
        Storage::disk('public')->assertMissing('collections/cu.jpg');
    }

    public function test_home_occasion_cards_use_the_main_image(): void
    {
        $category = Category::factory()->create(['name' => 'Sinh nhật', 'cover_image' => 'collections/sinh-nhat.jpg']);

        $this->get(route('home'))->assertOk()->assertSee($category->cover_url, false);
    }

    public function test_season_pages_use_the_matching_season_collection_image(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 7));
        $autumn = Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu', 'slug' => 'diu-dang-mua-thu', 'cover_image' => 'collections/thu.jpg']);
        Category::factory()->season()->hidden()->create(['name' => 'An lành mùa đông', 'slug' => 'an-lanh-mua-dong', 'cover_image' => 'collections/dong.jpg']);

        $this->get(route('seasons'))
            ->assertOk()
            ->assertSee($autumn->cover_url, false)
            ->assertDontSee('collections/dong.jpg', false);

        $this->get(route('home'))->assertOk()->assertSee($autumn->cover_url, false);
    }
}
