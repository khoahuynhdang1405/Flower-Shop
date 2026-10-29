<?php

return [

    'title' => 'Tài khoản của tôi',
    'greeting' => 'Chào :name, thật vui khi gặp lại bạn.',
    'subtitle' => 'Một góc nhỏ để giữ thông tin và những lần gửi thương cùng Lá.',
    'member_since' => 'Gắn bó từ tháng :date',
    'help' => 'Cần Lá giúp một chút? Gọi :hotline, tiệm luôn sẵn lòng lắng nghe.',

    'groups' => [
        'new' => 'Khách mới',
        'loyal' => 'Khách thân thiết',
        'best_friend' => 'Bạn thân của Lá',
    ],

    'nav' => [
        'label' => 'Mục tài khoản',
        'profile' => 'Thông tin tài khoản',
        'orders' => 'Đơn hàng của tôi',
        'password' => 'Thay đổi mật khẩu',
        'logout' => 'Đăng xuất',
    ],

    'profile' => [
        'title' => 'Thông tin tài khoản',
        'verified' => 'Đã xác thực',
        'description' => 'Lá dùng thông tin này để liên hệ và gửi hoa đến bạn đúng hẹn.',
        'name' => 'Họ và tên',
        'phone' => 'Số điện thoại',
        'email' => 'Địa chỉ email',
        'email_hint' => 'Email dùng để đăng nhập. Nếu cần đổi, bạn nhắn Lá giúp nhé.',
        'birthday' => 'Ngày sinh',
        'birthday_invalid' => 'Ngày sinh chưa hợp lệ, bạn kiểm tra lại giúp Lá nhé.',
        'saved_at' => 'Thông tin đã được lưu lúc :time',
        'saved' => 'Lá đã lưu thông tin mới của bạn.',
        'submit' => 'Lưu thông tin',
        'name_required' => 'Bạn cho Lá biết họ và tên nhé.',
        'phone_required' => 'Bạn để lại số điện thoại để tiệm liên hệ khi giao hoa nhé.',
    ],

    'address' => [
        'title' => 'Địa chỉ giao hoa mặc định',
        'description' => 'Lá sẽ điền sẵn địa chỉ này mỗi khi bạn đặt hoa. Bạn vẫn có thể đổi ở bước thanh toán.',
        'street' => 'Số nhà, tên đường, phường',
        'street_placeholder' => 'Ví dụ: 18 Võ Văn Tần, Phường 6',
        'district' => 'Quận/Huyện',
        'district_placeholder' => 'Chọn Quận/Huyện',
        'empty' => 'Bạn chưa lưu địa chỉ giao hoa mặc định.',
        'saved' => 'Lá đã ghi nhớ địa chỉ giao hoa của bạn.',
        'submit' => 'Lưu địa chỉ',
        'street_required' => 'Bạn nhập số nhà, tên đường giúp Lá nhé.',
        'district_required' => 'Bạn chọn Quận/Huyện nhận hoa giúp Lá nhé.',
    ],

    'orders' => [
        'title' => 'Những lần gửi thương',
        'summary' => ':total đơn hàng · :completed đơn hoàn thành · :preparing đơn đang chuẩn bị',
        'filter_label' => 'Lọc đơn hàng',
        'filter_all' => 'Tất cả (:count)',
        'filter_processing' => 'Đang xử lý (:count)',
        'filter_completed' => 'Hoàn thành (:count)',
        'meta' => ':code · :date · :count sản phẩm',
        'view' => 'Xem chi tiết',
        'reorder' => 'Đặt lại hoa',
        'deliver_today' => 'Giao hôm nay, :time',
        'empty' => 'Chưa có đơn nào ở mục này.',
        'latest_note' => 'Đơn :code đã thanh toán · Lá đang gói hoa và viết thiệp cho bạn.',
        'status' => [
            'preparing' => 'Đang chuẩn bị',
            'completed' => 'Hoàn thành',
        ],
    ],

    'password' => [
        'title' => 'Thay đổi mật khẩu',
        'description' => 'Giữ góc nhỏ của bạn an toàn. Không chia sẻ mật khẩu với người khác.',
        'google_description' => 'Bạn đang đăng nhập bằng Google. Đặt thêm mật khẩu để có thể đăng nhập bằng email.',
        'current' => 'Mật khẩu hiện tại',
        'new' => 'Mật khẩu mới',
        'confirm' => 'Xác nhận mật khẩu mới',
        'hint' => 'Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và số.',
        'matched' => 'Mật khẩu xác nhận đã khớp.',
        'not_matched' => 'Mật khẩu xác nhận chưa khớp.',
        'ready' => 'Mật khẩu mới đạt yêu cầu · Sẵn sàng cập nhật',
        'not_ready' => 'Điền đủ các ô để cập nhật mật khẩu.',
        'submit' => 'Cập nhật mật khẩu',
        'saved' => 'Mật khẩu đã được đổi. Các thiết bị khác sẽ cần đăng nhập lại.',
        'current_required' => 'Bạn nhập mật khẩu hiện tại giúp Lá nhé.',
        'current_wrong' => 'Mật khẩu hiện tại chưa đúng.',
        'new_required' => 'Bạn nhập mật khẩu mới nhé.',
        'same_as_current' => 'Mật khẩu mới cần khác mật khẩu đang dùng.',
    ],

];
