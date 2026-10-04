<?php
// includes/header.php
require_once __DIR__ . '/../config/db.php';
$currentCustomer = $_SESSION['customer_user'] ?? null;
$currentUser = $currentCustomer;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'منصة إدارة الحجوزات الذكية' ?></title>
    <!-- خط Cairo وأيقونات Font Awesome 6 الحديثة -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- شريط التنقل العلوي للعملاء والزوار -->
<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="brand-logo">
            <div class="brand-icon-box">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <span>نظام الحجوزات</span>
        </a>

        <div class="nav-links">
            <a href="index.php" class="nav-link">
                <i class="fa-solid fa-house"></i>
                <span>الرئيسية</span>
            </a>

            <?php if ($currentCustomer): ?>
                <a href="my-bookings.php" class="nav-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>حجوزاتي</span>
                </a>

                <span class="nav-link" style="color: var(--text-muted);">
                    <i class="fa-regular fa-user"></i>
                    <span>مرحباً، <strong><?= htmlspecialchars($currentCustomer['name']) ?></strong></span>
                </span>
                <a href="#" id="logout-btn" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>خروج</span>
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-primary btn-sm open-login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>دخول / حساب جديد</span>
                </button>
            <?php endif; ?>

            <?php if (isAdminLoggedIn()): ?>
                <a href="admin/index.php" target="_blank" class="nav-link" style="color: var(--primary); font-weight: 700; background: var(--primary-subtle); border-radius: 8px;">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>لوحة الإدارة ⚡</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- نافذة تسجيل الدخول / التسجيل المنبثقة للعملاء -->
<div class="modal-overlay" id="auth-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title" style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-lock" style="color: var(--primary);"></i>
                <span>تسجيل الدخول كعميل</span>
            </h3>
            <button type="button" class="modal-close" id="close-auth-btn">&times;</button>
        </div>

        <!-- نموذج تسجيل الدخول -->
        <form id="login-form">
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>دخول</span>
            </button>
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                ليس لديك حساب؟ <a href="#" id="switch-to-register" style="color: var(--primary); font-weight: 700;">إنشاء حساب جديد</a>
            </p>
        </form>

        <!-- نموذج إنشاء حساب جديد -->
        <form id="register-form" style="display: none;">
            <div class="form-group">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" class="form-input" placeholder="مثال: أحمد محمد" required>
            </div>
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">رقم الهاتف</label>
                <input type="tel" name="phone" class="form-input" placeholder="010XXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور (6 أحرف فأكثر)</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-user-plus"></i>
                <span>إنشاء الحساب</span>
            </button>
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                لديك حساب بالفعل؟ <a href="#" id="switch-to-login" style="color: var(--primary); font-weight: 700;">تسجيل الدخول</a>
            </p>
        </form>
    </div>
</div>
