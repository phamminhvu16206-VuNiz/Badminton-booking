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
- Chọn sân con số 1–5 tại mỗi địa điểm; lịch trùng được kiểm tra riêng từng sân con
- Theo dõi lịch sử đặt sân của người dùng
- Gửi yêu cầu hủy đơn đã thanh toán trong tương lai, kèm lý do; admin hoàn tiền thủ công rồi xác nhận trên hệ thống
- Mã QR định danh đơn đặt sân hiển thị trong email xác nhận và chi tiết lịch sử
- Quản lý hồ sơ cá nhân
- Trang quản trị admin:
  - Quản lý đơn đặt sân
  - Quản lý sân và giá
  - Cập nhật trạng thái sân: đang hoạt động / đang bảo trì
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
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
│       ├── admin/
│       ├── client/
│       └── layouts/
├── core/
│   ├── App.php              # Router
│   └── Controller.php       # Render view / load model
├── public/
│   ├── index.php            # Front Controller duy nhất
│   └── uploads/avatars/
├── assets/                 # CSS, JS, hình ảnh
├── config/                 # Cấu hình hệ thống
├── includes/               # Header/footer/auth
├── vendor/                 # Thư viện Composer
├── autoload.php            # Tải autoload
├── database.sql            # File SQL (nếu có schema)
├── .htaccess               # Rewrite URL
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

`config/database.php` đọc cấu hình từ biến môi trường; nếu không khai báo thì dùng:

```php
$host = 'localhost';
$dbname = 'badminton_booking';
$username = 'root';
$password = '';
```

Có thể đặt `DB_HOST`, `DB_NAME`, `DB_USER` và `DB_PASSWORD` trong môi trường PHP/Apache để không lưu thông tin kết nối trong mã nguồn.

### Bước 4.1: Cập nhật database hiện có

Nếu database đã được tạo trước khi thêm tính năng hủy sân, chạy migration sau **một lần** trên database `badminton_booking`:

```text
database/migrations/20261020_add_booking_cancellation_requests.sql
```

Có thể mở file trong phpMyAdmin, chọn database `badminton_booking`, vào tab **Import** và thực thi file SQL. Migration tạo trạng thái yêu cầu hủy, lý do hủy và thời điểm yêu cầu/xác nhận hoàn tiền. Với database mới, import schema mới nhất trước; không chạy lại migration nếu các cột này đã tồn tại.

Để lưu thông tin ngân hàng người dùng gửi kèm yêu cầu hoàn tiền, chạy tiếp migration `database/migrations/20261021_add_cancellation_bank_details_to_bookings.sql` một lần **sau** migration trên. Biểu mẫu yêu cầu ngân hàng, tên chủ tài khoản và số tài khoản; admin xem các thông tin này trong trang yêu cầu hủy để chuyển khoản thủ công.

### Bước 5: Cài đặt dependency

```bash
composer install
```

### Bước 6: Chạy ứng dụng

Truy cập trong trình duyệt:

```text
http://localhost/badminton-booking/
```

Đảm bảo Apache đã bật `mod_rewrite` và cho phép `.htaccess` (`AllowOverride All`) để URL được chuyển qua front controller.

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
5. Theo dõi trạng thái đơn đặt; với đơn đã xác nhận và chưa tới giờ chơi, có thể gửi yêu cầu hủy kèm lý do

### Admin

1. Đăng nhập vào trang quản trị
2. Xem danh sách đơn đặt sân
3. Xử lý yêu cầu hủy tại **Yêu cầu hủy / hoàn tiền**: chuyển tiền hoàn cho khách bên ngoài hệ thống trước, sau đó bấm **Đã hoàn tiền** để cập nhật đơn thành đã hủy
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

## 13. Front Controller và URL

Apache rewrite mọi request ứng dụng qua `public/index.php`. Các URL MVC chính:

- `/home/index`, `/home/courts`, `/home/detail/{id}`
- `/auth/login`, `/auth/register`, `/auth/forgot-password`, `/auth/logout`
- `/booking/index`, `/booking/mine`
- `/user/profile`
- `/admin/bookings`, `/admin/courts`, `/admin/services`, `/admin/users`

Các URL PHP cũ vẫn được ánh xạ để bảo toàn bookmark, biểu mẫu, webhook và tích hợp hiện có. Kết nối PDO lấy cấu hình từ `config/database.php`; có thể đặt `DB_HOST`, `DB_NAME`, `DB_USER` và `DB_PASSWORD` trong môi trường. Ảnh avatar mới được lưu tại `public/uploads/avatars/`.

## 14. Cấu hình webhook thanh toán SePay

Webhook nhận giao dịch tại:

```text
https://<domain-cong-khai>/badminton-booking/booking/webhook
```

Máy local `localhost` không nhận được callback từ SePay. Khi thử nghiệm, có thể mở tunnel HTTPS bằng ngrok:

```powershell
ngrok http 80
```

Thay `<domain-cong-khai>` bằng hostname HTTPS ngrok cung cấp. Đường dẫn `/badminton-booking/booking/webhook` phải được giữ nguyên.

Trong SePay, vào **Webhooks → Thêm webhook**, cấu hình:

- URL: URL webhook công khai ở trên.
- Loại giao dịch: tiền vào.
- Phương thức xác thực: **HMAC-SHA256**.
- Secret key: tạo trong SePay, sau đó lưu vào biến môi trường `SEPAY_WEBHOOK_SECRET` trên máy chạy Apache. Không đưa secret vào mã nguồn, URL hoặc chat.

Trên Windows có thể lưu secret cho tài khoản người dùng hiện tại bằng PowerShell:

```powershell
[Environment]::SetEnvironmentVariable('SEPAY_WEBHOOK_SECRET', '<secret-tu-SePay>', 'User')
```

Đóng và mở lại XAMPP Control Panel, sau đó restart Apache để tiến trình Apache nhận biến môi trường mới. Trên production, cấu hình secret bằng cơ chế quản lý secret của hosting/server và bắt buộc dùng HTTPS.

Nội dung chuyển khoản cần có mã `DATSAN <mã-đơn>`, ví dụ `DATSAN 12`. Ứng dụng kiểm tra chữ ký HMAC, timestamp, loại giao dịch và số tiền phải khớp đơn trước khi đổi đơn đang chờ sang `confirmed`. Giao dịch không khớp hoặc tới sau khi đơn đã bị hủy sẽ không tự xác nhận; cần đối soát thủ công. Có thể dùng nút **Gửi thử** trong SePay để kiểm tra endpoint, nhưng chỉ giao dịch thực có đúng mã đơn và số tiền mới xác nhận đơn.

## 15. Sân con tại mỗi địa điểm

Mỗi địa điểm có 5 sân con. Biểu mẫu đặt sân yêu cầu chọn sân số 1–5; tình trạng trống/đã đặt được tính độc lập theo sân con. Trang quản trị đơn đặt và lịch sử khách hàng hiển thị số sân đã chọn.

Sau khi cập nhật mã nguồn trên một cơ sở dữ liệu đã có bảng `bookings`, chạy migration một lần:

```text
database/migrations/20261019_add_court_number_to_bookings.sql
```

Các đơn cũ được gán mặc định vào Sân 1 để giữ nguyên dữ liệu hiện có.

Nếu bạn muốn, mình có thể viết tiếp cho bạn một phiên bản README chuyên nghiệp hơn theo kiểu portfolio / GitHub, hoặc phiên bản README tiếng Anh để đăng lên GitHub.
