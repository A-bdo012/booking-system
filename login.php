<?php
// login.php
require_once __DIR__ . '/config/db.php';

// إذا كان العميل مسجل دخول بالفعل، نوجهه لحجوزاته
if (isCustomerLoggedIn()) {
    header('Location: my-bookings.php');
    exit;
}

$pageTitle = 'دخول العملاء - نظام الحجوزات';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 480px; margin-top: 50px; margin-bottom: 70px;">
    <div class="wizard-card" style="padding: 35px;">
        <div style="text-align: center; margin-bottom: 25px;">
            <div class="brand-icon-box" style="margin: 0 auto 15px;">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2 id="page-auth-title" style="font-size: 1.5rem; font-weight: 800;">تسجيل دخول العميل</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">لمتابعة مواعيدك أو إدارة حسابك</p>
        </div>

        <!-- نموذج تسجيل الدخول -->
        <form id="standalone-login-form">
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-envelope" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>البريد الإلكتروني</span>
                </label>
                <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-lock" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>كلمة المرور</span>
                </label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>تسجيل الدخول</span>
            </button>
            <p style="text-align: center; margin-top: 20px; font-size: 0.95rem;">
                ليس لديك حساب؟ <a href="#" id="toggle-auth" style="color: var(--primary); font-weight: 700;">إنشاء حساب جديد</a>
            </p>
        </form>

        <!-- نموذج إنشاء حساب جديد -->
        <form id="standalone-register-form" style="display: none;">
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-user" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>الاسم الكامل</span>
                </label>
                <input type="text" name="name" class="form-input" placeholder="مثال: أحمد مصطفى" required>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-envelope" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>البريد الإلكتروني</span>
                </label>
                <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-phone" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>رقم الهاتف</span>
                </label>
                <input type="tel" name="phone" class="form-input" placeholder="010XXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-lock" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>كلمة المرور (6 أحرف فأكثر)</span>
                </label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                <i class="fa-solid fa-user-plus"></i>
                <span>إنشاء الحساب</span>
            </button>
            <p style="text-align: center; margin-top: 20px; font-size: 0.95rem;">
                لديك حساب بالفعل؟ <a href="#" id="toggle-auth-back" style="color: var(--primary); font-weight: 700;">تسجيل الدخول</a>
            </p>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('standalone-login-form');
    const registerForm = document.getElementById('standalone-register-form');
    const toggleToRegister = document.getElementById('toggle-auth');
    const toggleToLogin = document.getElementById('toggle-auth-back');
    const title = document.getElementById('page-auth-title');

    toggleToRegister.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        title.textContent = 'إنشاء حساب جديد كعميل';
    });

    toggleToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        registerForm.style.display = 'none';
        loginForm.style.display = 'block';
        title.textContent = 'تسجيل دخول العميل';
    });

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        formData.append('action', 'login');
        try {
            const res = await fetch('api/auth.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = 'index.php', 800);
            } else {
                showToast(data.message, 'error');
            }
        } catch (err) {
            showToast('حدث خطأ أثناء تسجيل الدخول', 'error');
        }
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        formData.append('action', 'register');
        try {
            const res = await fetch('api/auth.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = 'index.php', 800);
            } else {
                showToast(data.message, 'error');
            }
        } catch (err) {
            showToast('حدث خطأ أثناء إنشاء الحساب', 'error');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
