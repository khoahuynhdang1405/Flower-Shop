<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bài Góc nhỏ mẫu theo docs/designs/storefront/10-bai-viet-chi-tiet.png và docs/designs/admin/06-chuyen-hoa.png.
 * Cần chạy sau AdminSeeder (tác giả là admin đầu tiên).
 */
class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = Admin::query()->orderBy('id')->first() ?? Admin::factory()->create();

        foreach ($this->posts() as $index => $data) {
            // forceFill: created_at / updated_at không nằm trong Fillable, cần đặt tay để bài cách nhau từng ngày.
            Post::query()->firstOrNew(['slug' => Str::slug($data['title'])])->forceFill([
                ...$data,
                'admin_id' => $author->id,
                'created_at' => now()->subDays($index + 1),
                'updated_at' => now()->subDays($index + 1),
            ])->save();
        }
    }

    /**
     * @return list<array{title: string, summary: string, content: string, is_published: bool}>
     */
    private function posts(): array
    {
        return [
            [
                'title' => 'Để hoa tươi lâu hơn, thương lâu hơn',
                'summary' => 'Một bó hoa đẹp không chỉ trong ngày đầu. Với vài thói quen nhỏ, bạn có thể giữ những cánh hoa ở lại thêm nhiều ngày dịu dàng.',
                'is_published' => true,
                'content' => <<<'MD'
                Buổi sáng nhận hoa, bạn đặt bó hoa lên bàn và thấy cả căn phòng như sáng hơn một chút. Để niềm vui ấy không vội qua đi, Lá muốn kể bạn nghe vài điều giản dị mà chúng mình vẫn làm mỗi ngày ở tiệm.

                ## 01. Cho hoa một khởi đầu thật sạch

                Trước khi cắm, hãy rửa bình bằng nước và một chút nước rửa chén, rồi tráng thật kỹ. Một chiếc bình nhìn trong vắt có thể vẫn còn cặn ở đáy — nơi vi khuẩn dễ phát triển và làm cành hoa khó hút nước.

                Gỡ phần giấy gói, giữ lại dây buộc nếu bạn muốn lưu dáng bó. Tỉa những chiếc lá nằm dưới mực nước, sau đó dùng kéo sắc và sạch cắt xéo khoảng 1–2 cm ở gốc.

                > Chăm hoa không phải để giữ mãi. Là để mỗi ngày hoa ở đây đều thật đẹp.

                ## 02. Nước mới cho một ngày mới

                Đổ nước sạch, mát vào khoảng một phần ba đến một nửa bình, đủ ngập gốc nhưng không chạm tới lá. Nếu bó hoa có gói dưỡng hoa đi kèm, hãy pha theo hướng dẫn trên gói.

                Mỗi ngày, thay nước và rửa nhẹ phần thân ngập nước. Cứ hai ngày, cắt lại một đoạn gốc ngắn bằng kéo sạch.

                ## 03. Tìm một góc nhỏ thật dễ chịu

                Hoa thích nơi mát, thoáng và có ánh sáng gián tiếp. Tránh đặt bình ở cửa sổ có nắng gắt, cạnh bếp nóng hoặc ngay dưới luồng gió máy lạnh.

                Và khi vài cánh hoa bắt đầu rơi, bạn có thể giữ một bông để ép trong trang sách. Hoa không ở lại mãi, nhưng cảm giác được nhớ đến thì có.

                :::ghi-nho Nhớ ba điều nhỏ thôi
                - Cắt gốc xéo
                - Thay nước mỗi ngày
                - Đặt ở nơi mát
                :::
                MD,
            ],
            [
                'title' => 'Mỗi màu hoa hồng, một lời muốn nói',
                'summary' => 'Hồng đỏ cho yêu thương nồng nàn, hồng phấn cho lời cảm ơn dịu dàng. Cùng Lá đọc ngôn ngữ của từng sắc hồng.',
                'is_published' => true,
                'content' => <<<'MD'
                Hoa hồng có lẽ là loài hoa được gửi đi nhiều nhất ở tiệm. Nhưng mỗi sắc hồng lại mang một lời riêng.

                ## Hồng đỏ

                Lời yêu thương trọn vẹn, dành cho người bạn muốn đi cùng thật lâu.

                ## Hồng phấn

                Một lời cảm ơn, một lời ngưỡng mộ nhẹ nhàng — hợp cho mẹ, cho cô giáo, cho người bạn thân.

                ## Hồng vàng

                Niềm vui và tình bạn rạng rỡ như nắng sớm.
                MD,
            ],
            [
                'title' => 'Một góc nhà, một bình hoa, một niềm vui',
                'summary' => 'Không cần dịp đặc biệt, chỉ một bình hoa nhỏ cũng đủ làm góc nhà thêm ấm.',
                'is_published' => true,
                'content' => <<<'MD'
                Có những ngày rất bình thường, một bình hoa nhỏ trên bàn làm việc cũng đủ khiến mình mỉm cười.

                ## Chọn bình trước, chọn hoa sau

                Bình gốm thấp hợp với hoa cắm thả tự nhiên; bình thủy tinh cao hợp với cành dài và lá xanh.

                ## Ít mà xinh

                Ba đến năm cành cùng tông màu thường đẹp hơn một bó đầy đủ mọi loại hoa.
                MD,
            ],
            [
                'title' => 'Một ngày bình dị tại Lá & Hoa',
                'summary' => 'Từ chuyến hàng hoa lúc bảy giờ sáng đến bó hoa cuối cùng rời tiệm, một ngày ở Lá trôi qua như thế nào?',
                'is_published' => true,
                'content' => <<<'MD'
                Bảy giờ sáng, những thùng hoa từ vườn về tới tiệm, còn đọng hơi sương.

                ## Buổi sáng

                Cả tiệm cùng tỉa lá, cắt gốc và ngâm hoa cho chúng uống no nước trước khi lên bó.

                ## Buổi chiều

                Những bó hoa được gói tỉ mỉ, kèm tấm thiệp viết tay, rồi theo các anh giao hàng đi khắp thành phố.
                MD,
            ],
            [
                'title' => 'Chọn hoa gửi mẹ ngày 20/10',
                'summary' => 'Vài gợi ý nhỏ để bó hoa gửi mẹ thật đúng ý.',
                'is_published' => false,
                'content' => "Ngày 20/10 sắp đến, Lá gợi ý vài bó hoa dịu dàng để gửi mẹ.\n\n## Hồng phấn và cẩm chướng\n\nMột lời cảm ơn nhẹ nhàng.",
            ],
            [
                'title' => 'Những sắc hoa của mùa thu',
                'summary' => 'Cam đào, hồng phấn và vàng nắng — mùa thu ở Lá có những sắc gì?',
                'is_published' => false,
                'content' => "Mùa thu là mùa của những sắc ấm.\n\n## Cúc mẫu đơn\n\nTròn đầy và dịu dàng.",
            ],
        ];
    }
}
