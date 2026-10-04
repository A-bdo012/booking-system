<?php
// admin/login.php
require_once __DIR__ . '/../config/db.php';

// إذا كان مسجل كمدير بالفعل في جلسة الإدارة، يوجهه فوراً للوحة التحكم
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $errorMessage = 'عذراً، هذا الحساب مخصص للعملاء فقط ولا يملك صلاحيات وصول إدارية.';
            } else {
                // حفظ الجلسة في متغير الإدارة المستقل تماماً
                $_SESSION['admin_user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'role' => 'admin'
                ];
                header('Location: index.php');
                exit;
            }
        } else {
            $errorMessage = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول الإدارة - بوابة المدير</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .admin-login-box {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 440px;
            width: 100%;
            padding: 40px 35px;
            text-align: center;
        }
        .admin-badge-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: var(--primary-subtle);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="admin-login-box">
    <div class="admin-badge-icon">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    
    <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">بوابة الإدارة</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 25px;">منطقة تسجيل الدخول المخصصة لمدراء النظام فقط</p>

    <?php if ($errorMessage): ?>
        <div style="background: var(--danger-bg); color: var(--danger); padding: 12px 16px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($errorMessage) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" style="text-align: right;">
        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-envelope" style="color: var(--primary); margin-left: 6px;"></i>
                <span>البريد الإلكتروني للمدير</span>
            </label>
            <input type="email" name="email" class="form-input" placeholder="admin@booking.com" value="admin@booking.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-lock" style="color: var(--primary); margin-left: 6px;"></i>
                <span>كلمة المرور</span>
            </label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 13px; font-size: 1rem; margin-top: 10px;">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span>دخول إلى لوحة التحكم</span>
        </button>
    </form>

    <div style="margin-top: 25px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <a href="../index.php" target="_blank" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>فتح موقع العملاء في تبويب جديد</span>
        </a>
    </div>
</div>

</body>
</html>
