<?php

return [

    'payment_methods' => [
        'cod' => 'COD',
        'vnpay' => 'VNPAY',
        'momo' => 'MoMo',
        'bank_transfer' => 'Chuyển khoản',
    ],

    'payment_statuses' => [
        'pending' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'refunded' => 'Đã hoàn tiền',
    ],

    'status_logs' => [
        'received' => 'Đơn hoa đã được tiếp nhận',
        'received_description' => 'Lá đã nhận đơn của bạn và sẽ bắt đầu chọn hoa sớm thôi.',
    ],

    'errors' => [
        'empty_cart' => 'Giỏ hoa của bạn đang trống, bạn chọn hoa giúp tiệm trước nhé.',
        'time_slot_required' => 'Bạn chọn giúp tiệm một khung giờ giao hoa nhé.',
        'time_slot_passed' => 'Khung giờ này của hôm nay đã bắt đầu, bạn chọn khung giờ khác giúp tiệm nhé.',
    ],

    'confirmation' => [
        'title' => 'Tiệm đã nhận lời thương của bạn',
        'breadcrumb' => 'Xác nhận đơn hàng',
        'intro' => 'Cảm ơn :name đã để Lá & Hoa thay bạn gửi một món quà thật dịu dàng.',
        'success' => 'Đặt hoa thành công · :code',
        'success_note' => 'Tiệm sẽ chăm chút từng cành hoa.',
        'badge' => [
            'paid' => 'Đã thanh toán qua :method',
            'cod' => 'Thanh toán khi nhận hoa',
            'pending' => 'Chưa thanh toán',
            'failed' => 'Thanh toán chưa thành công',
        ],
        'payment' => [
            'pending' => 'Đơn hoa đang chờ bạn thanh toán qua :method. Tiệm giữ đơn cho bạn, bạn quét mã QR để hoàn tất nhé.',
            'failed' => 'Giao dịch qua :method chưa thành công. Đơn hoa vẫn được giữ, bạn thử thanh toán lại giúp tiệm nhé.',
            'gateway_error' => 'Cổng :method đang bận một chút. Đơn hoa vẫn được giữ, bạn thử thanh toán lại sau ít phút nhé.',
            'retry' => 'Thanh toán qua :method',
        ],
        'schedule' => [
            'title' => 'Hẹn giao một bó hoa xinh',
            'month' => 'Tháng :month',
            'slot' => ':slot · Giao tận tay người nhận',
        ],
        'receiver' => 'Người nhận',
        'sender' => 'Người đặt',
        'notes' => 'Ghi chú: :notes',
        'card' => [
            'title' => 'Tấm thiệp dành riêng cho :name',
            'meta' => 'Người gửi: :sender · Người nhận: :receiver',
        ],
        'next' => [
            'title' => 'Tiếp theo, tiệm sẽ chuẩn bị hoa cho bạn',
            'body' => 'Bạn có thể theo dõi hành trình của bó hoa bằng mã :code và số điện thoại người đặt. Tiệm sẽ nhắn khi hoa bắt đầu được giao.',
        ],
        'track' => 'Theo dõi đơn hoa',
        'home' => 'Về trang chủ',
        'help' => [
            'title' => 'Muốn nhắn thêm với tiệm?',
            'body' => 'Gọi :hotline và đọc mã :code. Nếu cần đổi lịch hoặc lời nhắn, hãy báo tiệm trước khi hoa được cắm nhé.',
        ],
    ],

    'mail' => [
        'subject' => 'Lá & Hoa · Đặt hoa thành công :code',
        'greeting' => 'Chào :name,',
        'intro' => 'Cảm ơn bạn đã để Lá & Hoa thay bạn gửi một món quà thật dịu dàng. Tiệm đã nhận đơn :code và sẽ chăm chút từng cành hoa.',
        'payment' => 'Thanh toán: :status',
        'items' => [
            'item' => 'Món',
            'quantity' => 'SL',
            'total' => 'Thành tiền',
        ],
        'subtotal' => 'Tạm tính',
        'discount' => 'Ưu đãi :code',
        'shipping' => 'Phí giao hoa',
        'free' => 'Miễn phí',
        'total' => 'Tổng cộng',
        'schedule' => 'Lịch giao',
        'receiver' => 'Người nhận',
        'address' => 'Địa chỉ',
        'card' => 'Lời nhắn trên thiệp',
        'visit' => 'Ghé Lá & Hoa',
        'signature' => 'Thương mến,',
    ],

];
