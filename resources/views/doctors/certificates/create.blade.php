@extends('layouts.app')

@section('page-title', 'إضافة شهادة جديدة - ' . $doctor->full_name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-award text-facebook"></i>
                        إضافة شهادة جديدة
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-info">
                            <i class="bi bi-person"></i>
                            ملف الطبيب
                        </a>
                        <a href="{{ route('doctor-certificates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right"></i>
                            قائمة الشهادات
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Doctor Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $doctor->profile_photo_url }}" 
                                         alt="{{ $doctor->user->name }}" 
                                         class="rounded-circle me-3" 
                                         width="50" height="50"
                                         style="object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $doctor->full_name }}</h6>
                                        <small class="text-muted">
                                            {{ $doctor->doctor_number }} - 
                                            {{ \App\Models\Doctor::getSpecializations()[$doctor->specialization] ?? $doctor->specialization }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('doctors.certificates.store', $doctor) }}" enctype="multipart/form-data" id="certificateForm">
                        @csrf
                        
                        <!-- Certificate Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle"></i>
                                    معلومات الشهادة
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">عنوان الشهادة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع الشهادة <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">اختر نوع الشهادة</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="institution" class="form-label">المؤسسة المانحة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('institution') is-invalid @enderror" 
                                       id="institution" name="institution" value="{{ old('institution') }}" required>
                                @error('institution')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">البلد</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country') }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="issue_date" class="form-label">تاريخ الإصدار <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                       id="issue_date" name="issue_date" value="{{ old('issue_date') }}" required>
                                @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">تاريخ الانتهاء</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">اتركه فارغاً إذا كانت الشهادة لا تنتهي</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="certificate_number" class="form-label">رقم الشهادة</label>
                                <input type="text" class="form-control @error('certificate_number') is-invalid @enderror" 
                                       id="certificate_number" name="certificate_number" value="{{ old('certificate_number') }}">
                                @error('certificate_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">حد أقصى 1000 حرف</div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-facebook border-bottom pb-2 mb-3">
                                    <i class="bi bi-file-earmark-arrow-up"></i>
                                    رفع ملف الشهادة
                                </h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="certificate_file" class="form-label">ملف الشهادة</label>
                                <input type="file" class="form-control @error('certificate_file') is-invalid @enderror" 
                                       id="certificate_file" name="certificate_file" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                @error('certificate_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    الأنواع المدعومة: PDF, JPG, JPEG, PNG | الحد الأقصى: 5 ميجابايت
                                </div>
                            </div>
                            
                            <!-- File Preview -->
                            <div class="col-12" id="filePreview" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">معاينة الملف</h6>
                                        <div id="previewContent"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-facebook">
                                        <i class="bi bi-check-circle"></i>
                                        حفظ الشهادة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// File preview functionality
document.getElementById('certificate_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const previewContent = document.getElementById('previewContent');
    
    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB
        const fileType = file.type;
        
        let content = `
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-${getFileIcon(fileType)} text-facebook me-3" style="font-size: 2rem;"></i>
                <div>
                    <div class="fw-bold">${file.name}</div>
                    <small class="text-muted">الحجم: ${fileSize} ميجابايت | النوع: ${fileType}</small>
                </div>
            </div>
        `;
        
        // If it's an image, show preview
        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                content += `
                    <div class="mt-3">
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                    </div>
                `;
                previewContent.innerHTML = content;
            };
            reader.readAsDataURL(file);
        } else {
            previewContent.innerHTML = content;
        }
        
        preview.style.display = 'block';
        
        // Validate file size
        if (fileSize > 5) {
            alert('حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت');
            this.value = '';
            preview.style.display = 'none';
        }
    } else {
        preview.style.display = 'none';
    }
});

function getFileIcon(fileType) {
    if (fileType === 'application/pdf') return 'pdf';
    if (fileType.startsWith('image/')) return 'image';
    return 'text';
}

// Form validation
document.getElementById('certificateForm').addEventListener('submit', function(e) {
    const issueDate = new Date(document.getElementById('issue_date').value);
    const expiryDate = document.getElementById('expiry_date').value;
    
    if (expiryDate) {
        const expiry = new Date(expiryDate);
        if (expiry <= issueDate) {
            e.preventDefault();
            alert('تاريخ الانتهاء يجب أن يكون بعد تاريخ الإصدار');
            document.getElementById('expiry_date').focus();
            return false;
        }
    }
    
    if (issueDate > new Date()) {
        e.preventDefault();
        alert('تاريخ الإصدار لا يمكن أن يكون في المستقبل');
        document.getElementById('issue_date').focus();
        return false;
    }
});

// Auto-fill suggestions based on type
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const titleField = document.getElementById('title');
    
    if (type && !titleField.value) {
        const suggestions = {
            'degree': 'بكالوريوس الطب والجراحة',
            'master': 'ماجستير في ',
            'phd': 'دكتوراه في ',
            'fellowship': 'زمالة ',
            'board': 'البورد العربي في ',
            'certificate': 'شهادة في ',
            'course': 'دورة تدريبية في ',
            'license': 'ترخيص مزاولة المهنة'
        };
        
        if (suggestions[type]) {
            titleField.value = suggestions[type];
            titleField.focus();
            titleField.setSelectionRange(titleField.value.length, titleField.value.length);
        }
    }
});

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const maxLength = 1000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    let counterElement = document.getElementById('descriptionCounter');
    if (!counterElement) {
        counterElement = document.createElement('div');
        counterElement.id = 'descriptionCounter';
        counterElement.className = 'form-text';
        this.parentNode.appendChild(counterElement);
    }
    
    counterElement.textContent = `${remaining} حرف متبقي`;
    counterElement.className = remaining < 100 ? 'form-text text-warning' : 'form-text';
});
</script>
@endpush
@endsection