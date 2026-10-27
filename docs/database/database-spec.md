# Đặc tả cơ sở dữ liệu — Lá & Hoa

**Dự án:** Hệ thống thương mại điện tử tiệm hoa tươi Lá & Hoa
**Công nghệ:** Laravel 12, MySQL 8.0
**Nguồn:** Google Docs "DB Hệ Thống Thương Mại Điện Tử Tiệm Hoa Tươi Lá & Hoa" (Thẻ 1 + Thẻ 2)
**Schema DBML:** `docs/database/schema.dbml`

Tổng cộng **17 bảng**, chia thành 4 nhóm.

---

## Phần A — Ý nghĩa nghiệp vụ (Thẻ 1)

### 1. Nhóm tài khoản & vận hành nội bộ (Authentication & RBAC)

**1.1. `users` — Khách hàng**
Quản lý tài khoản khách hàng thành viên trên Storefront. Lưu thông tin khi đăng ký trên web hoặc đăng nhập nhanh bằng Google OAuth. Khách vãng lai (Guest Checkout) không bắt buộc phải có bản ghi ở bảng này.
- `name`, `email`, `phone`: thông tin liên hệ cơ bản.
- `password`: mật khẩu mã hóa Bcrypt (cho phép NULL nếu đăng ký/đăng nhập qua Google).
- `google_id`: định danh OAuth của Google.
- `customer_group`: nhóm khách (`App\Enums\CustomerGroup`), admin gán; khách không tự đổi được. `birthday`: ngày sinh khách tự nhập.
- `default_shipping_address`, `default_shipping_district`: địa chỉ giao hoa mặc định khách lưu ở trang Tài khoản, dùng để điền sẵn khi thanh toán. Đơn hàng vẫn lưu snapshot riêng trong `orders.shipping_address`.

**1.2. `admins` — Nhân sự nội bộ tiệm hoa**
Toàn bộ tài khoản đăng nhập trang quản trị (`/admin`), gồm Chủ tiệm (Admin) và Nhân viên/Thợ cắm (Staff/Florist). **Tách biệt hoàn toàn khỏi `users`** để đảm bảo an toàn bảo mật.
- `role_id`: FK tới `roles`.
- `name`, `email`, `password`: định danh và mật khẩu đăng nhập nội bộ.
- `is_active`: dễ dàng vô hiệu hóa tài khoản khi nhân viên nghỉ việc.

**1.3. `roles` — Vai trò nội bộ**
Định nghĩa các nhóm quyền trong tiệm.
- `name`: Chủ tiệm, Nhân viên / Thợ cắm hoa.
- `slug`: `admin`, `staff`.

**1.4. `permissions` & `role_has_permissions` — Quyền hạn chi tiết**
- `admin`: xem doanh thu, cấu hình hệ thống, quản lý sản phẩm.
- `staff`: xem danh sách đơn theo khung giờ (time-slot), in thiệp, in bill giao hàng, bấm cập nhật trạng thái làm hoa.

### 2. Nhóm sản phẩm & quà tặng kèm (Catalog & Addons)

**2.1. `categories` — Danh mục & dịp tặng hoa**
- `name`, `slug`: tên và đường dẫn thân thiện.
- `type`: `season` (hoa mùa thu, mùa xuân), `occasion` (sinh nhật, chúc mừng), `flower_type` (hoa hồng, baby...).

**2.2. `products` — Bó hoa / bình hoa chính**
Các mẫu hoa cắm sẵn được bày bán (vd. bó "Lời thì thầm").
- `category_id`: FK danh mục.
- `base_price`: giá niêm yết chuẩn.
- `dimension_info`: kích thước bó hoa (chiều cao, đường kính).
- `freshness_guarantee`: cam kết độ tươi (vd. "Tươi xinh 3–5 ngày").
- `stock`: **số bó tối đa thợ có thể nhận cắm trong ngày**.

**2.3. `product_sizes` — Kích cỡ bó hoa**
Khách chọn Nhỏ / Vừa / Lớn kèm giá tương ứng tại trang chi tiết sản phẩm.
- `size_name`, `price`, `is_default` (size được chọn sẵn).

**2.4. `product_images` — Thư viện ảnh sản phẩm**
- `image_path`, `is_primary` (ảnh chính hiển thị khung vòm Arch Frame), `sort_order`.

**2.5. `addons` — Quà mua kèm**
Mục "Thêm một chút thương yêu": Hộp quà, Gấu bông, Chocolate, Thiệp viết tay miễn phí.
- `name`, `price`, `is_free` (vd. thiệp viết tay).

### 3. Nhóm đơn hàng, lịch giao & in ấn (Orders & Fulfillment)

**3.1. `promotions` — Mã giảm giá / voucher**
Vd. mã LAMOI giảm 40.000 đ, voucher 10% cho đơn đầu tiên.
- `code`: mã nhập ở giỏ hàng.
- `discount_type`: `fixed` (giảm tiền) hoặc `percentage` (giảm %).
- `discount_value`: giá trị giảm.

**3.2. `orders` — Thông tin tổng quát đơn hàng**
- `user_id`: NULL nếu mua không cần đăng nhập.
- `order_code`: mã đơn duy nhất cho khách tra cứu (`#LH-8291`).
- `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`: các mốc tiền của đơn.
- `payment_method`: `cod`, `vnpay`, `momo`, `bank_transfer`.
- `payment_status`: `pending`, `paid`, `failed` (+ `refunded`).
- `order_status`: `received`, `preparing`, `prepared_ready`, `shipping`, `completed`, `cancelled`.

**3.3. `order_delivery_details` — Lịch hẹn giao & thiệp viết tay**
Bảng nghiệp vụ quan trọng nhất của shop hoa (quan hệ **1–1** với `orders`).
- `sender_name`, `sender_phone`, `sender_email`: người đặt.
- `receiver_name`, `receiver_phone`, `shipping_address`: người nhận.
- `delivery_date`: ngày giao bắt buộc. `time_slot`: khung giờ (14:00–16:00).
- `card_sender`, `card_receiver`, `card_message`: nội dung thiệp để nhân viên in ra và viết tay.
- `delivery_notes`: lời dặn riêng cho shipper.

**3.4. `order_items` — Chi tiết mặt hàng trong đơn**
Lưu **snapshot** tên hoa, kích cỡ, đơn giá tại thời điểm đặt (phòng khi sản phẩm đổi giá/đổi tên).
- `item_type`: `flower` hoặc `addon`.
- `item_name`, `size_name`, `unit_price`, `quantity`, `total_price`.

**3.5. `order_status_logs` — Nhật ký hành trình đơn hàng**
Dữ liệu cho timeline 4 bước trên trang tra cứu: *Đã tiếp nhận → Đang cắm hoa → Đang giao → Hoàn thành*.
- `admin_id`: nhân viên/thợ cắm vừa đổi trạng thái (kiểm soát người phụ trách).
- `status`, `title`, `description`: nội dung hiển thị cho khách.

### 4. Nhóm bổ trợ (Marketing & Nội dung)

**4.1. `subscribers` — Danh sách nhận ưu đãi 10%**
Email khách vãng lai gửi từ khung Newsletter.
- `email`, `coupon_given` (mã 10% đã gửi).

**4.2. `posts` — Góc nhỏ / Chuyện của Lá (Blog)**
Bài viết cắm hoa, dưỡng hoa tươi lâu.
- `title`, `slug`, `content`; `admin_id`: người đăng (thuộc `admins`).

---

## Phần B — Đặc tả cột chi tiết (Thẻ 2)

> Ghi chú kiểu dữ liệu: `BIGINT UNSIGNED` cho khóa; boolean lưu dạng `TINYINT(1)`.

### 1. `users` — Tài khoản khách hàng

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| name | VARCHAR(255) | NOT NULL | Họ và tên khách hàng |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email đăng nhập |
| phone | VARCHAR(20) | NULLABLE | Số điện thoại nhận đơn hàng |
| password | VARCHAR(255) | NULLABLE | Mật khẩu mã hóa (NULL nếu đăng nhập Google) |
| google_id | VARCHAR(100) | NULLABLE | ID liên kết tài khoản Google |
| customer_group | VARCHAR(20) | NOT NULL, DEFAULT 'new' | Nhóm khách: `new` Khách mới, `loyal` Khách thân thiết, `best_friend` Bạn thân của Lá (admin gán) |
| birthday | DATE | NULLABLE | Ngày sinh |
| default_shipping_address | VARCHAR(500) | NULLABLE | Địa chỉ giao hoa mặc định (số nhà, đường, phường) |
| default_shipping_district | VARCHAR(100) | NULLABLE | Quận/Huyện giao hoa mặc định |
| remember_token | VARCHAR(100) | NULLABLE | Token ghi nhớ đăng nhập |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo tài khoản |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật gần nhất |

### 2. `admins` — Nhân sự tiệm hoa (Admin & Staff)

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| role_id | BIGINT UNSIGNED | NOT NULL, FK | Tham chiếu `roles.id` |
| name | VARCHAR(255) | NOT NULL | Tên nhân viên / thợ cắm / chủ tiệm |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email đăng nhập hệ thống nội bộ |
| phone | VARCHAR(20) | NULLABLE | Số điện thoại |
| avatar | VARCHAR(500) | NULLABLE | Ảnh đại diện góc trên trang admin |
| password | VARCHAR(255) | NOT NULL | Mật khẩu mã hóa |
| is_active | TINYINT(1) | DEFAULT 1 | 1: hoạt động, 0: khóa |
| remember_token | VARCHAR(100) | NULLABLE | Token duy trì đăng nhập |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |

### 3. `roles` — Vai trò nội bộ

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| name | VARCHAR(100) | NOT NULL | "Chủ tiệm", "Nhân viên / Thợ cắm" |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | `admin`, `staff` |
| description | VARCHAR(255) | NULLABLE | Mô tả trách nhiệm vai trò |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |

### 4. `permissions` — Danh mục quyền hạn

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| name | VARCHAR(100) | NOT NULL | "Xem đơn theo giờ", "In thiệp",... |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | `view_orders`, `print_cards`, `update_status` |
| group_name | VARCHAR(100) | NOT NULL | Nhóm chức năng: Đơn hàng, Kho hoa, Báo cáo |

### 5. `role_has_permissions` — Gán quyền cho vai trò

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| role_id | BIGINT UNSIGNED | PK, FK | Tham chiếu `roles.id` |
| permission_id | BIGINT UNSIGNED | PK, FK | Tham chiếu `permissions.id` |

Khóa chính kép (`role_id`, `permission_id`).

### 6. `categories` — Danh mục hoa & dịp tặng

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| name | VARCHAR(255) | NOT NULL | Hoa theo dịp, Hoa theo mùa,... |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | vd. `hoa-theo-mua` |
| type | VARCHAR(50) | NOT NULL, DEFAULT 'occasion' | `season`, `occasion`, `flower_type` |
| description | TEXT | NULLABLE | Mô tả danh mục |
| is_active | TINYINT(1) | DEFAULT 1 | 1: hiển thị, 0: ẩn |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |

### 7. `products` — Mẫu hoa cắm

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| category_id | BIGINT UNSIGNED | NOT NULL, FK | Tham chiếu `categories.id` |
| name | VARCHAR(255) | NOT NULL | "Lời thì thầm", "Nắng trong veo" |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | vd. `loi-thi-tham` |
| base_price | DECIMAL(12,2) | NOT NULL | Giá tiêu chuẩn cỡ Vừa (vd. 680.000 đ) |
| description | TEXT | NULLABLE | Lời tự sự: "Một chút hồng đào, một chút cam ấm..." |
| dimension_info | VARCHAR(255) | NULLABLE | vd. "Cỡ vừa: cao khoảng 45cm, đường kính 35cm" |
| freshness_guarantee | VARCHAR(255) | NULLABLE | "Tươi xinh từ 3–5 ngày" |
| stock | INT UNSIGNED | DEFAULT 0 | Số lượng có thể cắm hôm nay (vd. 12 bó) |
| is_featured | TINYINT(1) | DEFAULT 0 | 1: hiển thị mục nổi bật |
| is_active | TINYINT(1) | DEFAULT 1 | 1: đang bán, 0: ngừng bán |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |

### 8. `product_sizes` — Kích cỡ bó hoa

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| product_id | BIGINT UNSIGNED | NOT NULL, FK | Tham chiếu `products.id` |
| size_name | VARCHAR(50) | NOT NULL | Nhỏ, Vừa, Lớn |
| price | DECIMAL(12,2) | NOT NULL | vd. 480.000 / 680.000 / 880.000 đ |
| is_default | TINYINT(1) | DEFAULT 0 | 1: được chọn sẵn khi xem (cỡ Vừa) |

### 9. `product_images` — Bộ ảnh sản phẩm

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| product_id | BIGINT UNSIGNED | NOT NULL, FK | Tham chiếu `products.id` |
| image_path | VARCHAR(500) | NOT NULL | Đường dẫn file ảnh |
| is_primary | TINYINT(1) | DEFAULT 0 | 1: ảnh đại diện vòm chính |
| sort_order | INT | DEFAULT 0 | Thứ tự các ảnh phụ (thumbnail) |

### 10. `addons` — Quà mua kèm

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| name | VARCHAR(255) | NOT NULL | "Chocolate ngọt ngào", "Gấu bông êm ái", "Thiệp viết tay" |
| price | DECIMAL(12,2) | DEFAULT 0 | vd. 120.000 / 150.000 / 0 đ |
| image_path | VARCHAR(500) | NULLABLE | Ảnh minh họa |
| is_free | TINYINT(1) | DEFAULT 0 | 1: quà miễn phí (thiệp viết tay) |
| is_active | TINYINT(1) | DEFAULT 1 | 1: cho phép khách chọn |

### 11. `promotions` — Mã giảm giá

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| code | VARCHAR(50) | UNIQUE, NOT NULL | vd. LAMOI, CHAOBAN10 |
| discount_type | VARCHAR(20) | NOT NULL | `percentage` hoặc `fixed` |
| discount_value | DECIMAL(12,2) | NOT NULL | vd. 40.000 đ hoặc 10% |
| min_order_amount | DECIMAL(12,2) | DEFAULT 0 | Đơn tối thiểu (vd. 500.000 đ) |
| max_discount_amount | DECIMAL(12,2) | NULLABLE | Mức giảm tối đa (khi giảm theo %) |
| is_active | TINYINT(1) | DEFAULT 1 | 1: còn hạn dùng, 0: khóa |

### 12. `orders` — Thông tin tổng quát đơn hàng

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| user_id | BIGINT UNSIGNED | NULLABLE, FK | `users.id` (NULL nếu khách vãng lai) |
| order_code | VARCHAR(50) | UNIQUE, NOT NULL | Mã hiển thị: `#LH-8291` |
| subtotal | DECIMAL(12,2) | NOT NULL | Tạm tính (800.000 đ) |
| discount_amount | DECIMAL(12,2) | DEFAULT 0 | Tiền giảm giá (40.000 đ) |
| shipping_fee | DECIMAL(12,2) | DEFAULT 0 | Phí giao hàng (35.000 đ) |
| total_amount | DECIMAL(12,2) | NOT NULL | Tổng thanh toán (795.000 đ) |
| promotion_id | BIGINT UNSIGNED | NULLABLE, FK | `promotions.id` |
| payment_method | VARCHAR(30) | NOT NULL | `cod`, `vnpay`, `momo`, `bank_transfer` |
| payment_status | VARCHAR(30) | DEFAULT 'pending' | `pending`, `paid`, `failed`, `refunded` |
| order_status | VARCHAR(30) | DEFAULT 'received' | `received`, `preparing`, `prepared_ready`, `shipping`, `completed`, `cancelled` |
| transaction_ref | VARCHAR(255) | NULLABLE | Mã giao dịch từ cổng thanh toán |
| created_at | TIMESTAMP | NULLABLE | Thời gian đặt đơn |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật đơn |

### 13. `order_delivery_details` — Lịch hẹn giao & lời chúc thiệp (1–1 với `orders`)

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| order_id | BIGINT UNSIGNED | UNIQUE, NOT NULL, FK | `orders.id` |
| sender_name | VARCHAR(255) | NOT NULL | "Huỳnh Đăng Khoa" |
| sender_phone | VARCHAR(20) | NOT NULL | "0903 456 789" |
| sender_email | VARCHAR(255) | NOT NULL | Email nhận xác nhận đơn |
| is_gift | TINYINT(1) | DEFAULT 1 | 1: gửi tặng người thương, 0: tự nhận |
| receiver_name | VARCHAR(255) | NOT NULL | "Nguyễn Ngọc Đoan Trang" |
| receiver_phone | VARCHAR(20) | NOT NULL | "0912 345 678" |
| shipping_address | VARCHAR(500) | NOT NULL | "18 Võ Văn Tần, Phường 6, Quận 3, TP. HCM" |
| shipping_district | VARCHAR(100) | NOT NULL | "Quận 3" |
| delivery_date | DATE | NOT NULL | 2026-10-08 |
| time_slot | VARCHAR(50) | NOT NULL | 14:00-16:00 |
| card_sender | VARCHAR(100) | NULLABLE | Tên ký trên thiệp ("Đăng Khoa") |
| card_receiver | VARCHAR(100) | NULLABLE | Tên người nhận trên thiệp ("Đoan Trang") |
| card_message | TEXT | NULLABLE | "Chúc Trang một ngày thật dịu dàng..." |
| delivery_notes | TEXT | NULLABLE | "Gọi người nhận trước khi đến 15 phút" |

### 14. `order_items` — Chi tiết bó hoa & quà kèm trong đơn

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| order_id | BIGINT UNSIGNED | NOT NULL, FK | `orders.id` |
| item_type | VARCHAR(20) | NOT NULL | `flower` hoặc `addon` |
| item_id | BIGINT UNSIGNED | NOT NULL | ID của `products` hoặc `addons` (quan hệ đa hình, không có FK cứng) |
| item_name | VARCHAR(255) | NOT NULL | Snapshot tên: "Lời thì thầm", "Chocolate ngọt ngào" |
| size_name | VARCHAR(50) | NULLABLE | Kích cỡ đã chọn: "Cỡ vừa" |
| unit_price | DECIMAL(12,2) | NOT NULL | Đơn giá tại thời điểm mua |
| quantity | INT UNSIGNED | NOT NULL, DEFAULT 1 | Số lượng |
| total_price | DECIMAL(12,2) | NOT NULL | `unit_price * quantity` |

### 15. `order_status_logs` — Nhật ký hành trình đơn & thao tác làm hoa

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| order_id | BIGINT UNSIGNED | NOT NULL, FK | `orders.id` |
| admin_id | BIGINT UNSIGNED | NULLABLE, FK | Thợ cắm/nhân viên bấm cập nhật (`admins.id`) |
| status | VARCHAR(30) | NOT NULL | `received`, `preparing`, `prepared_ready`, `shipping`, `completed` |
| title | VARCHAR(255) | NOT NULL | "Tiệm bắt đầu cắm hoa", "Đơn hoa đã được tiếp nhận" |
| description | TEXT | NULLABLE | "Hoa tươi đã được chọn. Bó hoa đang được cắm và gói ghém." |
| created_at | TIMESTAMP | NOT NULL | Thời gian ghi nhận mốc |

### 16. `subscribers` — Danh sách nhận bản tin ưu đãi 10%

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email nhập tại form Newsletter |
| coupon_given | VARCHAR(50) | NULLABLE | Mã giảm 10% đã gửi tặng |
| created_at | TIMESTAMP | NOT NULL | Thời gian đăng ký |

### 17. `posts` — Góc nhỏ / Chuyện của Lá

| Cột | Kiểu | Thuộc tính | Diễn giải |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Khóa chính |
| title | VARCHAR(255) | NOT NULL | "Để hoa tươi lâu hơn, thương lâu hơn" |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | vd. `de-hoa-tuoi-lau-hon` |
| summary | TEXT | NULLABLE | Lời dẫn đầu bài |
| content | LONGTEXT | NOT NULL | Nội dung chi tiết |
| thumbnail | VARCHAR(500) | NULLABLE | Ảnh bìa |
| admin_id | BIGINT UNSIGNED | NOT NULL, FK | Tác giả (`admins.id`) |
| is_published | TINYINT(1) | DEFAULT 1 | 1: đã xuất bản, 0: bản nháp |
| created_at | TIMESTAMP | NULLABLE | Thời gian đăng |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |

---

## Phần C — Quan hệ (Foreign Keys)

| Quan hệ | Kiểu |
|---|---|
| `admins.role_id` → `roles.id` | N–1 |
| `role_has_permissions.role_id` → `roles.id` | N–1 |
| `role_has_permissions.permission_id` → `permissions.id` | N–1 |
| `products.category_id` → `categories.id` | N–1 |
| `product_sizes.product_id` → `products.id` | N–1 |
| `product_images.product_id` → `products.id` | N–1 |
| `orders.user_id` → `users.id` | N–1 (nullable) |
| `orders.promotion_id` → `promotions.id` | N–1 (nullable) |
| `order_delivery_details.order_id` → `orders.id` | 1–1 |
| `order_items.order_id` → `orders.id` | N–1 |
| `order_status_logs.order_id` → `orders.id` | N–1 |
| `order_status_logs.admin_id` → `admins.id` | N–1 (nullable) |
| `posts.admin_id` → `admins.id` | N–1 |

## Phần D — Lưu ý khi triển khai Laravel

- Hai guard riêng: `web` (provider `users`) cho storefront, `admin` (provider `admins`) cho `/admin`.
- Nhất quán kiểu cột trong DBML: `posts.content` dùng `LONGTEXT` theo đặc tả; `boolean` trong DBML tương ứng `TINYINT(1)` của MySQL.
- `order_items.item_id` là quan hệ đa hình theo `item_type` (`flower` → `products`, `addon` → `addons`), nên không đặt FK cứng.
- Ánh xạ trạng thái đơn sang giao diện: xem `docs/designs/README.md`, mục "Ánh xạ trạng thái".
