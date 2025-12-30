<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">معلومات الحدث</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="fw-medium" style="width: 40%;">نوع الحدث:</td>
                <td><code class="text-primary">{{ $securityLog->event_type }}</code></td>
            </tr>
            <tr>
                <td class="fw-medium">المستوى:</td>
                <td>
                    @switch($securityLog->level)
                        @case('info')
                            <span class="badge bg-info">معلومات</span>
                            @break
                        @case('warning')
                            <span class="badge bg-warning">تحذير</span>
                            @break
                        @case('error')
                            <span class="badge bg-danger">خطأ</span>
                            @break
                        @case('critical')
                            <span class="badge bg-dark">حرج</span>
                            @break
                        @default
                            <span class="badge bg-secondary">{{ $securityLog->level }}</span>
                    @endswitch
                </td>
            </tr>
            <tr>
                <td class="fw-medium">المستخدم:</td>
                <td>
                    @if($securityLog->user)
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $securityLog->user->name }}</div>
                                <small class="text-muted">{{ $securityLog->user->email }}</small>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="fw-medium">عنوان IP:</td>
                <td><code>{{ $securityLog->ip_address }}</code></td>
            </tr>
            <tr>
                <td class="fw-medium">وقت الحدث:</td>
                <td>
                    <div>{{ $securityLog->created_at->format('Y-m-d H:i:s') }}</div>
                    <small class="text-muted">{{ $securityLog->created_at->diffForHumans() }}</small>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="mb-3">تفاصيل الطلب</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="fw-medium" style="width: 40%;">الرابط:</td>
                <td>
                    @if($securityLog->url)
                        <code class="text-break">{{ $securityLog->url }}</code>
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="fw-medium">طريقة الطلب:</td>
                <td>
                    @if($securityLog->method)
                        <span class="badge bg-secondary">{{ $securityLog->method }}</span>
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="fw-medium">المتصفح:</td>
                <td>
                    @if($securityLog->user_agent)
                        <div class="text-break small">{{ $securityLog->user_agent }}</div>
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="mb-3">وصف الحدث</h6>
        <div class="bg-light p-3 rounded">
            <p class="mb-0">{{ $securityLog->description }}</p>
        </div>
    </div>
</div>

@if($securityLog->additional_data)
<div class="row mt-4">
    <div class="col-12">
        <h6 class="mb-3">بيانات إضافية</h6>
        <div class="bg-dark text-light p-3 rounded">
            <pre class="mb-0 text-light"><code>{{ json_encode(json_decode($securityLog->additional_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
        </div>
    </div>
</div>
@endif

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.text-break {
    word-break: break-all;
}

pre code {
    font-size: 0.85rem;
    line-height: 1.4;
}
</style>