<?php
// File: includes/footer.php
// Chức năng: Hiển thị chân trang và nạp các tệp JavaScript
?>
</main> <!-- Đóng thẻ main từ header.php -->

<footer class="bg-dark text-white pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-table-tennis-paddle-ball me-2"></i>BADMINTON BOOKING</h5>
                <p class="text-secondary small">
                    Hệ thống đặt sân cầu lông online nhanh chóng, tiện lợi và uy tín hàng đầu. Giúp bạn dễ dàng tìm kiếm và giữ chỗ sân tập yêu thích.
                </p>
            </div>
            
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Liên kết nhanh</h5>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2"><a href="home/index" class="text-decoration-none text-secondary"><i class="fa-solid fa-angle-right me-1"></i> Trang chủ</a></li>
                    <li class="mb-2"><a href="home/courts" class="text-decoration-none text-secondary"><i class="fa-solid fa-angle-right me-1"></i> Danh sách sân</a></li>
                    <li class="mb-2"><a href="auth/login" class="text-decoration-none text-secondary"><i class="fa-solid fa-angle-right me-1"></i> Đăng nhập</a></li>
                    <li class="mb-2"><a href="auth/register" class="text-decoration-none text-secondary"><i class="fa-solid fa-angle-right me-1"></i> Đăng ký</a></li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Thông tin liên hệ</h5>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-success"></i> 123 Đường Nguyễn Văn Linh, Quận 7, TP.HCM</li>
                    <li class="mb-2"><i class="fa-solid fa-phone me-2 text-success"></i> Hotline: 0339936910</li>
                    <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-success"></i> Email: support@badmintonbooking.vn</li>
                </ul>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white fs-5" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-white fs-5" aria-label="Zalo"><i class="fa-brands fa-zalo"></i></a>
                    <a href="#" class="text-white fs-5" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="text-center text-secondary small">
            &copy; <?= date('Y') ?> BADMINTON BOOKING. Đồ án môn học PHP2.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>