@extends('layouts.app')

@section('title', 'إدارة العمليات الجراحية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">إدارة العمليات الجراحية</h1>
            <p class="mb-0 text-muted">عرض وإدارة جميع العمليات الجراحية</p>
        </div>
        <div>
            <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> جدولة عملية جديدة
            </a>
            <a href="{{ route('surgeries.today') }}" class="btn btn-outline-info">
                <i class="fas fa-calendar-day"></i> عمليات اليوم
            </a>
            <a href="{{ route('surgeries.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">البحث والتصفية</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('surgeries.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>البحث</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="رقم العملية، اسم المريض، أو الجراح">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="">جميع الحالات</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                <option value="pre_op" {{ request('status') == 'pre_op' ? 'selected' : '' }}>ما قبل العملية</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>جارية</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                <option value="postponed" {{ request('status') == 'postponed' ? 'selected' : '' }}>مؤجلة</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>الأولوية</label>
                            <select name="priority" class="form-control">
                                <option value="">جميع الأولويات</option>
                                <option value="routine" {{ request('priority') == 'routine' ? 'selected' : '' }}>روتينية</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجلة</option>
                                <option value="emergency" {{ request('priority') == 'emergency' ? 'selected' : '' }}>طارئة</option>
                                <option value="elective" {{ request('priority') == 'elective' ? 'selected' : '' }}>اختيارية</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>الجراح</label>
                            <select name="surgeon_id" class="form-control">
                                <option value="">جميع الجراحين</option>
                                @foreach($surgeons as $surgeon)
                                    <option value="{{ $surgeon->id }}" {{ request('surgeon_id') == $surgeon->id ? 'selected' : '' }}>
                                        {{ $surgeon->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>غرفة العمليات</label>
                            <select name="operating_room_id" class="form-control">
                                <option value="">جميع الغرف</option>
                                @foreach($operatingRooms as $room)
                                    <option value="{{ $room->id }}" {{ request('operating_room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->or_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <a href="{{ route('surgeries.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Surgeries List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                قائمة العمليات الجراحية 
                <span class="badge badge-secondary">{{ $surgeries->total() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($surgeries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>رقم العملية</th>
                                <th>التاريخ والوقت</th>
                                <th>المريض</th>
                                <th>الجراح الرئيسي</th>
                                <th>العملية</th>
                                <th>غرفة العمليات</th>
                                <th>الحالة</th>
                                <th>الأولوية</th>
                                <th>النوع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surgeries as $surgery)
                            <tr>
                                <td>
                                    <a href="{{ route('surgeries.show', $surgery) }}" class="text-decoration-none">
                                        {{ $surgery->surgery_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $surgery->scheduled_start_time->format('Y-m-d') }}</strong>
                                        <small class="text-muted d-block">
                                            {{ $surgery->scheduled_start_time->format('H:i') }} - 
                                            {{ $surgery->scheduled_end_time->format('H:i') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $surgery->patient) }}" class="text-decoration-none">
                                        {{ $surgery->patient->name }}
                                    </a>
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
                                <td>{{ $surgery->operatingRoom->or_number }}</td>
                                <td>
                                    <span class="badge badge-{{ $surgery->status_color }}">
                                        {{ $surgery->status_display }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $surgery->priority_color }}">
                                        {{ $surgery->priority_display }}
                                    </span>
                                    @if($surgery->is_emergency)
                                        <small class="text-danger d-block">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $surgery->type_display }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('surgeries.show', $surgery) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!in_array($surgery->status, ['completed', 'cancelled']))
                                            <a href="{{ route('surgeries.edit', $surgery) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($surgery->canBeCancelled())
                                            <form method="POST" action="{{ route('surgeries.destroy', $surgery) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('هل تريد حذف هذه العملية؟')" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $surgeries->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-procedures fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">لا توجد عمليات جراحية</h5>
                    <p class="text-muted">لم يتم العثور على عمليات تطابق معايير البحث</p>
                    <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> جدولة عملية جديدة
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection