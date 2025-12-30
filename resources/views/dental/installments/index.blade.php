@extends('layouts.app')

@section('title', 'أقساط الأسنان')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">أقساط الأسنان</h3>
                    <div>
                        <a href="{{ route('dental.installments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة قسط جديد
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">إجمالي الأقساط</span>
                                    <span class="info-box-number">{{ $stats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">معلقة</span>
                                    <span class="info-box-number">{{ $stats['pending'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">مدفوعة</span>
                                    <span class="info-box-number">{{ $stats['paid'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">متأخرة</span>
                                    <span class="info-box-number">{{ $stats['overdue'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="treatment_id" class="form-control">
                                    <option value="">جميع العلاجات</option>
                                    @foreach($treatments as $treatment)
                                        <option value="{{ $treatment->id }}" {{ request('treatment_id') == $treatment->id ? 'selected' : '' }}>
                                            {{ $treatment->patient->name }} - {{ $treatment->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>متأخر</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="due_from" class="form-control" value="{{ request('due_from') }}" placeholder="من تاريخ">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="due_to" class="form-control" value="{{ request('due_to') }}" placeholder="إلى تاريخ">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="بحث...">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">بحث</button>
                            </div>
                        </div>
                    </form>

                    <!-- Installments Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>رقم القسط</th>
                                    <th>المريض</th>
                                    <th>العلاج</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($installments as $installment)
                                    <tr>
                                        <td>{{ $installment->installment_number }}</td>
                                        <td>{{ $installment->dentalTreatment->patient->name }}</td>
                                        <td>{{ $installment->dentalTreatment->title }}</td>
                                        <td>{{ number_format($installment->amount, 2) }} ريال</td>
                                        <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $installment->status_color }}">
                                                {{ $installment->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('dental.installments.show', $installment) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('dental.installments.edit', $installment) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($installment->status === 'pending')
                                                    <form method="POST" action="{{ route('dental.installments.mark-paid', $installment) }}" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد من تحديد هذا القسط كمدفوع؟')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد أقساط</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $installments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection