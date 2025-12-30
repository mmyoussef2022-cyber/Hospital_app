{{-- This partial view is loaded via AJAX for adding results to lab orders --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>{{ $order->labTest->name }}</strong> - {{ $order->patient->name }}
    <br>
    <small>رقم الطلب: {{ $order->order_number }}</small>
</div>

@if($order->labTest->normal_ranges)
<div class="alert alert-secondary">
    <h6 class="alert-heading">المعايير الطبيعية:</h6>
    <div class="small">{{ $order->labTest->normal_ranges }}</div>
</div>
@endif

<div id="resultsContainer">
    @if($order->results->count() > 0)
        {{-- Edit existing results --}}
        @foreach($order->results as $index => $result)
        <div class="result-row border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">اسم المعامل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][parameter_name]" 
                           value="{{ $result->parameter_name }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">القيمة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][value]" 
                           value="{{ $result->value }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الوحدة</label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][unit]" 
                           value="{{ $result->unit }}"
                           placeholder="mg/dL">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المدى المرجعي</label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][reference_range]" 
                           value="{{ $result->reference_range }}"
                           placeholder="10-50">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                    <select class="form-select" name="results[{{ $index }}][flag]" required>
                        <option value="normal" {{ $result->flag == 'normal' ? 'selected' : '' }}>طبيعي</option>
                        <option value="high" {{ $result->flag == 'high' ? 'selected' : '' }}>مرتفع</option>
                        <option value="low" {{ $result->flag == 'low' ? 'selected' : '' }}>منخفض</option>
                        <option value="critical_high" {{ $result->flag == 'critical_high' ? 'selected' : '' }}>مرتفع حرج</option>
                        <option value="critical_low" {{ $result->flag == 'critical_low' ? 'selected' : '' }}>منخفض حرج</option>
                        <option value="abnormal" {{ $result->flag == 'abnormal' ? 'selected' : '' }}>غير طبيعي</option>
                    </select>
                </div>
                <div class="col-md-8 mt-2">
                    <label class="form-label">الملاحظات</label>
                    <textarea class="form-control" name="results[{{ $index }}][notes]" rows="2" 
                              placeholder="أي ملاحظات إضافية...">{{ $result->notes }}</textarea>
                </div>
                <div class="col-12 mt-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeResult(this)">
                        <i class="fas fa-trash me-1"></i>
                        حذف النتيجة
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    @else
        {{-- Add new results based on common lab test parameters --}}
        @php
            $commonParameters = [];
            
            // Define common parameters based on test type
            switch(strtoupper($order->labTest->code ?? '')) {
                case 'CBC':
                    $commonParameters = [
                        ['name' => 'WBC', 'unit' => '10³/μL', 'range' => '4.0-11.0'],
                        ['name' => 'RBC', 'unit' => '10⁶/μL', 'range' => '4.5-5.5'],
                        ['name' => 'Hemoglobin', 'unit' => 'g/dL', 'range' => '12.0-16.0'],
                        ['name' => 'Hematocrit', 'unit' => '%', 'range' => '36-46'],
                        ['name' => 'Platelets', 'unit' => '10³/μL', 'range' => '150-450']
                    ];
                    break;
                case 'FBS':
                    $commonParameters = [
                        ['name' => 'Glucose', 'unit' => 'mg/dL', 'range' => '70-100']
                    ];
                    break;
                case 'LIPID':
                    $commonParameters = [
                        ['name' => 'Total Cholesterol', 'unit' => 'mg/dL', 'range' => '<200'],
                        ['name' => 'HDL', 'unit' => 'mg/dL', 'range' => '>40'],
                        ['name' => 'LDL', 'unit' => 'mg/dL', 'range' => '<100'],
                        ['name' => 'Triglycerides', 'unit' => 'mg/dL', 'range' => '<150']
                    ];
                    break;
                case 'LIVER':
                    $commonParameters = [
                        ['name' => 'ALT', 'unit' => 'U/L', 'range' => '7-56'],
                        ['name' => 'AST', 'unit' => 'U/L', 'range' => '10-40'],
                        ['name' => 'Bilirubin Total', 'unit' => 'mg/dL', 'range' => '0.3-1.2'],
                        ['name' => 'Albumin', 'unit' => 'g/dL', 'range' => '3.5-5.0']
                    ];
                    break;
                case 'KIDNEY':
                    $commonParameters = [
                        ['name' => 'Creatinine', 'unit' => 'mg/dL', 'range' => '0.7-1.3'],
                        ['name' => 'BUN', 'unit' => 'mg/dL', 'range' => '7-20'],
                        ['name' => 'Uric Acid', 'unit' => 'mg/dL', 'range' => '3.5-7.2']
                    ];
                    break;
                default:
                    $commonParameters = [
                        ['name' => 'Result', 'unit' => '', 'range' => '']
                    ];
            }
        @endphp

        @foreach($commonParameters as $index => $param)
        <div class="result-row border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">اسم المعامل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][parameter_name]" 
                           value="{{ $param['name'] }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">القيمة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][value]" 
                           placeholder="أدخل القيمة" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الوحدة</label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][unit]" 
                           value="{{ $param['unit'] }}"
                           placeholder="mg/dL">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المدى المرجعي</label>
                    <input type="text" class="form-control" 
                           name="results[{{ $index }}][reference_range]" 
                           value="{{ $param['range'] }}"
                           placeholder="10-50">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                    <select class="form-select" name="results[{{ $index }}][flag]" required>
                        <option value="">اختر الحالة</option>
                        <option value="normal">طبيعي</option>
                        <option value="high">مرتفع</option>
                        <option value="low">منخفض</option>
                        <option value="critical_high">مرتفع حرج</option>
                        <option value="critical_low">منخفض حرج</option>
                        <option value="abnormal">غير طبيعي</option>
                    </select>
                </div>
                <div class="col-md-8 mt-2">
                    <label class="form-label">الملاحظات</label>
                    <textarea class="form-control" name="results[{{ $index }}][notes]" rows="2" 
                              placeholder="أي ملاحظات إضافية..."></textarea>
                </div>
                @if($index > 0)
                <div class="col-12 mt-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeResult(this)">
                        <i class="fas fa-trash me-1"></i>
                        حذف النتيجة
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @endif
</div>

<div class="mt-3">
    <button type="button" class="btn btn-success btn-sm" onclick="addNewResult()">
        <i class="fas fa-plus me-1"></i>
        إضافة نتيجة أخرى
    </button>
</div>

<script>
let resultIndex = {{ $order->results->count() > 0 ? $order->results->count() : count($commonParameters) }};

function addNewResult() {
    const container = document.getElementById('resultsContainer');
    const resultHtml = `
        <div class="result-row border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">اسم المعامل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="results[${resultIndex}][parameter_name]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">القيمة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="results[${resultIndex}][value]" placeholder="أدخل القيمة" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الوحدة</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][unit]" placeholder="mg/dL">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المدى المرجعي</label>
                    <input type="text" class="form-control" name="results[${resultIndex}][reference_range]" placeholder="10-50">
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                    <select class="form-select" name="results[${resultIndex}][flag]" required>
                        <option value="">اختر الحالة</option>
                        <option value="normal">طبيعي</option>
                        <option value="high">مرتفع</option>
                        <option value="low">منخفض</option>
                        <option value="critical_high">مرتفع حرج</option>
                        <option value="critical_low">منخفض حرج</option>
                        <option value="abnormal">غير طبيعي</option>
                    </select>
                </div>
                <div class="col-md-8 mt-2">
                    <label class="form-label">الملاحظات</label>
                    <textarea class="form-control" name="results[${resultIndex}][notes]" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                </div>
                <div class="col-12 mt-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeResult(this)">
                        <i class="fas fa-trash me-1"></i>
                        حذف النتيجة
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', resultHtml);
    resultIndex++;
}

function removeResult(button) {
    if (confirm('هل أنت متأكد من حذف هذه النتيجة؟')) {
        button.closest('.result-row').remove();
    }
}

// Auto-detect critical values
document.addEventListener('change', function(e) {
    if (e.target.name && e.target.name.includes('[value]')) {
        const row = e.target.closest('.result-row');
        const flagSelect = row.querySelector('select[name*="[flag]"]');
        const referenceRange = row.querySelector('input[name*="[reference_range]"]').value;
        const value = parseFloat(e.target.value);
        
        if (!isNaN(value) && referenceRange) {
            // Simple range detection (assumes format like "10-50" or "<100" or ">40")
            if (referenceRange.includes('-')) {
                const [min, max] = referenceRange.split('-').map(v => parseFloat(v.trim()));
                if (!isNaN(min) && !isNaN(max)) {
                    if (value < min * 0.5 || value > max * 2) {
                        flagSelect.value = value < min ? 'critical_low' : 'critical_high';
                    } else if (value < min || value > max) {
                        flagSelect.value = value < min ? 'low' : 'high';
                    } else {
                        flagSelect.value = 'normal';
                    }
                }
            } else if (referenceRange.startsWith('<')) {
                const max = parseFloat(referenceRange.substring(1));
                if (!isNaN(max)) {
                    if (value > max * 2) {
                        flagSelect.value = 'critical_high';
                    } else if (value > max) {
                        flagSelect.value = 'high';
                    } else {
                        flagSelect.value = 'normal';
                    }
                }
            } else if (referenceRange.startsWith('>')) {
                const min = parseFloat(referenceRange.substring(1));
                if (!isNaN(min)) {
                    if (value < min * 0.5) {
                        flagSelect.value = 'critical_low';
                    } else if (value < min) {
                        flagSelect.value = 'low';
                    } else {
                        flagSelect.value = 'normal';
                    }
                }
            }
        }
    }
});
</script>