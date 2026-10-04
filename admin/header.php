<?php
// admin/header.php
require_once __DIR__ . '/../config/db.php';

// حماية الصفحة: طرد أي شخص ليس مديراً
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentAdmin = $_SESSION['admin_user'];
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'لوحة الإدارة - نظام الحجوزات' ?></title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

<!-- القائمة الجانبية المستقلة للإدارة (Sidebar) -->
<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="brand-icon-box" style="width: 38px; height: 38px; font-size: 1rem;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <h2>لوحة التحكم</h2>
            <span style="font-size: 0.75rem; color: #64748b;">إدارة نظام الحجوزات</span>
        </div>
    </div>

    <ul class="admin-nav">
        <li class="admin-nav-item">
            <a href="index.php" class="admin-nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie" style="width: 20px;"></i>
                <span>الرئيسية والمواعيد</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="services.php" class="admin-nav-link <?= $activePage === 'services' ? 'active' : '' ?>">
                <i class="fa-solid fa-briefcase" style="width: 20px;"></i>
                <span>إدارة الخدمات</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="notifications.php" class="admin-nav-link <?= $activePage === 'notifications' ? 'active' : '' ?>">
                <i class="fa-solid fa-bell" style="width: 20px;"></i>
                <span>سجل الإشعارات</span>
            </a>
        </li>

        <li style="margin: 25px 0 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 15px;">
            <span style="font-size: 0.75rem; color: #64748b; padding: 0 16px; text-transform: uppercase;">روابط سريعة</span>
        </li>

        <li class="admin-nav-item">
            <a href="../index.php" target="_blank" class="admin-nav-link">
                <i class="fa-solid fa-arrow-up-right-from-square" style="width: 20px;"></i>
                <span>معاينة موقع العملاء</span>
            </a>
        </li>
        <li class="admin-nav-item">
            <a href="logout.php" class="admin-nav-link" style="color: #f87171;">
                <i class="fa-solid fa-arrow-right-from-bracket" style="width: 20px;"></i>
                <span>تسجيل الخروج</span>
            </a>
        </li>
    </ul>
</aside>

<!-- المحتوى الرئيسي للوحة التحكم -->
<div class="admin-main">
    <!-- الشريط العلوي -->
    <header class="admin-topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">
                <?= $pageTitle ?? 'لوحة الإدارة' ?>
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.9rem; font-weight: 700;"><?= htmlspecialchars($currentAdmin['name']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($currentAdmin['email']) ?></div>
                </div>
            </div>
            
            <a href="logout.php" class="btn btn-outline btn-sm" style="color: var(--danger);" title="تسجيل خروج المدير">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </header>

    <div class="admin-content">
