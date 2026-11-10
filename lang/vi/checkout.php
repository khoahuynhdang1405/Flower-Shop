<?php

return [

    'page' => [
        'title' => 'Gửi hoa, gửi yêu thương',
        'breadcrumb' => 'Thanh toán',
        'intro' => 'Bạn kể tiệm nghe một chút. Phần dịu dàng còn lại, để tiệm lo.',
        'back' => 'Quay lại giỏ hàng',
    ],

    'sender' => [
        'title' => 'Thông tin người đặt',
        'name' => 'Họ và tên',
        'name_placeholder' => 'Họ và tên của bạn',
        'phone' => 'Số điện thoại',
        'email' => 'Email nhận xác nhận',
        'email_placeholder' => 'ban@email.com',
        'recipient_type' => 'Hoa này dành cho ai',
        'gift' => 'Gửi tặng người thương',
        'self' => 'Tôi là người nhận',
    ],

    'receiver' => [
        'title' => 'Hoa sẽ đến với ai?',
        'description' => 'Tiệm chỉ liên hệ người nhận khi cần hỗ trợ giao hoa.',
        'name' => 'Họ và tên người nhận',
        'phone' => 'Số điện thoại người nhận',
        'address' => 'Địa chỉ nhận hoa',
        'address_placeholder' => 'Số nhà, tên đường, phường',
        'city' => 'Tỉnh / Thành phố',
        'city_hint' => 'Tiệm hiện chỉ giao hoa trong TP. Hồ Chí Minh.',
        'district' => 'Khu vực giao hoa',
        'district_placeholder' => 'Chọn quận / huyện',
        'notes' => 'Ghi chú giao hàng (không bắt buộc)',
        'notes_placeholder' => 'Gọi người nhận trước khi đến 15 phút.',
    ],

    'schedule' => [
        'title' => 'Hẹn một ngày thật đẹp',
        'date' => 'Ngày giao hoa',
        'date_placeholder' => 'Chọn ngày giao hoa',
        'date_required' => 'Bạn chọn giúp tiệm một ngày giao hoa nhé.',
        'time_slot' => 'Khung giờ giao hoa',
        'past_slots_note' => 'Những khung giờ đã qua của hôm nay, tiệm xin phép khép lại.',
        'note' => 'Tiệm giao trong khung giờ bạn chọn; giờ đến có thể thay đổi nhẹ do giao thông.',
    ],

    'card' => [
        'title' => 'Những điều muốn gửi gắm',
        'description' => 'Thiệp viết tay miễn phí, tiệm sẽ chép thật cẩn thận lời của bạn.',
        'sender' => 'Người gửi',
        'receiver' => 'Người nhận',
        'message' => 'Lời nhắn',
        'message_placeholder' => 'Chúc bạn một ngày thật dịu dàng...',
        'note' => 'Lời nhắn sẽ được giữ riêng tư và đặt bên trong bó hoa.',
    ],

    'payment' => [
        'title' => 'Phương thức thanh toán',
        'methods' => [
            'cod' => ['label' => 'COD', 'hint' => 'Khi nhận hoa', 'note' => 'Bạn thanh toán bằng tiền mặt khi hoa đến tay người nhận.'],
            'vnpay' => ['label' => 'VNPAY', 'hint' => 'Thẻ / QR ngân hàng', 'note' => 'Sau khi đặt hoa, bạn sẽ thanh toán an toàn qua VNPAY.'],
            'momo' => ['label' => 'MoMo', 'hint' => 'Ví điện tử', 'note' => 'Sau khi đặt hoa, bạn sẽ thanh toán an toàn qua ví MoMo.'],
        ],
    ],

    'summary' => [
        'title' => 'Đơn hoa của bạn',
        'flower_meta' => 'Cỡ :size · Số lượng :quantity',
        'flower_meta_no_size' => 'Số lượng :quantity',
        'addon' => ':name · :quantity',
        'free' => 'Miễn phí',
        'subtotal' => 'Tạm tính',
        'discount' => 'Ưu đãi :code',
        'shipping' => 'Giao hoa · :city',
        'shipping_district' => 'Giao hoa · :district, :city',
        'total' => 'Tổng thanh toán',
        'total_note' => 'Đã bao gồm VAT và phí giao hoa.',
        'submit' => 'Đặt hoa · :amount',
        'terms' => 'Bằng việc đặt hoa, bạn đồng ý với điều khoản mua hàng và chính sách của Lá & Hoa.',
        'pending' => 'Tiệm đang hoàn thiện bước gửi đơn. Thông tin của bạn đã hợp lệ, bạn vui lòng gọi tiệm để đặt hoa trong lúc chờ nhé.',
    ],

    'appointment' => [
        'title' => 'Lịch hẹn với người thương',
        'empty' => 'Chọn ngày và khung giờ để tiệm giữ lịch cho bạn.',
    ],

    'help' => [
        'title' => 'Cần tiệm giúp một chút?',
        'body' => 'Gọi :hotline (:hours). Tiệm luôn sẵn lòng cùng bạn chuẩn bị món quà.',
    ],

];
