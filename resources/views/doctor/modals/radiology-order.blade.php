<!-- Radiology Order Modal -->
<div class="modal fade" id="radiologyOrderModal" tabindex="-1" role="dialog" aria-labelledby="radiologyOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="radiologyOrderModalLabel">طلب فحص أشعة جديد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="radiologyOrderForm">
                    @csrf
                    
                    <!-- Patient Selection -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="radiology_patient_search">البحث عن المريض</label>
                            <input type="text" class="form-control" id="radiology_patient_search" placeholder="ابحث بالاسم أو الرقم الطبي...">
                            <input type="hidden" id="radiology_patient_id" name="patient_id">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="searchRadiologyPatients()">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                    
                    <div id="selectedRadiologyPatientInfo" class="alert alert-info" style="display: none;">
                        <strong>المريض المحدد:</strong> <span id="selectedRadiologyPatientName"></span>
                        <div class="mt-2">
                            <small><strong>العمر:</strong> <span id="selectedRadiologyPatientAge"></span> سنة</small> |
                            <small><strong>الجنس:</strong> <span id="selectedRadiologyPatientGender"></span></small>
                        </div>
                    </div>
                    
                    <!-- Order Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="radiology_order_date">تاريخ الطلب</label>
                            <input type="datetime-local" class="form-control" id="radiology_order_date" name="order_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="radiology_priority">الأولوية</label>
                            <select class="form-control" id="radiology_priority" name="priority" required>
                                <option value="routine">روتيني</option>
                                <option value="urgent">عاجل</option>
                                <option value="stat">طارئ</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Clinical Information -->
                    <div class="form-group mb-3">
                        <label for="radiology_clinical_info">المعلومات السريرية والتشخيص المبدئي</label>
                        <textarea class="form-control" id="radiology_clinical_info" name="clinical_info" rows="2" placeholder="اكتب المعلومات السريرية والتشخيص المبدئي..." required></textarea>
                    </div>
                    
                    <!-- Radiology Studies -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">الفحوصات الإشعاعية المطلوبة</h6>
                        </div>
                        <div class="card-body">
                            <div id="radiologyStudiesContainer">
                                <div class="radiology-study-item mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>نوع الفحص الإشعاعي</label>
                                            <select class="form-control radiology-study-select" name="studies[0][study_id]" required>
                                                <option value="">اختر نوع الفحص...</option>
                                                <!-- سيتم تحميل الفحوصات ديناميكياً -->
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>المنطقة المراد فحصها</label>
                                            <select class="form-control" name="studies[0][body_part]" required>
                                                <option value="">اختر المنطقة...</option>
                                                <option value="head">الرأس</option>
                                                <option value="neck">الرقبة</option>
                                                <option value="chest">الصدر</option>
                                                <option value="abdomen">البطن</option>
                                                <option value="pelvis">الحوض</option>
                                                <option value="spine">العمود الفقري</option>
                                                <option value="upper_extremity">الطرف العلوي</option>
                                                <option value="lower_extremity">الطرف السفلي</option>
                                                <option value="whole_body">الجسم كاملاً</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>مع الصبغة؟</label>
                                            <select class="form-control" name="studies[0][with_contrast]">
                                                <option value="0">لا</option>
                                                <option value="1">نعم</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>الجانب</label>
                                            <select class="form-control" name="studies[0][laterality]">
                                                <option value="bilateral">الجانبين</option>
                                                <option value="right">الأيمن</option>
                                                <option value="left">الأيسر</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeRadiologyStudy(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <label>تعليمات خاصة للفحص</label>
                                            <input type="text" class="form-control" name="studies[0][special_instructions]" placeholder="أي تعليمات خاصة للفحص الإشعاعي...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-sm" onclick="addRadiologyStudy()">
                                <i class="fas fa-plus"></i> إضافة فحص إشعاعي
                            </button>
                        </div>
                    </div>
                    
                    <!-- Patient Preparation -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">تحضير المريض</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="fasting_required" name="fasting_required" value="1">
                                        <label class="form-check-label" for="fasting_required">
                                            صيام مطلوب
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_metal" name="remove_metal" value="1">
                                        <label class="form-check-label" for="remove_metal">
                                            إزالة المعادن
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pregnancy_check" name="pregnancy_check" value="1">
                                        <label class="form-check-label" for="pregnancy_check">
                                            فحص الحمل (للنساء)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <label for="preparation_instructions">تعليمات التحضير</label>
                                <textarea class="form-control" id="preparation_instructions" name="preparation_instructions" rows="2" placeholder="تعليمات خاصة لتحضير المريض..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Allergies and Contraindications -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">الحساسية وموانع الاستعمال</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="known_allergies">الحساسية المعروفة</label>
                                <textarea class="form-control" id="known_allergies" name="known_allergies" rows="2" placeholder="اكتب أي حساسية معروفة للمريض..."></textarea>
                            </div>
                            <div class="form-group">
                                <label for="contraindications">موانع الاستعمال</label>
                                <textarea class="form-control" id="contraindications" name="contraindications" rows="2" placeholder="اكتب أي موانع للفحص الإشعاعي..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expected Results Date -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="radiology_expected_date">التاريخ المتوقع للنتائج</label>
                            <input type="date" class="form-control" id="radiology_expected_date" name="expected_date">
                        </div>
                        <div class="col-md-6">
                            <label for="radiology_department">قسم الأشعة</label>
                            <select class="form-control" id="radiology_department" name="department">
                                <option value="">اختر القسم...</option>
                                <option value="general_radiology">الأشعة العامة</option>
                                <option value="ct_scan">الأشعة المقطعية</option>
                                <option value="mri">الرنين المغناطيسي</option>
                                <option value="ultrasound">الموجات فوق الصوتية</option>
                                <option value="nuclear_medicine">الطب النووي</option>
                                <option value="interventional">الأشعة التداخلية</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="form-group">
                        <label for="radiology_notes">ملاحظات إضافية</label>
                        <textarea class="form-control" id="radiology_notes" name="notes" rows="3" placeholder="أي ملاحظات أو تعليمات إضافية..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-info" onclick="previewRadiologyOrder()">
                    <i class="fas fa-eye"></i> معاينة الطلب
                </button>
                <button type="button" class="btn btn-primary" onclick="saveRadiologyOrder()">
                    <i class="fas fa-save"></i> حفظ الطلب
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let radiologyStudyCounter = 1;

function addRadiologyStudy() {
    const container = document.getElementById('radiologyStudiesContainer');
    const newStudy = document.querySelector('.radiology-study-item').cloneNode(true);
    
    // Update field names
    newStudy.querySelectorAll('input, select').forEach(field => {
        if (field.name) {
            field.name = field.name.replace('[0]', `[${radiologyStudyCounter}]`);
            field.value = '';
        }
    });
    
    container.appendChild(newStudy);
    radiologyStudyCounter++;
}

function removeRadiologyStudy(button) {
    const studyItems = document.querySelectorAll('.radiology-study-item');
    if (studyItems.length > 1) {
        button.closest('.radiology-study-item').remove();
    } else {
        alert('يجب أن يحتوي الطلب على فحص واحد على الأقل');
    }
}

function searchRadiologyPatients() {
    const query = document.getElementById('radiology_patient_search').value;
    
    if (query.length < 2) {
        alert('يرجى إدخال حرفين على الأقل للبحث');
        return;
    }
    
    fetch(`/doctor/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.patients.length > 0) {
                // For simplicity, select the first patient
                // In a real implementation, show a dropdown or list
                selectRadiologyPatient(data.patients[0]);
            } else {
                alert('لم يتم العثور على مرضى');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('حدث خطأ أثناء البحث');
        });
}

function selectRadiologyPatient(patient) {
    document.getElementById('radiology_patient_id').value = patient.id;
    document.getElementById('selectedRadiologyPatientName').textContent = patient.name;
    document.getElementById('selectedRadiologyPatientAge').textContent = patient.age || 'غير محدد';
    document.getElementById('selectedRadiologyPatientGender').textContent = patient.gender || 'غير محدد';
    document.getElementById('selectedRadiologyPatientInfo').style.display = 'block';
}

function previewRadiologyOrder() {
    const patientId = document.getElementById('radiology_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    // Generate preview content
    const patientName = document.getElementById('selectedRadiologyPatientName').textContent;
    const orderDate = document.getElementById('radiology_order_date').value;
    const priority = document.getElementById('radiology_priority').value;
    
    let previewContent = `
        <h5>طلب فحص أشعة</h5>
        <p><strong>المريض:</strong> ${patientName}</p>
        <p><strong>تاريخ الطلب:</strong> ${orderDate}</p>
        <p><strong>الأولوية:</strong> ${priority}</p>
        <h6>الفحوصات المطلوبة:</h6>
        <ul>
    `;
    
    document.querySelectorAll('.radiology-study-select').forEach(select => {
        if (select.value) {
            previewContent += `<li>${select.options[select.selectedIndex].text}</li>`;
        }
    });
    
    previewContent += '</ul>';
    
    // Show preview in a simple alert (in real implementation, use a proper modal)
    alert(previewContent.replace(/<[^>]*>/g, '\n'));
}

function saveRadiologyOrder() {
    const patientId = document.getElementById('radiology_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    const formData = new FormData(document.getElementById('radiologyOrderForm'));
    
    fetch(`/doctor/radiology-orders/save/${patientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ طلب الفحص الإشعاعي بنجاح');
            $('#radiologyOrderModal').modal('hide');
            
            // Reset form
            document.getElementById('radiologyOrderForm').reset();
            document.getElementById('selectedRadiologyPatientInfo').style.display = 'none';
            
            // Refresh dashboard if needed
            if (typeof refreshDashboard === 'function') {
                refreshDashboard();
            }
        } else {
            alert('خطأ في حفظ طلب الفحص: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('حدث خطأ أثناء حفظ طلب الفحص');
    });
}

// Load radiology studies when modal opens
$('#radiologyOrderModal').on('shown.bs.modal', function () {
    loadRadiologyStudies();
    
    // Set current date/time
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('radiology_order_date').value = localDateTime;
    
    // Set expected date (2 days from now for radiology)
    const expectedDate = new Date();
    expectedDate.setDate(expectedDate.getDate() + 2);
    document.getElementById('radiology_expected_date').value = expectedDate.toISOString().slice(0, 10);
});

function loadRadiologyStudies() {
    // Load available radiology studies
    fetch('/api/radiology-studies')
        .then(response => response.json())
        .then(data => {
            const selects = document.querySelectorAll('.radiology-study-select');
            selects.forEach(select => {
                select.innerHTML = '<option value="">اختر نوع الفحص...</option>';
                if (data.studies) {
                    data.studies.forEach(study => {
                        select.innerHTML += `<option value="${study.id}">${study.name}</option>`;
                    });
                }
            });
        })
        .catch(error => {
            console.error('Load radiology studies error:', error);
            // Add some default options if API fails
            const selects = document.querySelectorAll('.radiology-study-select');
            selects.forEach(select => {
                select.innerHTML = `
                    <option value="">اختر نوع الفحص...</option>
                    <option value="xray">أشعة سينية (X-Ray)</option>
                    <option value="ct_scan">أشعة مقطعية (CT Scan)</option>
                    <option value="mri">رنين مغناطيسي (MRI)</option>
                    <option value="ultrasound">موجات فوق صوتية (Ultrasound)</option>
                    <option value="mammography">تصوير الثدي (Mammography)</option>
                    <option value="bone_scan">مسح العظام</option>
                    <option value="pet_scan">مسح PET</option>
                    <option value="angiography">تصوير الأوعية الدموية</option>
                `;
            });
        });
}
</script>