<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->disk = Storage::fake('public');

        $this->product = Product::factory()->create(['name' => 'Nắng trong veo', 'slug' => 'nang-trong-veo']);
    }

    public function test_guests_and_customers_cannot_manage_images(): void
    {
        $this->get(route('admin.products.images', $this->product))->assertRedirect(route('admin.login'));
        $this->post(route('admin.products.images.store', $this->product), ['images' => [$this->photo()]])
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.images', $this->product))
            ->assertRedirect(route('admin.login'));

        $this->assertSame(0, ProductImage::count());
        $this->assertEmpty($this->disk->allFiles());
    }

    public function test_page_shows_empty_state_and_size_options(): void
    {
        ProductSize::factory()->for($this->product)->create(['size_name' => 'Lớn', 'price' => 750000]);

        $this->asAdmin()
            ->get(route('admin.products.images', $this->product))
            ->assertOk()
            ->assertSeeText('Ảnh của “Nắng trong veo”')
            ->assertSeeText('Sản phẩm chưa có ảnh nào')
            ->assertSeeText('Lớn · 750.000 VNĐ')
            ->assertSee('<span aria-current="page">Sản phẩm</span>', false);
    }

    public function test_admin_uploads_several_images_linked_to_a_size(): void
    {
        $large = ProductSize::factory()->for($this->product)->create(['size_name' => 'Lớn']);

        $this->asAdmin()
            ->post(route('admin.products.images.store', $this->product), [
                'images' => [$this->photo('bo-lon-1.jpg'), $this->photo('bo-lon-2.png', 'image/png')],
                'product_size_id' => $large->id,
            ])
            ->assertRedirect(route('admin.products.images', $this->product))
            ->assertSessionHas('status', 'Đã tải lên 2 ảnh.');

        $images = $this->product->images()->get();

        $this->assertCount(2, $images);
        $this->assertSame([$large->id, $large->id], $images->pluck('product_size_id')->all());
        $this->assertSame([true, false], $images->pluck('is_primary')->all());
        $this->assertSame([0, 1], $images->pluck('sort_order')->all());

        foreach ($images as $image) {
            $this->assertStringStartsWith("products/{$this->product->id}/", $image->image_path);
            $this->disk->assertExists($image->image_path);
        }
    }

    public function test_upload_without_size_creates_shared_images_after_existing_ones(): void
    {
        ProductImage::factory()->for($this->product)->primary()->create(['sort_order' => 4]);

        $this->asAdmin()->post(route('admin.products.images.store', $this->product), ['images' => [$this->photo()]]);

        $newImage = ProductImage::query()->latest('id')->first();

        $this->assertNull($newImage->product_size_id);
        $this->assertFalse($newImage->is_primary);
        $this->assertSame(5, $newImage->sort_order);
    }

    public function test_upload_validates_files_and_size(): void
    {
        $otherProductSize = ProductSize::factory()->create();

        $this->asAdmin()->post(route('admin.products.images.store', $this->product), [])
            ->assertSessionHasErrors(['images' => __('admin.product_images.form.images_required')]);

        $this->post(route('admin.products.images.store', $this->product), [
            'images' => [
                UploadedFile::fake()->create('bang-gia.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('qua-nang.jpg', 5000, 'image/jpeg'),
            ],
            'product_size_id' => $otherProductSize->id,
        ])->assertSessionHasErrors([
            'images.0' => __('admin.product_images.form.image_type'),
            'images.1' => __('admin.product_images.form.image_size', ['max' => 4]),
            'product_size_id' => __('admin.product_images.form.size_invalid'),
        ]);

        $this->post(route('admin.products.images.store', $this->product), [
            'images' => array_map(fn (int $index): UploadedFile => $this->photo("anh-{$index}.jpg"), range(1, 11)),
        ])->assertSessionHasErrors(['images' => __('admin.product_images.form.images_max', ['max' => 10])]);

        $this->assertSame(0, ProductImage::count());
        $this->assertEmpty($this->disk->allFiles());
    }

    public function test_admin_changes_image_size_and_back_to_shared(): void
    {
        $small = ProductSize::factory()->for($this->product)->create(['size_name' => 'Nhỏ']);
        $image = ProductImage::factory()->for($this->product)->create();

        $this->asAdmin()
            ->patch(route('admin.products.images.update', [$this->product, $image]), ['product_size_id' => $small->id])
            ->assertRedirect(route('admin.products.images', $this->product));
        $this->assertSame($small->id, $image->refresh()->product_size_id);

        $this->patch(route('admin.products.images.update', [$this->product, $image]), ['product_size_id' => '']);
        $this->assertNull($image->refresh()->product_size_id);
    }

    public function test_admin_sets_a_new_primary_image(): void
    {
        $oldPrimary = ProductImage::factory()->for($this->product)->primary()->create();
        $image = ProductImage::factory()->for($this->product)->create();

        $this->asAdmin()
            ->post(route('admin.products.images.primary', [$this->product, $image]))
            ->assertSessionHas('status', __('admin.product_images.flash.primary_updated'));

        $this->assertTrue($image->refresh()->is_primary);
        $this->assertFalse($oldPrimary->refresh()->is_primary);
    }

    public function test_deleting_primary_image_removes_file_and_promotes_next_image(): void
    {
        $this->disk->put('products/1/chinh.jpg', 'x');
        $primary = ProductImage::factory()->for($this->product)->primary()->create(['image_path' => 'products/1/chinh.jpg', 'sort_order' => 0]);
        $next = ProductImage::factory()->for($this->product)->create(['sort_order' => 1]);

        $this->asAdmin()
            ->delete(route('admin.products.images.destroy', [$this->product, $primary]))
            ->assertSessionHas('status', __('admin.product_images.flash.deleted'));

        $this->assertModelMissing($primary);
        $this->disk->assertMissing('products/1/chinh.jpg');
        $this->assertTrue($next->refresh()->is_primary);
    }

    public function test_image_of_another_product_is_not_reachable(): void
    {
        $foreignImage = ProductImage::factory()->create();

        $this->asAdmin()
            ->delete(route('admin.products.images.destroy', [$this->product, $foreignImage]))
            ->assertNotFound();

        $this->assertModelExists($foreignImage);
    }

    public function test_page_renders_in_english(): void
    {
        app()->setLocale('en');
        ProductImage::factory()->for($this->product)->primary()->create();

        $this->asAdmin()
            ->get(route('admin.products.images', $this->product))
            ->assertOk()
            ->assertSeeText('Photos of “Nắng trong veo”')
            ->assertSeeText('1 photo')
            ->assertSeeText('Main photo')
            ->assertSeeText('Shared photo');
    }

    private function asAdmin(): static
    {
        return $this->actingAs(Admin::factory()->create(), 'admin');
    }

    private function photo(string $name = 'hoa.jpg', string $mimeType = 'image/jpeg'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 200, $mimeType);
    }
}
