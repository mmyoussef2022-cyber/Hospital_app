<!-- Urgent Findings Modal -->
<div class="modal fade" id="urgentFindingsModal" tabindex="-1" aria-labelledby="urgentFindingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="urgentFindingsModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    النتائج العاجلة - تحتاج إشعار فوري
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($urgentFindings->count() > 0)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تنبيه مهم:</strong> يوجد {{ $urgentFindings->count() }} نتيجة عاجلة تحتاج إشعار الطبيب المعالج فوراً!
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>المريض</th>
                                <th>الطبيب</th>
                                <th>نوع الفحص</th>
                                <th>الموجودات العاجلة</th>
                                <th>وقت التقرير</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($urgentFindings as $finding)
                            <tr>
                                <td>
                                    <strong class="text-danger">#{{ $finding->order_number }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $finding->patient->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $finding->patient->national_id }}</small>
                                    </div>
                                </td>
                                <td>{{ $finding->doctor->name }}</td>
                                <td>{{ $finding->radiologyStudy->name }}</td>
                                <td>
                                    <div class="text-danger">
                                        @if($finding->report && $finding->report->urgent_findings)
                                        <small>{{ Str::limit($finding->report->urgent_findings, 100) }}</small>
                                        @else
                                        <small>موجودات عاجلة - راجع التقرير</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $finding->completed_at ? $finding->completed_at->format('Y-m-d H:i') : 'غير محدد' }}</small>
                                    <br>
                                    <small class="text-muted">{{ $finding->completed_at ? $finding->completed_at->diffForHumans() : '' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="markUrgentNotified({{ $finding->id }})" title="تم الإشعار">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="viewFullReport({{ $finding->id }})" title="عرض التقرير كاملاً">
                                            <i class="fas fa-file-medical-alt"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="callDoctor('{{ $finding->doctor->phone ?? '' }}')" title="اتصال بالطبيب">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="sendWhatsApp('{{ $finding->doctor->phone ?? '' }}', '{{ $finding->radiologyStudy->name }}', '{{ $finding->order_number }}')" title="واتساب">
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
                            <button class="btn btn-success w-100" onclick="markAllUrgentNotified()">
                                <i class="fas fa-check-double me-2"></i>
                                تسجيل إشعار جميع النتائج
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100" onclick="exportUrgentFindings()">
                                <i class="fas fa-download me-2"></i>
                                تصدير النتائج العاجلة
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h5 class="text-success">ممتاز!</h5>
                    <p class="text-muted">لا توجد نتائج عاجلة في الوقت الحالي</p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                @if($urgentFindings->count() > 0)
                <button type="button" class="btn btn-danger" onclick="refreshUrgentFindings()">
                    <i class="fas fa-sync-alt me-1"></i>
                    تحديث
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Full Report Modal -->
<div class="modal fade" id="fullReportModal" tabindex="-1" aria-labelledby="fullReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullReportModalLabel">
                    <i class="fas fa-file-medical-alt text-primary me-2"></i>
                    التقرير الكامل
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fullReportContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="printFullReport()">
                    <i class="fas fa-print me-1"></i>
                    طباعة
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function markUrgentNotified(orderId) {
    if (confirm('هل تم إشعار الطبيب بهذه النتيجة العاجلة؟')) {
        fetch(`/radiology-specialized/orders/${orderId}/mark-notified`, {
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

function markAllUrgentNotified() {
    if (confirm('هل تم إشعار جميع الأطباء بالنتائج العاجلة؟')) {
        const orderIds = [];
        document.querySelectorAll('#urgentFindingsModal tbody tr').forEach(row => {
            const button = row.querySelector('button[onclick*="markUrgentNotified"]');
            if (button) {
                const onclick = button.getAttribute('onclick');
                const orderId = onclick.match(/\d+/)[0];
                orderIds.push(orderId);
            }
        });
        
        Promise.all(orderIds.map(id => 
            fetch(`/radiology-specialized/orders/${id}/mark-notified`, {
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
            alert(`تم تسجيل إشعار ${successCount} نتيجة عاجلة`);
            refreshDashboard();
            bootstrap.Modal.getInstance(document.getElementById('urgentFindingsModal')).hide();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء تسجيل الإشعارات');
        });
    }
}

function viewFullReport(orderId) {
    // Load full report via AJAX
    fetch(`/radiology-specialized/orders/${orderId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.order.report) {
                const report = data.order.report;
                const reportHTML = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6>معلومات الطلب</h6>
                            <p><strong>رقم الطلب:</strong> #${data.order.order_number}</p>
                            <p><strong>المريض:</strong> ${data.order.patient.name}</p>
                            <p><strong>نوع الفحص:</strong> ${data.order.radiology_study.name}</p>
                            <p><strong>تاريخ التقرير:</strong> ${report.reported_at}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <h6>الموجودات (Findings)</h6>
                            <div class="border p-3 bg-light">
                                ${report.findings.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <h6>الانطباع التشخيصي (Impression)</h6>
                            <div class="border p-3 bg-light">
                                ${report.impression.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        ${report.urgent_findings ? `
                        <div class="col-12 mb-3">
                            <h6 class="text-danger">الموجودات العاجلة</h6>
                            <div class="border border-danger p-3 bg-danger bg-opacity-10">
                                ${report.urgent_findings.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        ` : ''}
                        ${report.recommendations ? `
                        <div class="col-12 mb-3">
                            <h6>التوصيات</h6>
                            <div class="border p-3 bg-light">
                                ${report.recommendations.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('fullReportContent').innerHTML = reportHTML;
                new bootstrap.Modal(document.getElementById('fullReportModal')).show();
            } else {
                alert('لا يوجد تقرير متاح لهذا الطلب');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء جلب التقرير');
        });
}

function callDoctor(phone) {
    if (phone) {
        window.open(`tel:${phone}`, '_self');
    } else {
        alert('رقم الهاتف غير متوفر');
    }
}

function sendWhatsApp(phone, studyName, orderNumber) {
    if (phone) {
        const message = `تنبيه: نتيجة عاجلة في الأشعة\nنوع الفحص: ${studyName}\nرقم الطلب: #${orderNumber}\nيرجى المراجعة فوراً`;
        const whatsappUrl = `https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    } else {
        alert('رقم الهاتف غير متوفر');
    }
}

function refreshUrgentFindings() {
    location.reload();
}

function exportUrgentFindings() {
    window.open('/radiology-specialized/reports/generate?report_type=urgent&date_from=' + 
                new Date().toISOString().split('T')[0] + 
                '&date_to=' + new Date().toISOString().split('T')[0], '_blank');
}

function printFullReport() {
    const content = document.getElementById('fullReportContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>تقرير الأشعة</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; }
                    .border { border: 1px solid #ddd; }
                    .p-3 { padding: 1rem; }
                    .bg-light { background-color: #f8f9fa; }
                    .bg-danger { background-color: #dc3545; }
                    .bg-opacity-10 { opacity: 0.1; }
                    .text-danger { color: #dc3545; }
                    h6 { margin-bottom: 0.5rem; font-weight: bold; }
                    p { margin-bottom: 0.5rem; }
                </style>
            </head>
            <body>
                ${content}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>