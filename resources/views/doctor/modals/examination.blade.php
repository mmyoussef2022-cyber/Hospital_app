<!-- Examination Modal -->
<div class="modal fade" id="examinationModal" tabindex="-1" role="dialog" aria-labelledby="examinationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="examinationModalLabel">إجراء فحص طبي جديد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="examinationForm">
                    @csrf
                    
                    <!-- Patient Selection -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="examination_patient_search">البحث عن المريض</label>
                            <input type="text" class="form-control" id="examination_patient_search" placeholder="ابحث بالاسم أو الرقم الطبي...">
                            <input type="hidden" id="examination_patient_id" name="patient_id">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="searchExaminationPatients()">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                    
                    <div id="selectedExaminationPatientInfo" class="alert alert-info" style="display: none;">
                        <strong>المريض المحدد:</strong> <span id="selectedExaminationPatientName"></span>
                        <div class="mt-2">
                            <small><strong>العمر:</strong> <span id="selectedExaminationPatientAge"></span> سنة</small> |
                            <small><strong>الجنس:</strong> <span id="selectedExaminationPatientGender"></span></small>
                        </div>
                    </div>
                    
                    <!-- Examination Type -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="examination_type">نوع الفحص</label>
                            <select class="form-control" id="examination_type" name="examination_type" required>
                                <option value="">اختر نوع الفحص...</option>
                                <option value="routine">فحص روتيني</option>
                                <option value="follow_up">متابعة</option>
                                <option value="emergency">طوارئ</option>
                                <option value="consultation">استشارة</option>
                                <option value="pre_surgery">ما قبل الجراحة</option>
                                <option value="post_surgery">ما بعد الجراحة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="examination_date">تاريخ الفحص</label>
                            <input type="datetime-local" class="form-control" id="examination_date" name="examination_date" required>
                        </div>
                    </div>
                    
                    <!-- Chief Complaint -->
                    <div class="form-group mb-3">
                        <label for="chief_complaint">الشكوى الرئيسية</label>
                        <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2" placeholder="اكتب الشكوى الرئيسية للمريض..." required></textarea>
                    </div>
                    
                    <!-- Vital Signs -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">العلامات الحيوية</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="blood_pressure">ضغط الدم</label>
                                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="120/80">
                                </div>
                                <div class="col-md-3">
                                    <label for="heart_rate">معدل النبض</label>
                                    <input type="number" class="form-control" id="heart_rate" name="heart_rate" placeholder="72">
                                </div>
                                <div class="col-md-3">
                                    <label for="temperature">درجة الحرارة (°C)</label>
                                    <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" placeholder="37.0">
                                </div>
                                <div class="col-md-3">
                                    <label for="weight">الوزن (كغ)</label>
                                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" placeholder="70.0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Physical Examination -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">الفحص السريري</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="physical_examination">نتائج الفحص السريري</label>
                                <textarea class="form-control" id="physical_examination" name="physical_examination" rows="4" placeholder="اكتب نتائج الفحص السريري..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Diagnosis -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">التشخيص</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="primary_diagnosis">التشخيص الأولي</label>
                                <input type="text" class="form-control" id="primary_diagnosis" name="primary_diagnosis" placeholder="التشخيص الأولي..." required>
                            </div>
                            <div class="form-group">
                                <label for="secondary_diagnosis">التشخيص الثانوي</label>
                                <textarea class="form-control" id="secondary_diagnosis" name="secondary_diagnosis" rows="2" placeholder="تشخيصات إضافية أو محتملة..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Treatment Plan -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">خطة العلاج</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="treatment_plan">خطة العلاج</label>
                                <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3" placeholder="اكتب خطة العلاج المقترحة..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Follow-up -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="follow_up_required">متابعة مطلوبة؟</label>
                            <select class="form-control" id="follow_up_required" name="follow_up_required">
                                <option value="0">لا</option>
                                <option value="1">نعم</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="follow_up_date">تاريخ المتابعة</label>
                            <input type="date" class="form-control" id="follow_up_date" name="follow_up_date">
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-group">
                        <label for="examination_notes">ملاحظات إضافية</label>
                        <textarea class="form-control" id="examination_notes" name="notes" rows="3" placeholder="أي ملاحظات أو تعليمات إضافية..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" onclick="saveExamination()">
                    <i class="fas fa-save"></i> حفظ الفحص
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function searchExaminationPatients() {
    const query = document.getElementById('examination_patient_search').value;
    
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
                selectExaminationPatient(data.patients[0]);
            } else {
                alert('لم يتم العثور على مرضى');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('حدث خطأ أثناء البحث');
        });
}

function selectExaminationPatient(patient) {
    document.getElementById('examination_patient_id').value = patient.id;
    document.getElementById('selectedExaminationPatientName').textContent = patient.name;
    document.getElementById('selectedExaminationPatientAge').textContent = patient.age || 'غير محدد';
    document.getElementById('selectedExaminationPatientGender').textContent = patient.gender || 'غير محدد';
    document.getElementById('selectedExaminationPatientInfo').style.display = 'block';
}

function saveExamination() {
    const patientId = document.getElementById('examination_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    const formData = new FormData(document.getElementById('examinationForm'));
    
    fetch(`/doctor/examinations/save/${patientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ الفحص بنجاح');
            $('#examinationModal').modal('hide');
            
            // Reset form
            document.getElementById('examinationForm').reset();
            document.getElementById('selectedExaminationPatientInfo').style.display = 'none';
            
            // Refresh dashboard if needed
            if (typeof refreshDashboard === 'function') {
                refreshDashboard();
            }
        } else {
            alert('خطأ في حفظ الفحص: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('حدث خطأ أثناء حفظ الفحص');
    });
}

// Set current date/time when modal opens
$('#examinationModal').on('shown.bs.modal', function () {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('examination_date').value = localDateTime;
});

// Toggle follow-up date field
document.getElementById('follow_up_required').addEventListener('change', function() {
    const followUpDate = document.getElementById('follow_up_date');
    if (this.value === '1') {
        followUpDate.required = true;
        followUpDate.parentElement.style.display = 'block';
    } else {
        followUpDate.required = false;
        followUpDate.value = '';
    }
});
</script>