<!-- Prescription Modal -->
<div class="modal fade" id="prescriptionModal" tabindex="-1" role="dialog" aria-labelledby="prescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prescriptionModalLabel">إنشاء وصفة طبية جديدة</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="prescriptionForm">
                    @csrf
                    
                    <!-- Patient Selection -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="prescription_patient_search">البحث عن المريض</label>
                            <input type="text" class="form-control" id="prescription_patient_search" placeholder="ابحث بالاسم أو الرقم الطبي...">
                            <input type="hidden" id="prescription_patient_id" name="patient_id">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="searchPrescriptionPatients()">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                    
                    <div id="selectedPatientInfo" class="alert alert-info" style="display: none;">
                        <strong>المريض المحدد:</strong> <span id="selectedPatientName"></span>
                        <div class="mt-2">
                            <small><strong>الحساسية:</strong> <span id="selectedPatientAllergies">لا توجد</span></small>
                        </div>
                    </div>
                    
                    <!-- Medications -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">الأدوية</h6>
                        </div>
                        <div class="card-body">
                            <div id="medicationsContainer">
                                <div class="medication-item mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>اسم الدواء</label>
                                            <select class="form-control medication-select" name="medications[0][medication_id]" required>
                                                <option value="">اختر الدواء...</option>
                                                <!-- سيتم تحميل الأدوية ديناميكياً -->
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>الجرعة</label>
                                            <input type="text" class="form-control" name="medications[0][dosage]" placeholder="500mg" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>التكرار</label>
                                            <select class="form-control" name="medications[0][frequency]" required>
                                                <option value="">اختر...</option>
                                                <option value="مرة واحدة يومياً">مرة واحدة يومياً</option>
                                                <option value="مرتين يومياً">مرتين يومياً</option>
                                                <option value="ثلاث مرات يومياً">ثلاث مرات يومياً</option>
                                                <option value="أربع مرات يومياً">أربع مرات يومياً</option>
                                                <option value="عند الحاجة">عند الحاجة</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>المدة</label>
                                            <input type="text" class="form-control" name="medications[0][duration]" placeholder="7 أيام" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>تعليمات</label>
                                            <input type="text" class="form-control" name="medications[0][instructions]" placeholder="بعد الأكل">
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeMedication(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-sm" onclick="addMedication()">
                                <i class="fas fa-plus"></i> إضافة دواء
                            </button>
                        </div>
                    </div>
                    
                    <!-- Drug Interactions Alert -->
                    <div id="interactionsAlert" class="alert alert-warning" style="display: none;">
                        <h6><i class="fas fa-exclamation-triangle"></i> تحذير: تفاعلات دوائية</h6>
                        <div id="interactionsList"></div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-group">
                        <label for="prescription_notes">ملاحظات إضافية</label>
                        <textarea class="form-control" id="prescription_notes" name="notes" rows="3" placeholder="أي ملاحظات أو تعليمات إضافية..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-warning" onclick="checkInteractions()">
                    <i class="fas fa-exclamation-triangle"></i> فحص التفاعلات
                </button>
                <button type="button" class="btn btn-primary" onclick="savePrescription()">
                    <i class="fas fa-save"></i> حفظ الوصفة
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let medicationCounter = 1;

function addMedication() {
    const container = document.getElementById('medicationsContainer');
    const newMedication = document.querySelector('.medication-item').cloneNode(true);
    
    // Update field names
    newMedication.querySelectorAll('input, select').forEach(field => {
        if (field.name) {
            field.name = field.name.replace('[0]', `[${medicationCounter}]`);
            field.value = '';
        }
    });
    
    container.appendChild(newMedication);
    medicationCounter++;
}

function removeMedication(button) {
    const medicationItems = document.querySelectorAll('.medication-item');
    if (medicationItems.length > 1) {
        button.closest('.medication-item').remove();
    } else {
        alert('يجب أن تحتوي الوصفة على دواء واحد على الأقل');
    }
}

function searchPrescriptionPatients() {
    const query = document.getElementById('prescription_patient_search').value;
    
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
                selectPrescriptionPatient(data.patients[0]);
            } else {
                alert('لم يتم العثور على مرضى');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('حدث خطأ أثناء البحث');
        });
}

function selectPrescriptionPatient(patient) {
    document.getElementById('prescription_patient_id').value = patient.id;
    document.getElementById('selectedPatientName').textContent = patient.name;
    document.getElementById('selectedPatientAllergies').textContent = patient.allergies ? patient.allergies.join(', ') : 'لا توجد';
    document.getElementById('selectedPatientInfo').style.display = 'block';
}

function checkInteractions() {
    const patientId = document.getElementById('prescription_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    const formData = new FormData(document.getElementById('prescriptionForm'));
    
    fetch('/doctor/prescriptions/check-interactions', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.interactions && data.interactions.length > 0) {
            showInteractions(data.interactions);
        } else {
            document.getElementById('interactionsAlert').style.display = 'none';
            alert('لا توجد تفاعلات دوائية معروفة');
        }
    })
    .catch(error => {
        console.error('Interaction check error:', error);
        alert('حدث خطأ أثناء فحص التفاعلات');
    });
}

function showInteractions(interactions) {
    const alert = document.getElementById('interactionsAlert');
    const list = document.getElementById('interactionsList');
    
    let html = '<ul>';
    interactions.forEach(interaction => {
        html += `<li><strong>${interaction.severity}:</strong> ${interaction.description}</li>`;
    });
    html += '</ul>';
    
    list.innerHTML = html;
    alert.style.display = 'block';
}

function savePrescription() {
    const patientId = document.getElementById('prescription_patient_id').value;
    
    if (!patientId) {
        alert('يرجى اختيار المريض أولاً');
        return;
    }
    
    const formData = new FormData(document.getElementById('prescriptionForm'));
    
    fetch(`/doctor/prescriptions/save/${patientId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ الوصفة بنجاح');
            $('#prescriptionModal').modal('hide');
            
            // Reset form
            document.getElementById('prescriptionForm').reset();
            document.getElementById('selectedPatientInfo').style.display = 'none';
            document.getElementById('interactionsAlert').style.display = 'none';
        } else {
            alert('خطأ في حفظ الوصفة: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('حدث خطأ أثناء حفظ الوصفة');
    });
}

// Load medications when modal opens
$('#prescriptionModal').on('shown.bs.modal', function () {
    loadMedications();
});

function loadMedications() {
    // Load available medications
    fetch('/api/medications')
        .then(response => response.json())
        .then(data => {
            const selects = document.querySelectorAll('.medication-select');
            selects.forEach(select => {
                select.innerHTML = '<option value="">اختر الدواء...</option>';
                data.medications.forEach(med => {
                    select.innerHTML += `<option value="${med.id}">${med.name}</option>`;
                });
            });
        })
        .catch(error => {
            console.error('Load medications error:', error);
        });
}
</script>