<?php
// api/auth.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. تسجيل مستخدم/عميل جديد
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        sendJsonResponse(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['success' => false, 'message' => 'البريد الإلكتروني غير صالح.'], 400);
    }

    if (strlen($password) < 6) {
        sendJsonResponse(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.'], 400);
    }

    // التحقق من تكرار البريد
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'البريد الإلكتروني مسجل بالفعل.'], 400);
    }

    // تشفير وحفظ
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
    $insertStmt->execute([$name, $email, $hashedPassword, $phone]);

    $newUserId = $pdo->lastInsertId();
    // حفظ في جلسة العميل فقط دون التأثير على جلسة الإدارة
    $_SESSION['customer_user'] = [
        'id' => $newUserId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => 'customer'
    ];

    sendJsonResponse([
        'success' => true,
        'message' => 'تم إنشاء الحساب وتسجيل الدخول بنجاح!',
        'user' => $_SESSION['customer_user']
    ]);
}

// 2. تسجيل دخول العميل
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendJsonResponse(['success' => false, 'message' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور.'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // حفظ في جلسة العميل فقط
        $_SESSION['customer_user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role']
        ];

        sendJsonResponse([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح!',
            'user' => $_SESSION['customer_user']
        ]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'], 401);
    }
}

// 3. تسجيل خروج العميل فقط (دون المساس بمدير النظام إذا كان مفتوحاً)
if ($action === 'logout') {
    unset($_SESSION['customer_user']);
    sendJsonResponse(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح.']);
}

// 4. تسجيل خروج المدير فقط (دون المساس بالعميل)
if ($action === 'admin_logout') {
    unset($_SESSION['admin_user']);
    sendJsonResponse(['success' => true, 'message' => 'تم تسجيل خروج المدير بنجاح.']);
}

// 5. فحص العميل الحالي
if ($action === 'me') {
    sendJsonResponse(['success' => true, 'user' => $_SESSION['customer_user'] ?? null]);
}

sendJsonResponse(['success' => false, 'message' => 'إجراء غير معروف.'], 400);
