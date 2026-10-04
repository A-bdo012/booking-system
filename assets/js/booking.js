// assets/js/booking.js

let selectedServiceId = null;
let selectedDate = null;
let selectedTime = null;

document.addEventListener('DOMContentLoaded', () => {
    const serviceCards = document.querySelectorAll('.service-card');
    const dateInput = document.getElementById('booking-date');
    const slotsContainer = document.getElementById('slots-grid');
    const slotsLoading = document.getElementById('slots-loading');
    const slotsEmpty = document.getElementById('slots-empty');
    const bookingForm = document.getElementById('booking-form');
    const bookingSubmitBtn = document.getElementById('booking-submit-btn');

    // ضبط الحد الأدنى للتاريخ ليكون اليوم
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
        selectedDate = today;

        dateInput.addEventListener('change', (e) => {
            selectedDate = e.target.value;
            selectedTime = null;
            if (selectedServiceId && selectedDate) {
                fetchSlots(selectedServiceId, selectedDate);
            }
        });
    }

    // اختيار الخدمة
    serviceCards.forEach(card => {
        card.addEventListener('click', () => {
            serviceCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedServiceId = card.getAttribute('data-service-id');
            selectedTime = null;

            // تمرير الشاشة بلطف لقسم التاريخ والمواعيد
            const step2 = document.getElementById('step-datetime');
            if (step2) {
                step2.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (selectedDate) {
                fetchSlots(selectedServiceId, selectedDate);
            }
        });
    });

    // دالة جلب المواعيد المتاحة عبر الـ AJAX
    async function fetchSlots(serviceId, date) {
        if (!slotsContainer) return;

        slotsContainer.innerHTML = '';
        if (slotsLoading) slotsLoading.style.display = 'block';
        if (slotsEmpty) slotsEmpty.style.display = 'none';

        try {
            const res = await fetch(`api/get_slots.php?service_id=${serviceId}&date=${date}`);
            const data = await res.json();

            if (slotsLoading) slotsLoading.style.display = 'none';

            if (data.success && data.slots.length > 0) {
                renderSlots(data.slots);
            } else {
                if (slotsEmpty) {
                    slotsEmpty.textContent = data.message || 'لا توجد مواعيد متاحة في هذا اليوم.';
                    slotsEmpty.style.display = 'block';
                }
            }
        } catch (err) {
            if (slotsLoading) slotsLoading.style.display = 'none';
            if (slotsEmpty) {
                slotsEmpty.textContent = 'حدث خطأ في تحميل المواعيد. يرجى المحاولة لاحقاً.';
                slotsEmpty.style.display = 'block';
            }
        }
    }

    // رسم المواعيد
    function renderSlots(slots) {
        slotsContainer.innerHTML = '';
        let hasAvailableSlots = false;

        slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'slot-btn';
            btn.textContent = slot.formatted;
            btn.setAttribute('data-time', slot.time);

            if (!slot.is_available) {
                btn.disabled = true;
                btn.title = 'محجوز مسبقاً أو غير متاح';
            } else {
                hasAvailableSlots = true;
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    selectedTime = slot.time;
                    
                    // تمرير الشاشة لنموذج البيانات
                    const step3 = document.getElementById('step-details');
                    if (step3) {
                        step3.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }

            slotsContainer.appendChild(btn);
        });

        if (!hasAvailableSlots && slotsEmpty) {
            slotsEmpty.textContent = 'اكتملت جميع مواعيد هذا اليوم. يرجى اختيار تاريخ آخر.';
            slotsEmpty.style.display = 'block';
        }
    }

    // إرسال الحجز عبر AJAX
    if (bookingForm) {
        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!selectedServiceId) {
                showToast('يرجى اختيار الخدمة أولاً.', 'error');
                return;
            }

            if (!selectedDate || !selectedTime) {
                showToast('يرجى اختيار اليوم والموعد المناسب.', 'error');
                return;
            }

            const formData = new FormData(bookingForm);
            formData.append('service_id', selectedServiceId);
            formData.append('booking_date', selectedDate);
            formData.append('booking_time', selectedTime);

            bookingSubmitBtn.disabled = true;
            bookingSubmitBtn.innerHTML = 'جاري تأكيد الحجز...';

            try {
                const res = await fetch('api/book.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showSuccessModal(data.booking);
                    bookingForm.reset();
                    // إعادة تحميل المواعيد لتحديث الحالة بدون إعادة تحميل الصفحة
                    fetchSlots(selectedServiceId, selectedDate);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('تعذر الاتصال بالسيرفر لإتمام الحجز.', 'error');
            } finally {
                bookingSubmitBtn.disabled = false;
                bookingSubmitBtn.innerHTML = 'تأكيد الحجز الآن ✨';
            }
        });
    }

    // نافذة نجاح الحجز المنبثقة
    function showSuccessModal(booking) {
        const modal = document.getElementById('success-modal');
        if (!modal) return;

        document.getElementById('conf-booking-id').textContent = '#' + booking.id;
        document.getElementById('conf-service').textContent = booking.service;
        document.getElementById('conf-date').textContent = booking.date;
        document.getElementById('conf-time').textContent = booking.time;
        document.getElementById('conf-email').textContent = booking.email;

        modal.classList.add('active');
    }

    const closeSuccessBtn = document.getElementById('close-success-btn');
    if (closeSuccessBtn) {
        closeSuccessBtn.addEventListener('click', () => {
            document.getElementById('success-modal').classList.remove('active');
        });
    }
});
