<!-- Critical Results Modal -->
<div class="modal fade" id="criticalResultsModal" tabindex="-1" aria-labelledby="criticalResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="criticalResultsModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    النتائج الحرجة - تحتاج إشعار فوري
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($criticalResults->count() > 0)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تنبيه مهم:</strong> يوجد {{ $criticalResults->count() }} نتيجة حرجة تحتاج إشعار الطبيب المعالج فوراً!
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>المريض</th>
                                <th>الطبيب</th>
                                <th>المعامل</th>
                                <th>القيمة</th>
                                <th>التقييم</th>
                                <th>وقت النتيجة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criticalResults as $result)
                            <tr>
                                <td>
                                    <strong class="text-danger">#{{ $result->labOrder->order_number }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $result->labOrder->patient->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $result->labOrder->patient->national_id }}</small>
                                    </div>
                                </td>
                                <td>{{ $result->labOrder->doctor->name }}</td>
                                <td>{{ $result->parameter_name }}</td>
                                <td>
                                    <strong class="text-danger">{{ $result->value }} {{ $result->unit }}</strong>
                                    @if($result->reference_range)
                                    <br>
                                    <small class="text-muted">الطبيعي: {{ $result->reference_range }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $flagClass = match($result->flag) {
                                            'critical_high' => 'danger',
                                            'critical_low' => 'danger',
                                            'high' => 'warning',
                                            'low' => 'warning',
                                            'abnormal' => 'warning',
                                            'normal' => 'success',
                                            default => 'secondary'
                                        };
                                        $flagText = match($result->flag) {
                                            'critical_high' => 'مرتفع جداً',
                                            'critical_low' => 'منخفض جداً',
                                            'high' => 'مرتفع',
                                            'low' => 'منخفض',
                                            'abnormal' => 'غير طبيعي',
                                            'normal' => 'طبيعي',
                                            default => 'غير محدد'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $flagClass }}">{{ $flagText }}</span>
                                </td>
                                <td>
                                    <small>{{ $result->created_at->format('Y-m-d H:i') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $result->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="markCriticalNotified({{ $result->id }})" title="تم الإشعار">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="callDoctor('{{ $result->labOrder->doctor->phone }}')" title="اتصال بالطبيب">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="sendWhatsApp('{{ $result->labOrder->doctor->phone }}', '{{ $result->parameter_name }}', '{{ $result->value }}')" title="واتساب">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-success w-100" onclick="markAllCriticalNotified()">
                                <i class="fas fa-check-double me-2"></i>
                                تسجيل إشعار جميع النتائج
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100" onclick="exportCriticalResults()">
                                <i class="fas fa-download me-2"></i>
                                تصدير النتائج الحرجة
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h5 class="text-success">ممتاز!</h5>
                    <p class="text-muted">لا توجد نتائج حرجة في الوقت الحالي</p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                @if($criticalResults->count() > 0)
                <button type="button" class="btn btn-danger" onclick="refreshCriticalResults()">
                    <i class="fas fa-sync-alt me-1"></i>
                    تحديث
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function markCriticalNotified(resultId) {
    if (confirm('هل تم إشعار الطبيب بهذه النتيجة الحرجة؟')) {
        fetch(`/lab-specialized/results/${resultId}/mark-notified`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                refreshDashboard();
                // Remove the row from the table
                event.target.closest('tr').remove();
            } else {
                alert(data.message || 'حدث خطأ أثناء تسجيل الإشعار');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تسجيل الإشعار');
        });
    }
}

function markAllCriticalNotified() {
    if (confirm('هل تم إشعار جميع الأطباء بالنتائج الحرجة؟')) {
        const resultIds = [];
        document.querySelectorAll('#criticalResultsModal tbody tr').forEach(row => {
            const button = row.querySelector('button[onclick*="markCriticalNotified"]');
            if (button) {
                const onclick = button.getAttribute('onclick');
                const resultId = onclick.match(/\d+/)[0];
                resultIds.push(resultId);
            }
        });
        
        Promise.all(resultIds.map(id => 
            fetch(`/lab-specialized/results/${id}/mark-notified`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
        ))
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            alert(`تم تسجيل إشعار ${successCount} نتيجة حرجة`);
            refreshDashboard();
            bootstrap.Modal.getInstance(document.getElementById('criticalResultsModal')).hide();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تسجيل الإشعارات');
        });
    }
}

function callDoctor(phone) {
    if (phone) {
        window.open(`tel:${phone}`, '_self');
    } else {
        alert('رقم الهاتف غير متوفر');
    }
}

function sendWhatsApp(phone, parameter, value) {
    if (phone) {
        const message = `تنبيه: نتيجة حرجة\nالمعامل: ${parameter}\nالقيمة: ${value}\nيرجى المراجعة فوراً`;
        const whatsappUrl = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    } else {
        alert('رقم الهاتف غير متوفر');
    }
}

function refreshCriticalResults() {
    location.reload();
}

function exportCriticalResults() {
    window.open('/lab-specialized/reports/generate?report_type=critical&date_from=' + 
                new Date().toISOString().split('T')[0] + 
                '&date_to=' + new Date().toISOString().split('T')[0], '_blank');
}
</script>