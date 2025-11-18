# Tài liệu hướng dẫn sử dụng hệ thống Website Bán Hàng Camera

## 1. Giới thiệu

Hệ thống Website Bán Hàng Camera là nền tảng e-commerce hỗ trợ khách hàng xem catalog sản phẩm camera, ống kính, phụ kiện; đặt hàng trực tuyến, theo dõi đơn hàng, và sử dụng dịch vụ cho thuê/bảo dưỡng camera.

Đồng thời, hệ thống cho phép quản trị viên quản lý nội dung website, quản lý sản phẩm, danh mục, đơn hàng, khách hàng, dịch vụ và thanh toán.

Hệ thống gồm ba vai trò chính:
- **Khách hàng** - Mua hàng, đặt dịch vụ, xem lịch sử đơn
- **Nhân viên/Nhân viên bán hàng** - Quản lý sản phẩm, dịch vụ, đơn hàng
- **Quản trị viên (Admin)** - Quản lý hệ thống toàn bộ

## 2. Chức năng theo vai trò

### 2.1. Khách hàng

#### Xem trang chủ
- **Đường dẫn:** `/index.php`
- **Chức năng:**
  - Xem banner sản phẩm nổi bật
  - Xem hình ảnh sản phẩm và giới thiệu
  - Xem thông tin liên hệ cửa hàng
  - Xem các dịch vụ chính

#### Xem danh sách sản phẩm
- **Đường dẫn:** `/products.php`
- **Chức năng:**
  - Tìm kiếm sản phẩm theo tên
  - Xem danh sách sản phẩm theo danh mục:
    - Máy ảnh DSLR
    - Máy ảnh Mirrorless
    - Ống kính
    - Phụ kiện Camera
  - Xem chi tiết sản phẩm: tên, mô tả, giá, hình ảnh, thông số kỹ thuật
  - Lọc sản phẩm theo danh mục, hãng, giá
  - Xem rating và đánh giá từ khách hàng khác

#### Dịch vụ cho thuê camera
- **Đường dẫn:** `/service.php`
- **Chức năng:**
  - Xem danh sách dịch vụ cho thuê camera
  - Xem chi tiết từng dịch vụ: giá thuê, hạn sử dụng, điều khoản
  - Xem giá thuê theo ngày, tuần, tháng
  - Đặt dịch vụ cho thuê trực tuyến

#### Giỏ hàng & Thanh toán
- **Đường dẫn:** `/cart.php`, `/checkout.php`
- **Chức năng:**
  - Thêm sản phẩm vào giỏ hàng
  - Xem và chỉnh sửa giỏ hàng (tăng/giảm số lượng, xóa sản phẩm)
  - Nhập thông tin giao hàng (tên, địa chỉ, số điện thoại)
  - Chọn phương thức thanh toán:
    - Thanh toán tại cửa hàng
    - VNPay
    - Chuyển khoản ngân hàng
  - Xác nhận đơn hàng

#### Lịch sử đơn hàng & Dịch vụ
- **Đường dẫn:** `/profile.php` → "Lịch sử đơn hàng"
- **Chức năng:**
  - Xem danh sách đơn hàng đã đặt
  - Xem chi tiết từng đơn hàng: trạng thái (Pending/Processing/Shipped/Delivered), ngày đặt, tổng tiền
  - Theo dõi tình trạng giao hàng
  - Xem lịch sử dịch vụ cho thuê camera
  - Hủy đơn hàng (nếu còn ở trạng thái Pending)

#### Tài khoản cá nhân
- **Đường dẫn:** `/profile.php`
- **Chức năng:**
  - Xem thông tin tài khoản: tên, email, số điện thoại
  - Cập nhật thông tin cá nhân
  - Thay đổi mật khẩu
  - Quản lý địa chỉ giao hàng

#### Đăng ký & Đăng nhập
- **Đường dẫn:** `/register.php`, `/login.php`
- **Chức năng:**
  - Đăng ký tài khoản mới
  - Đăng nhập với email/mật khẩu
  - Đăng nhập với Google
  - Quên mật khẩu

### 2.2. Nhân viên / Nhân viên bán hàng

#### Quản lý sản phẩm
- **Đường dẫn:** `/admin/products.php`
- **Chức năng:**
  - Xem danh sách sản phẩm
  - Thêm sản phẩm mới (tên, mô tả, giá, hình ảnh, danh mục, hãng)
  - Sửa thông tin sản phẩm
  - Xóa sản phẩm
  - Quản lý kho hàng (số lượng tồn)

#### Quản lý danh mục
- **Đường dẫn:** `/admin/categories.php`
- **Chức năng:**
  - Xem danh sách danh mục
  - Thêm danh mục mới
  - Sửa danh mục
  - Xóa danh mục

#### Quản lý hãng sản xuất
- **Đường dẫn:** `/admin/brands.php`
- **Chức năng:**
  - Xem danh sách hãng sản xuất
  - Thêm hãng mới
  - Sửa thông tin hãng
  - Xóa hãng

#### Quản lý dịch vụ
- **Đường dẫn:** `/admin/bookings.php` (Dịch vụ cho thuê)
- **Chức năng:**
  - Xem danh sách dịch vụ cho thuê
  - Thêm dịch vụ mới
  - Chỉnh sửa dịch vụ
  - Xóa dịch vụ
  - Quản lý giá thuê theo chu kỳ

#### Quản lý đơn hàng
- **Đường dẫn:** `/admin/orders.php`
- **Chức năng:**
  - Xem danh sách đơn hàng
  - Xem chi tiết đơn hàng
  - Cập nhật trạng thái đơn (Processing, Shipped, Delivered)
  - Tính tiền vận chuyển
  - In phiếu gửi hàng
  - Hủy đơn hàng

#### Quản lý yêu cầu cho thuê camera
- **Đường dẫn:** `/admin/bookings.php` (Yêu cầu cho thuê)
- **Chức năng:**
  - Xem danh sách yêu cầu cho thuê
  - Xác nhận hoặc từ chối yêu cầu
  - Xem chi tiết từng yêu cầu
  - Ghi chú về tình trạng sản phẩm cho thuê

#### Quản lý liên hệ từ khách
- **Đường dẫn:** `/admin/contacts.php`
- **Chức năng:**
  - Xem danh sách tin nhắn từ khách hàng
  - Xem chi tiết từng tin nhắn
  - Trả lời tin nhắn
  - Đánh dấu đã xử lý

### 2.3. Quản trị viên (Admin)

#### Quản lý người dùng
- **Đường dẫn:** `/admin/users.php`
- **Chức năng:**
  - Xem danh sách tất cả người dùng (Admin/Nhân viên/Khách hàng)
  - Tạo tài khoản admin/nhân viên mới
  - Sửa thông tin tài khoản
  - Xóa tài khoản
  - Phân quyền người dùng

#### Quản lý khách hàng
- **Đường dẫn:** `/admin/customers.php`
- **Chức năng:**
  - Xem danh sách khách hàng
  - Xem chi tiết khách hàng: lịch sử mua, đánh giá, liên hệ
  - Theo dõi khách hàng VIP
  - Gửi thông báo/khuyến mãi

#### Quản lý cấu hình
- **Đường dẫn:** `/admin/settings.php`
- **Chức năng:**
  - Cập nhật thông tin cửa hàng: địa chỉ, số điện thoại, email
  - Cập nhật giờ hoạt động
  - Quản lý link mạng xã hội
  - Cấu hình phương thức thanh toán
  - Quản lý chi phí vận chuyển

#### Quản lý hồ sơ Admin
- **Đường dẫn:** `/admin/profile.php`
- **Chức năng:**
  - Xem thông tin tài khoản
  - Cập nhật thông tin cá nhân
  - Thay đổi mật khẩu
  - Xem lịch hoạt động

## 3. Hướng dẫn xem và mua sản phẩm

### 3.1. Xem danh sách sản phẩm
- **Đường dẫn:** `/products.php`
- **Cách sử dụng:**
  1. Sử dụng thanh tìm kiếm để tìm sản phẩm theo tên
  2. Chọn danh mục sản phẩm (nếu cần)
  3. Chọn hãng sản xuất (nếu cần)
  4. Xem danh sách sản phẩm dạng card
  5. Nhấn vào sản phẩm để xem chi tiết

### 3.2. Xem chi tiết sản phẩm
- **Đường dẫn:** `/products.php` (nhấn vào sản phẩm)
- **Hiển thị:**
  - Hình ảnh sản phẩm (có zoom)
  - Tên, hãng, mô tả chi tiết
  - Giá và tình trạng kho
  - Thông số kỹ thuật
  - Rating và bình luận từ khách hàng
  - Nút "Thêm vào giỏ hàng"

### 3.3. Xem dịch vụ cho thuê
- **Đường dẫn:** `/service.php`
- **Cách sử dụng:**
  1. Xem danh sách dịch vụ camera cho thuê
  2. Xem chi tiết giá thuê: ngày, tuần, tháng
  3. Xem điều khoản sử dụng
  4. Nhấn "Đặt dịch vụ" để đặt lịch

## 4. Hướng dẫn mua hàng

### 4.1. Quy trình mua hàng
1. **Xem sản phẩm:** Vào `/products.php` chọn sản phẩm cần mua
2. **Thêm vào giỏ hàng:** Nhấn "Thêm vào giỏ hàng"
3. **Xem giỏ hàng:** Vào `/cart.php` để xem lại
4. **Thanh toán:** Nhấn "Tiến hành thanh toán"
5. **Nhập thông tin giao hàng:** Điền địa chỉ, số điện thoại
6. **Chọn phương thức thanh toán:**
   - Thanh toán tại cửa hàng
   - VNPay (thẻ ngân hàng)
   - Chuyển khoản ngân hàng
7. **Xác nhận đơn:** Nhấn "Hoàn tất đơn hàng"
8. **Theo dõi đơn:** Vào `/profile.php` để xem trạng thái

### 4.2. Theo dõi đơn hàng
- **Trạng thái đơn hàng:**
  - **Pending:** Chờ xử lý
  - **Processing:** Đang chuẩn bị hàng
  - **Shipped:** Đã gửi hàng
  - **Delivered:** Đã giao hàng
- **Xem chi tiết:** Vào `/profile.php` → "Lịch sử đơn hàng"

### 4.3. Đặt dịch vụ cho thuê camera
- **Đường dẫn:** `/service.php`
- **Quy trình:**
  1. Xem danh sách dịch vụ cho thuê
  2. Chọn camera cần thuê
  3. Chọn thời gian thuê (ngày, tuần, tháng)
  4. Nhấn "Đặt dịch vụ"
  5. Nhập thông tin cá nhân
  6. Chọn phương thức thanh toán
  7. Xác nhận đặt

## 5. Hướng dẫn dành cho Admin

### 5.1. Quản lý sản phẩm
- **Đường dẫn:** `/admin/products.php`
- **Thao tác:**
  - Nhấn **"Thêm sản phẩm"** → nhập: tên, mô tả, giá, chọn danh mục, hãng, upload hình ảnh
  - Nhấn **"Sửa"** để chỉnh sửa thông tin sản phẩm
  - Nhấn **"Xóa"** để loại bỏ sản phẩm

### 5.2. Quản lý danh mục & hãng
- **Danh mục:** `/admin/categories.php`
  - Thêm, sửa, xóa danh mục (DSLR, Mirrorless, Ống kính, Phụ kiện)
- **Hãng:** `/admin/brands.php`
  - Thêm, sửa, xóa hãng (Canon, Nikon, Sony, v.v.)

### 5.3. Quản lý đơn hàng
- **Đường dẫn:** `/admin/orders.php`
- **Thao tác:**
  - Xem danh sách đơn hàng
  - Cập nhật trạng thái đơn
  - In phiếu gửi hàng
  - Hủy đơn hàng nếu cần

### 5.4. Quản lý dịch vụ cho thuê
- **Đường dẫn:** `/admin/bookings.php`
- **Thao tác:**
  - Thêm dịch vụ cho thuê mới
  - Sửa giá thuê, điều khoản
  - Xem yêu cầu cho thuê từ khách
  - Xác nhận hoặc từ chối yêu cầu

### 5.5. Quản lý cấu hình & thông tin
- **Đường dẫn:** `/admin/settings.php`
- **Cho phép cập nhật:**
  - Thông tin cửa hàng: địa chỉ, số điện thoại, email
  - Giờ hoạt động
  - Link mạng xã hội
  - Thông tin thanh toán

### 5.6. Quản lý người dùng
- **Đường dẫn:** `/admin/users.php`
- **Thao tác:**
  - Xem danh sách người dùng
  - Tạo tài khoản mới (Admin/Nhân viên)
  - Sửa thông tin người dùng
  - Xóa tài khoản

## 6. Lưu ý quan trọng

- **Khách hàng phải đăng nhập** trước khi mua hàng hoặc đặt dịch vụ
- **Đơn hàng ở trạng thái Pending** cần nhân viên xác nhận trong 24 giờ
- **Phương thức thanh toán đa dạng:** Hỗ trợ VNPay, chuyển khoản, thanh toán tại cửa hàng
- **Dịch vụ cho thuê camera** cần xác nhận từ cửa hàng trước khi khách nhận hàng
- **Thông tin sản phẩm cần đầy đủ** (hình ảnh, mô tả, thông số) để tăng độ tin cậy
- **Khách hàng có thể hủy đơn** nếu đơn vẫn ở trạng thái Pending
- **Hỗ trợ khách hàng:** Sử dụng trang liên hệ `/contact.php` để gửi tin nhắn
- **Bảo mật tài khoản:** Thay đổi mật khẩu định kỳ, không chia sẻ thông tin đăng nhập
