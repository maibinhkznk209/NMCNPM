# Hệ Thống Quản Lý Thư Viện

Hệ thống quản lý thư viện được xây dựng bằng Laravel framework để quản lý sách, độc giả, hồ sơ mượn trả và thanh toán tiền phạt.

## Tính Năng

- **Quản Lý Người Dùng**: Vai trò Admin và Thủ thư với các quyền khác nhau
- **Quản Lý Sách**: Thêm, sửa, xóa sách với thông tin tác giả và nhà xuất bản
- **Quản Lý Độc Giả**: Quản lý hồ sơ độc giả và loại độc giả
- **Hệ Thống Mượn Trả**: Theo dõi việc mượn và trả sách
- **Quản Lý Tiền Phạt**: Xử lý tiền phạt quá hạn và thanh toán
- **Báo Cáo**: Tạo các báo cáo và thống kê đa dạng

## Yêu Cầu Hệ Thống

### PHP
- **Phiên bản**: PHP >= 8.2
- **Cài đặt**: Tải và cài đặt qua XAMPP tại https://www.apachefriends.org/download.html
- **Lưu ý**: Ghi nhớ đường dẫn cài đặt để cài đặt Composer

### Composer
- **Tải xuống**: https://getcomposer.org/download/
- **Cấu hình**: Chọn đường dẫn thư mục chứa PHP từ XAMPP và thêm PHP vào PATH

## Hướng Dẫn Cài Đặt

### 1. Cấu Hình XAMPP

1. Mở XAMPP Control Panel
2. Trong dòng của module Apache, nhấn vào nút Config và chọn PHP (php.ini)
3. Trong file php.ini vừa mở, nhấn Ctrl + F để tìm kiếm 2 dòng sau:
   - `;extension=gd`
   - `;extension=zip`
4. Xóa dấu chấm phẩy (;) ở đầu 2 dòng đó để kích hoạt extension
5. Lưu file lại (Ctrl + S)

### 2. Thiết Lập Dự Án

#### Bước 1: Clone Repository
```bash
git https://github.com/maibinhkznk209/NMCNPM
cd NMCNPM
```

#### Bước 2: Cài Đặt Dependencies
```bash
composer install
```

#### Bước 3: Cấu Hình Environment
1. Copy file environment:
```bash
copy .env.example .env
```

2. Cập nhật thông tin kết nối CSDL trong file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=QLTVS
DB_USERNAME=root
DB_PASSWORD=
```

#### Bước 4: Thiết Lập Cơ Sở Dữ Liệu

1. **Khởi động MySQL trong XAMPP**

2. **Tạo Database**:
```bash
# Di chuyển đến thư mục MySQL bin (điều chỉnh đường dẫn theo vị trí cài đặt XAMPP)
cd C:\xampp\mysql\bin
.\mysql -u root -p
```
**Lưu ý**: Điều chỉnh đường dẫn `C:\xampp\mysql\bin` theo vị trí cài đặt XAMPP.
Nhấn Enter (mật khẩu mặc định của XAMPP là trống)

```sql
CREATE DATABASE QLTVS CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
exit;
```

3. **Chạy Migrations và Seeders**:
```bash
# Tạo APP_KEY
php artisan key:generate

# Tạo các bảng CSDL

php artisan migrate:fresh

# Tạo dữ liệu mẫu

php artisan db:seed

# Chạy tests (tùy chọn)
php artisan test
```

#### Bước 5: Khởi Động Ứng Dụng
```bash
php artisan serve
```

Ứng dụng sẽ khởi động và có thể truy cập tại: **http://127.0.0.1:8000**

## Thông Tin Truy Cập

### URL Đăng Nhập
http://127.0.0.1:8000/login

### Tài Khoản Mặc Định

| Vai Trò | Email | Mật Khẩu |
|---------|-------|----------|
| Admin | admin@library.com | 123456 |
| Thủ Thư | librarian@library.com | 123456 |

## Cấu Trúc Dự Án

```
NMCNPM/
├── app/
│   ├── Http/Controllers/    # Controllers của ứng dụng
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic services
│   └── Providers/          # Service providers
├── database/
│   ├── migrations/         # Database migrations
│   ├── seeders/           # Database seeders
│   └── factories/         # Model factories
├── resources/
│   └── views/             # Blade templates
├── routes/                 # Application routes
├── public/                # Public assets
└── tests/                 # Application tests
```

## Tính Năng Chính

### Dành Cho Admin
- Quản lý tất cả người dùng và vai trò trong hệ thống
- Cấu hình quy định thư viện
- Tạo báo cáo toàn diện
- Giám sát hoạt động hệ thống

### Dành Cho Thủ Thư
- Quản lý sách và kho sách
- Xử lý mượn trả sách
- Xử lý thanh toán tiền phạt
- Quản lý thông tin độc giả

## Công Nghệ Sử Dụng

- **Backend**: Laravel 12.0
- **Frontend**: Blade templates với Bootstrap
- **Database**: MySQL
- **Testing**: PHPUnit
- **Package Manager**: Composer


