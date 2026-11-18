<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Bài viết Góc nhỏ (Admin › Chuyện Hoa). content viết bằng Markdown; thumbnail là đường dẫn trên disk public.
 */
#[Fillable(['title', 'slug', 'summary', 'content', 'thumbnail', 'admin_id', 'is_published'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * Số chữ đọc được mỗi phút, để ước tính thời gian đọc.
     */
    public const WORDS_PER_MINUTE = 200;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null);
    }

    /**
     * Thời gian đọc ước tính (phút, tối thiểu 1) từ số chữ trong nội dung.
     *
     * @return Attribute<int, never>
     */
    protected function readingMinutes(): Attribute
    {
        return Attribute::get(function (): int {
            $words = preg_split('/\s+/u', trim((string) $this->content), flags: PREG_SPLIT_NO_EMPTY);

            return max(1, (int) ceil(count($words) / self::WORDS_PER_MINUTE));
        });
    }
}
