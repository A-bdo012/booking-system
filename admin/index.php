<?php
// admin/index.php
$activePage = 'dashboard';
$pageTitle = 'لوحة التحكم والمواعيد';
require_once __DIR__ . '/header.php';

// إحصائيات عامة
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$confirmedBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

// جلب جميع الحجوزات مع تفاصيل العميل والخدمة
$sql = "
    SELECT b.id, b.booking_date, b.booking_time, b.status, b.notes, b.created_at,
           u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
           s.title as service_title, s.price as service_price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    ORDER BY b.booking_date DESC, b.booking_time DESC
";
$bookings = $pdo->query($sql)->fetchAll();
?>

<!-- بطاقات الإحصائيات -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--primary-subtle); color: var(--primary);">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalBookings ?></h3>
            <p>إجمالي الحجوزات</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--warning-bg); color: var(--warning);">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="stat-info">
            <h3><?= $pendingBookings ?></h3>
            <p>قيد الانتظار</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--success-bg); color: var(--success);">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <h3><?= $confirmedBookings ?></h3>
            <p>مواعيد مؤكدة</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalServices ?></h3>
            <p>الخدمات النشطة</p>
        </div>
    </div>
</div>

<!-- جدول إدارة الحجوزات -->
<div class="dashboard-table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800;">سجل الحجوزات والمواعيد</h2>
            <p style="color: var(--text-muted); font-size: 0.88rem;">متابعة طلبات العملاء وتأكيد أو إلغاء المواعيد بضغطة زر</p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-weight: 700; font-size: 0.88rem; color: var(--text-muted);">
                <i class="fa-solid fa-filter"></i> تصفية:
            </label>
            <select id="filter-status" class="form-select" style="width: auto; padding: 8px 14px; font-size: 0.88rem;">
                <option value="all">كل الحجوزات</option>
                <option value="pending">قيد الانتظار</option>
                <option value="confirmed">مؤكدة</option>
                <option value="cancelled">ملغاة</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>رقم المرجع</th>
                    <th>العميل</th>
                    <th>بيانات التواصل</th>
                    <th>الخدمة المطلوبة</th>
                    <th>الموعد والتاريخ</th>
                    <th>الحالة</th>
                    <th>ملاحظات</th>
                    <th>تغيير الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr class="booking-row" data-status="<?= $b['status'] ?>">
                            <td><strong style="color: var(--primary);">#<?= $b['id'] ?></strong></td>
                            <td>
                                <div style="font-weight: 700;"><?= htmlspecialchars($b['customer_name']) ?></div>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                <div><i class="fa-solid fa-phone" style="font-size: 0.75rem; margin-left: 4px;"></i> <?= htmlspecialchars($b['customer_phone'] ?: '-') ?></div>
                                <div><i class="fa-solid fa-envelope" style="font-size: 0.75rem; margin-left: 4px;"></i> <?= htmlspecialchars($b['customer_email']) ?></div>
                            </td>
                            <td>
                                <span style="font-weight: 600;"><?= htmlspecialchars($b['service_title']) ?></span>
                                <div style="font-size: 0.8rem; color: var(--primary); font-weight: 700;"><?= number_format($b['service_price'], 2) ?> ج.م</div>
                            </td>
                            <td>
                                <div><i class="fa-regular fa-calendar" style="color: var(--primary); margin-left: 4px;"></i> <?= $b['booking_date'] ?></div>
                                <div style="font-weight: 700; color: var(--text-main);"><i class="fa-regular fa-clock" style="color: var(--primary); margin-left: 4px;"></i> <?= date('g:i A', strtotime($b['booking_time'])) ?></div>
                            </td>
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
                            <td style="max-width: 140px; font-size: 0.85rem; color: var(--text-muted);">
                                <?= htmlspecialchars($b['notes'] ?: '-') ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <?php if ($b['status'] !== 'confirmed'): ?>
                                        <button type="button" class="btn btn-success btn-sm update-status-btn" data-id="<?= $b['id'] ?>" data-status="confirmed">
                                            <i class="fa-solid fa-check"></i> تأكيد
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($b['status'] !== 'cancelled'): ?>
                                        <button type="button" class="btn btn-danger btn-sm update-status-btn" data-id="<?= $b['id'] ?>" data-status="cancelled">
                                            <i class="fa-solid fa-xmark"></i> إلغاء
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                            <i class="fa-regular fa-calendar-xmark" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                            لا توجد أي حجوزات واردة حتى الآن.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
