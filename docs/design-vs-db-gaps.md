# Đối chiếu thiết kế ↔ cơ sở dữ liệu

Tài liệu này ghi lại những gì **thiết kế đang hiển thị nhưng DB (17 bảng) chưa hỗ trợ**, và những **chỗ thiết kế chưa nhất quán với nhau**.
Mục đích: để khi code, không tự ý thêm cột/bảng hoặc tự chọn số liệu mà không hỏi lại. **Mỗi mục cần chủ dự án quyết định trước khi triển khai.**

## 1. Thiết kế có, DB chưa có

| # | Màn hình | Thiết kế cần | DB hiện tại | Hướng xử lý gợi ý |
|---|---|---|---|---|
| 1 | Admin › Sản phẩm & đánh giá; Trang chủ (đánh giá 4,9/5) | Đánh giá sản phẩm: số sao, nội dung, ngày gửi, trạng thái Đã duyệt / Chờ duyệt / Ẩn, phản hồi của tiệm; chỉ từ đơn đã hoàn thành | Không có bảng | Thêm bảng `reviews` (user_id, order_id, product_id, rating, content, status, reply, ...) |
| 2 | Admin › Bộ sưu tập | Ảnh bìa, khẩu hiệu, loại Theo mùa/Theo dịp, trạng thái Đang hiển thị/Bản nháp, thời gian áp dụng (01/10–30/11/2026, "Quanh năm", "Dự kiến"), **một sản phẩm thuộc nhiều bộ sưu tập** | `categories` chỉ có type + is_active; `products.category_id` là quan hệ 1 danh mục | Thêm cột cho `categories` (cover_image, tagline, starts_at, ends_at, status) + bảng pivot `category_product`; hoặc tách bảng `collections` riêng |
| 3 | Tất cả hoa (bộ lọc) | Lọc theo Kiểu hoa (Bó hoa / Giỏ & hộp hoa / Hoa cắm bình), Dịp, Khoảng giá, Sắc hoa (Hồng, Đào, Vàng, Trắng, Đỏ, Xanh) | Không có cột kiểu hoa, màu sắc | Thêm `products.product_type`, bảng/cột màu sắc; dùng pivot ở mục 2 cho dịp/mùa |
| 4 | Admin › Sản phẩm | Mã sản phẩm `LH-H001`, đơn vị tồn kho (bó / bình / giỏ) | Không có | Thêm `products.sku`, đơn vị suy ra từ product_type |
| 5 | Thẻ sản phẩm | Dòng thành phần hoa ("Hồng vàng · Cúc tana · Lá bạc"), nhãn "Được yêu thích / Thiết kế mới / Hoa tháng 10" | Chỉ có `is_featured` | Thêm `products.flower_composition`, `products.badge` (hoặc tính tự động) |
| 6 | Chi tiết sản phẩm | Video trong gallery ("Vidéo · 00:18") | `product_images` chỉ có ảnh | Thêm `media_type` vào `product_images` |
| 7 | Thanh toán; Chi tiết sản phẩm | Khu vực giao + phí theo quận ("Quận 3 · 35.000 đ", "Phí từ 25.000 đ theo khu vực"), ngưỡng miễn phí 800.000 đ, 4 khung giờ cố định | Không có | Thêm bảng `delivery_zones` (district, fee) hoặc config; time slot để config |
| 8 | Admin › Ưu đãi | Tên chương trình, thời hạn từ–đến, giới hạn lượt dùng, đã dùng, trạng thái Sắp diễn ra/Tạm dừng/Đã kết thúc, loại "Phí giao hàng" (freeship), điều kiện theo đơn đầu / bộ sưu tập / khu vực / nhóm khách, mỗi khách dùng tối đa 1 lần | Chỉ có code, type, value, min, max, is_active | Thêm `name`, `starts_at`, `ends_at`, `usage_limit`, `used_count`, `per_user_limit`, loại `free_shipping`, các cột điều kiện; có thể thêm `promotion_usages` |
| 9 | Admin › Khách hàng; Tài khoản | Mã khách `KH0012`, nhóm khách (Khách mới / Khách thân thiết / Bạn thân của Lá), ngày sinh, địa chỉ giao mặc định, "Gắn bó từ tháng 09/2026" | `users` không có các cột này | Thêm `birthday`, `default_address`, `customer_group` (hoặc tính tự động theo số đơn/chi tiêu) |
| 10 | Đăng ký | Checkbox "Gửi tôi chuyện Hoa và ưu đãi" | Không có cờ đồng ý nhận tin trên `users` | Thêm `users.marketing_opt_in` hoặc ghi vào `subscribers` |
| 11 | Admin › Chuyện Hoa; Bài viết | Chuyên mục bài (Chăm hoa, Ngôn ngữ của hoa, Hoa & cuộc sống, Nhật ký tiệm, Gợi ý tặng hoa, Hoa theo mùa — 6 chuyên mục), thời gian đọc, hashtag | `posts` không có | Thêm bảng `post_categories` + `posts.post_category_id`, `reading_time` (hoặc tính từ content), tags |
| 12 | Thẻ sản phẩm (icon tim) | Danh sách yêu thích | Không có | Thêm bảng `wishlists` nếu cần lưu |
| 13 | Đăng nhập ("Một giỏ hoa đã lưu") | Giỏ hàng lưu theo tài khoản | Không có bảng giỏ | Dùng session, hoặc thêm `carts`/`cart_items` |
| 14 | Admin (chuông), "lời nhắn cần phản hồi" | Thông báo, tin nhắn khách | Không có | Dùng bảng `notifications` mặc định của Laravel; làm rõ "lời nhắn" là gì |
| 15 | CTA "Nhắn Lá tư vấn / Nhờ Lá thiết kế riêng" | Yêu cầu tư vấn | Không có | Làm rõ: link Zalo/Messenger hay form lưu DB |
| 16 | Admin › Cài đặt tiệm | Thông tin tiệm (hotline, địa chỉ, giờ mở cửa...) | Không có | Bảng `settings` (key-value) hoặc config |
| 17 | Bộ lọc trạng thái đơn (admin) | Không có trạng thái riêng cho `prepared_ready` | DB có `prepared_ready` | Quyết định nhãn hiển thị (đề xuất "Sẵn sàng giao") |

## 2. Thiết kế chưa nhất quán với nhau

| # | Vấn đề | Chi tiết |
|---|---|---|
| 1 | **Phí giao & tổng tiền ở trang Thanh toán** | Dropdown ghi "Quận 3 · Phí giao 35.000 đ", tóm tắt ghi giao hoa 0 đ, tổng 760.000 đ, nhưng nút "Đặt hoa · 795.000 đ". Trang xác nhận ghi 760.000 đ; trang tra cứu và tài liệu DB ghi 795.000 đ. Cần chốt: ngưỡng freeship 800.000 đ tính **trước hay sau** giảm giá (trang Ưu đãi ghi "giá trị tối thiểu tính trước phí giao hàng"). |
| 2 | Giá "Lời thì thầm" | 680.000 đ (chi tiết, giỏ hàng, trang chủ) và 690.000 đ (Tất cả hoa, Admin, Đơn hàng, Tài khoản). Ảnh sản phẩm cũng khác nhau giữa trang chi tiết (tông kem/đào) và danh sách (hồng đậm). |
| 3 | Định dạng mã đơn | `#LH-8291` (storefront, DB) và `#LH1028` (admin, tài khoản). |
| 4 | Hotline tiệm | `0378599249` (footer), `0901 234 567` (thanh toán, xác nhận, tra cứu), `0378590249` (sidebar tài khoản). |
| 5 | Mã chào mừng | `CHAOHOA` 10%, tối đa 100.000 đ (đăng ký) và `CHAOBAN10` 10%, tối đa 80.000 đ (admin); `LAMOI` giảm 40.000 đ đơn đầu (giỏ hàng) không có trong danh sách admin. |
| 6 | Email xác nhận | Thanh toán nhập `Dangkhoa@gmail.com`, trang xác nhận ghi đã gửi tới `khoa@gmail.com`. |
| 7 | Địa chỉ | `18 Võ Văn Tần, Phường 6, Quận 3` (đơn hàng) và `Phường Xuân Hòa` (tài khoản). Cần chốt dùng đơn vị hành chính cũ hay mới. |
| 8 | Tồn kho "Thu dịu dàng" | "3 bó" (Tổng quan) và "3 bình" (Sản phẩm). |
| 9 | Kích thước cỡ Vừa | Trang chi tiết: cao 40 cm, đường kính 30 cm. Tài liệu DB: 45 cm / 35 cm. |
| 10 | Tác giả bài "Để hoa tươi lâu hơn" | Trang bài viết: "Khoa"; admin: "Mai". |
| 11 | Badge giỏ hàng | Giỏ có 2 sản phẩm nhưng icon ghi (1). |
| 12 | Menu active | Trang giỏ hàng, thanh toán, xác nhận, tra cứu, chi tiết sản phẩm đều gạch chân "Trang chủ". |
| 13 | Footer | Một số trang ghi "© 2026 Lá & Hoagh" (lỗi chính tả). |
| 14 | Nhãn trạng thái đơn | Admin dùng "Chờ xác nhận / Đang chuẩn bị", khách dùng "Đã tiếp nhận / Đang cắm hoa". Đây có thể là chủ ý; đã ánh xạ ở `docs/designs/README.md`. |
