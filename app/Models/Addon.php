<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AddonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Quà kèm bó hoa ("Thêm một chút thương yêu"): hộp quà, gấu bông, chocolate, thiệp viết tay (miễn phí).
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
}
