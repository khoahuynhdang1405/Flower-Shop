<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Category\ListSeasonCoversAction;
use App\Actions\Collection\CreateCollectionAction;
use App\Actions\Collection\DeleteCollectionAction;
use App\Actions\Collection\UpdateCollectionAction;
use App\DTOs\Collection\CollectionData;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * Ảnh chính bộ sưu tập với repository giả: lưu / thay / gỡ / xóa file trên disk public, và ảnh của 4 mùa.
 */
class CollectionCoverActionsTest extends TestCase
{
    private CategoryRepositoryInterface&MockInterface $categories;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Mockery::mock(CategoryRepositoryInterface::class);
        $this->disk = Storage::fake('public');
    }

    public function test_create_stores_cover_in_collections_folder(): void
    {
        $this->categories->expects('create')
            ->with(Mockery::on(fn (array $attributes): bool => str_starts_with((string) $attributes['cover_image'], 'collections/')))
            ->andReturnUsing(fn (array $attributes): Category => (new Category)->forceFill($attributes));

        $category = $this->createAction()->execute($this->data(cover: UploadedFile::fake()->create('bia.jpg', 200, 'image/jpeg')));

        $this->disk->assertExists($category->cover_image);
    }

    public function test_create_removes_stored_cover_when_saving_fails(): void
    {
        $this->categories->expects('create')->andThrow(new RuntimeException('DB down'));

        try {
            $this->createAction()->execute($this->data(cover: UploadedFile::fake()->create('bia.jpg', 200, 'image/jpeg')));
            $this->fail('The exception should be rethrown.');
        } catch (RuntimeException) {
        }

        $this->assertSame([], $this->disk->allFiles('collections'));
    }

    public function test_update_replaces_cover_and_deletes_the_old_file(): void
    {
        $this->disk->put('collections/cu.jpg', 'x');
        $collection = (new Category)->forceFill(['id' => 1, 'cover_image' => 'collections/cu.jpg']);
        $this->categories->expects('update')
            ->with($collection, Mockery::on(fn (array $attributes): bool => $attributes['cover_image'] !== 'collections/cu.jpg'));

        $this->updateAction()->execute($collection, $this->data(cover: UploadedFile::fake()->create('moi.webp', 200, 'image/webp')));

        $this->disk->assertMissing('collections/cu.jpg');
        $this->assertCount(1, $this->disk->allFiles('collections'));
    }

    public function test_update_keeps_cover_without_a_new_file_and_can_remove_it(): void
    {
        $this->disk->put('collections/cu.jpg', 'x');
        $collection = (new Category)->forceFill(['id' => 1, 'cover_image' => 'collections/cu.jpg']);
        $this->categories->expects('update')->with($collection, Mockery::on(fn (array $attributes): bool => $attributes['cover_image'] === 'collections/cu.jpg'));
        $this->categories->expects('update')->with($collection, Mockery::on(fn (array $attributes): bool => $attributes['cover_image'] === null));

        $this->updateAction()->execute($collection, $this->data());
        $this->disk->assertExists('collections/cu.jpg');

        $this->updateAction()->execute($collection, $this->data(removeCover: true));
        $this->disk->assertMissing('collections/cu.jpg');
    }

    public function test_delete_removes_record_and_cover_but_not_with_primary_products(): void
    {
        $this->disk->put('collections/cu.jpg', 'x');
        $collection = (new Category)->forceFill(['id' => 1, 'cover_image' => 'collections/cu.jpg']);
        $this->categories->expects('hasPrimaryProducts')->with($collection)->twice()->andReturn(true, false);
        $this->categories->expects('delete')->once()->with($collection);
        $action = new DeleteCollectionAction($this->categories, $this->app->make(FilesystemFactory::class));

        $this->assertFalse($action->execute($collection));
        $this->disk->assertExists('collections/cu.jpg');

        $this->assertTrue($action->execute($collection));
        $this->disk->assertMissing('collections/cu.jpg');
    }

    public function test_season_covers_map_each_season_to_its_collection_image(): void
    {
        $autumn = (new Category)->forceFill(['slug' => 'diu-dang-mua-thu', 'cover_image' => 'collections/thu.jpg']);
        $this->categories->expects('findActiveSeasonsBySlugs')
            ->with(['trong-treo-mua-xuan', 'ruc-ro-mua-ha', 'diu-dang-mua-thu', 'an-lanh-mua-dong'])
            ->andReturn(new Collection(['diu-dang-mua-thu' => $autumn]));

        $covers = (new ListSeasonCoversAction($this->categories))->execute();

        $this->assertSame(['spring', 'summer', 'autumn', 'winter'], array_keys($covers));
        $this->assertSame($this->disk->url('collections/thu.jpg'), $covers['autumn']);
        $this->assertNull($covers['spring']);
    }

    private function data(?UploadedFile $cover = null, bool $removeCover = false): CollectionData
    {
        return new CollectionData('Dịu dàng mùa thu', 'diu-dang-mua-thu', CategoryType::Season, null, true, $cover, $removeCover);
    }

    private function createAction(): CreateCollectionAction
    {
        return new CreateCollectionAction($this->categories, $this->app->make(FilesystemFactory::class));
    }

    private function updateAction(): UpdateCollectionAction
    {
        return new UpdateCollectionAction($this->categories, $this->app->make(FilesystemFactory::class));
    }
}
