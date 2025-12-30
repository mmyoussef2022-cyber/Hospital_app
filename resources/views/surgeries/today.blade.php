@extends('layouts.app')

@section('title', 'عمليات اليوم')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">عمليات اليوم</h1>
            <p class="mb-0 text-muted">{{ now()->format('Y-m-d') }} - {{ now()->translatedFormat('l') }}</p>
        </div>
        <div>
            <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> جدولة عملية جديدة
            </a>
            <a href="{{ route('surgeries.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['total'] }}</h4>
                    <small>إجمالي العمليات</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['scheduled'] }}</h4>
                    <small>مجدولة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['in_progress'] }}</h4>
                    <small>جارية</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['completed'] }}</h4>
                    <small>مكتملة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['cancelled'] }}</h4>
                    <small>ملغاة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <h4>{{ $statistics['emergency'] }}</h4>
                    <small>طوارئ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Surgeries List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">قائمة العمليات</h6>
        </div>
        <div class="card-body">
            @if($todaySurgeries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="surgeriesTable">
                        <thead>
                            <tr>
                                <th>رقم العملية</th>
                                <th>الوقت</th>
                                <th>المريض</th>
                                <th>الجراح الرئيسي</th>
                                <th>العملية</th>
                                <th>غرفة العمليات</th>
                                <th>الحالة</th>
                                <th>الأولوية</th>
                                <th>الفريق الجراحي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todaySurgeries as $surgery)
                            <tr class="surgery-row" data-surgery-id="{{ $surgery->id }}">
                                <td>
                                    <a href="{{ route('surgeries.show', $surgery) }}" class="text-decoration-none">
                                        {{ $surgery->surgery_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $surgery->scheduled_start_time->format('H:i') }}</strong>
                                        <small class="text-muted d-block">
                                            إلى {{ $surgery->scheduled_end_time->format('H:i') }}
                                        </small>
                                        @if($surgery->actual_start_time)
                                            <small class="text-success d-block">
                                                بدأت: {{ $surgery->actual_start_time->format('H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <a href="{{ route('patients.show', $surgery->patient) }}" class="text-decoration-none">
                                            {{ $surgery->patient->name }}
                                        </a>
                                        <small class="text-muted d-block">
                                            {{ $surgery->patient->age }} سنة - {{ $surgery->patient->gender_display }}
                                        </small>
                                    </div>
                                </td>
                                <td>{{ $surgery->primarySurgeon->name }}</td>
                                <td>
                                    <div>
                                        {{ $surgery->surgicalProcedure->display_name }}
                                        <small class="text-muted d-block">
                                            {{ $surgery->surgicalProcedure->complexity_display }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $surgery->operatingRoom->status_color }}">
                                        {{ $surgery->operatingRoom->or_number }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $surgery->status_color }}">
                                        {{ $surgery->status_display }}
                                    </span>
                                    @if($surgery->is_overdue)
                                        <small class="text-danger d-block">متأخرة</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $surgery->priority_color }}">
                                        {{ $surgery->priority_display }}
                                    </span>
                                    @if($surgery->is_emergency)
                                        <small class="text-danger d-block">
                                            <i class="fas fa-exclamation-triangle"></i> طارئة
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($surgery->surgicalTeam->count() > 0)
                                        <small>
                                            @foreach($surgery->surgicalTeam->take(3) as $member)
                                                <div>{{ $member->user->name }} ({{ $member->role_display }})</div>
                                            @endforeach
                                            @if($surgery->surgicalTeam->count() > 3)
                                                <div class="text-muted">+{{ $surgery->surgicalTeam->count() - 3 }} أخرى</div>
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">لم يتم تحديد الفريق</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('surgeries.show', $surgery) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($surgery->canBeStarted())
                                            <form method="POST" action="{{ route('surgeries.start', $surgery) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('هل تريد بدء العملية؟')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($surgery->canBeCompleted())
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#completeModal{{ $surgery->id }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if($surgery->canBeCancelled())
                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#cancelModal{{ $surgery->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">لا توجد عمليات مجدولة لليوم</h5>
                    <p class="text-muted">يمكنك جدولة عملية جديدة من خلال النقر على الزر أعلاه</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Complete Surgery Modals -->
@foreach($todaySurgeries->where('status', 'in_progress') as $surgery)
<div class="modal fade" id="completeModal{{ $surgery->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('surgeries.complete', $surgery) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">إكمال العملية - {{ $surgery->surgery_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>ملاحظات العملية *</label>
                        <textarea name="operative_notes" class="form-control" rows="4" required 
                                  placeholder="تفاصيل سير العملية والإجراءات المتخذة..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>ملاحظات ما بعد العملية</label>
                        <textarea name="post_operative_notes" class="form-control" rows="3" 
                                  placeholder="تعليمات ما بعد العملية..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>المضاعفات (إن وجدت)</label>
                        <textarea name="complications" class="form-control" rows="2" 
                                  placeholder="أي مضاعفات حدثت أثناء العملية..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>التكلفة الفعلية</label>
                        <input type="number" name="actual_cost" class="form-control" step="0.01" 
                               value="{{ $surgery->estimated_cost }}" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">إكمال العملية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Cancel Surgery Modals -->
@foreach($todaySurgeries->whereIn('status', ['scheduled', 'pre_op']) as $surgery)
<div class="modal fade" id="cancelModal{{ $surgery->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('surgeries.cancel', $surgery) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">إلغاء العملية - {{ $surgery->surgery_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>سبب الإلغاء *</label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required 
                                  placeholder="اذكر سبب إلغاء العملية..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">إلغاء العملية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#surgeriesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
        },
        "order": [[ 1, "asc" ]], // Sort by time
        "pageLength": 25,
        "responsive": true
    });

    // Auto refresh every 2 minutes
    setInterval(function() {
        location.reload();
    }, 120000);

    // Highlight overdue surgeries
    $('.surgery-row').each(function() {
        var row = $(this);
        if (row.find('.text-danger:contains("متأخرة")').length > 0) {
            row.addClass('table-warning');
        }
    });
});
</script>
@endpush
@endsection