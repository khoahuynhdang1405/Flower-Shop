# Lá & Hoa

Website thương mại điện tử cho tiệm hoa tươi **Lá & Hoa**: trang cửa hàng cho khách (chọn hoa, giỏ hàng, đặt hoa, thanh toán VNPAY/MoMo sandbox, tra cứu đơn) và trang quản trị `/admin` cho chủ tiệm và thợ cắm hoa.

Laravel 13 · PHP 8.3+ · PostgreSQL · Tailwind CSS 4 + DaisyUI · Vite · Chart.js

## Yêu cầu

- PHP 8.3 trở lên, có các extension `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`
- Composer 2
- Node.js 20 trở lên và npm
- PostgreSQL 14 trở lên

## Cài đặt

```bash
# 1. Cài thư viện
composer install
npm install

# 2. Tạo file cấu hình
cp .env.example .env
php artisan key:generate
```

Mở `.env` và sửa phần cơ sở dữ liệu cho đúng máy của bạn:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=la_va_hoa_db
DB_USERNAME=postgres
DB_PASSWORD=mat_khau_cua_ban
```

Tạo database rỗng `la_va_hoa_db` trong PostgreSQL, sau đó:

```bash
# 3. Tạo bảng và dữ liệu mẫu (sản phẩm, bộ sưu tập, quà kèm, mã ưu đãi, bài viết, tài khoản mẫu)
php artisan migrate --seed

# 4. Cho phép hiển thị ảnh tải lên trong trang quản trị
php artisan storage:link
```

## Chạy

```bash
composer run dev
```

Lệnh này chạy cùng lúc web server, hàng đợi (gửi email xác nhận đơn) và Vite. Mở http://localhost:8000.

Nếu gói mã nguồn đã có sẵn thư mục `public/build`, bạn có thể chỉ chạy `php artisan serve` và `php artisan queue:work` mà không cần Vite.

## Tài khoản mẫu

| Vai trò | Trang đăng nhập | Email | Mật khẩu |
|---|---|---|---|
| Chủ tiệm (admin) | http://localhost:8000/admin/login | `admin@lavahoa.com` | xem `database/seeders/AdminSeeder.php` |
| Thợ cắm hoa (staff) | http://localhost:8000/admin/login | `staff@lavahoa.com` | xem `database/seeders/AdminSeeder.php` |
| Khách | http://localhost:8000/login | `khach@lavahoa.test` | xem `database/seeders/CustomerSeeder.php` |

Mã ưu đãi mẫu: `CHAOHOA`.

## Tùy chọn

- **Đăng nhập Google:** điền `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` trong `.env`.
- **Thanh toán VNPAY / MoMo (sandbox):** điền các biến `VNPAY_*`, `MOMO_*`. `MOMO_REQUEST_TYPE=payWithATM` cho phép thử thanh toán bằng thẻ ATM giả lập trên trình duyệt.
- **Email:** mặc định `MAIL_MAILER=log`, nội dung email được ghi vào `storage/logs/laravel.log`.

## Kiểm thử

```bash
composer run test
```

Test dùng SQLite trong bộ nhớ (`phpunit.xml`), không đụng tới database PostgreSQL. Luôn chạy `php artisan config:clear` trước khi chạy test nếu bạn đã từng chạy `php artisan config:cache` hoặc `php artisan optimize`; nếu không, test sẽ chạy trên database thật và xóa sạch dữ liệu.

## Tài liệu

- `docs/database/schema.dbml`, `docs/database/database-spec.md`: cấu trúc và ý nghĩa các bảng
- `docs/designs/`: ảnh thiết kế từng màn hình
- `docs/design-vs-db-gaps.md`: những phần thiết kế có mà database chưa hỗ trợ
