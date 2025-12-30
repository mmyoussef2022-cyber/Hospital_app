<!-- Patient Search Modal -->
<div class="modal fade" id="patientSearchModal" tabindex="-1" role="dialog" aria-labelledby="patientSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientSearchModalLabel">البحث عن مريض</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="patientSearchInput" placeholder="ابحث بالاسم، الرقم الطبي، أو رقم الهاتف...">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary btn-block" onclick="searchPatients()">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </div>
                
                <div id="searchResults">
                    <div class="text-center text-muted">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>ابدأ البحث للعثور على المرضى</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function searchPatients() {
    const query = document.getElementById('patientSearchInput').value;
    
    if (query.length < 2) {
        alert('يرجى إدخال حرفين على الأقل للبحث');
        return;
    }
    
    document.getElementById('searchResults').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">جاري البحث...</span>
            </div>
            <p class="mt-2">جاري البحث...</p>
        </div>
    `;
    
    fetch(`/doctor/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data.patients);
        })
        .catch(error => {
            console.error('Search error:', error);
            document.getElementById('searchResults').innerHTML = `
                <div class="alert alert-danger">
                    حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.
                </div>
            `;
        });
}

function displaySearchResults(patients) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-user-slash fa-2x mb-2"></i>
                <p>لم يتم العثور على مرضى</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="list-group">';
    
    patients.forEach(patient => {
        html += `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <div>
                        <h6 class="mb-1">${patient.name}</h6>
                        <p class="mb-1">
                            <small class="text-muted">
                                <i class="fas fa-id-card"></i> ${patient.medical_number} | 
                                <i class="fas fa-phone"></i> ${patient.phone} | 
                                <i class="fas fa-birthday-cake"></i> ${patient.age} سنة
                            </small>
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="selectPatient(${patient.id})">
                            اختيار
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    resultsContainer.innerHTML = html;
}

function selectPatient(patientId) {
    window.location.href = `/doctor/examination/conduct/${patientId}`;
}

// Search on Enter key
document.getElementById('patientSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchPatients();
    }
});
</script>