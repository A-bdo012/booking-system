// assets/js/main.js

// دالة عرض التنبيهات المنبثقة (Toasts)
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// التحكم بنافذة تسجيل الدخول / إنشاء الحساب
document.addEventListener('DOMContentLoaded', () => {
    const authModal = document.getElementById('auth-modal');
    const openLoginBtns = document.querySelectorAll('.open-login-btn');
    const closeAuthBtn = document.getElementById('close-auth-btn');
    const switchToRegister = document.getElementById('switch-to-register');
    const switchToLogin = document.getElementById('switch-to-login');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // فتح النافذة
    openLoginBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (authModal) authModal.classList.add('active');
        });
    });

    // إغلاق النافذة
    if (closeAuthBtn) {
        closeAuthBtn.addEventListener('click', () => {
            authModal.classList.remove('active');
        });
    }

    // التبديل بين الدخول والتسجيل
    if (switchToRegister) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            document.getElementById('modal-title').textContent = 'إنشاء حساب جديد';
        });
    }

    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
            document.getElementById('modal-title').textContent = 'تسجيل الدخول';
        });
    }

    // إرسال نموذج تسجيل الدخول عبر AJAX
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            formData.append('action', 'login');

            try {
                const res = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('حدث خطأ أثناء الاتصال بالسيرفر.', 'error');
            }
        });
    }

    // إرسال نموذج إنشاء الحساب عبر AJAX
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            formData.append('action', 'register');

            try {
                const res = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('حدث خطأ أثناء إنشاء الحساب.', 'error');
            }
        });
    }

    // تسجيل الخروج
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('api/auth.php?action=logout');
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.href = 'index.php', 800);
                }
            } catch (err) {
                showToast('خطأ أثناء تسجيل الخروج', 'error');
            }
        });
    }
});
