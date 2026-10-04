<?php
// my-bookings.php
require_once __DIR__ . '/config/db.php';

// التحقق من تسجيل دخول العميل
if (!isCustomerLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'حجوزاتي ومواعيدي - نظام الحجوزات';
$userId = $_SESSION['customer_user']['id'];

// جلب حجوزات هذا العميل فقط
$stmt = $pdo->prepare("
    SELECT b.id, b.booking_date, b.booking_time, b.status, b.notes, b.created_at,
           s.title as service_title, s.price as service_price, s.duration_minutes
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$stmt->execute([$userId]);
$myBookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 70px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i>
                <span>سجل حجوزاتي</span>
            </h1>
            <p style="color: var(--text-muted);">متابعة حالة مواعيدك السابقة والقادمة.</p>
        </div>
        <a href="index.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            <span>حجز موعد جديد</span>
        </a>
    </div>

    <div class="wizard-card" style="padding: 25px;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>رقم الحجز</th>
                        <th>الخدمة</th>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>المدة</th>
                        <th>السعر</th>
                        <th>الحالة</th>
                        <th>ملاحظاتك</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($myBookings) > 0): ?>
                        <?php foreach ($myBookings as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);">
                                        <?= htmlspecialchars($b['service_title']) ?>
                                    </div>
                                </td>
                                <td>
                                    <i class="fa-regular fa-calendar" style="color: var(--primary); margin-left: 5px;"></i>
                                    <?= $b['booking_date'] ?>
                                </td>
                                <td>
                                    <i class="fa-regular fa-clock" style="color: var(--primary); margin-left: 5px;"></i>
                                    <?= date('g:i A', strtotime($b['booking_time'])) ?>
                                </td>
                                <td><?= $b['duration_minutes'] ?> دقيقة</td>
                                <td><strong><?= number_format($b['service_price'], 2) ?> ج.م</strong></td>
                                <td>
                                    <span class="badge badge-<?= $b['status'] ?>">
                                        <?php if ($b['status'] === 'confirmed'): ?>
                                            <i class="fa-solid fa-circle-check"></i> مؤكد
                                        <?php elseif ($b['status'] === 'pending'): ?>
                                            <i class="fa-solid fa-clock"></i> قيد الانتظار
                                        <?php else: ?>
                                            <i class="fa-solid fa-ban"></i> ملغي
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.88rem; max-width: 180px;">
                                    <?= htmlspecialchars($b['notes'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                لا توجد لديك أي حجوزات مسجلة حتى الآن.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
