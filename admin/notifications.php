<?php
// admin/notifications.php
$activePage = 'notifications';
$pageTitle = 'سجل الإشعارات البريدية';
require_once __DIR__ . '/header.php';

$logFile = __DIR__ . '/../notifications.log';
$logContent = file_exists($logFile) ? file_get_contents($logFile) : '';
?>

<div class="wizard-card" style="padding: 28px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-envelope-circle-check" style="color: var(--primary);"></i>
                <span>سجل الإشعارات المرسلة للعملاء</span>
            </h2>
            <p style="color: var(--text-muted); font-size: 0.88rem;">كل إشعار يتم إرساله للعميل بالبريد عند تأكيد الحجز أو تعديل حالته يظهر هنا فورياً.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="window.location.reload();">
            <i class="fa-solid fa-rotate-right"></i>
            <span>تحديث السجل</span>
        </button>
    </div>

    <?php if (!empty(trim($logContent))): ?>
        <pre style="background: #0f172a; color: #38bdf8; padding: 22px; border-radius: 12px; font-family: 'Consolas', monospace; font-size: 0.88rem; line-height: 1.6; max-height: 550px; overflow-y: auto; white-space: pre-wrap; direction: ltr; text-align: left;"><?= htmlspecialchars($logContent) ?></pre>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fa-regular fa-bell-slash" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
            لم يتم تسجيل أي إشعارات بعد. قم بحجز موعد تجريبي لمعاينة السجل.
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
