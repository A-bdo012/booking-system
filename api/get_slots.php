<?php
// api/get_slots.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$serviceId = intval($_GET['service_id'] ?? 0);
$date = trim($_GET['date'] ?? '');

if (!$serviceId || !$date) {
    sendJsonResponse(['success' => false, 'message' => 'يرجى تحديد الخدمة والتاريخ.'], 400);
}

// التحقق من صحة صيغة التاريخ YYYY-MM-DD
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    sendJsonResponse(['success' => false, 'message' => 'صيغة التاريخ غير صحيحة.'], 400);
}

// جلب تفاصيل الخدمة لمعرفة مدة الموعد
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    sendJsonResponse(['success' => false, 'message' => 'الخدمة غير موجودة.'], 404);
}

$duration = intval($service['duration_minutes']) > 0 ? intval($service['duration_minutes']) : 30;

// جلب الحجوزات المحجوزة بالفعل في هذا اليوم (غير الملغاة)
$bookedStmt = $pdo->prepare("SELECT booking_time FROM bookings WHERE booking_date = ? AND status != 'cancelled'");
$bookedStmt->execute([$date]);
$bookedTimes = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

// تطبيع أوقات الحجز لتكون بصيغة H:i
$bookedTimesNormalized = array_map(function($t) {
    return date('H:i', strtotime($t));
}, $bookedTimes);

// أوقات العمل: من 10:00 صباحاً إلى 08:00 مساءً (20:00)
$startTime = strtotime('10:00');
$endTime = strtotime('20:00');
$currentTime = time();
$isToday = ($date === date('Y-m-d'));

$slots = [];
$currentSlot = $startTime;

while ($currentSlot + ($duration * 60) <= $endTime) {
    $timeKey = date('H:i', $currentSlot);
    $timeFormatted = date('g:i A', $currentSlot);
    
    // تحويل AM/PM إلى العربية
    $timeFormattedAr = str_replace(['AM', 'PM'], ['ص', 'م'], $timeFormatted);

    // التحقق من إتاحة الموعد
    $isAvailable = true;

    // إذا كان التاريخ اليوم والوقت مر بالفعل
    if ($isToday && $currentSlot <= strtotime(date('H:i'))) {
        $isAvailable = false;
    }

    // إذا كان الموعد محجوزاً
    if (in_array($timeKey, $bookedTimesNormalized)) {
        $isAvailable = false;
    }

    $slots[] = [
        'time' => $timeKey,
        'formatted' => $timeFormattedAr,
        'is_available' => $isAvailable
    ];

    // الانتقال للموعد التالي بناءً على مدة الخدمة (أو 30 دقيقة كحد أدنى)
    $stepMinutes = max(30, $duration);
    $currentSlot = strtotime("+$stepMinutes minutes", $currentSlot);
}

sendJsonResponse([
    'success' => true,
    'date' => $date,
    'service' => [
        'id' => $service['id'],
        'title' => $service['title'],
        'duration_minutes' => $service['duration_minutes'],
        'price' => $service['price']
    ],
    'slots' => $slots
]);
