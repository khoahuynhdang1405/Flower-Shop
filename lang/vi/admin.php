<?php

return [

    'brand' => 'Lá & Hoa',
    'workspace' => 'Không gian quản trị',
    'quote' => ['line_1' => 'Chăm tiệm mỗi ngày,', 'line_2' => 'để thương luôn nở.'],
    'footer' => '© :year Lá & Hoa · Không gian quản trị nội bộ',
    'skip_to_content' => 'Bỏ qua, đến nội dung chính',

    'nav' => [
        'main' => 'Điều hướng quản trị',
        'secondary' => 'Liên kết khác',
        'open' => 'Mở menu',
        'close' => 'Đóng menu',
        'orders_badge' => ':count đơn mới',
    ],

    'menu' => [
        'dashboard' => 'Tổng quan',
        'orders' => 'Đơn hàng',
        'products' => 'Sản phẩm',
        'collections' => 'Bộ sưu tập',
        'customers' => 'Khách hàng',
        'posts' => 'Chuyện Hoa',
        'promotions' => 'Ưu đãi',
        'settings' => 'Cài đặt tiệm',
        'help' => 'Trợ giúp',
        'storefront' => 'Quay về cửa hàng',
    ],

    'header' => [
        'breadcrumb' => 'Đường dẫn',
        'root' => 'Quản trị',
        'notifications' => 'Thông báo',
        'account' => 'Tài khoản của :name',
    ],

    'roles' => [
        'admin' => 'Quản trị viên',
        'staff' => 'Nhân viên',
    ],

    'formats' => [
        'date_long' => 'DD [tháng] MM, YYYY',
        'date_short' => 'DD/MM',
    ],

    'dashboard' => [
        'title' => 'Một ngày đẹp ở Lá & Hoa',
        'description' => ':weekday, :date · Cùng chăm chút những điều thương.',
        'today' => 'Hôm nay, :date',
    ],

    'list' => [
        'search' => 'Tìm kiếm',
        'edit' => 'Chỉnh sửa',
        'edit_item' => 'Chỉnh sửa :name',
        'view' => 'Xem chi tiết',
        'view_item' => 'Xem chi tiết :name',
        'empty' => 'Không có mục nào khớp với bộ lọc.',
    ],

    'collections' => [
        'title' => 'Những mùa hoa của tiệm',
        'description' => 'Sắp xếp hoa theo mùa và những dịp gửi thương của khách hàng.',
        'create' => 'Thêm bộ sưu tập',
        'stats' => [
            'total' => 'Bộ sưu tập',
            'total_note' => ':published đang hiển thị · :drafts bản nháp',
            'season' => 'Theo mùa',
            'season_note' => 'Mùa thu đang được giới thiệu',
            'occasion' => 'Theo dịp',
            'occasion_note' => 'Sinh nhật, yêu thương, chúc mừng, hằng ngày',
            'featured' => 'Sản phẩm nổi bật',
            'featured_note' => 'Trong :name',
        ],
        'search_placeholder' => 'Tìm tên bộ sưu tập...',
        'filter_type' => 'Lọc theo loại',
        'filter_status' => 'Lọc theo trạng thái',
        'all_types' => 'Tất cả loại',
        'all_statuses' => 'Tất cả trạng thái',
        'types' => [
            'season' => 'Theo mùa',
            'occasion' => 'Theo dịp',
        ],
        'statuses' => [
            'published' => 'Đang hiển thị',
            'draft' => 'Bản nháp',
        ],
        'products' => ':count sản phẩm',
        'all_year' => 'Quanh năm',
        'planned' => 'Dự kiến :date',
        'cover_alt' => 'Ảnh bìa bộ sưu tập :name',
        'note' => 'Một sản phẩm có thể thuộc nhiều bộ sưu tập. Bộ sưu tập bản nháp chưa xuất hiện trên cửa hàng; hãy kiểm tra ảnh bìa và sản phẩm trước khi đăng.',
    ],

    'promotions' => [
        'title' => 'Một chút thương, thêm một ưu đãi',
        'description' => 'Quản lý mã giảm giá và chương trình dành cho những người bạn của tiệm.',
        'create' => 'Thêm ưu đãi',
        'stats' => [
            'active' => 'Đang áp dụng',
            'active_note' => ':count chương trình đang nhận lượt dùng',
            'usage' => 'Lượt sử dụng',
            'usage_note' => 'Trong các chương trình đang áp dụng',
            'upcoming' => 'Sắp diễn ra',
            'upcoming_note' => ':name · :date',
            'codes' => 'Mã ưu đãi',
            'codes_note' => ':ended đã kết thúc · :paused tạm dừng',
        ],
        'table_title' => 'Mã giảm giá & chương trình ưu đãi',
        'table_description' => 'Khách chỉ sử dụng 1 ưu đãi mỗi đơn · Giá trị tiền tệ tính bằng VNĐ',
        'search_placeholder' => 'Tìm mã hoặc tên chương trình...',
        'filter_type' => 'Lọc theo loại giảm',
        'filter_status' => 'Lọc theo trạng thái',
        'all_types' => 'Tất cả loại giảm',
        'all_statuses' => 'Tất cả trạng thái',
        'columns' => [
            'code' => 'Mã / Chương trình',
            'value' => 'Loại / Giá trị giảm',
            'condition' => 'Điều kiện áp dụng',
            'period' => 'Thời hạn',
            'usage' => 'Lượt dùng',
            'status' => 'Trạng thái',
            'actions' => 'Thao tác',
        ],
        'types' => [
            'percent' => 'Phần trăm',
            'fixed' => 'Số tiền',
            'shipping' => 'Phí giao hàng',
        ],
        'max_discount' => 'Tối đa :amount',
        'direct_discount' => 'Giảm trực tiếp',
        'free_inner_city' => 'Miễn phí giao nội thành',
        'statuses' => [
            'active' => 'Đang áp dụng',
            'upcoming' => 'Sắp diễn ra',
            'ended' => 'Đã kết thúc',
            'paused' => 'Tạm dừng',
        ],
        'showing_all' => 'Hiển thị tất cả :total chương trình ưu đãi',
        'showing_some' => 'Hiển thị :shown / :total chương trình ưu đãi',
        'upcoming_chip' => 'Sắp diễn ra · :date',
        'upcoming_body' => 'Mã :code giảm :value cho đơn từ :min, tối đa :max. Giới hạn :limit lượt, kết thúc ngày :end.',
        'edit_program' => 'Chỉnh sửa chương trình',
        'checklist_title' => 'Kiểm tra trước khi gửi ưu đãi',
        'checklist' => [
            'Mỗi khách dùng mỗi mã tối đa một lần.',
            'Không áp dụng đồng thời nhiều mã trên một đơn.',
            'Giá trị tối thiểu tính trước phí giao hàng.',
            'Mã tự hết hạn lúc 23:59 ngày kết thúc.',
        ],
    ],

];
