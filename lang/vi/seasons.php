<?php

return [

    'title' => 'Hoa theo mùa',

    // Hero đổi theo mùa hiện tại (App\Enums\Season); :month là tháng hiện tại.
    'hero' => [
        'eyebrow' => 'Hoa theo mùa · Tháng :month',
        'image_alt' => 'Bình hoa thược dược, hồng đào và lá thu trên bàn đá',
        'spring' => [
            'title_line_1' => 'Xuân vừa chạm ngõ,',
            'title_line_2' => 'lòng mình cũng nở hoa.',
            'body' => 'Một chút trắng trong, một chút hồng non, một chút tươi mới. Những đóa hoa đẹp nhất mùa được Lá chọn để gửi đến người bạn thương.',
            'collection' => 'Bộ sưu tập Trong trẻo mùa xuân · Từ :price',
            'cta' => 'Chọn một bó hoa mùa xuân',
        ],
        'summer' => [
            'title_line_1' => 'Hạ rực nắng lên,',
            'title_line_2' => 'thương cũng rạng ngời.',
            'body' => 'Một chút vàng nắng, một chút cam tươi, một chút rực rỡ. Những đóa hoa đẹp nhất mùa được Lá chọn để gửi đến người bạn thương.',
            'collection' => 'Bộ sưu tập Rực rỡ mùa hạ · Từ :price',
            'cta' => 'Chọn một bó hoa mùa hạ',
        ],
        'autumn' => [
            'title_line_1' => 'Thu ghé qua,',
            'title_line_2' => 'để thương ở lại.',
            'body' => 'Một chút hồng phấn, một chút cam đào, một chút dịu êm. Những đóa hoa đẹp nhất mùa được Lá chọn để gửi đến người bạn thương.',
            'collection' => 'Bộ sưu tập Dịu dàng mùa thu · Từ :price',
            'cta' => 'Chọn một bó hoa mùa thu',
        ],
        'winter' => [
            'title_line_1' => 'Đông về chậm rãi,',
            'title_line_2' => 'giữ ấm lời thương.',
            'body' => 'Một chút kem sữa, một chút xanh lá, một chút ấm áp. Những đóa hoa đẹp nhất mùa được Lá chọn để gửi đến người bạn thương.',
            'collection' => 'Bộ sưu tập An lành mùa đông · Từ :price',
            'cta' => 'Chọn một bó hoa mùa đông',
        ],
    ],

    'seasons' => [
        'eyebrow' => 'Mùa nào, hoa nấy',
        'title' => 'Mỗi mùa, một sắc thương.',
        'aside' => 'Hoa đẹp nhất khi nở đúng mùa.',
        'current' => 'Đang vào mùa',
        'from' => 'Từ :price',
        'items' => [
            'spring' => ['name' => 'Xuân trong trẻo', 'months' => 'Tháng 2 – 4', 'body' => 'Tulip, mao lương & những khởi đầu'],
            'summer' => ['name' => 'Hạ rực rỡ', 'months' => 'Tháng 5 – 7', 'body' => 'Hướng dương, hồng vàng & nắng'],
            'autumn' => ['name' => 'Thu dịu dàng', 'months' => 'Tháng 8 – 10', 'body' => 'Cúc, hồng đào & một chiều êm'],
            'winter' => ['name' => 'Đông an lành', 'months' => 'Tháng 11 – 1', 'body' => 'Hồng kem, lá xanh & lời ấm áp'],
        ],
    ],

    'products' => [
        'eyebrow' => 'Được Lá chọn trong mùa',
        'title' => 'Những đóa hoa của tháng :month',
        'descriptions' => [
            'spring' => 'Sắc tươi trong trẻo, gói ghém chút xuân để đặt vào tay người bạn thương.',
            'summer' => 'Sắc nắng rực rỡ, gói ghém chút hạ để đặt vào tay người bạn thương.',
            'autumn' => 'Sắc ấm nhẹ nhàng, gói ghém chút thu để đặt vào tay người bạn thương.',
            'winter' => 'Sắc kem dịu và lá xanh, gói ghém chút đông ấm để đặt vào tay người bạn thương.',
        ],
        'count' => ':count thiết kế dành cho mùa này',
        'badge' => 'Được thương nhất',
        'note' => 'Hoa theo mùa có thể thay đổi đôi chút theo vườn và thời tiết. Lá luôn giữ sắc màu, tinh thần thiết kế và trao đổi với bạn trước khi thay hoa.',
    ],

    'nature' => [
        'eyebrow' => 'Thuận theo tự nhiên',
        'title_line_1' => 'Đúng mùa hoa,',
        'title_line_2' => 'đúng lúc thương.',
        'items' => [
            'fresh' => ['title' => 'Tươi từ vườn', 'body' => 'Hoa được chọn vào độ nở đẹp nhất, khỏe hơn và ở lại lâu hơn.'],
            'colors' => ['title' => 'Sắc màu tự nhiên', 'body' => 'Mỗi mùa một bảng màu riêng, không cần cầu kỳ vẫn thật nên thơ.'],
            'gentle' => ['title' => 'Dịu dàng với đất', 'body' => 'Ưu tiên hoa địa phương, giảm hành trình xa từ vườn đến tay bạn.'],
        ],
    ],

    'cta' => [
        'title' => 'Muốn gói một chút mùa cho riêng mình?',
        'body_line_1' => 'Bạn chọn sắc hoa, Lá chọn những cành đẹp nhất hôm nay.',
        'body_line_2' => 'Cùng nhau làm nên một bó hoa không dành cho ai khác.',
        'button' => 'Nhắn Lá chọn hoa theo mùa',
    ],

];
