# Hệ Thống Website Bán Hàng Camera Online

## Tổng Quan

Website Bán Hàng Camera Online là nền tảng e-commerce toàn diện được thiết kế để bán các sản phẩm camera, ống kính, phụ kiện và dịch vụ cho thuê/bảo dưỡng camera. Hệ thống cho phép khách hàng mua hàng trực tuyến, quản trị viên quản lý sản phẩm và đơn hàng, nhân viên xử lý đơn và dịch vụ.

## Yêu Cầu Hệ Thống

### Yêu Cầu Kỹ Thuật

- **PHP:** 7.4 hoặc cao hơn
- **MySQL:** 5.7 hoặc cao hơn
- **Máy chủ web:** Apache/Nginx
- **PHP Extensions:** PDO, GD (xử lý hình ảnh)
- **Trình duyệt:** Hiện đại có hỗ trợ JavaScript (Chrome, Firefox, Safari, Edge)

### Cấu Hình Cơ Sở Dữ Liệu

- **Host:** localhost
- **Tên Database:** camera_db
- **Tên người dùng:** root
- **Mật khẩu:** (mặc định trống, có thể sửa đổi trong `config/database.php`)
- **Cổng:** 3306 (mặc định)

## Vai Trò Người Dùng và Quyền Truy Cập

### Khách Hàng

- Xem danh sách sản phẩm camera, ống kính, phụ kiện
- Tìm kiếm sản phẩm theo tên, danh mục, hãng, giá
- Xem chi tiết sản phẩm (mô tả, hình ảnh, thông số, rating)
- Thêm sản phẩm vào giỏ hàng
- Quản lý giỏ hàng (xem, chỉnh sửa, xóa sản phẩm)
- Thanh toán đơn hàng (VNPay, chuyển khoản, thanh toán tại cửa hàng)
- Xem lịch sử đơn hàng
- Theo dõi trạng thái giao hàng
- Đặt dịch vụ cho thuê camera
- Xem lịch sử dịch vụ cho thuê
- Quản lý tài khoản cá nhân
- Gửi tin nhắn liên hệ

### Nhân Viên / Nhân Viên Bán Hàng

- Quản lý sản phẩm (thêm, sửa, xóa)
- Quản lý danh mục sản phẩm
- Quản lý hãng sản xuất
- Quản lý kho hàng (số lượng tồn)
- Quản lý đơn hàng (xem, cập nhật trạng thái)
- Quản lý dịch vụ cho thuê camera
- Xem và xác nhận yêu cầu cho thuê
- Quản lý liên hệ từ khách hàng (xem, trả lời)
- Xem báo cáo bán hàng cơ bản
- Quản lý hồ sơ cá nhân

### Quản Trị Viên (Admin)

- **Quản lý người dùng:** Thêm, sửa, xóa người dùng (Admin, Nhân viên, Khách hàng)
- **Quản lý sản phẩm:** Thêm, sửa, xóa sản phẩm; quản lý kho hàng
- **Quản lý danh mục:** Thêm, sửa, xóa danh mục sản phẩm
- **Quản lý hãng:** Thêm, sửa, xóa hãng sản xuất
- **Quản lý đơn hàng:** Xem tất cả đơn, cập nhật trạng thái, hủy đơn
- **Quản lý dịch vụ:** Quản lý dịch vụ cho thuê, giá thuê
- **Quản lý thanh toán:** Cấu hình phương thức thanh toán, xem giao dịch
- **Quản lý cấu hình:** Cập nhật thông tin cửa hàng, giờ hoạt động, link mạng xã hội
- **Xem báo cáo:** Thống kê bán hàng, doanh thu, sản phẩm bán chạy
- **Quản lý liên hệ:** Xem tất cả tin nhắn từ khách hàng
- **Quản lý khách hàng:** Xem lịch sử mua, theo dõi khách VIP

## Use Cases (Trường Hợp Sử Dụng)

### Use Cases Xác Thực

#### 1. Đăng Nhập

- **Tác nhân:** Khách hàng, Nhân viên, Quản trị viên
- **Mô tả:** Người dùng đăng nhập vào hệ thống bằng email/username và mật khẩu
- **Luồng chính:**
  1. Người dùng truy cập `/login.php`
  2. Điền email/username và mật khẩu
  3. Hệ thống xác thực thông tin
  4. Chuyển hướng đến trang chính tương ứng với vai trò

#### 2. Đăng Ký

- **Tác nhân:** Khách hàng mới
- **Mô tả:** Người dùng tạo tài khoản mới
- **Luồng chính:**
  1. Người dùng truy cập `/register.php`
  2. Điền thông tin: tên, email, mật khẩu, số điện thoại
  3. Xác nhận mật khẩu
  4. Hệ thống xác thực và lưu thông tin
  5. Chuyển hướng đến trang đăng nhập

#### 3. Đăng Nhập Với Google

- **Tác nhân:** Khách hàng
- **Mô tả:** Đăng nhập bằng tài khoản Google
- **Luồng chính:**
  1. Người dùng nhấn "Đăng nhập với Google"
  2. Chuyển hướng đến Google OAuth
  3. Hệ thống tạo hoặc cập nhật tài khoản
  4. Chuyển hướng đến trang chính

#### 4. Đăng Xuất

- **Tác nhân:** Người dùng đã đăng nhập
- **Mô tả:** Người dùng đăng xuất khỏi hệ thống
- **Luồng chính:**
  1. Người dùng nhấn "Đăng xuất"
  2. Hệ thống xóa phiên làm việc
  3. Chuyển hướng đến trang chủ

### Use Cases Khách Hàng

#### 1. Xem Danh Sách Sản Phẩm

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng xem danh sách các sản phẩm camera
- **Luồng chính:**
  1. Khách hàng truy cập `/products.php`
  2. Hệ thống hiển thị danh sách sản phẩm (có phân trang)
  3. Khách hàng có thể xem theo danh mục, hãng, khoảng giá
  4. Sắp xếp sản phẩm theo tên, giá, mới nhất

#### 2. Tìm Kiếm Sản Phẩm

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng tìm kiếm sản phẩm cụ thể
- **Luồng chính:**
  1. Khách hàng nhập từ khóa tìm kiếm
  2. Hệ thống tìm kiếm sản phẩm theo tên, mô tả
  3. Hiển thị kết quả tìm kiếm

#### 3. Xem Chi Tiết Sản Phẩm

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng xem thông tin chi tiết của sản phẩm
- **Luồng chính:**
  1. Khách hàng chọn một sản phẩm từ danh sách
  2. Hệ thống hiển thị trang chi tiết sản phẩm
  3. Xem hình ảnh (có zoom), mô tả, thông số kỹ thuật
  4. Xem giá, tình trạng kho, rating
  5. Xem bình luận từ khách hàng khác

#### 4. Thêm Sản Phẩm Vào Giỏ Hàng

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng thêm sản phẩm vào giỏ hàng
- **Luồng chính:**
  1. Khách hàng xem chi tiết sản phẩm
  2. Nhập số lượng cần mua
  3. Nhấn "Thêm vào giỏ hàng"
  4. Hệ thống cập nhật giỏ hàng
  5. Hiển thị thông báo thành công

#### 5. Quản Lý Giỏ Hàng

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng xem và chỉnh sửa giỏ hàng
- **Luồng chính:**
  1. Khách hàng truy cập `/cart.php`
  2. Xem danh sách sản phẩm trong giỏ
  3. Thay đổi số lượng hoặc xóa sản phẩm
  4. Xem tổng tiền (có thể hiển thị đơn giá, tiền hàng, phí vận chuyển)
  5. Nhấn "Tiến hành thanh toán"

#### 6. Thanh Toán Đơn Hàng

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng thanh toán đơn hàng
- **Luồng chính:**
  1. Khách hàng truy cập `/checkout.php`
  2. Nhập/xác nhận thông tin giao hàng
  3. Chọn phương thức thanh toán:
     - Thanh toán tại cửa hàng
     - VNPay (thẻ ngân hàng)
     - Chuyển khoản ngân hàng
  4. Xem tóm tắt đơn hàng
  5. Nhấn "Hoàn tất đơn hàng"
  6. Hệ thống tạo đơn hàng với trạng thái Pending
  7. Chuyển hướng đến trang xác nhận

#### 7. Xem Lịch Sử Đơn Hàng

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng xem lịch sử các đơn hàng đã đặt
- **Luồng chính:**
  1. Khách hàng truy cập `/profile.php`
  2. Chọn "Lịch sử đơn hàng"
  3. Xem danh sách các đơn hàng
  4. Xem chi tiết đơn hàng (trạng thái, ngày đặt, tổng tiền)
  5. Theo dõi tình trạng giao hàng

#### 8. Đặt Dịch Vụ Cho Thuê Camera

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng đặt dịch vụ cho thuê camera
- **Luồng chính:**
  1. Khách hàng truy cập `/service.php`
  2. Xem danh sách camera cho thuê
  3. Chọn camera cần thuê
  4. Chọn thời gian thuê (ngày, tuần, tháng)
  5. Nhập thông tin cá nhân
  6. Chọn phương thức thanh toán
  7. Xác nhận đặt
  8. Hệ thống tạo yêu cầu cho thuê (chờ duyệt)

#### 9. Quản Lý Tài Khoản

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng quản lý thông tin cá nhân
- **Luồng chính:**
  1. Khách hàng truy cập `/profile.php`
  2. Xem/cập nhật thông tin: tên, email, số điện thoại
  3. Thay đổi mật khẩu
  4. Quản lý địa chỉ giao hàng
  5. Lưu thay đổi

#### 10. Gửi Tin Nhắn Liên Hệ

- **Tác nhân:** Khách hàng
- **Mô tả:** Khách hàng gửi tin nhắn hoặc câu hỏi tới cửa hàng
- **Luồng chính:**
  1. Khách hàng truy cập `/contact.php`
  2. Điền tên, email, tiêu đề, nội dung
  3. Nhấn "Gửi"
  4. Hệ thống lưu tin nhắn
  5. Nhân viên sẽ trả lời qua email

### Use Cases Nhân Viên

#### 1. Quản Lý Sản Phẩm

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên quản lý danh sách sản phẩm
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/products.php`
  2. Xem danh sách sản phẩm hiện có
  3. **Thêm sản phẩm mới:**
     - Nhấn "Thêm sản phẩm"
     - Điền: tên, mô tả, giá, danh mục, hãng
     - Upload hình ảnh
     - Nhấn "Lưu"
  4. **Sửa sản phẩm:**
     - Nhấn "Sửa" trên sản phẩm
     - Cập nhật thông tin
     - Nhấn "Cập nhật"
  5. **Xóa sản phẩm:**
     - Nhấn "Xóa"
     - Xác nhận xóa

#### 2. Quản Lý Kho Hàng

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên cập nhật số lượng tồn kho
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/products.php`
  2. Chọn sản phẩm cần cập nhật
  3. Cập nhật số lượng tồn
  4. Đánh dấu sản phẩm hết hàng nếu cần
  5. Lưu thay đổi

#### 3. Quản Lý Danh Mục

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên quản lý danh mục sản phẩm
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/categories.php`
  2. Xem danh sách danh mục
  3. Thêm, sửa hoặc xóa danh mục

#### 4. Quản Lý Hãng Sản Xuất

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên quản lý hãng sản xuất
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/brands.php`
  2. Xem danh sách hãng
  3. Thêm, sửa hoặc xóa hãng

#### 5. Quản Lý Đơn Hàng

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên quản lý các đơn hàng
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/orders.php`
  2. Xem danh sách đơn hàng
  3. **Xem chi tiết đơn:**
     - Xem sản phẩm, số lượng, giá
     - Xem thông tin giao hàng
     - Xem phương thức thanh toán
  4. **Cập nhật trạng thái:**
     - Processing (đang chuẩn bị)
     - Shipped (đã gửi)
     - Delivered (đã giao)
  5. **Hủy đơn (nếu cần)**

#### 6. Quản Lý Dịch Vụ Cho Thuê

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên quản lý dịch vụ cho thuê
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/bookings.php` (hoặc tương tự)
  2. Xem danh sách yêu cầu cho thuê
  3. **Xác nhận yêu cầu:**
     - Xem chi tiết (camera, thời gian, giá)
     - Nhấn "Xác nhận"
     - Ghi chú nếu cần
  4. **Từ chối yêu cầu (nếu cần):**
     - Nhấn "Từ chối"
     - Ghi lý do

#### 7. Quản Lý Liên Hệ

- **Tác nhân:** Nhân viên
- **Mô tả:** Nhân viên xem và trả lời tin nhắn từ khách
- **Luồng chính:**
  1. Nhân viên truy cập `/admin/contacts.php`
  2. Xem danh sách tin nhắn chưa đọc
  3. Xem chi tiết tin nhắn
  4. Trả lời tin nhắn (gửi email đến khách)
  5. Đánh dấu "Đã xử lý"

### Use Cases Quản Trị Viên (Admin)

#### 1. Quản Lý Người Dùng

- **Tác nhân:** Admin
- **Mô tả:** Admin quản lý tất cả người dùng
- **Luồng chính:**
  1. Admin truy cập `/admin/users.php`
  2. Xem danh sách tất cả người dùng (Admin, Nhân viên, Khách hàng)
  3. **Thêm người dùng mới:**
     - Nhấn "Thêm người dùng"
     - Điền thông tin: tên, email, mật khẩu, vai trò
     - Lưu
  4. **Sửa thông tin người dùng:**
     - Chọn người dùng
     - Cập nhật thông tin
     - Lưu
  5. **Xóa người dùng:**
     - Chọn người dùng
     - Nhấn "Xóa"
     - Xác nhận

#### 2. Quản Lý Cấu Hình Hệ Thống

- **Tác nhân:** Admin
- **Mô tả:** Admin cấu hình thông tin cửa hàng
- **Luồng chính:**
  1. Admin truy cập `/admin/settings.php`
  2. **Cập nhật thông tin cửa hàng:**
     - Tên cửa hàng
     - Địa chỉ
     - Số điện thoại
     - Email
     - Giờ hoạt động
  3. **Cấu hình thanh toán:**
     - Kích hoạt VNPay
     - Kích hoạt chuyển khoản
     - Thông tin tài khoản
  4. **Cấu hình vận chuyển:**
     - Phí vận chuyển
     - Thời gian giao
  5. **Cập nhật mạng xã hội:**
     - Facebook, Instagram, YouTube
  6. Lưu thay đổi

#### 3. Xem Báo Cáo & Thống Kê

- **Tác nhân:** Admin
- **Mô tả:** Admin xem các báo cáo bán hàng
- **Luồng chính:**
  1. Admin truy cập `/admin/index.php` (Dashboard)
  2. Xem thống kê:
     - Tổng doanh thu
     - Số đơn hàng
     - Số sản phẩm bán
     - Sản phẩm bán chạy nhất
     - Khách hàng mới
  3. Xem biểu đồ theo thời gian

#### 4. Quản Lý Khách Hàng

- **Tác nhân:** Admin
- **Mô tả:** Admin xem thông tin chi tiết khách hàng
- **Luồng chính:**
  1. Admin truy cập `/admin/customers.php`
  2. Xem danh sách khách hàng
  3. Xem chi tiết khách hàng:
     - Lịch sử mua
     - Tổng chi tiêu
     - Đánh giá
  4. Gửi thông báo/khuyến mãi (nếu có)

#### 5. Quản Lý Hồ Sơ Admin

- **Tác nhân:** Admin
- **Mô tả:** Admin quản lý tài khoản cá nhân
- **Luồng chính:**
  1. Admin truy cập `/admin/profile.php`
  2. Xem/cập nhật thông tin cá nhân
  3. Thay đổi mật khẩu
  4. Lưu thay đổi

## Tính Năng Chính

### Hệ Thống Xác Thực

- Đăng nhập/Đăng ký với email
- Đăng nhập với Google OAuth
- Quản lý phiên làm việc
- Mã hóa mật khẩu (password_hash)
- Kiểm soát truy cập dựa trên vai trò

### Quản Lý Sản Phẩm

- Thêm/sửa/xóa sản phẩm
- Quản lý hình ảnh sản phẩm (upload, xóa)
- Quản lý danh mục
- Quản lý hãng sản xuất
- Quản lý kho hàng (số lượng tồn)
- Xem thống kê sản phẩm (bán chạy, hết hàng)

### Tìm Kiếm & Lọc

- Tìm kiếm sản phẩm theo tên
- Lọc theo danh mục, hãng, khoảng giá
- Sắp xếp theo tên, giá, mới nhất
- Phân trang kết quả

### Giỏ Hàng & Thanh Toán

- Thêm/xóa sản phẩm trong giỏ
- Cập nhật số lượng
- Lưu giỏ hàng (session/database)
- Tính tổng tiền tự động
- Nhiều phương thức thanh toán:
  - Thanh toán tại cửa hàng
  - VNPay
  - Chuyển khoản ngân hàng
- Xác nhận đơn hàng

### Quản Lý Đơn Hàng

- Tạo đơn hàng
- Xem lịch sử đơn (khách hàng)
- Quản lý đơn hàng (admin/nhân viên)
- Cập nhật trạng thái (Pending/Processing/Shipped/Delivered)
- Theo dõi giao hàng
- Hủy đơn (nếu còn Pending)

### Dịch Vụ Cho Thuê Camera

- Quản lý danh sách camera cho thuê
- Đặt dịch vụ (khách hàng)
- Xác nhận yêu cầu (nhân viên)
- Quản lý giá thuê (ngày, tuần, tháng)
- Xem lịch sử cho thuê

### Tính Năng Khác

- Đánh giá & bình luận sản phẩm
- Liên hệ (form gửi tin nhắn)
- Quản lý tài khoản cá nhân
- Thông báo email
- Dashboard với thống kê
- Responsive design (di động friendly)
- Hỗ trợ tiếng Việt

## Mô Hình Dữ Liệu

### Bảng Users

```
id (INT, Primary Key)
username (VARCHAR)
email (VARCHAR, Unique)
password (VARCHAR - mã hóa)
full_name (VARCHAR)
phone (VARCHAR)
role (ENUM: customer/employee/admin)
address (TEXT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Bảng Products

```
id (INT, Primary Key)
name (VARCHAR)
description (TEXT)
price (DECIMAL)
cost_price (DECIMAL)
quantity (INT)
category_id (INT, Foreign Key)
brand_id (INT, Foreign Key)
sku (VARCHAR)
image_url (VARCHAR)
technical_specs (JSON)
status (ENUM: active/inactive)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Bảng Categories

```
id (INT, Primary Key)
name (VARCHAR)
description (TEXT)
image_url (VARCHAR)
created_at (TIMESTAMP)
```

### Bảng Brands

```
id (INT, Primary Key)
name (VARCHAR)
description (TEXT)
image_url (VARCHAR)
created_at (TIMESTAMP)
```

### Bảng Orders

```
id (INT, Primary Key)
user_id (INT, Foreign Key)
total_amount (DECIMAL)
shipping_address (TEXT)
payment_method (VARCHAR)
status (ENUM: pending/processing/shipped/delivered/cancelled)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Bảng OrderItems

```
id (INT, Primary Key)
order_id (INT, Foreign Key)
product_id (INT, Foreign Key)
quantity (INT)
unit_price (DECIMAL)
```

### Bảng Bookings

```
id (INT, Primary Key)
user_id (INT, Foreign Key)
product_id (INT, Foreign Key)
rental_start_date (DATE)
rental_end_date (DATE)
rental_period_type (ENUM: daily/weekly/monthly)
total_price (DECIMAL)
status (ENUM: pending/confirmed/cancelled)
created_at (TIMESTAMP)
```

### Bảng Contacts

```
id (INT, Primary Key)
name (VARCHAR)
email (VARCHAR)
subject (VARCHAR)
message (TEXT)
status (ENUM: new/read/replied)
created_at (TIMESTAMP)
```

### Bảng Cart

```
id (INT, Primary Key)
user_id (INT, Foreign Key)
product_id (INT, Foreign Key)
quantity (INT)
created_at (TIMESTAMP)
```

## Yêu Cầu Giao Diện Người Dùng

- Thiết kế **Responsive** (desktop, tablet, mobile)
- Giao diện **hiện đại và trực quan**
- Tải trang **nhanh**
- **Navigation** dễ sử dụng
- Chỉ báo **trạng thái sản phẩm** rõ ràng
- **Giỏ hàng** dễ quản lý
- Hỗ trợ **tiếng Việt** đầy đủ
- **Tìm kiếm** mạnh mẽ
- **Rating & Review** sản phẩm
- **Thông báo email** khi có cập nhật

## Yêu Cầu Bảo Mật

- Mã hóa mật khẩu (password_hash)
- Quản lý phiên làm việc an toàn
- Xác thực đầu vào (input validation)
- Bảo vệ CSRF
- Kiểm soát truy cập dựa trên vai trò (RBAC)
- Bảo vệ dữ liệu nhạy cảm
- Ghi log hoạt động quan trọng
- HTTPS (khuyến nghị cho môi trường sản xuất)

## Cấu Trúc Dự Án

```
camera/
├── admin/                    # Panel quản trị
│   ├── bookings.php
│   ├── brands.php
│   ├── categories.php
│   ├── config.php
│   ├── contact-detail.php
│   ├── contacts.php
│   ├── customers.php
│   ├── index.php            # Dashboard
│   ├── login.php
│   ├── logout.php
│   ├── orders.php
│   ├── product-add.php
│   ├── product-edit.php
│   ├── products.php
│   ├── profile.php
│   ├── settings.php
│   ├── users.php
│   ├── css/
│   ├── includes/
│   └── js/
├── api/                     # API endpoints
│   ├── add-to-cart.php
│   ├── admin-management.php
│   ├── change-password.php
│   ├── clear-cart.php
│   ├── confirm-transfer.php
│   ├── google-login.php
│   ├── process-checkout.php
│   ├── remove-from-cart.php
│   ├── save-shipping-info.php
│   ├── service-booking.php
│   └── update-cart.php
├── auth/                    # Xác thực
│   └── auth.php
├── classes/                 # Business Logic
│   ├── Booking.php
│   ├── Brand.php
│   ├── Cart.php
│   ├── Page.php
│   ├── Payment.php
│   ├── Product.php
│   ├── ProductFilter.php
│   ├── Request.php
│   ├── Service.php
│   ├── ServiceBooking.php
│   ├── ShippingInfo.php
│   └── TransferPayment.php
├── config/                  # Cấu hình
│   └── database.php
├── controllers/             # Điều khiển logic
│   ├── CartController.php
│   └── ProductController.php
├── css/                     # Stylesheet
│   ├── style.css
│   ├── products.css
│   ├── cart.css
│   ├── checkout.css
│   └── ...
├── database/                # SQL & Database
│   ├── camera_db.sql
│   └── add_google_uid.sql
├── includes/                # Header, Footer
│   ├── header.php
│   └── footer.php
├── public/                  # Thư mục uploads
│   └── (hình ảnh sản phẩm)
├── about.php
├── booking.php
├── cart.php
├── checkout.php
├── contact.php
├── index.php
├── login.php
├── logout.php
├── order-success.php
├── products.php
├── profile.php
├── register.php
├── service.php
├── transfer-payment.php
├── README.md
└── useguide.md
```

## Chi Tiết Triển Khai

- Kiến trúc **MVC** (Model-View-Controller)
- **PDO** cho tương tác cơ sở dữ liệu an toàn
- Định tuyến URL sạch thông qua `.htaccess`
- Tách biệt logic nghiệp vụ khỏi giao diện
- **Responsive Design** với Bootstrap/CSS
- Font Awesome 6.5.1 cho icon
- AOS (Animate On Scroll) 2.3.1 cho hiệu ứng
- Tích hợp VNPay, chuyển khoản ngân hàng
- Google OAuth 2.0 cho đăng nhập

## Các Tuyến Đường (Routes) Chính

### Routes Công Khai

- `/index.php` - Trang chủ
- `/products.php` - Danh sách sản phẩm
- `/service.php` - Dịch vụ cho thuê
- `/about.php` - Giới thiệu
- `/contact.php` - Liên hệ
- `/login.php` - Đăng nhập
- `/register.php` - Đăng ký
- `/cart.php` - Giỏ hàng
- `/checkout.php` - Thanh toán
- `/profile.php` - Hồ sơ cá nhân

### Routes Admin

- `/admin/index.php` - Dashboard
- `/admin/products.php` - Quản lý sản phẩm
- `/admin/categories.php` - Quản lý danh mục
- `/admin/brands.php` - Quản lý hãng
- `/admin/orders.php` - Quản lý đơn hàng
- `/admin/bookings.php` - Quản lý cho thuê
- `/admin/users.php` - Quản lý người dùng
- `/admin/customers.php` - Quản lý khách hàng
- `/admin/contacts.php` - Quản lý liên hệ
- `/admin/settings.php` - Cấu hình hệ thống

## Hướng Dẫn Cài Đặt

### 1. Chuẩn Bị Môi Trường

```bash
# Đảm bảo PHP 7.4+ và MySQL 5.7+ đã được cài đặt
php -v
mysql -v
```

### 2. Cấu Hình Cơ Sở Dữ Liệu

```bash
# Tạo database
mysql -u root < database/camera_db.sql

# Hoặc nhập thủ công qua phpMyAdmin
# 1. Tạo database "camera_db"
# 2. Import file database/camera_db.sql
```

### 3. Cấu Hình File config/database.php

```php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = ''; // Nhập mật khẩu nếu có
$dbName = 'camera_db';
```

### 4. Cấu Hình Web Server

```apache
# Chỉnh DocumentRoot trong httpd.conf
DocumentRoot "C:/xampp/htdocs/camera/camera"
<Directory "C:/xampp/htdocs/camera/camera">
    AllowOverride All
    Require all granted
</Directory>
```

### 5. Khởi Động Server

```bash
# XAMPP
php -S localhost:8000

# Hoặc mở trong trình duyệt
http://localhost/camera
```

### 6. Tài Khoản Mặc Định

```
Admin:
- username: admin
- Mật khẩu: password
```

## Hướng Dẫn Sử Dụng

Tài liệu hướng dẫn chi tiết cách sử dụng hệ thống có thể được tìm thấy trong file **`useguide.md`**.

## Các Công Nghệ Sử Dụng

| Công Nghệ | Phiên Bản | Mục Đích |
|-----------|-----------|---------|
| PHP | 7.4+ | Backend |
| MySQL | 5.7+ | Database |
| HTML | - | Markup |
| CSS | - | Styling |
| JavaScript | - | Frontend Logic |
| Bootstrap | 5.x | CSS Framework |
| Font Awesome | 6.5.1 | Icons |
| AOS | 2.3.1 | Animations |
| VNPay | - | Payment Gateway |
| Google OAuth | 2.0 | Authentication |

## Đóng Góp


## Thông Tin Liên Hệ

- **Email:** support@camerastore.com
- **Website:** https://camerastore.com
- **Địa chỉ:** [Thêm địa chỉ cửa hàng]
- **Số điện thoại:** 
---

**Phiên bản:** 1.0.0
