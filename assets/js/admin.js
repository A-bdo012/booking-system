// assets/js/admin.js

document.addEventListener('DOMContentLoaded', () => {
    // تحديد مسار الـ API التلقائي بناءً على مسار الصفحة الحالية
    const apiBase = window.location.pathname.includes('/admin') ? '../api/' : 'api/';

    // 1. تحديث حالة الحجز (تأكيد / إلغاء)
    document.querySelectorAll('.update-status-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const bookingId = btn.getAttribute('data-id');
            const newStatus = btn.getAttribute('data-status');

            const statusText = newStatus === 'confirmed' ? 'تأكيد الحجز' : 'إلغاء الحجز';
            if (!confirm(`هل أنت متأكد من ${statusText} رقم #${bookingId}؟`)) {
                return;
            }

            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('booking_id', bookingId);
            formData.append('status', newStatus);

            try {
                const res = await fetch(`${apiBase}admin_actions.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                }
            } catch (err) {
                showToast('خطأ أثناء تحديث حالة الحجز.', 'error');
                btn.disabled = false;
            }
        });
    });

    // 2. فلترة جدول الحجوزات
    const filterSelect = document.getElementById('filter-status');
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => {
            const selectedStatus = e.target.value;
            const rows = document.querySelectorAll('.booking-row');

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (selectedStatus === 'all' || rowStatus === selectedStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 3. إضافة خدمة جديدة
    const addServiceForm = document.getElementById('add-service-form');
    if (addServiceForm) {
        addServiceForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addServiceForm);
            formData.append('action', 'add_service');

            try {
                const res = await fetch(`${apiBase}admin_actions.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('خطأ أثناء إضافة الخدمة.', 'error');
            }
        });
    }

    // 4. حذف خدمة
    document.querySelectorAll('.delete-service-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const serviceId = btn.getAttribute('data-id');
            if (!confirm('هل أنت متأكد من حذف هذه الخدمة؟ قد يؤثر ذلك على الحجوزات المرتبطة بها.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_service');
            formData.append('service_id', serviceId);

            try {
                const res = await fetch(`${apiBase}admin_actions.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('خطأ أثناء حذف الخدمة.', 'error');
            }
        });
    });
});
