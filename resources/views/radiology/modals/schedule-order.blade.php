<!-- Schedule Order Modal -->
<div class="modal fade" id="scheduleOrderModal" tabindex="-1" aria-labelledby="scheduleOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleOrderModalLabel">
                    <i class="fas fa-calendar-plus text-info me-2"></i>
                    جدولة فحص الأشعة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scheduleOrderForm">
                <div class="modal-body">
                    <input type="hidden" id="scheduleOrderId" name="order_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduled_date" class="form-label">تاريخ الفحص <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="scheduled_time" class="form-label">وقت الفحص <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="equipment" class="form-label">الجهاز المطلوب</label>
                            <select class="form-select" id="equipment" name="equipment">
                                <option value="">اختر الجهاز</option>
                                <option value="xray_1">جهاز الأشعة السينية 1</option>
                                <option value="xray_2">جهاز الأشعة السينية 2</option>
                                <option value="ct_scan">جهاز الأشعة المقطعية</option>
                                <option value="mri">جهاز الرنين المغناطيسي</option>
                                <option value="ultrasound_1">جهاز الموجات فوق الصوتية 1</option>
                                <option value="ultrasound_2">جهاز الموجات فوق الصوتية 2</option>
                                <option value="mammography">جهاز تصوير الثدي</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="technician" class="form-label">الفني المسؤول</label>
                            <select class="form-select" id="technician" name="technician">
                                <option value="">اختر الفني</option>
                                <option value="tech_1">أحمد محمد - فني أشعة أول</option>
                                <option value="tech_2">فاطمة علي - فني أشعة</option>
                                <option value="tech_3">محمد حسن - فني أشعة مقطعية</option>
                                <option value="tech_4">سارة أحمد - فني رنين مغناطيسي</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="preparation_notes" class="form-label">تعليمات التحضير</label>
                        <textarea class="form-control" id="preparation_notes" name="preparation_notes" rows="4" placeholder="أي تعليمات خاصة للمريض قبل الفحص"></textarea>
                        <div class="form-text">
                            <strong>أمثلة:</strong> الصيام لمدة 8 ساعات، شرب الماء قبل الفحص، إزالة المجوهرات، إلخ.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="estimated_duration" class="form-label">المدة المتوقعة (بالدقائق)</label>
                            <input type="number" class="form-control" id="estimated_duration" name="estimated_duration" min="5" max="180" value="30">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contrast_required" class="form-label">الصبغة</label>
                            <select class="form-select" id="contrast_required" name="contrast_required">
                                <option value="no">غير مطلوبة</option>
                                <option value="oral">صبغة فموية</option>
                                <option value="iv">صبغة وريدية</option>
                                <option value="both">كلاهما</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>تنبيه:</strong> سيتم إرسال رسالة تذكير للمريض قبل موعد الفحص بـ 24 ساعة تتضمن تعليمات التحضير.
                    </div>
                    
                    <!-- Available Time Slots -->
                    <div class="mb-3">
                        <label class="form-label">المواعيد المتاحة اليوم</label>
                        <div id="availableSlots" class="d-flex flex-wrap gap-2">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-outline-primary" onclick="checkAvailability()">
                        <i class="fas fa-search me-1"></i>
                        فحص التوفر
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-calendar-check me-1"></i>
                        جدولة الفحص
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('scheduleOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = document.getElementById('scheduleOrderId').value;
    
    // Combine date and time
    const date = formData.get('scheduled_date');
    const time = formData.get('scheduled_time');
    const scheduledAt = `${date} ${time}`;
    
    const data = {
        scheduled_at: scheduledAt,
        preparation_notes: formData.get('preparation_notes'),
        equipment: formData.get('equipment'),
        technician: formData.get('technician'),
        estimated_duration: formData.get('estimated_duration'),
        contrast_required: formData.get('contrast_required')
    };
    
    fetch(`/radiology-specialized/orders/${orderId}/schedule`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('scheduleOrderModal')).hide();
            refreshDashboard();
        } else {
            alert(data.message || 'حدث خطأ أثناء جدولة الفحص');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء جدولة الفحص');
    });
});

function checkAvailability() {
    const date = document.getElementById('scheduled_date').value;
    const equipment = document.getElementById('equipment').value;
    
    if (!date) {
        alert('يرجى اختيار التاريخ أولاً');
        return;
    }
    
    // Simulate checking availability
    const availableSlots = [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
    ];
    
    const slotsContainer = document.getElementById('availableSlots');
    slotsContainer.innerHTML = '';
    
    availableSlots.forEach(slot => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-success btn-sm';
        button.textContent = slot;
        button.onclick = () => {
            document.getElementById('scheduled_time').value = slot;
            // Remove active class from all buttons
            slotsContainer.querySelectorAll('.btn').forEach(b => b.classList.remove('btn-success'));
            slotsContainer.querySelectorAll('.btn').forEach(b => b.classList.add('btn-outline-success'));
            // Add active class to clicked button
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-success');
        };
        slotsContainer.appendChild(button);
    });
}

// Set default date to tomorrow
document.addEventListener('DOMContentLoaded', function() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('scheduled_date').value = tomorrow.toISOString().split('T')[0];
});

// Reset form when modal is hidden
document.getElementById('scheduleOrderModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('scheduleOrderForm').reset();
    document.getElementById('availableSlots').innerHTML = '';
    
    // Reset default date
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('scheduled_date').value = tomorrow.toISOString().split('T')[0];
});
</script>