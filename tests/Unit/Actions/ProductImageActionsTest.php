<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ProductImage\AssignProductImageSizeAction;
use App\Actions\ProductImage\DeleteProductImageAction;
use App\Actions\ProductImage\SetPrimaryProductImageAction;
use App\Actions\ProductImage\UploadProductImagesAction;
use App\DTOs\ProductImage\UploadProductImagesData;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * Nghiệp vụ ảnh sản phẩm, với repository giả (không chạm database).
 */
class ProductImageActionsTest extends TestCase
{
    private ProductImageRepositoryInterface&MockInterface $images;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->images = Mockery::mock(ProductImageRepositoryInterface::class);
        $this->disk = Storage::fake('public');
    }

    public function test_upload_stores_files_and_makes_first_image_primary_when_product_has_none(): void
    {
        $this->images->shouldReceive('hasPrimary')->once()->with(7)->andReturn(false);
        $this->images->shouldReceive('nextSortOrder')->once()->with(7)->andReturn(3);
        $this->images->shouldReceive('create')->twice()->andReturnUsing(fn (array $attributes): ProductImage => new ProductImage($attributes));

        $created = $this->uploadAction()->execute(new UploadProductImagesData(7, 2, [$this->photo(), $this->photo()]));

        $this->assertSame([true, false], array_map(fn (ProductImage $image): bool => $image->is_primary, $created));
        $this->assertSame([3, 4], array_map(fn (ProductImage $image): int => $image->sort_order, $created));
        $this->assertSame([2, 2], array_map(fn (ProductImage $image): ?int => $image->product_size_id, $created));
        $this->assertCount(2, $this->disk->files('products/7'));
    }

    public function test_upload_keeps_existing_primary_image(): void
    {
        $this->images->shouldReceive('hasPrimary')->andReturn(true);
        $this->images->shouldReceive('nextSortOrder')->andReturn(0);
        $this->images->shouldReceive('create')->once()
            ->with(Mockery::on(fn (array $attributes): bool => $attributes['is_primary'] === false && $attributes['product_size_id'] === null))
            ->andReturn(new ProductImage);

        $this->uploadAction()->execute(new UploadProductImagesData(7, null, [$this->photo()]));
    }

    public function test_upload_removes_already_stored_files_when_saving_fails(): void
    {
        $this->images->shouldReceive('hasPrimary')->andReturn(false);
        $this->images->shouldReceive('nextSortOrder')->andReturn(0);
        $this->images->shouldReceive('create')->once()->andReturn(new ProductImage);
        $this->images->shouldReceive('create')->once()->andThrow(new RuntimeException('DB lỗi'));

        try {
            $this->uploadAction()->execute(new UploadProductImagesData(7, null, [$this->photo(), $this->photo()]));
            $this->fail('Action phải ném lại lỗi.');
        } catch (RuntimeException $exception) {
            $this->assertSame('DB lỗi', $exception->getMessage());
        }

        $this->assertEmpty($this->disk->allFiles());
    }

    public function test_set_primary_clears_old_primary_first(): void
    {
        $image = $this->image(['id' => 5, 'product_id' => 7]);

        $this->images->shouldReceive('clearPrimary')->once()->with(7)->ordered();
        $this->images->shouldReceive('update')->once()->with($image, ['is_primary' => true])->ordered();

        (new SetPrimaryProductImageAction($this->images))->execute($image);
    }

    public function test_assign_size_updates_only_the_size(): void
    {
        $image = $this->image(['id' => 5, 'product_id' => 7]);

        $this->images->shouldReceive('update')->once()->with($image, ['product_size_id' => 3]);

        (new AssignProductImageSizeAction($this->images))->execute($image, 3);
    }

    public function test_deleting_primary_image_promotes_next_image_and_removes_file(): void
    {
        $this->disk->put('products/7/chinh.jpg', 'x');
        $primary = $this->image(['id' => 5, 'product_id' => 7, 'is_primary' => true, 'image_path' => 'products/7/chinh.jpg']);
        $next = $this->image(['id' => 6, 'product_id' => 7, 'is_primary' => false]);

        $this->images->shouldReceive('delete')->once()->with($primary);
        $this->images->shouldReceive('findFirstByProduct')->once()->with(7)->andReturn($next);
        $this->images->shouldReceive('clearPrimary')->once()->with(7);
        $this->images->shouldReceive('update')->once()->with($next, ['is_primary' => true]);

        $this->deleteAction()->execute($primary);

        $this->disk->assertMissing('products/7/chinh.jpg');
    }

    public function test_deleting_secondary_image_keeps_current_primary(): void
    {
        $image = $this->image(['id' => 6, 'product_id' => 7, 'is_primary' => false, 'image_path' => 'products/7/phu.jpg']);

        $this->images->shouldReceive('delete')->once()->with($image);
        $this->images->shouldNotReceive('findFirstByProduct');
        $this->images->shouldNotReceive('update');

        $this->deleteAction()->execute($image);
    }

    private function uploadAction(): UploadProductImagesAction
    {
        return new UploadProductImagesAction($this->images, $this->app->make(FilesystemFactory::class));
    }

    private function deleteAction(): DeleteProductImageAction
    {
        return new DeleteProductImageAction(
            $this->images,
            new SetPrimaryProductImageAction($this->images),
            $this->app->make(FilesystemFactory::class),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function image(array $attributes): ProductImage
    {
        return (new ProductImage)->forceFill($attributes);
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->create('hoa.jpg', 100, 'image/jpeg');
    }
}
