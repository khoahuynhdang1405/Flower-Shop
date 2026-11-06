<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AddonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Quà kèm bó hoa: hộp quà, gấu bông, chocolate, thiệp viết tay (miễn phí).
 */
#[Fillable(['name', 'price', 'image_path', 'is_free', 'is_active'])]
class Addon extends Model
{
    /** @use HasFactory<AddonFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ảnh trên disk public; chưa có ảnh thì null (giao diện hiện khung giữ chỗ).
     *
     * @return Attribute<?string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image_path) {
                return null;
            }

            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            return $disk->url($this->image_path);
        });
    }
}
