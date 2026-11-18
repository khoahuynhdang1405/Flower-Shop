<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\RenderedPostContent;
use Illuminate\Support\Str;

/**
 * Chuyển nội dung Markdown của bài sang HTML an toàn để hiển thị (trang Góc nhỏ và "Xem trước" ở admin):
 * - bỏ mọi HTML viết tay và link không an toàn (javascript:, data:...) để chặn XSS;
 * - gắn id cho mỗi tiêu đề "##" và trả về danh sách đó làm mục lục "Trong câu chuyện này";
 * - ảnh đứng riêng một đoạn thành <figure>, chữ alt làm chú thích ảnh;
 * - khối ghi nhớ (hộp hồng "Nhớ ba điều nhỏ thôi" trong thiết kế), viết là:
 *       :::ghi-nho Nhớ ba điều nhỏ thôi
 *       - Cắt gốc xéo
 *       - Thay nước mỗi ngày
 *       :::
 */
final class RenderPostContentAction
{
    private const NOTE_PATTERN = '/^:::ghi-nho[ \t]*(.*?)[ \t]*\R(.*?)^:::[ \t]*$/ms';

    public function execute(string $markdown): RenderedPostContent
    {
        $notes = [];
        $markdown = (string) preg_replace_callback(self::NOTE_PATTERN, function (array $match) use (&$notes): string {
            $notes[] = ['title' => trim($match[1]), 'items' => $this->noteItems($match[2])];

            return "\n\nLAVAHOA-NOTE-".(count($notes) - 1)."\n\n";
        }, $markdown);

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

        $html = (string) preg_replace_callback(
            '/<p>LAVAHOA-NOTE-(\d+)<\/p>/',
            fn (array $match): string => isset($notes[(int) $match[1]]) ? $this->noteHtml($notes[(int) $match[1]]) : '',
            $html,
        );

        return new RenderedPostContent($html, $headings);
    }

    /**
     * @return list<string>
     */
    private function noteItems(string $body): array
    {
        return collect(preg_split('/\R/', $body))
            ->map(fn (string $line): string => trim((string) preg_replace('/^\s*[-*]\s*/', '', $line)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Chữ trong khối ghi nhớ được escape, không đi qua Markdown.
     *
     * @param  array{title: string, items: list<string>}  $note
     */
    private function noteHtml(array $note): string
    {
        $icon = svg('lucide-leaf', 'post-note-icon', ['aria-hidden' => 'true'])->toHtml();
        $items = implode('', array_map(fn (string $item): string => '<li>'.$icon.'<span>'.e($item).'</span></li>', $note['items']));

        return '<aside class="post-note">'
            .($note['title'] !== '' ? '<p class="post-note-title">'.e($note['title']).'</p>' : '')
            .($items !== '' ? '<ul>'.$items.'</ul>' : '')
            .'</aside>';
    }
}
