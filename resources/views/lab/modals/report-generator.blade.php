<!-- Report Generator Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    مولد التقارير
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="report_type" class="form-label">نوع التقرير <span class="text-danger">*</span></label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">اختر نوع التقرير</option>
                                <option value="daily">تقرير يومي</option>
                                <option value="summary">تقرير ملخص</option>
                                <option value="critical">تقرير النتائج الحرجة</option>
                                <option value="turnaround">تقرير أوقات الإنجاز</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_range" class="form-label">الفترة الزمنية</label>
                            <select class="form-select" id="date_range" onchange="handleDateRangeChange()">
                                <option value="today">اليوم</option>
                                <option value="yesterday">أمس</option>
                                <option value="this_week">هذا الأسبوع</option>
                                <option value="last_week">الأسبوع الماضي</option>
                                <option value="this_month">هذا الشهر</option>
                                <option value="last_month">الشهر الماضي</option>
                                <option value="custom">فترة مخصصة</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="customDateRange" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="date_from" class="form-label">من تاريخ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_from" name="date_from">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_to" class="form-label">إلى تاريخ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_to" name="date_to">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تفاصيل التقرير</label>
                        <div id="reportDescription" class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            اختر نوع التقرير لعرض التفاصيل
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تنسيق التصدير</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="export_format" id="format_pdf" value="pdf" checked>
                                    <label class="form-check-label" for="format_pdf">
                                        <i class="fas fa-file-pdf text-danger me-1"></i>
                                        PDF
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="export_format" id="format_excel" value="excel">
                                    <label class="form-check-label" for="format_excel">
                                        <i class="fas fa-file-excel text-success me-1"></i>
                                        Excel
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="export_format" id="format_csv" value="csv">
                                    <label class="form-check-label" for="format_csv">
                                        <i class="fas fa-file-csv text-info me-1"></i>
                                        CSV
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-outline-primary" onclick="previewReport()">
                        <i class="fas fa-eye me-1"></i>
                        معاينة
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>
                        تصدير التقرير
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Report descriptions
const reportDescriptions = {
    'daily': 'تقرير شامل عن أنشطة المختبر اليومية يتضمن عدد الطلبات، النتائج المكتملة، والنتائج الحرجة',
    'summary': 'ملخص إحصائي يتضمن توزيع التحاليل، الأولويات، والحالات خلال الفترة المحددة',
    'critical': 'تقرير مفصل عن جميع النتائج الحرجة وحالة الإشعارات خلال الفترة المحددة',
    'turnaround': 'تقرير أوقات الإنجاز لجميع التحاليل مع متوسط الأوقات حسب نوع التحليل والأولوية'
};

document.getElementById('report_type').addEventListener('change', function() {
    const description = reportDescriptions[this.value] || 'اختر نوع التقرير لعرض التفاصيل';
    document.getElementById('reportDescription').innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        ${description}
    `;
});

function handleDateRangeChange() {
    const dateRange = document.getElementById('date_range').value;
    const customDateRange = document.getElementById('customDateRange');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    if (dateRange === 'custom') {
        customDateRange.style.display = 'block';
        dateFrom.required = true;
        dateTo.required = true;
    } else {
        customDateRange.style.display = 'none';
        dateFrom.required = false;
        dateTo.required = false;
        
        // Set dates based on selection
        const today = new Date();
        let fromDate, toDate;
        
        switch (dateRange) {
            case 'today':
                fromDate = toDate = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                fromDate = toDate = yesterday.toISOString().split('T')[0];
                break;
            case 'this_week':
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                fromDate = startOfWeek.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'last_week':
                const lastWeekEnd = new Date(today);
                lastWeekEnd.setDate(today.getDate() - today.getDay() - 1);
                const lastWeekStart = new Date(lastWeekEnd);
                lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
                fromDate = lastWeekStart.toISOString().split('T')[0];
                toDate = lastWeekEnd.toISOString().split('T')[0];
                break;
            case 'this_month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'last_month':
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                fromDate = lastMonth.toISOString().split('T')[0];
                toDate = lastMonthEnd.toISOString().split('T')[0];
                break;
        }
        
        dateFrom.value = fromDate;
        dateTo.value = toDate;
    }
}

document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Set dates if not custom
    if (document.getElementById('date_range').value !== 'custom') {
        data.date_from = document.getElementById('date_from').value;
        data.date_to = document.getElementById('date_to').value;
    }
    
    generateReport(data);
});

function generateReport(data) {
    const params = new URLSearchParams(data);
    const url = `/lab-specialized/reports/generate?${params.toString()}`;
    
    // Show loading
    const submitBtn = document.querySelector('#reportForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري التصدير...';
    submitBtn.disabled = true;
    
    // Open report in new window
    window.open(url, '_blank');
    
    // Reset button after delay
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
    }, 2000);
}

function previewReport() {
    const formData = new FormData(document.getElementById('reportForm'));
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Set dates if not custom
    if (document.getElementById('date_range').value !== 'custom') {
        data.date_from = document.getElementById('date_from').value;
        data.date_to = document.getElementById('date_to').value;
    }
    
    data.preview = true;
    const params = new URLSearchParams(data);
    const url = `/lab-specialized/reports/generate?${params.toString()}`;
    
    window.open(url, '_blank');
}

// Initialize with today's date
document.addEventListener('DOMContentLoaded', function() {
    handleDateRangeChange();
});
</script>