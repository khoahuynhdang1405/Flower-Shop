<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\RenderedPostContent;
use Illuminate\Support\Str;

/**
 * Chuyển nội dung Markdown của bài sang HTML an toàn để hiển thị (trang Góc nhỏ và "Xem trước" ở admin):
 * - bỏ mọi HTML viết tay và link không an toàn (javascript:, data:...) để chặn XSS;
 * - gắn id cho mỗi tiêu đề "##" và trả về danh sách đó làm mục lục "Trong câu chuyện này";
 * - ảnh đứng riêng một đoạn thành <figure>, chữ alt làm chú thích ảnh.
 */
final class RenderPostContentAction
{
    public function execute(string $markdown): RenderedPostContent
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $headings = [];
        $html = (string) preg_replace_callback('/<h2>(.*?)<\/h2>/s', function (array $match) use (&$headings): string {
            $id = 'muc-'.(count($headings) + 1);
            $headings[] = ['id' => $id, 'title' => html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5)];

            return '<h2 id="'.$id.'">'.$match[1].'</h2>';
        }, $html);

        $html = (string) preg_replace_callback(
            '/<p><img src="([^"]*)" alt="([^"]*)" \/><\/p>/',
            fn (array $match): string => '<figure><img src="'.$match[1].'" alt="'.$match[2].'" loading="lazy">'
                .($match[2] !== '' ? '<figcaption>'.$match[2].'</figcaption>' : '').'</figure>',
            $html,
        );

        return new RenderedPostContent($html, $headings);
    }
}
