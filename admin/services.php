<?php
// admin/services.php
$activePage = 'services';
$pageTitle = 'إدارة الخدمات';
require_once __DIR__ . '/header.php';

$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
    <!-- إضافة خدمة جديدة -->
    <div class="wizard-card" style="padding: 28px; margin-bottom: 0;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <div class="brand-icon-box" style="width: 36px; height: 36px; font-size: 0.95rem;">
                <i class="fa-solid fa-plus"></i>
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 800;">إضافة خدمة جديدة</h2>
        </div>

        <form id="add-service-form">
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-tag" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>اسم الخدمة *</span>
                </label>
                <input type="text" name="title" class="form-input" placeholder="مثال: جلسة استشارة نفسية / عيادة" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-align-right" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>وصف تفصيلي للخدمة</span>
                </label>
                <textarea name="description" rows="3" class="form-textarea" placeholder="اكتب ما تقدمه هذه الخدمة للعميل..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-regular fa-clock" style="color: var(--primary); margin-left: 6px;"></i>
                        <span>المدة (دقيقة) *</span>
                    </label>
                    <input type="number" name="duration_minutes" class="form-input" value="30" min="10" step="5" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-coins" style="color: var(--primary); margin-left: 6px;"></i>
                        <span>السعر (ج.م) *</span>
                    </label>
                    <input type="number" name="price" class="form-input" value="200" min="0" step="10" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
                <i class="fa-solid fa-check"></i>
                <span>حفظ وإتاحة الخدمة للحجز</span>
            </button>
        </form>
    </div>

    <!-- قائمة الخدمات الحالية -->
    <div class="wizard-card" style="padding: 28px; margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="brand-icon-box" style="width: 36px; height: 36px; font-size: 0.95rem; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800;">الخدمات المتاحة (<?= count($services) ?>)</h2>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px; max-height: 520px; overflow-y: auto;">
            <?php if (count($services) > 0): ?>
                <?php foreach ($services as $srv): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
                        <div>
                            <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 4px;"><?= htmlspecialchars($srv['title']) ?></div>
                            <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 8px;">
                                <?= htmlspecialchars($srv['description'] ?: 'لا يوجد وصف مدخل.') ?>
                            </div>
                            <div style="display: flex; gap: 15px; font-size: 0.85rem; font-weight: 700;">
                                <span style="color: var(--primary);"><i class="fa-regular fa-clock"></i> <?= $srv['duration_minutes'] ?> دقيقة</span>
                                <span style="color: var(--success);"><i class="fa-solid fa-wallet"></i> <?= number_format($srv['price'], 2) ?> ج.م</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline btn-sm delete-service-btn" data-id="<?= $srv['id'] ?>" style="color: var(--danger); border-color: #fca5a5; flex-shrink: 0; margin-right: 15px;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 30px;">لا توجد أي خدمات متاحة حالياً.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
