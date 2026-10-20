# Thiết kế giao diện — Lá & Hoa

20 màn hình thiết kế: 13 trang Storefront (khách hàng) và 7 trang Admin (quản trị).
Khi code một trang, **mở đúng file ảnh tương ứng** bên dưới và bám sát bố cục, nội dung, trạng thái hiển thị trong ảnh.

## 1. Danh sách màn hình

### Storefront — `docs/designs/storefront/`

| File                       | Trang             | Nội dung chính                                                                                                                                                                                                                                                | Bảng DB liên quan                                 |
| -------------------------- | ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- |
| `01-trang-chu.png`         | Trang chủ         | Hero vòm + CTA, 4 cam kết, "Mỗi dịp một cách gửi thương" (4 dịp), "Những bó hoa được thương nhất" (tab Tất cả / Bó hoa / Giỏ & hộp hoa / Hoa cắm bình), banner hoa theo mùa, giới thiệu tiệm, 3 bước tư vấn, 3 đánh giá khách, 3 bài Góc nhỏ, form newsletter | products, categories, posts, subscribers          |
| `02-tat-ca-hoa.png`        | Tất cả hoa        | Bộ lọc trái (Kiểu hoa, Dịp gửi thương, Khoảng giá, Sắc hoa, Chỉ còn hàng), sắp xếp, lưới 3 cột 9 sản phẩm/trang, nhãn (Được yêu thích, Thiết kế mới, Hoa tháng 10), phân trang                                                                                | products, categories, product_images              |
| `03-hoa-theo-mua.png`      | Hoa theo mùa      | Hero mùa thu, 4 mùa (Xuân/Hạ/Thu/Đông, mùa hiện tại được đánh dấu), 4 thiết kế của tháng, khối "Đúng mùa hoa", CTA tư vấn                                                                                                                                     | categories (`season`), products                   |
| `04-chi-tiet-san-pham.png` | Chi tiết sản phẩm | Gallery ảnh + video, chọn size Nhỏ/Vừa/Lớn, số lượng, trạng thái còn hoa, Thêm vào giỏ / Mua ngay, cam kết độ tươi, phí giao, addon "Thêm một chút thương yêu", hướng dẫn chăm hoa                                                                            | products, product_sizes, product_images, addons   |
| `05-gio-hang.png`          | Giỏ hàng          | Stepper 1/3, danh sách hoa + addon, số lượng, nhập mã ưu đãi, thiệp viết tay miễn phí, tóm tắt tiền, nút thanh toán                                                                                                                                           | promotions, addons                                |
| `06-thanh-toan.png`        | Thanh toán        | Stepper 2/3, 5 khối: người đặt (gửi tặng / tôi là người nhận), người nhận + địa chỉ + quận + ghi chú, ngày + khung giờ (4 khung 2 tiếng), lời nhắn thiệp, phương thức (COD / VNPAY / MoMo); cột phải tóm tắt đơn + lịch hẹn                                   | orders, order_delivery_details, order_items       |
| `07-xac-nhan-don-hang.png` | Xác nhận đơn      | Stepper 3/3, banner thành công + mã đơn + trạng thái thanh toán, lịch hẹn, người nhận/người đặt, thiệp, tóm tắt tiền, nút Theo dõi đơn / Về trang chủ                                                                                                         | orders, order_delivery_details                    |
| `08-tra-cuu-don-hang.png`  | Tra cứu đơn hàng  | Form mã đơn + SĐT người đặt, timeline 4 bước, thông điệp trạng thái, lịch hẹn, nhật ký cập nhật, món quà đã gửi + lời nhắn                                                                                                                                    | orders, order_status_logs, order_delivery_details |
| `09-cau-chuyen-cua-la.png` | Câu chuyện của Lá | Giới thiệu tiệm, 3 triết lý, "Một ngày bình dị" (timeline 07:00–18:00), CTA tư vấn                                                                                                                                                                            | (tĩnh)                                            |
| `10-bai-viet-chi-tiet.png` | Bài viết Góc nhỏ  | Breadcrumb, chuyên mục, tiêu đề, tác giả + ngày + thời gian đọc, ảnh bìa, mục lục cột phải, trích dẫn, ảnh trong bài, hashtag, chia sẻ, 3 bài liên quan                                                                                                       | posts                                             |
| `11-dang-nhap.png`         | Đăng nhập         | Email, mật khẩu (ẩn/hiện), ghi nhớ, quên mật khẩu, đăng nhập Google                                                                                                                                                                                           | users                                             |
| `12-dang-ky.png`           | Đăng ký           | Họ tên, email, SĐT, mật khẩu + xác nhận (≥ 8 ký tự, có hoa/thường/số), đồng ý điều khoản, nhận tin; ưu đãi 10% đơn đầu                                                                                                                                        | users, subscribers                                |
| `13-tai-khoan.png`         | Tài khoản của tôi | Sidebar (thông tin, đơn hàng, đổi mật khẩu, đăng xuất), form thông tin + ngày sinh + địa chỉ mặc định, lịch sử đơn (lọc theo trạng thái, đặt lại hoa), đổi mật khẩu                                                                                           | users, orders                                     |

### Admin — `docs/designs/admin/`

| File                       | Trang               | Nội dung chính                                                                                                                                                      | Bảng DB liên quan                           |
| -------------------------- | ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------- |
| `01-tong-quan.png`         | Tổng quan           | 4 thẻ số liệu (doanh thu, đơn mới, khách mới, sản phẩm đang bán), biểu đồ doanh thu 7 ngày, "Tiệm cần bạn một chút" (việc cần làm), đơn gần đây, sản phẩm nổi bật   | orders, users, products                     |
| `02-don-hang.png`          | Đơn hàng            | 4 thẻ số liệu, tìm kiếm, lọc ngày + thanh toán, tab trạng thái có đếm, bảng (mã đơn, khách + hoa, giá trị, thanh toán, giao hàng, giờ giao), xuất lịch giao hôm nay | orders, order_delivery_details, order_items |
| `03-san-pham-danh-gia.png` | Sản phẩm & đánh giá | Bảng sản phẩm (mã, danh mục, giá, tồn kho, trạng thái) + bảng đánh giá khách (số sao, nội dung, duyệt / ẩn / phản hồi)                                              | products, (reviews — chưa có)               |
| `04-bo-suu-tap.png`        | Bộ sưu tập          | Thẻ số liệu, lưới thẻ bộ sưu tập (ảnh bìa, loại Theo mùa/Theo dịp, trạng thái hiển thị/bản nháp, số sản phẩm, thời gian áp dụng)                                    | categories (+ thiếu, xem gaps)              |
| `05-khach-hang.png`        | Khách hàng          | Thẻ số liệu, bảng khách (mã KH, liên hệ, số đơn, chi tiêu chỉ tính đơn hoàn thành sau ưu đãi, nhóm khách, đơn gần nhất)                                             | users, orders                               |
| `06-chuyen-hoa.png`        | Chuyện Hoa          | Thẻ số liệu, bảng bài viết (thumbnail, thời gian đọc, chuyên mục, tác giả, cập nhật, trạng thái Đã đăng/Bản nháp), nhắc bản nháp                                    | posts                                       |
| `07-uu-dai.png`            | Ưu đãi              | Thẻ số liệu, bảng mã (loại/giá trị, điều kiện, thời hạn, lượt dùng/giới hạn, trạng thái), khối chương trình sắp diễn ra, checklist quy tắc                          | promotions                                  |

## 2. Design language

### Bảng màu chính xác (lấy từ Figma — trang chủ + admin)

Trong code, mọi màu được khai báo trong `resources/css/app.css` (`@theme`) với tiền tố `lh-` (vd. `primary` → `bg-lh-primary`) để không đụng tên màu của daisyUI.

| Mã        | Độ mờ | Token đề xuất     | Vai trò (suy ra từ thiết kế — cần xác nhận)                   |
| --------- | ----- | ----------------- | ------------------------------------------------------------- |
| `#355B46` | 100%  | `primary`         | Xanh lá chính: nút primary, top bar, tab đang chọn, link nhấn |
| `#273F33` | 100%  | `primary-dark`    | Xanh đậm: footer (storefront), sidebar (admin)                |
| `#1F472B` | 100%  | `heading`         | Xanh rêu sâu: tiêu đề serif, icon                             |
| `#778078` | 100%  | `text-muted`      | Chữ phụ, mô tả, placeholder                                   |
| `#FBF9F4` | 100%  | `background`      | Nền kem toàn trang                                            |
| `#FFFFFF` | 100%  | `surface`         | Nền thẻ, input, khối đánh giá                                 |
| `#E8EDE3` | 100%  | `sage-50`         | Nền khối nhấn xanh nhạt (dải cam kết, khối tư vấn, badge)     |
| `#DCE1D7` | 100%  | `border`          | Viền thẻ, đường kẻ phân cách                                  |
| `#D5DFD0` | 100%  | `sage-100`        | Viền/nền sage đậm hơn một bậc (tab, chip, nút "+")            |
| `#F1E4DF` | 100%  | `blush`           | Hồng phấn: banner hoa theo mùa, khối thiệp/lời nhắn           |
| `#F4F5EF` | 100%  | `lh-canvas`       | Nền vùng nội dung admin                                       |
| `#CBD8C6` | 100%  | `lh-sage-200`     | Cột biểu đồ các ngày trước (admin Tổng quan)                  |
| `#F5EBD9` | 100%  | `lh-warning-bg`   | Nền badge cảnh báo (Chờ xác nhận, tồn kho thấp...)            |
| `#926B35` | 100%  | `lh-warning-fg`   | Chữ badge cảnh báo                                            |
| `#4E8752` | 40%   | `accent-soft`     | Xanh trong suốt: viền nét đứt logo, hover/overlay nhẹ         |
| `#F59E0B` | 100%  | `star`            | Sao đánh giá                                                  |
| `#FFFFFF` | 14.9% | `on-dark-divider` | Đường kẻ / chữ mờ trên nền xanh đậm (footer)                  |
| `#000000` | 0%    | —                 | Trong suốt (fill rỗng, bỏ qua)                                |
| Linear    | 100%  | `gradient`        | Có một gradient — **chưa có điểm màu**, cần lấy thêm từ Figma |

### Màu chưa xác minh (vẫn là ước lượng từ ảnh)

- Badge xám (Bản nháp, Khách mới, Tạm dừng, Đã kết thúc): nền ~`#EEEEEA`, chữ ~`#8A8A85`

**Typography**

- Tiêu đề (vd. "Một ngày đẹp ở Lá & Hoa") và câu trích dẫn nghiêng ("Chăm tiệm mỗi ngày, để thương luôn nở."): **Lora** (đã xác nhận từ Figma).
- Phần còn lại (nội dung, nhãn, bảng, chữ trong logo): **Inter** (đã xác nhận từ Figma).
- Logo: `storefront/14-logo.png` — đã dựng lại thành SVG ở `resources/views/components/brand/flower-mark.blade.php`.
- Nhãn nhỏ phía trên tiêu đề (eyebrow): in hoa, cỡ nhỏ, màu xanh lá.

**Thành phần lặp lại**

- Top bar xanh: "Một chút hoa, một chút thương • Miễn phí giao nội thành cho đơn từ 800.000 VNĐ".
- Header storefront: logo tròn "Lá & Hoa", menu (Trang chủ, Tất cả hoa, Hoa theo dịp, Câu chuyện của Lá, Góc nhỏ), icon tìm kiếm / tài khoản / ngôn ngữ / giỏ hàng (số lượng). Trang đăng nhập/đăng ký ẩn icon tìm kiếm và giỏ.
- Footer storefront 4 cột: thương hiệu + mạng xã hội, Khám phá tiệm, Lá luôn ở đây, Ghé Lá một chút (địa chỉ, giờ mở cửa, hotline, email). Trang đăng nhập/đăng ký dùng footer rút gọn.
- Khung ảnh vòm (arch) cho ảnh hero, ảnh đăng nhập/đăng ký.
- Stepper 3 bước Giỏ hàng → Thanh toán → Hoàn tất.
- Thẻ sản phẩm: ảnh, nhãn góc trái, nút tim góc phải, tên (serif), thành phần hoa, giá, nút "+".
- Admin layout: sidebar xanh đậm (Tổng quan, Đơn hàng có badge số, Sản phẩm, Bộ sưu tập, Khách hàng, Chuyện Hoa, Ưu đãi; nhóm phụ Cài đặt tiệm, Trợ giúp, Quay về cửa hàng; câu "Chăm tiệm mỗi ngày, để thương luôn nở."), header breadcrumb + chuông + avatar.
- Mỗi trang admin: tiêu đề serif + mô tả, nút primary góc phải, 4 thẻ số liệu, khối bảng có tìm kiếm + 2 dropdown lọc + phân trang, khối nhắc việc màu xanh nhạt cuối trang.
- Giọng văn: mềm mại, ấm áp, xưng "Lá"/"tiệm", gọi khách là "bạn"; tránh ngôn ngữ kỹ thuật khô cứng ngay cả ở admin.
- Tiền tệ: định dạng `680.000 đ` (storefront) hoặc `680.000 VNĐ`; bảng admin ghi đơn vị ở tiêu đề cột "(VNĐ)". Ngày: `dd/mm/yyyy`.

## 3. Ánh xạ trạng thái (DB → giao diện)

| `orders.order_status` | Admin                                                | Khách (tra cứu / tài khoản) | Màu badge |
| --------------------- | ---------------------------------------------------- | --------------------------- | --------- |
| `received`            | Chờ xác nhận                                         | Đã tiếp nhận                | cam       |
| `preparing`           | Đang chuẩn bị                                        | Đang cắm hoa                | xanh nhạt |
| `prepared_ready`      | _(chưa có trong thiết kế — đề xuất "Sẵn sàng giao")_ | Đang cắm hoa                | xanh nhạt |
| `shipping`            | Đang giao                                            | Đang giao                   | xanh nhạt |
| `completed`           | Hoàn thành                                           | Hoàn thành                  | xanh nhạt |
| `cancelled`           | Đã hủy                                               | Đã hủy                      | xám       |

| `orders.payment_status` | Hiển thị                        | Màu       |
| ----------------------- | ------------------------------- | --------- |
| `pending`               | Chưa thanh toán                 | cam       |
| `paid`                  | Đã thanh toán                   | xanh nhạt |
| `failed`                | Thanh toán thất bại _(đề xuất)_ | đỏ nhạt   |
| `refunded`              | Đã hoàn tiền _(đề xuất)_        | xám       |

| `orders.payment_method` | Hiển thị                                       |
| ----------------------- | ---------------------------------------------- |
| `cod`                   | COD · Khi nhận hoa (admin hiển thị "Tiền mặt") |
| `vnpay`                 | VNPAY · Thẻ / QR ngân hàng                     |
| `momo`                  | MoMo · Ví điện tử                              |
| `bank_transfer`         | Chuyển khoản                                   |

Những điểm thiết kế chưa có trong DB và các chỗ thiết kế chưa nhất quán: xem `docs/design-vs-db-gaps.md`.
