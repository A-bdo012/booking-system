<?php
// reset_admin.php
require_once __DIR__ . '/config/db.php';

$email = 'admin@booking.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// التحقق من وجود المستخدم أو إنشاؤه
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$exists = $stmt->fetch();

if ($exists) {
    $updateStmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
    $updateStmt->execute([$hashed, $email]);
    $message = "تم تحديث كلمة مرور المدير بنجاح!";
} else {
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES ('المدير العام', ?, ?, '01000000000', 'admin')");
    $insertStmt->execute([$email, $hashed]);
    $message = "تم إنشاء حساب المدير بنجاح!";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفعيل حساب المدير</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="wizard-card" style="max-width: 480px; text-align: center; padding: 35px;">
        <div style="font-size: 3rem; margin-bottom: 10px;">✅</div>
        <h2 style="color: var(--success); margin-bottom: 15px;"><?= $message ?></h2>
        
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 15px; text-align: right; margin-bottom: 20px;">
            <p style="margin-bottom: 8px;"><strong>البريد الإلكتروني:</strong> admin@booking.com</p>
            <p style="margin-bottom: 0;"><strong>كلمة المرور:</strong> admin123</p>
        </div>

        <a href="login.php" class="btn btn-primary" style="width: 100%;">الانتقال لصفحة تسجيل الدخول الآن</a>
    </div>
</body>
</html>
