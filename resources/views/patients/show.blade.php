@extends('layouts.app')

@section('title', 'ملف المريض - ' . $patient->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Patient Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @if($patient->profile_photo)
                                <img src="{{ asset('storage/' . $patient->profile_photo) }}" 
                                     alt="صورة المريض" 
                                     class="rounded-circle" 
                                     width="120" height="120">
                            @else
                                <div class="bg-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                     style="width: 120px; height: 120px;">
                                    <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-1">{{ $patient->name }}</h2>
                            @if($patient->name_en)
                                <p class="text-muted mb-2">{{ $patient->name_en }}</p>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>رقم المريض:</strong> 
                                        <span class="badge bg-primary">{{ $patient->patient_number }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>العمر:</strong> {{ $patient->age }} سنة
                                    </p>
                                    <p class="mb-1">
                                        <strong>الجنس:</strong> 
                                        <i class="bi {{ $patient->gender == 'male' ? 'bi-gender-male text-primary' : 'bi-gender-female text-danger' }}"></i>
                                        {{ $patient->gender == 'male' ? 'ذكر' : 'أنثى' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>فصيلة الدم:</strong> 
                                        <span class="badge bg-info">{{ $patient->blood_type }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>نوع المريض:</strong> 
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
                                    </p>
                                    <p class="mb-1">
                                        <strong>تصنيف المريض:</strong> 
                                        <span class="badge bg-{{ $patient->patient_classification_color }}">
                                            <i class="bi bi-{{ $patient->patient_classification === 'insurance' ? 'shield-check' : 'cash-coin' }} me-1"></i>
                                            {{ $patient->patient_classification_display }}
                                        </span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>الحالة:</strong> 
                                        @if($patient->is_active)
                                            <span class="badge bg-success">مفعل</span>
                                        @else
                                            <span class="badge bg-secondary">غير مفعل</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="btn-group-vertical" role="group">
                                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning mb-2">
                                    <i class="bi bi-pencil me-1"></i>
                                    تعديل
                                </a>
                                <button class="btn btn-info mb-2" onclick="printBarcode()">
                                    <i class="bi bi-upc-scan me-1"></i>
                                    طباعة الباركود
                                </button>
                                <form action="{{ route('patients.toggle-status', $patient) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $patient->is_active ? 'secondary' : 'success' }} w-100">
                                        <i class="bi bi-{{ $patient->is_active ? 'pause' : 'play' }} me-1"></i>
                                        {{ $patient->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Alerts -->
            @if(count($patient->medical_alerts) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning border-start border-warning border-4">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            تنبيهات طبية مهمة
                        </h6>
                        <div class="row">
                            @foreach($patient->medical_alerts as $alert)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi {{ $alert['icon'] }} text-{{ $alert['color'] }} me-2"></i>
                                    <span class="small">{{ $alert['message'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-circle text-primary me-2"></i>
                                المعلومات الشخصية
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>رقم الهوية:</strong></td>
                                    <td>{{ $patient->national_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ الميلاد:</strong></td>
                                    <td>{{ $patient->date_of_birth?->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الجوال:</strong></td>
                                    <td>{{ $patient->mobile }}</td>
                                </tr>
                                @if($patient->phone)
                                <tr>
                                    <td><strong>الهاتف:</strong></td>
                                    <td>{{ $patient->phone }}</td>
                                </tr>
                                @endif
                                @if($patient->email)
                                <tr>
                                    <td><strong>البريد الإلكتروني:</strong></td>
                                    <td>{{ $patient->email }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>الحالة الاجتماعية:</strong></td>
                                    <td>
                                        @php
                                            $maritalStatus = [
                                                'single' => 'أعزب',
                                                'married' => 'متزوج',
                                                'divorced' => 'مطلق',
                                                'widowed' => 'أرمل'
                                            ];
                                        @endphp
                                        {{ $maritalStatus[$patient->marital_status] ?? $patient->marital_status }}
                                    </td>
                                </tr>
                                @if($patient->occupation)
                                <tr>
                                    <td><strong>المهنة:</strong></td>
                                    <td>{{ $patient->occupation }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-geo-alt text-primary me-2"></i>
                                معلومات العنوان
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>العنوان:</strong></td>
                                    <td>{{ $patient->address }}</td>
                                </tr>
                                <tr>
                                    <td><strong>المدينة:</strong></td>
                                    <td>{{ $patient->city }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الدولة:</strong></td>
                                    <td>{{ $patient->country }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الجنسية:</strong></td>
                                    <td>{{ $patient->nationality }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-telephone-fill text-danger me-2"></i>
                                جهة الاتصال في حالات الطوارئ
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($patient->emergency_contact)
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>الاسم:</strong></td>
                                        <td>{{ $patient->emergency_contact['name'] ?? 'غير محدد' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>صلة القرابة:</strong></td>
                                        <td>{{ $patient->emergency_contact['relationship'] ?? 'غير محدد' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>رقم الهاتف:</strong></td>
                                        <td>{{ $patient->emergency_contact['phone'] ?? 'غير محدد' }}</td>
                                    </tr>
                                </table>
                            @else
                                <p class="text-muted">لم يتم تحديد جهة اتصال للطوارئ</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Family Information -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people text-primary me-2"></i>
                                معلومات العائلة
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($patient->family_code)
                                <p><strong>رمز العائلة:</strong> <span class="badge bg-info">{{ $patient->family_code }}</span></p>
                            @endif

                            @if($patient->familyHead)
                                <p><strong>رب الأسرة:</strong> 
                                    <a href="{{ route('patients.show', $patient->familyHead) }}">
                                        {{ $patient->familyHead->name }}
                                    </a>
                                </p>
                                <p><strong>صلة القرابة:</strong> {{ $patient->family_relation }}</p>
                            @endif

                            @if($patient->familyMembers->count() > 0)
                                <h6 class="mt-3">أفراد العائلة:</h6>
                                <div class="list-group">
                                    @foreach($patient->familyMembers as $member)
                                        <a href="{{ route('patients.show', $member) }}" 
                                           class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $member->name }}</strong>
                                                    <br><small class="text-muted">{{ $member->family_relation }}</small>
                                                </div>
                                                <span class="badge bg-primary">{{ $member->patient_number }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if(!$patient->family_code && !$patient->familyHead && $patient->familyMembers->count() == 0)
                                <p class="text-muted">لا توجد معلومات عائلة</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-heart-pulse text-danger me-2"></i>
                                المعلومات الطبية
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>الحساسية:</h6>
                                    @if($patient->allergies && count($patient->allergies) > 0)
                                        <div class="mb-3">
                                            @foreach($patient->allergies as $allergy)
                                                <span class="badge bg-warning text-dark me-1 mb-1">{{ $allergy }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">لا توجد حساسية معروفة</p>
                                    @endif

                                    <h6>الأمراض المزمنة:</h6>
                                    @if($patient->chronic_conditions && count($patient->chronic_conditions) > 0)
                                        <div class="mb-3">
                                            @foreach($patient->chronic_conditions as $condition)
                                                <span class="badge bg-danger me-1 mb-1">{{ $condition }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-3">لا توجد أمراض مزمنة</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6>ملاحظات طبية:</h6>
                                    @if($patient->medical_notes)
                                        <div class="alert alert-info">
                                            {{ $patient->medical_notes }}
                                        </div>
                                    @else
                                        <p class="text-muted">لا توجد ملاحظات طبية</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Insurance Information -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                معلومات التأمين المتقدمة
                            </h5>
                            @if($patient->patient_classification === 'cash')
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#assignInsuranceModal">
                                <i class="bi bi-plus-circle me-1"></i>
                                ربط تأمين
                            </button>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($patient->patient_classification === 'insurance')
                                @php $primaryInsurance = $patient->primary_insurance_details; @endphp
                                @if($primaryInsurance)
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">التأمين الأساسي</h6>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td><strong>شركة التأمين:</strong></td>
                                                <td>{{ $primaryInsurance['company_name'] }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>رقم البوليصة:</strong></td>
                                                <td>{{ $primaryInsurance['policy_number'] }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>رقم العضوية:</strong></td>
                                                <td>{{ $primaryInsurance['member_id'] }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>رقم البطاقة:</strong></td>
                                                <td>{{ $primaryInsurance['card_number'] }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>نسبة التغطية:</strong></td>
                                                <td>
                                                    <span class="badge bg-info">{{ $primaryInsurance['coverage_percentage'] }}%</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info">حالة التغطية</h6>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td><strong>الحالة:</strong></td>
                                                <td>
                                                    <span class="badge bg-success">{{ $primaryInsurance['status'] }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>تاريخ الانتهاء:</strong></td>
                                                <td>
                                                    {{ $primaryInsurance['expiry_date'] ?? 'غير محدد' }}
                                                    @if($primaryInsurance['is_expiring_soon'])
                                                        <span class="badge bg-warning text-dark ms-1">ينتهي قريباً</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>المبلغ المستخدم:</strong></td>
                                                <td>{{ number_format($primaryInsurance['annual_limit_used'], 2) }} ريال</td>
                                            </tr>
                                            <tr>
                                                <td><strong>المبلغ المتبقي:</strong></td>
                                                <td>
                                                    <span class="text-success">{{ number_format($primaryInsurance['annual_limit_remaining'], 2) }} ريال</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Coverage Calculator -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <h6 class="text-primary">حاسبة التغطية التأمينية</h6>
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">مبلغ الخدمة</label>
                                            <input type="number" class="form-control" id="coverageAmount" placeholder="أدخل المبلغ">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">نوع الخدمة (اختياري)</label>
                                            <select class="form-select" id="serviceType">
                                                <option value="">جميع الخدمات</option>
                                                <option value="consultation">استشارة</option>
                                                <option value="treatment">علاج</option>
                                                <option value="surgery">جراحة</option>
                                                <option value="lab">مختبر</option>
                                                <option value="radiology">أشعة</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-primary" onclick="calculateCoverage()">
                                                <i class="bi bi-calculator me-1"></i>
                                                احسب التغطية
                                            </button>
                                        </div>
                                    </div>
                                    <div id="coverageResult" class="mt-3" style="display: none;">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>المبلغ المغطى:</strong>
                                                    <div class="text-success fs-5" id="coveredAmount">0 ريال</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>مسؤولية المريض:</strong>
                                                    <div class="text-danger fs-5" id="patientResponsibility">0 ريال</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>نسبة التغطية:</strong>
                                                    <div class="text-info fs-5" id="coveragePercentage">0%</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>الحالة:</strong>
                                                    <div class="text-primary" id="coverageStatus">جاهز</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- All Active Insurances -->
                                @if($patient->active_insurances->count() > 1)
                                <div class="mt-4">
                                    <h6 class="text-secondary">جميع التأمينات النشطة</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>شركة التأمين</th>
                                                    <th>نوع البوليصة</th>
                                                    <th>نسبة التغطية</th>
                                                    <th>الأولوية</th>
                                                    <th>الحالة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($patient->active_insurances as $insurance)
                                                <tr>
                                                    <td>{{ $insurance->insuranceCompany->name ?? 'غير محدد' }}</td>
                                                    <td>{{ $insurance->insurancePolicy->policy_type_display ?? 'غير محدد' }}</td>
                                                    <td>{{ $insurance->insurancePolicy->coverage_percentage ?? 0 }}%</td>
                                                    <td>
                                                        @if($insurance->is_primary)
                                                            <span class="badge bg-primary">أساسي</span>
                                                        @else
                                                            <span class="badge bg-secondary">ثانوي</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $insurance->status_display }}</span>
                                                    </td>
                                                    <td>
                                                        <form action="{{ route('patients.remove-insurance', [$patient, $insurance]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('هل أنت متأكد من إلغاء ربط هذا التأمين؟')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                @else
                                <div class="text-center py-4">
                                    <i class="bi bi-shield-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">لا توجد تفاصيل تأمين أساسي</p>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-cash-coin text-primary" style="font-size: 3rem;"></i>
                                    <h6 class="text-primary mt-2">مريض نقدي</h6>
                                    <p class="text-muted">هذا المريض لا يملك تأمين صحي نشط</p>
                                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#assignInsuranceModal">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        ربط تأمين صحي
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Medical History Summary -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clipboard-data text-info me-2"></i>
                                ملخص التاريخ الطبي
                            </h5>
                        </div>
                        <div class="card-body">
                            @php $medicalSummary = $patient->medical_history_summary; @endphp
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="h4 text-primary mb-0">{{ $medicalSummary['total_visits'] }}</div>
                                        <small class="text-muted">إجمالي الزيارات</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="h4 text-success mb-0">{{ $medicalSummary['total_prescriptions'] }}</div>
                                        <small class="text-muted">الوصفات الطبية</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="h4 text-info mb-0">{{ $medicalSummary['total_medical_records'] }}</div>
                                        <small class="text-muted">السجلات الطبية</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="h4 text-warning mb-0">{{ $medicalSummary['allergies_count'] }}</div>
                                        <small class="text-muted">الحساسيات</small>
                                    </div>
                                </div>
                            </div>
                            @if($medicalSummary['last_visit'])
                            <div class="mt-2">
                                <small class="text-muted">آخر زيارة: {{ $medicalSummary['last_visit'] }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-wallet2 text-success me-2"></i>
                                الملخص المالي
                            </h5>
                        </div>
                        <div class="card-body">
                            @php $billingSummary = $patient->billing_history; @endphp
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                        <span>الرصيد المستحق:</span>
                                        <span class="h5 mb-0 {{ $medicalSummary['outstanding_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($medicalSummary['outstanding_balance'], 2) }} ريال
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">إجمالي الفواتير:</span>
                                        <span>{{ $billingSummary['total_invoices'] }}</span>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">إجمالي المدفوع:</span>
                                        <span class="text-success">{{ number_format($billingSummary['total_paid'], 2) }} ريال</span>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">طريقة الدفع المفضلة:</span>
                                        <span class="badge bg-info">{{ $billingSummary['payment_method_preference'] === 'cash' ? 'نقدي' : 'بطاقة' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Family Insurance Status -->
                @if($patient->family_insurance_status)
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people-fill text-primary me-2"></i>
                                حالة التأمين العائلي
                            </h5>
                        </div>
                        <div class="card-body">
                            @php $familyInsurance = $patient->family_insurance_status; @endphp
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <div class="h4 text-primary mb-1">{{ $familyInsurance['total_members'] }}</div>
                                        <small class="text-muted">إجمالي أفراد العائلة</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <div class="h4 text-success mb-1">{{ $familyInsurance['insured_members'] }}</div>
                                        <small class="text-muted">الأفراد المؤمنين</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <div class="h4 text-warning mb-1">{{ $familyInsurance['uninsured_members'] }}</div>
                                        <small class="text-muted">الأفراد غير المؤمنين</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        @if($familyInsurance['family_policy_exists'])
                                            <i class="bi bi-check-circle-fill text-success h4 mb-1"></i>
                                            <br><small class="text-success">بوليصة عائلية موجودة</small>
                                        @else
                                            <i class="bi bi-x-circle-fill text-danger h4 mb-1"></i>
                                            <br><small class="text-danger">لا توجد بوليصة عائلية</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- System Information -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                معلومات النظام
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>الباركود:</strong></td>
                                    <td><code>{{ $patient->barcode }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ التسجيل:</strong></td>
                                    <td>{{ $patient->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                                @if($patient->first_visit_date)
                                <tr>
                                    <td><strong>أول زيارة:</strong></td>
                                    <td>{{ $patient->first_visit_date?->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endif
                                @if($patient->last_visit_date)
                                <tr>
                                    <td><strong>آخر زيارة:</strong></td>
                                    <td>{{ $patient->last_visit_date?->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endif
                                @if($patient->outstanding_balance > 0)
                                <tr>
                                    <td><strong>الرصيد المستحق:</strong></td>
                                    <td><span class="text-danger">{{ number_format($patient->outstanding_balance, 2) }} ريال</span></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            العودة للقائمة
                        </a>
                        <div>
                            <button class="btn btn-info me-2" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>
                                طباعة
                            </button>
                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل البيانات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Insurance Modal -->
<div class="modal fade" id="assignInsuranceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ربط تأمين صحي للمريض</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('patients.assign-insurance', $patient) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">شركة التأمين *</label>
                            <select name="insurance_company_id" class="form-select" required onchange="loadPolicies(this.value)">
                                <option value="">اختر شركة التأمين</option>
                                @foreach($activeInsuranceCompanies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البوليصة *</label>
                            <select name="insurance_policy_id" class="form-select" required id="policySelect">
                                <option value="">اختر البوليصة</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم العضوية *</label>
                            <input type="text" name="member_id" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم البطاقة</label>
                            <input type="text" name="card_number" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم حامل البوليصة *</label>
                            <input type="text" name="policy_holder_name" class="form-control" value="{{ $patient->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">صلة القرابة *</label>
                            <select name="policy_holder_relation" class="form-select" required>
                                <option value="self">المؤمن عليه</option>
                                <option value="spouse">الزوج/الزوجة</option>
                                <option value="child">الطفل</option>
                                <option value="parent">الوالد/الوالدة</option>
                                <option value="sibling">الأخ/الأخت</option>
                                <option value="other">أخرى</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ بداية التغطية *</label>
                            <input type="date" name="coverage_start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ نهاية التغطية</label>
                            <input type="date" name="coverage_end_date" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_primary" class="form-check-input" id="isPrimary" value="1" checked>
                                <label class="form-check-label" for="isPrimary">
                                    تأمين أساسي (سيتم إلغاء الأولوية من التأمينات الأخرى)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-shield-check me-1"></i>
                        ربط التأمين
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Barcode Print Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">باركود المريض</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="barcode-container">
                    <h6>{{ $patient->name }}</h6>
                    <p>{{ $patient->patient_number }}</p>
                    <div class="barcode-display">
                        <svg id="barcode"></svg>
                    </div>
                    <p><small>{{ $patient->barcode }}</small></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="printBarcodeOnly()">طباعة الباركود</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
// Barcode functions
function printBarcode() {
    JsBarcode("#barcode", "{{ $patient->barcode }}", {
        format: "CODE128",
        width: 2,
        height: 100,
        displayValue: true
    });
    
    new bootstrap.Modal(document.getElementById('barcodeModal')).show();
}

function printBarcodeOnly() {
    const barcodeContent = document.getElementById('barcode-container').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>باركود المريض</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                    .barcode-display { margin: 20px 0; }
                </style>
            </head>
            <body>
                ${barcodeContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Insurance functions
function loadPolicies(companyId) {
    const policySelect = document.getElementById('policySelect');
    policySelect.innerHTML = '<option value="">جاري التحميل...</option>';
    
    if (!companyId) {
        policySelect.innerHTML = '<option value="">اختر البوليصة</option>';
        return;
    }
    
    fetch(`{{ route('patients.get-insurance-policies') }}?company_id=${companyId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                policySelect.innerHTML = '<option value="">اختر البوليصة</option>';
                data.policies.forEach(policy => {
                    policySelect.innerHTML += `<option value="${policy.id}">${policy.policy_name_ar} - ${policy.policy_number} (${policy.coverage_percentage}%)</option>`;
                });
            } else {
                policySelect.innerHTML = '<option value="">خطأ في تحميل البوليصات</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            policySelect.innerHTML = '<option value="">خطأ في تحميل البوليصات</option>';
        });
}

function calculateCoverage() {
    const amount = document.getElementById('coverageAmount').value;
    const serviceType = document.getElementById('serviceType').value;
    
    if (!amount || amount <= 0) {
        alert('يرجى إدخال مبلغ صحيح');
        return;
    }
    
    const formData = new FormData();
    formData.append('amount', amount);
    formData.append('service_type', serviceType);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(`{{ route('patients.calculate-coverage', $patient) }}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const coverage = data.coverage;
            document.getElementById('coveredAmount').textContent = coverage.covered_amount.toFixed(2) + ' ريال';
            document.getElementById('patientResponsibility').textContent = coverage.patient_responsibility.toFixed(2) + ' ريال';
            document.getElementById('coveragePercentage').textContent = coverage.coverage_percentage + '%';
            document.getElementById('coverageStatus').textContent = coverage.reason || 'مغطى';
            document.getElementById('coverageResult').style.display = 'block';
        } else {
            alert('حدث خطأ أثناء حساب التغطية: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حساب التغطية');
    });
}

// Print styles for the page
const printStyles = `
    <style media="print">
        .btn, .card-header .btn-group-vertical { display: none !important; }
        .card { border: 1px solid #ddd !important; margin-bottom: 20px !important; }
        .card-header { background-color: #f8f9fa !important; }
        @page { margin: 1cm; }
    </style>
`;
document.head.insertAdjacentHTML('beforeend', printStyles);
</script>
@endpush