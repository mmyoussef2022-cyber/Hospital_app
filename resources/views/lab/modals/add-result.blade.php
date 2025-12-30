<!-- Add Result Modal -->
<div class="modal fade" id="addResultModal" tabindex="-1" aria-labelledby="addResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addResultModalLabel">
                    <i class="fas fa-plus-circle text-success me-2"></i>
                    إضافة نتيجة تحليل
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addResultForm">
                <div class="modal-body">
                    <input type="hidden" id="addResultOrderId" name="order_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parameter_name" class="form-label">اسم المعامل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="parameter_name" name="parameter_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label">القيمة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="value" name="value" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">الوحدة</label>
                            <input type="text" class="form-control" id="unit" name="unit" placeholder="مثل: mg/dl, %">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_range" class="form-label">المدى الطبيعي</label>
                            <input type="text" class="form-control" id="reference_range" name="reference_range" placeholder="مثل: 70-100">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="flag" class="form-label">التقييم <span class="text-danger">*</span></label>
                            <select class="form-select" id="flag" name="flag" required>
                                <option value="">اختر التقييم</option>
                                <option value="normal">طبيعي</option>
                                <option value="high">مرتفع</option>
                                <option value="low">منخفض</option>
                                <option value="critical_high">مرتفع جداً (حرج)</option>
                                <option value="critical_low">منخفض جداً (حرج)</option>
                                <option value="abnormal">غير طبيعي</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_critical" name="is_critical">
                                <label class="form-check-label text-danger" for="is_critical">
                                    <strong>نتيجة حرجة - تحتاج إشعار فوري</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="أي ملاحظات إضافية حول النتيجة"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>تنبيه:</strong> في حالة تحديد النتيجة كحرجة، سيتم إرسال إشعار فوري للطبيب المعالج.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        حفظ النتيجة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addResultForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = document.getElementById('addResultOrderId').value;
    
    // Convert form data to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'is_critical') {
            data[key] = document.getElementById('is_critical').checked;
        } else {
            data[key] = value;
        }
    });
    
    fetch(`/lab-specialized/orders/${orderId}/result`, {
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
            bootstrap.Modal.getInstance(document.getElementById('addResultModal')).hide();
            refreshDashboard();
        } else {
            alert(data.message || 'حدث خطأ أثناء حفظ النتيجة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حفظ النتيجة');
    });
});

// Reset form when modal is hidden
document.getElementById('addResultModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addResultForm').reset();
});
</script>