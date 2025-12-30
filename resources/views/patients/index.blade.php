@extends('layouts.app')

@section('title', 'إدارة المرضى')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        إدارة المرضى
                    </h3>
                    <div>
                        <a href="{{ route('patients.families') }}" class="btn btn-outline-info me-2">
                            <i class="bi bi-houses me-1"></i>
                            إدارة العائلات
                        </a>
                        <a href="{{ route('patients.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            إضافة مريض جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('patients.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="البحث بالاسم، رقم المريض، أو الهاتف"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <select name="gender" class="form-select">
                                    <option value="">جميع الأجناس</option>
                                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select name="blood_type" class="form-select">
                                    <option value="">جميع فصائل الدم</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                        <option value="{{ $bloodType }}" {{ request('blood_type') == $bloodType ? 'selected' : '' }}>
                                            {{ $bloodType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select name="patient_type" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    <option value="outpatient" {{ request('patient_type') == 'outpatient' ? 'selected' : '' }}>خارجي</option>
                                    <option value="inpatient" {{ request('patient_type') == 'inpatient' ? 'selected' : '' }}>داخلي</option>
                                    <option value="emergency" {{ request('patient_type') == 'emergency' ? 'selected' : '' }}>طوارئ</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-funnel"></i>
                                        تصفية
                                    </button>
                                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                        إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Patients Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم المريض</th>
                                    <th>الاسم</th>
                                    <th>العمر</th>
                                    <th>الجنس</th>
                                    <th>فصيلة الدم</th>
                                    <th>الهاتف</th>
                                    <th>نوع المريض</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patients as $patient)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $patient->patient_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($patient->profile_photo)
                                                    <img src="{{ asset('storage/' . $patient->profile_photo) }}" 
                                                         alt="صورة المريض" 
                                                         class="rounded-circle me-2" 
                                                         width="32" height="32">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $patient->name }}</strong>
                                                    @if($patient->family_head_id)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-people"></i>
                                                            {{ $patient->family_relation }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $patient->age }} سنة</td>
                                        <td>
                                            <i class="bi {{ $patient->gender == 'male' ? 'bi-gender-male text-primary' : 'bi-gender-female text-danger' }}"></i>
                                            {{ $patient->gender == 'male' ? 'ذكر' : 'أنثى' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $patient->blood_type }}</span>
                                        </td>
                                        <td>{{ $patient->mobile }}</td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'outpatient' => 'success',
                                                    'inpatient' => 'warning',
                                                    'emergency' => 'danger'
                                                ];
                                                $typeNames = [
                                                    'outpatient' => 'خارجي',
                                                    'inpatient' => 'داخلي',
                                                    'emergency' => 'طوارئ'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$patient->patient_type] ?? 'secondary' }}">
                                                {{ $typeNames[$patient->patient_type] ?? $patient->patient_type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($patient->is_active)
                                                <span class="badge bg-success">مفعل</span>
                                            @else
                                                <span class="badge bg-secondary">غير مفعل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('patients.show', $patient) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="عرض">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('patients.edit', $patient) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('patients.toggle-status', $patient) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-{{ $patient->is_active ? 'secondary' : 'success' }}" 
                                                            title="{{ $patient->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}">
                                                        <i class="bi bi-{{ $patient->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                لا توجد بيانات مرضى
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($patients->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $patients->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">البحث بالباركود</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="barcodeInput" class="form-label">رقم الباركود</label>
                    <input type="text" class="form-control" id="barcodeInput" placeholder="امسح أو أدخل رقم الباركود">
                </div>
                <div id="barcodeResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="searchByBarcode()">بحث</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function searchByBarcode() {
    const barcode = document.getElementById('barcodeInput').value;
    const resultDiv = document.getElementById('barcodeResult');
    
    if (!barcode) {
        resultDiv.innerHTML = '<div class="alert alert-warning">يرجى إدخال رقم الباركود</div>';
        return;
    }
    
    fetch('{{ route("patients.search-barcode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ barcode: barcode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const patient = data.patient;
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6>تم العثور على المريض:</h6>
                    <p><strong>الاسم:</strong> ${patient.name}</p>
                    <p><strong>رقم المريض:</strong> ${patient.patient_number}</p>
                    <p><strong>العمر:</strong> ${patient.age} سنة</p>
                    <p><strong>الجنس:</strong> ${patient.gender == 'male' ? 'ذكر' : 'أنثى'}</p>
                    <a href="/patients/${patient.id}" class="btn btn-primary btn-sm">عرض الملف</a>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء البحث</div>';
    });
}

// Add barcode scanner button to header
document.addEventListener('DOMContentLoaded', function() {
    const cardHeader = document.querySelector('.card-header');
    const scannerBtn = document.createElement('button');
    scannerBtn.className = 'btn btn-outline-primary me-2';
    scannerBtn.innerHTML = '<i class="bi bi-upc-scan me-1"></i>مسح الباركود';
    scannerBtn.setAttribute('data-bs-toggle', 'modal');
    scannerBtn.setAttribute('data-bs-target', '#barcodeModal');
    
    cardHeader.querySelector('a').parentNode.insertBefore(scannerBtn, cardHeader.querySelector('a'));
});
</script>
@endpush