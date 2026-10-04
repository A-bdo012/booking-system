<?php
// api/book.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'طريقة الطلب غير صحيحة.'], 405);
}

$serviceId = intval($_POST['service_id'] ?? 0);
$bookingDate = trim($_POST['booking_date'] ?? '');
$bookingTime = trim($_POST['booking_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (!$serviceId || empty($bookingDate) || empty($bookingTime)) {
    sendJsonResponse(['success' => false, 'message' => 'يرجى استكمال جميع بيانات الحجز.'], 400);
}

// التحقق من صحة الخدمة
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();
if (!$service) {
    sendJsonResponse(['success' => false, 'message' => 'الخدمة المحددة غير موجودة.'], 404);
}

// التحقق من هوية العميل (جلسة العميل المستقلة)
$userId = null;
$userEmail = '';
$userName = '';

if (isCustomerLoggedIn()) {
    $userId = $_SESSION['customer_user']['id'];
    $userName = $_SESSION['customer_user']['name'];
    $userEmail = $_SESSION['customer_user']['email'];
} else {
    // زائر جديد بدون تسجيل دخول مسبق
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['success' => false, 'message' => 'يرجى إدخال اسمك وبريدك الإلكتروني الصحيح لإتمام الحجز.'], 400);
    }

    // فحص هل البريد مسجل مسبقاً
    $userCheck = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $userCheck->execute([$email]);
    $existingUser = $userCheck->fetch();

    if ($existingUser) {
        $userId = $existingUser['id'];
        $userName = $existingUser['name'];
        $userEmail = $existingUser['email'];
    } else {
        $randomPassword = substr(md5(uniqid(rand(), true)), 0, 8);
        $hashed = password_hash($randomPassword, PASSWORD_DEFAULT);
        $newUserStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
        $newUserStmt->execute([$name, $email, $hashed, $phone]);
        $userId = $pdo->lastInsertId();
        $userName = $name;
        $userEmail = $email;
    }

    // حفظ في جلسة العميل فقط دون المساس بالمدير
    $_SESSION['customer_user'] = [
        'id' => $userId,
        'name' => $userName,
        'email' => $userEmail,
        'role' => 'customer'
    ];
}

// التحقق من عدم حجز نفس الموعد مسبقاً (منع التضارب)
$conflictStmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND booking_time = ? AND status != 'cancelled'");
$conflictStmt->execute([$bookingDate, $bookingTime]);
if ($conflictStmt->fetch()) {
    sendJsonResponse(['success' => false, 'message' => 'عذراً، تم حجز هذا الموعد للتو! يرجى اختيار موعد آخر.'], 409);
}

// حفظ الحجز في قاعدة البيانات
$insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, booking_date, booking_time, status, notes) VALUES (?, ?, ?, ?, 'confirmed', ?)");
$insertStmt->execute([$userId, $serviceId, $bookingDate, $bookingTime, $notes]);
$bookingId = $pdo->lastInsertId();

// إرسال الإشعار وتسجيله
$emailSubject = "تأكيد حجز موعدك رقم #{$bookingId} - {$service['title']}";
$emailBody = "
مرحباً {$userName}،

تم تأكيد حجزك بنجاح في منصة الحجوزات!
تفاصيل الموعد:
- رقم الحجز: #{$bookingId}
- الخدمة: {$service['title']}
- التاريخ: {$bookingDate}
- الوقت: {$bookingTime}
- السعر: {$service['price']} ج.م
- حالة الحجز: مؤكد

شكراً لاختيارك خدماتنا!
";

@mail($userEmail, $emailSubject, $emailBody, "From: noreply@booking-system.local\r\nContent-Type: text/plain; charset=UTF-8");

$logEntry = "[" . date('Y-m-d H:i:s') . "] إشعار تأكيد حجز إلى {$userEmail} (#{$bookingId}):\n{$emailBody}\n" . str_repeat("-", 50) . "\n";
@file_put_contents(__DIR__ . '/../notifications.log', $logEntry, FILE_APPEND);

sendJsonResponse([
    'success' => true,
    'message' => 'تم تأكيد حجزك بنجاح! تم إرسال تفاصيل الموعد إلى بريدك الإلكتروني.',
    'booking' => [
        'id' => $bookingId,
        'service' => $service['title'],
        'date' => $bookingDate,
        'time' => $bookingTime,
        'customer' => $userName,
        'email' => $userEmail
    ]
]);
