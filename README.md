# Badminton Booking

Hệ thống đặt sân cầu lông trực tuyến cho người dùng, giúp khách hàng tìm sân, xem thông tin chi tiết, đặt lịch và quản lý các đơn đặt sân một cách nhanh chóng, tiện lợi. Dự án hỗ trợ cả giao diện người dùng lẫn trang quản trị dành cho admin.

## 1. Giới thiệu

Badminton Booking là một ứng dụng web xây dựng bằng PHP thuần, sử dụng MySQL làm cơ sở dữ liệu và Bootstrap cho giao diện. Project này phù hợp cho việc học tập, demo hoặc mở rộng thành hệ thống đặt sân thực tế.

## 2. Tính năng chính

- Đăng ký / đăng nhập / đăng xuất
- Khôi phục mật khẩu bằng email
- Xem danh sách sân cầu lông
- Xem chi tiết từng sân
- Đặt sân theo ngày, khung giờ và giá
- Theo dõi lịch sử đặt sân của người dùng
- Quản lý hồ sơ cá nhân
- Trang quản trị admin:
  - Quản lý đơn đặt sân
  - Quản lý sân và giá
  - Quản lý dịch vụ
  - Quản lý người dùng
- Gửi email thông báo qua PHPMailer

## 3. Công nghệ sử dụng

- PHP 7/8
- MySQL / MariaDB
- PDO (Database Access)
- Composer
- Bootstrap 5
- PHPMailer
- JavaScript + jQuery (nếu có trong giao diện)

## 4. Cấu trúc thư mục

```text
badminton-booking/
├── admin/                  # Trang quản trị admin
├── app/
│   ├── controllers/        # Controller xử lý logic
│   ├── core/               # Core: database, mailer
│   └── models/             # Model dữ liệu
├── assets/                 # CSS, JS, hình ảnh
├── config/                 # Cấu hình hệ thống
├── includes/               # Header/footer/auth
├── vendor/                 # Thư viện Composer
├── autoload.php            # Tải autoload
├── booking.php             # Trang đặt sân
├── court-detail.php        # Chi tiết sân
├── courts.php              # Danh sách sân
├── database.sql            # File SQL (nếu có schema)
├── forgot-password.php     # Quên mật khẩu
├── index.php               # Trang chủ
├── login.php               # Đăng nhập
├── logout.php              # Đăng xuất
├── my-bookings.php         # Lịch sử đặt sân
├── profile.php             # Trang cá nhân
├── register.php            # Đăng ký
├── webhook.php             # Endpoint webhook / xử lý bổ sung
├── composer.json           # Cấu hình Composer
├── composer.lock           # Lock file Composer
└── README.md               # Tài liệu dự án
```

## 5. Yêu cầu hệ thống

- XAMPP / WAMP / MAMP hoặc Apache + MySQL trên máy local
- PHP 7.4 hoặc cao hơn
- MySQL 5.7+ hoặc MariaDB 10+
- Composer
- Trình duyệt hiện đại (Chrome, Edge, Firefox)

## 6. Cài đặt và chạy dự án

### Bước 1: Clone / sao chép project

```bash
git clone <link-repository>
cd badminton-booking
```

### Bước 2: Khởi động môi trường

- Khởi động Apache và MySQL trong XAMPP/WAMP
- Copy thư mục project vào thư mục `htdocs` nếu dùng XAMPP

Ví dụ:

```text
C:\xampp\htdocs\badminton-booking
```

### Bước 3: Tạo database

Tạo database MySQL với tên:

```sql
CREATE DATABASE badminton_booking;
```

Nếu có file `database.sql`, import schema vào database vừa tạo.

### Bước 4: Cấu hình kết nối database

Mở file `config/database.php` và chỉnh sửa thông tin kết nối nếu cần:

```php
$host = 'localhost';
$dbname = 'badminton_booking';
$username = 'root';
$password = '';
```

### Bước 5: Cài đặt dependency

```bash
composer install
```

### Bước 6: Chạy ứng dụng

Truy cập trong trình duyệt:

```text
http://localhost/badminton-booking/
```

Nếu muốn chạy local server PHP:

```bash
php -S localhost:8000
```

Sau đó truy cập:

```text
http://localhost:8000/
```

## 7. Tài khoản Admin

Sau khi hệ thống đã hoạt động, bạn có thể đăng nhập với tài khoản admin nếu đã được tạo trong cơ sở dữ liệu. Một số hệ thống admin thường có cột `role` hoặc `user_role` với giá trị `admin`.

Ví dụ dữ liệu mẫu:

```sql
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', 'hashed_password_here', 'admin');
```

## 8. Quy trình sử dụng

### Người dùng

1. Đăng ký tài khoản
2. Đăng nhập
3. Chọn sân và khung giờ
4. Xác nhận đặt sân
5. Theo dõi trạng thái đơn đặt

### Admin

1. Đăng nhập vào trang quản trị
2. Xem danh sách đơn đặt sân
3. Duyệt hoặc hủy đơn
4. Quản lý sân, dịch vụ và người dùng

## 9. Lưu ý

- Dự án này là phiên bản PHP native, không dùng framework chính thống như Laravel hoặc CodeIgniter.
- Nếu bạn muốn triển khai lên môi trường production, cần bổ sung:
  - Validate dữ liệu đầu vào
  - Bảo mật session / CSRF
  - Mã hóa password bằng password_hash()
  - Kiểm tra quyền truy cập chặt chẽ hơn
  - Backup database định kỳ

## 10. Đóng góp và phát triển

Bạn có thể mở rộng dự án bằng cách:

- Thêm chức năng thanh toán online
- Tích hợp lịch đặt real-time
- Thêm quản lý khuyến mãi
- Tạo API RESTful
- Tối ưu giao diện mobile

## 11. License

Dự án này phục vụ mục đích học tập và demo. Bạn có thể tùy chỉnh và phát triển theo nhu cầu cá nhân hoặc doanh nghiệp.

## 12. Liên hệ

Nếu bạn muốn tiếp tục phát triển dự án này, có thể làm thêm các module như:

- Quản lý nhân viên / lễ tân
- Xem doanh thu theo ngày
- Báo cáo đặt sân
- Tích hợp Google Calendar / Zalo / SMS

---

Nếu bạn muốn, mình có thể viết tiếp cho bạn một phiên bản README chuyên nghiệp hơn theo kiểu portfolio / GitHub, hoặc phiên bản README tiếng Anh để đăng lên GitHub.
