<?php
// index.php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'احجز موعدك بسهولة - منصة الحجوزات الذكية';

// جلب الخدمات المتاحة من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
$services = $stmt->fetchAll();

$extraScripts = ['assets/js/booking.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- مقدمة الصفحة -->
    <header class="hero">
        <div class="hero-badge">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>حجز فوري ومؤكد بنقرة واحدة</span>
        </div>
        <h1>احجز موعدك أونلاين بكل سهولة</h1>
        <p>اختر الخدمة، حدد اليوم والوقت المناسب لك، وسنرسل لك تفاصيل التأكيد مباشرة إلى بريدك الإلكتروني.</p>
    </header>

    <!-- بطاقة معالج الحجز التفاعلي -->
    <main class="wizard-card">
        <!-- مؤشر الخطوات -->
        <div class="steps-nav">
            <div class="step-item active">
                <span class="step-number"><i class="fa-solid fa-list-check"></i></span>
                <span>اختيار الخدمة</span>
            </div>
            <div class="step-item">
                <span class="step-number"><i class="fa-regular fa-calendar-days"></i></span>
                <span>الموعد والوقت</span>
            </div>
            <div class="step-item">
                <span class="step-number"><i class="fa-solid fa-check"></i></span>
                <span>تأكيد الحجز</span>
            </div>
        </div>

        <!-- الخطوة 1: اختيار الخدمة -->
        <section id="step-services" style="margin-bottom: 45px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="background: var(--primary-subtle); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.85rem;">1</span>
                <h2 style="font-size: 1.25rem; font-weight: 800;">اختر الخدمة المطلوبة</h2>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 18px;">انقر على الخدمة التي ترغب بحجز موعد لها:</p>
            
            <div class="services-grid">
                <?php if (count($services) > 0): ?>
                    <?php 
                    $icons = ['fa-stethoscope', 'fa-spa', 'fa-graduation-cap', 'fa-briefcase', 'fa-heart-pulse', 'fa-laptop-code'];
                    $i = 0;
                    foreach ($services as $service): 
                        $icon = $icons[$i % count($icons)];
                        $i++;
                    ?>
                        <div class="service-card" data-service-id="<?= $service['id'] ?>">
                            <div>
                                <div class="service-icon">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>
                                <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
                                <p class="service-desc"><?= htmlspecialchars($service['description'] ?: 'خدمة احترافية ومتميزة تلبي تطلعاتكم بدقة.') ?></p>
                            </div>
                            <div class="service-footer">
                                <div class="service-duration">
                                    <i class="fa-regular fa-clock" style="color: var(--primary);"></i>
                                    <span><?= $service['duration_minutes'] ?> دقيقة</span>
                                </div>
                                <div class="service-price">
                                    <?= number_format($service['price'], 2) ?> <span style="font-size: 0.8rem; font-weight: 600;">ج.م</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">لا توجد خدمات متاحة حالياً.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- الخطوة 2: اختيار التاريخ والموعد -->
        <section id="step-datetime" style="margin-bottom: 45px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="background: var(--primary-subtle); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.85rem;">2</span>
                <h2 style="font-size: 1.25rem; font-weight: 800;">حدد اليوم والوقت المتاح</h2>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 18px;">اختر التاريخ لعرض المواعيد الشاغرة تلقائياً بدون إعادة تحميل الصفحة:</p>

            <div style="max-width: 340px; margin-bottom: 22px;">
                <label class="form-label">
                    <i class="fa-regular fa-calendar" style="color: var(--primary); margin-left: 6px;"></i>
                    <span>تاريخ الموعد</span>
                </label>
                <input type="date" id="booking-date" class="form-input">
            </div>

            <div class="slots-container">
                <label class="form-label" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-clock" style="color: var(--primary);"></i>
                    <span>الأوقات الشاغرة لليوم المحدد:</span>
                </label>
                
                <div id="slots-loading" style="display: none; color: var(--primary); font-weight: 700; padding: 12px 0;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span style="margin-right: 8px;">جاري فحص المواعيد المتاحة...</span>
                </div>
                
                <div id="slots-empty" style="color: var(--text-muted); font-size: 0.95rem; margin-top: 10px; background: #f8fafc; padding: 12px 18px; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-left: 6px;"></i>
                    يرجى اختيار الخدمة والتاريخ أعلاه لعرض المواعيد المتاحة.
                </div>
                <div class="slots-grid" id="slots-grid"></div>
            </div>
        </section>

        <!-- الخطوة 3: بيانات العميل والملاحظات -->
        <section id="step-details">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="background: var(--primary-subtle); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.85rem;">3</span>
                <h2 style="font-size: 1.25rem; font-weight: 800;">بيانات التواصل وتأكيد الحجز</h2>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 22px;">أكمل بياناتك لتأكيد الحجز واستلام الإشعار عبر البريد الإلكتروني:</p>

            <form id="booking-form">
                <?php if (!$currentUser): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 18px;">
                        <div class="form-group">
                            <label class="form-label">الاسم بالكامل *</label>
                            <input type="text" name="name" class="form-input" placeholder="مثال: أحمد مصطفى" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني * (لاستلام التأكيد)</label>
                            <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف للتواصل</label>
                            <input type="tel" name="phone" class="form-input" placeholder="010XXXXXXXX">
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: var(--primary-subtle); padding: 16px 20px; border-radius: 12px; margin-bottom: 22px; display: flex; align-items: center; gap: 12px; border: 1px solid rgba(67, 97, 238, 0.2);">
                        <i class="fa-solid fa-circle-check" style="font-size: 1.3rem; color: var(--primary);"></i>
                        <div>
                            <div style="font-weight: 700; color: var(--primary);">الحجز لحساب: <?= htmlspecialchars($currentUser['name']) ?></div>
                            <div style="font-size: 0.88rem; color: var(--text-muted);">سيتم إرسال تأكيد الموعد إلى: <?= htmlspecialchars($currentUser['email']) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">ملاحظات إضافية (اختياري)</label>
                    <textarea name="notes" rows="3" class="form-textarea" placeholder="أي تفاصيل أو ملاحظات خاصة ترغب بمشاركتها معنا مسبقاً..."></textarea>
                </div>

                <button type="submit" id="booking-submit-btn" class="btn btn-primary" style="padding: 14px 38px; font-size: 1.05rem;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>تأكيد الحجز الآن</span>
                </button>
            </form>
        </section>
    </main>
</div>

<!-- نافذة تأكيد نجاح الحجز -->
<div class="modal-overlay" id="success-modal">
    <div class="modal-content" style="text-align: center;">
        <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--success-bg); color: var(--success); display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; margin-bottom: 18px;">
            <i class="fa-solid fa-check"></i>
        </div>
        
        <h2 style="color: var(--text-main); font-weight: 800; margin-bottom: 8px;">تم تأكيد حجزك بنجاح!</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 22px;">
            تم إدراج موعدك في النظام وإرسال تفاصيل الإشعار إلى بريدك الإلكتروني.
        </p>

        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; text-align: right; margin-bottom: 25px;">
            <p style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">رقم المرجع:</span>
                <strong id="conf-booking-id" style="color: var(--primary);">#0</strong>
            </p>
            <p style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">الخدمة:</span>
                <strong id="conf-service">-</strong>
            </p>
            <p style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">التاريخ:</span>
                <strong id="conf-date">-</strong>
            </p>
            <p style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">الوقت:</span>
                <strong id="conf-time">-</strong>
            </p>
            <p style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">البريد المسجل:</span>
                <strong id="conf-email">-</strong>
            </p>
        </div>

        <button type="button" class="btn btn-primary" id="close-success-btn" style="width: 100%;">
            <span>تم، شكراً لك</span>
        </button>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
