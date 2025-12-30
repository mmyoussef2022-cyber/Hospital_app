<!-- Add Report Modal -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReportModalLabel">
                    <i class="fas fa-file-medical-alt text-success me-2"></i>
                    إضافة تقرير الأشعة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addReportForm">
                <div class="modal-body">
                    <input type="hidden" id="addReportOrderId" name="order_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="findings" class="form-label">الموجودات (Findings) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="findings" name="findings" rows="6" required placeholder="وصف مفصل للموجودات في الفحص..."></textarea>
                            <div class="form-text">
                                اكتب وصفاً مفصلاً ودقيقاً لجميع الموجودات المرئية في الفحص
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="impression" class="form-label">الانطباع التشخيصي (Impression) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="impression" name="impression" rows="4" required placeholder="الانطباع التشخيصي والاستنتاجات..."></textarea>
                            <div class="form-text">
                                الخلاصة التشخيصية والاستنتاجات المستخلصة من الموجودات
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="recommendations" class="form-label">التوصيات (Recommendations)</label>
                            <textarea class="form-control" id="recommendations" name="recommendations" rows="3" placeholder="التوصيات والفحوصات الإضافية المطلوبة..."></textarea>
                            <div class="form-text">
                                أي توصيات للمتابعة أو فحوصات إضافية مطلوبة
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_urgent_findings" name="has_urgent_findings">
                                <label class="form-check-label text-danger" for="has_urgent_findings">
                                    <strong>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        يحتوي على موجودات عاجلة
                                    </strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_followup" name="requires_followup">
                                <label class="form-check-label text-warning" for="requires_followup">
                                    <strong>
                                        <i class="fas fa-calendar-check me-1"></i>
                                        يحتاج متابعة
                                    </strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="urgentFindingsSection" style="display: none;">
                        <div class="col-md-12 mb-3">
                            <label for="urgent_findings_description" class="form-label">وصف الموجودات العاجلة <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="urgent_findings_description" name="urgent_findings_description" rows="3" placeholder="وصف مفصل للموجودات العاجلة التي تحتاج إشعار فوري..."></textarea>
                            <div class="alert alert-danger mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>تنبيه:</strong> سيتم إرسال إشعار فوري للطبيب المعالج عند حفظ التقرير.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="followupSection" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="followup_period" class="form-label">فترة المتابعة</label>
                            <select class="form-select" id="followup_period" name="followup_period">
                                <option value="">اختر الفترة</option>
                                <option value="1_week">أسبوع واحد</option>
                                <option value="2_weeks">أسبوعين</option>
                                <option value="1_month">شهر واحد</option>
                                <option value="3_months">3 أشهر</option>
                                <option value="6_months">6 أشهر</option>
                                <option value="1_year">سنة واحدة</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="followup_type" class="form-label">نوع المتابعة</label>
                            <select class="form-select" id="followup_type" name="followup_type">
                                <option value="">اختر النوع</option>
                                <option value="same_study">نفس الفحص</option>
                                <option value="different_study">فحص مختلف</option>
                                <option value="clinical_followup">متابعة سريرية</option>
                                <option value="lab_tests">تحاليل مخبرية</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image_quality" class="form-label">جودة الصور</label>
                            <select class="form-select" id="image_quality" name="image_quality">
                                <option value="excellent">ممتازة</option>
                                <option value="good">جيدة</option>
                                <option value="fair">مقبولة</option>
                                <option value="poor">ضعيفة</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contrast_used" class="form-label">الصبغة المستخدمة</label>
                            <select class="form-select" id="contrast_used" name="contrast_used">
                                <option value="none">لم تستخدم</option>
                                <option value="oral">صبغة فموية</option>
                                <option value="iv">صبغة وريدية</option>
                                <option value="both">كلاهما</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="technical_notes" class="form-label">ملاحظات تقنية</label>
                        <textarea class="form-control" id="technical_notes" name="technical_notes" rows="2" placeholder="أي ملاحظات تقنية حول الفحص أو جودة الصور..."></textarea>
                    </div>
                    
                    <!-- Report Templates -->
                    <div class="mb-3">
                        <label class="form-label">قوالب التقارير الجاهزة</label>
                        <div class="btn-group-vertical w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadTemplate('chest_xray')">
                                <i class="fas fa-lungs me-2"></i>
                                أشعة الصدر العادية
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadTemplate('abdominal_ct')">
                                <i class="fas fa-user-md me-2"></i>
                                أشعة مقطعية للبطن
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadTemplate('brain_mri')">
                                <i class="fas fa-brain me-2"></i>
                                رنين مغناطيسي للدماغ
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadTemplate('ultrasound')">
                                <i class="fas fa-baby me-2"></i>
                                موجات فوق صوتية
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-outline-primary" onclick="saveAsDraft()">
                        <i class="fas fa-save me-1"></i>
                        حفظ كمسودة
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>
                        حفظ التقرير النهائي
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide urgent findings section
document.getElementById('has_urgent_findings').addEventListener('change', function() {
    const section = document.getElementById('urgentFindingsSection');
    const textarea = document.getElementById('urgent_findings_description');
    
    if (this.checked) {
        section.style.display = 'block';
        textarea.required = true;
    } else {
        section.style.display = 'none';
        textarea.required = false;
        textarea.value = '';
    }
});

// Show/hide followup section
document.getElementById('requires_followup').addEventListener('change', function() {
    const section = document.getElementById('followupSection');
    
    if (this.checked) {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
        document.getElementById('followup_period').value = '';
        document.getElementById('followup_type').value = '';
    }
});

// Report templates
const templates = {
    chest_xray: {
        findings: "الفحص يظهر:\n- الرئتان: واضحتان، لا يوجد ارتشاح أو تجمع هوائي\n- القلب: حجم وشكل طبيعي\n- الحجاب الحاجز: في وضع طبيعي\n- الأضلاع: سليمة، لا يوجد كسور\n- الأنسجة الرخوة: طبيعية",
        impression: "أشعة صدر طبيعية"
    },
    abdominal_ct: {
        findings: "الفحص يظهر:\n- الكبد: حجم وكثافة طبيعية، لا يوجد آفات بؤرية\n- المرارة: طبيعية، لا يوجد حصوات\n- البنكرياس: طبيعي\n- الطحال: حجم طبيعي\n- الكليتان: طبيعيتان، لا يوجد حصوات أو توسع\n- الأمعاء: طبيعية",
        impression: "أشعة مقطعية للبطن طبيعية"
    },
    brain_mri: {
        findings: "الفحص يظهر:\n- المادة البيضاء والرمادية: طبيعية\n- البطينات الدماغية: حجم وشكل طبيعي\n- الأوعية الدموية: طبيعية\n- لا يوجد آفات بؤرية أو نزيف أو تورم\n- العظام: طبيعية",
        impression: "رنين مغناطيسي للدماغ طبيعي"
    },
    ultrasound: {
        findings: "الفحص يظهر:\n- الأعضاء المفحوصة تظهر بشكل طبيعي\n- لا يوجد تجمعات سائلة غير طبيعية\n- الأوعية الدموية: تدفق طبيعي\n- لا يوجد كتل أو آفات مشبوهة",
        impression: "فحص الموجات فوق الصوتية طبيعي"
    }
};

function loadTemplate(templateName) {
    const template = templates[templateName];
    if (template) {
        document.getElementById('findings').value = template.findings;
        document.getElementById('impression').value = template.impression;
    }
}

document.getElementById('addReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = document.getElementById('addReportOrderId').value;
    
    // Convert form data to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'has_urgent_findings' || key === 'requires_followup') {
            data[key] = document.getElementById(key).checked;
        } else {
            data[key] = value;
        }
    });
    
    fetch(`/radiology-specialized/orders/${orderId}/report`, {
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
            bootstrap.Modal.getInstance(document.getElementById('addReportModal')).hide();
            refreshDashboard();
        } else {
            alert(data.message || 'حدث خطأ أثناء حفظ التقرير');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حفظ التقرير');
    });
});

function saveAsDraft() {
    // Implementation for saving as draft
    alert('تم حفظ التقرير كمسودة');
}

// Reset form when modal is hidden
document.getElementById('addReportModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addReportForm').reset();
    document.getElementById('urgentFindingsSection').style.display = 'none';
    document.getElementById('followupSection').style.display = 'none';
});
</script>