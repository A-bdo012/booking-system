<?php
// api/admin_actions.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// حماية لوحة الإدارة: يجب أن يكون المستخدم مديراً
if (!isAdmin()) {
    sendJsonResponse(['success' => false, 'message' => 'غير مصرح لك بالوصول إلى لوحة الإدارة.'], 403);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. تحديث حالة الحجز (تأكيد، إلغاء، قيد الانتظار)
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
    if (!$bookingId || !in_array($status, $allowedStatuses)) {
        sendJsonResponse(['success' => false, 'message' => 'بيانات التحديث غير صالحة.'], 400);
    }

    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $bookingId]);

    // جلب بيانات العميل لإرسال إشعار بتحديث الحالة
    $infoStmt = $pdo->prepare("
        SELECT b.*, u.email, u.name as customer_name, s.title as service_title 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN services s ON b.service_id = s.id 
        WHERE b.id = ?
    ");
    $infoStmt->execute([$bookingId]);
    $booking = $infoStmt->fetch();

    if ($booking) {
        $statusLabels = [
            'confirmed' => 'مؤكد',
            'cancelled' => 'ملغي',
            'pending' => 'قيد الانتظار'
        ];
        $statusAr = $statusLabels[$status] ?? $status;
        $body = "مرحباً {$booking['customer_name']}، تم تحديث حالة حجزك رقم #{$bookingId} لخدمة ({$booking['service_title']}) إلى: {$statusAr}.";
        
        @mail($booking['email'], "تحديث حالة الحجز #{$bookingId}", $body, "From: noreply@booking-system.local\r\nContent-Type: text/plain; charset=UTF-8");
        
        $logEntry = "[" . date('Y-m-d H:i:s') . "] إشعار تحديث حالة إلى {$booking['email']}:\n{$body}\n" . str_repeat("-", 50) . "\n";
        @file_put_contents(__DIR__ . '/../notifications.log', $logEntry, FILE_APPEND);
    }

    sendJsonResponse(['success' => true, 'message' => 'تم تحديث حالة الحجز بنجاح!']);
}

// 2. إضافة خدمة جديدة
if ($action === 'add_service' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = intval($_POST['duration_minutes'] ?? 30);
    $price = floatval($_POST['price'] ?? 0.00);

    if (empty($title) || $duration <= 0 || $price < 0) {
        sendJsonResponse(['success' => false, 'message' => 'يرجى إدخال اسم الخدمة، المدة، والسعر بشكل صحيح.'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO services (title, description, duration_minutes, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $duration, $price]);

    sendJsonResponse([
        'success' => true, 
        'message' => 'تمت إضافة الخدمة بنجاح!',
        'service_id' => $pdo->lastInsertId()
    ]);
}

// 3. حذف خدمة
if ($action === 'delete_service' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = intval($_POST['service_id'] ?? 0);
    if (!$serviceId) {
        sendJsonResponse(['success' => false, 'message' => 'رقم الخدمة غير صحيح.'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);

    sendJsonResponse(['success' => true, 'message' => 'تم حذف الخدمة بنجاح.']);
}

// 4. جلب جميع الحجوزات
if ($action === 'get_bookings') {
    $status = $_GET['status'] ?? 'all';
    $sql = "
        SELECT b.id, b.booking_date, b.booking_time, b.status, b.notes, b.created_at,
               u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
               s.title as service_title, s.price as service_price, s.duration_minutes
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
    ";
    
    $params = [];
    if (in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        $sql .= " WHERE b.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY b.booking_date DESC, b.booking_time DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    sendJsonResponse(['success' => true, 'bookings' => $bookings]);
}

sendJsonResponse(['success' => false, 'message' => 'إجراء إداري غير معروف.'], 400);
