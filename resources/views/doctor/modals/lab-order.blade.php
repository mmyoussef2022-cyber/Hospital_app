<!-- Lab Order Modal -->
<div class="modal fade" id="labOrderModal" tabindex="-1" role="dialog" aria-labelledby="labOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labOrderModalLabel">طلب فحص مختبري جديد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="labOrderForm">
                    @csrf
                    
                    <!-- Patient Selection -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="lab_patient_search">البحث عن المريض</label>
                            <input type="text" class="form-control" id="lab_patient_search" placeholder="ابحث بالاسم أو الرقم الطبي...">
                            <input type="hidden" id="lab_patient_id" name="patient_id">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="searchLabPatients()">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                    
                    <div id="selectedLabPatientInfo" class="alert alert-info" style="display: none;">
                        <strong>المريض المحدد:</strong> <span id="selectedLabPatientName"></span>
                        <div class="mt-2">
                            <small><strong>العمر:</strong> <span id="selectedLabPatientAge"></span> سنة</small> |
                            <small><strong>الجنس:</strong> <span id="selectedLabPatientGender"></span></small>
                        </div>
                    </div>
                    
                    <!-- Order Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lab_order_date">تاريخ الطلب</label>
                            <input type="datetime-local" class="form-control" id="lab_order_date" name="order_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lab_priority">الأولوية</label>
                            <select class="form-control" id="lab_priority" name="priority" required>
                                <option value="routine">روتيني</option>
                                <option value="urgent">عاجل</option>
                                <option value="stat">طارئ</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Clinical Information -->
                    <div class="form-group mb-3">
                        <label for="clinical_info">المعلومات السريرية</label>
                        <textarea class="form-control" id="clinical_info" name="clinical_info" rows="2" placeholder="اكتب المعلومات السريرية والتشخيص المبدئي..." required></textarea>
                    </div>
                    
                    <!-- Lab Tests -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">الفحوصات المطلوبة</h6>
                        </div>
                        <div class="card-body">
                            <div id="labTestsContainer">
                                <div class="lab-test-item mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>نوع الفحص</label>
                                            <select class="form-control lab-test-select" name="tests[0][test_id]" required>
                                                <option value="">اختر الفحص...</option>
                                                <!-- سيتم تحميل الفحوصات ديناميكياً -->
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>العينة المطلوبة</label>
                                            <select class="form-control" name="tests[0][specimen_type]" required>
                                                <option value="">اختر نوع العينة...</option>
                                                <option value="blood">دم</option>
                                                <option value="urine">بول</option>
                                                <option value="stool">براز</option>
                                                <option value="sputum">بلغم</option>
                                                <option value="csf">سائل شوكي</option>
                                                <option value="tissue">نسيج</option>
                                                <option value="swab">مسحة</option>
                                                <option value="other">أخرى</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>الكمية</label>
                                            <input type="text" class="form-control" name="tests[0][quantity]" placeholder="5ml">
                                        </div>
                                        <div class="col-md-2">
                                            <label>صيام مطلوب؟</label>
                                            <select class="form-control" name="tests[0][fasting_required]">
                                                <option value="0">لا</option>
                                                <option value="1">نعم</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeLabTest(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <label>تعليمات خاصة</label>
                                            <input type="text" class="form-control" name="tests[0][special_instructions]" placeholder="أي تعليمات خاصة للفحص...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-sm" onclick="addLabTest()">
                                <i class="fas fa-plus"></i> إضافة فحص
                            </button>
                        </div>
                    </div>
                    
                    <!-- Special Instructions -->
                    <div class="form-group">
                        <label for="lab_special_instructions">تعليمات خاصة للمختبر</label>
                        <textarea class="form-control" id="lab_special_instructions" name="special_instructions" rows="2" placeholder="أي تعليمات خاصة للمختبر..."></textarea>
                    </div>
                    
                    <!-- Expected Results Date -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expected_date">التاريخ المتوقع للنتائج</label>
                            <input type="date" class="form-control" id="expected_date" name="expected_date">
                        </div>
                        <div class="col-md-6">
                            <label for="lab_department">القسم المختص</label>
                            <select class="form-control" id="lab_department" name="department">
                                <option value="">اختر القسم...</option>
                                <option value="hematology">أمراض الدم</option>
                                <option value="biochemistry">الكيمياء الحيوية</option>
                                <option value="microbiology">الأحياء الدقيقة</option>
                                <option value="immunology">المناعة</option>
                                <option value="pathology">علم الأمراض</option>
                                <option value="genetics">الوراثة</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-info" onclick="previewLabOrder()">
                    <i class="fas fa-eye"></i> معاينة الطلب
                </button>
                <button type="button" class="btn btn-primary" onclick="saveLabOrder()">
                    <i class="fas fa-save"></i> حفظ الطلب
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let labTestCounter = 1;

function addLabTest() {
    const container = document.getElementById('labTestsContainer');
    const newTest = document.querySelector('.lab-test-item').cloneNode(true);
    
    // Update field names
    newTest.querySelectorAll('input, select').forEach(field => {
        if (field.name) {
            field.name = field.name.replace('[0]', `[${labTestCounter}]`);
            field.value = '';
        }
    });
    
    container.appendChild(newTest);
    labTestCounter++;
}

function removeLabTest(button) {
    const testItems = document.querySelectorAll('.lab-test-item');
    if (testItems.length > 1) {
        button.closest('.lab-test-item').remove();
    } else {
        alert('يجب أن يحتوي الطلب على فحص واحد على الأقل');
    }
}

function searchLabPatients() {
    const query = document.getElementById('lab_patient_search').value;
    
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
                selectLabPatient(data.patients[0]);
            } else {
                alert('لم يتم العثور على مرضى');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('حدث خطأ أثناء البحث');
        });
}

function selectLabPatient(patient) {
    document.getElementById('lab_patient_id').value = patient.id;
    document.getElementById('selectedLabPatientName').textContent = patient.name;
    document.getElementById('selectedLabPatientAge').textContent = patient.age || 'غير محدد';
    document.getElementById('selectedLabPatientGender').textContent = patient.gender || 'غير محدد';
    document.getElementById('selectedLabPatientInfo').style.display = 'block';
}

function previewLabOrder() {
    const patientId = document.getElementById('lab_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    // Generate preview content
    const patientName = document.getElementById('selectedLabPatientName').textContent;
    const orderDate = document.getElementById('lab_order_date').value;
    const priority = document.getElementById('lab_priority').value;
    
    let previewContent = `
        <h5>طلب فحص مختبري</h5>
        <p><strong>المريض:</strong> ${patientName}</p>
        <p><strong>تاريخ الطلب:</strong> ${orderDate}</p>
        <p><strong>الأولوية:</strong> ${priority}</p>
        <h6>الفحوصات المطلوبة:</h6>
        <ul>
    `;
    
    document.querySelectorAll('.lab-test-select').forEach(select => {
        if (select.value) {
            previewContent += `<li>${select.options[select.selectedIndex].text}</li>`;
        }
    });
    
    previewContent += '</ul>';
    
    // Show preview in a simple alert (in real implementation, use a proper modal)
    alert(previewContent.replace(/<[^>]*>/g, '\n'));
}

function saveLabOrder() {
    const patientId = document.getElementById('lab_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    const formData = new FormData(document.getElementById('labOrderForm'));
    
    fetch(`/doctor/lab-orders/save/${patientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ طلب الفحص المختبري بنجاح');
            $('#labOrderModal').modal('hide');
            
            // Reset form
            document.getElementById('labOrderForm').reset();
            document.getElementById('selectedLabPatientInfo').style.display = 'none';
            
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

// Load lab tests when modal opens
$('#labOrderModal').on('shown.bs.modal', function () {
    loadLabTests();
    
    // Set current date/time
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('lab_order_date').value = localDateTime;
    
    // Set expected date (3 days from now)
    const expectedDate = new Date();
    expectedDate.setDate(expectedDate.getDate() + 3);
    document.getElementById('expected_date').value = expectedDate.toISOString().slice(0, 10);
});

function loadLabTests() {
    // Load available lab tests
    fetch('/api/lab-tests')
        .then(response => response.json())
        .then(data => {
            const selects = document.querySelectorAll('.lab-test-select');
            selects.forEach(select => {
                select.innerHTML = '<option value="">اختر الفحص...</option>';
                if (data.tests) {
                    data.tests.forEach(test => {
                        select.innerHTML += `<option value="${test.id}">${test.name}</option>`;
                    });
                }
            });
        })
        .catch(error => {
            console.error('Load lab tests error:', error);
            // Add some default options if API fails
            const selects = document.querySelectorAll('.lab-test-select');
            selects.forEach(select => {
                select.innerHTML = `
                    <option value="">اختر الفحص...</option>
                    <option value="cbc">تعداد الدم الكامل (CBC)</option>
                    <option value="glucose">سكر الدم</option>
                    <option value="creatinine">الكرياتينين</option>
                    <option value="urea">اليوريا</option>
                    <option value="lipid_profile">دهون الدم</option>
                    <option value="liver_function">وظائف الكبد</option>
                    <option value="thyroid_function">وظائف الغدة الدرقية</option>
                    <option value="urine_analysis">تحليل البول</option>
                `;
            });
        });
}
</script>