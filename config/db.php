<?php
// config/db.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'booking_system_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE));
}

// التحقق من جلسة العميل (مفصولة تماماً)
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_user']);
}

// التحقق من جلسة المدير (مفصولة تماماً)
function isAdminLoggedIn() {
    return isset($_SESSION['admin_user']) && $_SESSION['admin_user']['role'] === 'admin';
}

// دوال مساعدة للتوافق
function isLoggedIn() {
    return isCustomerLoggedIn();
}

function isAdmin() {
    return isAdminLoggedIn();
}

// دالة إرسال استجابة JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
