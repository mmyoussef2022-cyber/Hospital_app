@extends('layouts.app')

@section('title', __('app.medical_records'))

@push('styles')
<link href="{{ asset('css/medical-records.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.medical_records') }}</h3>
                    <a href="{{ route('medical-records.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('app.add_new') }}
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="patient_id" class="form-control">
                                    <option value="">{{ __('app.all') }} {{ __('app.patients') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="doctor_id" class="form-control">
                                    <option value="">{{ __('app.all') }} {{ __('app.doctors') }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="visit_type" class="form-control">
                                    <option value="">{{ __('app.all') }} {{ __('app.visit_type') }}</option>
                                    <option value="consultation" {{ request('visit_type') == 'consultation' ? 'selected' : '' }}>{{ __('app.consultation') }}</option>
                                    <option value="follow_up" {{ request('visit_type') == 'follow_up' ? 'selected' : '' }}>{{ __('app.follow_up') }}</option>
                                    <option value="emergency" {{ request('visit_type') == 'emergency' ? 'selected' : '' }}>{{ __('app.emergency') }}</option>
                                    <option value="routine_checkup" {{ request('visit_type') == 'routine_checkup' ? 'selected' : '' }}>{{ __('app.routine_checkup') }}</option>
                                    <option value="procedure" {{ request('visit_type') == 'procedure' ? 'selected' : '' }}>{{ __('app.procedure') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="{{ __('app.date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">{{ __('app.filter') }}</button>
                                <a href="{{ route('medical-records.index') }}" class="btn btn-secondary">{{ __('app.clear') }}</a>
                            </div>
                        </div>
                    </form>

                    <!-- Records Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('app.patient') }}</th>
                                    <th>{{ __('app.doctor') }}</th>
                                    <th>{{ __('app.visit_date') }}</th>
                                    <th>{{ __('app.visit_type') }}</th>
                                    <th>{{ __('app.chief_complaint') }}</th>
                                    <th>{{ __('app.is_emergency') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td>
                                            <strong>{{ $record->patient->name }}</strong><br>
                                            <small class="text-muted">{{ $record->patient->phone }}</small>
                                        </td>
                                        <td>{{ $record->doctor->name }}</td>
                                        <td>{{ $record->visit_date->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @php
                                                $visitTypeColors = [
                                                    'consultation' => 'bg-primary',
                                                    'follow_up' => 'bg-success',
                                                    'emergency' => 'bg-danger',
                                                    'routine_checkup' => 'bg-info',
                                                    'procedure' => 'bg-warning'
                                                ];
                                                $colorClass = $visitTypeColors[$record->visit_type] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $colorClass }}">{{ $record->visit_type_localized }}</span>
                                        </td>
                                        <td>{{ Str::limit($record->chief_complaint_localized, 50) }}</td>
                                        <td>
                                            @if($record->is_emergency)
                                                <span class="badge bg-danger">{{ __('app.yes') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('app.no') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-info" title="{{ __('app.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('medical-records.patient-history', $record->patient) }}" class="btn btn-sm btn-outline-success" title="{{ __('app.patient_history') }}">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-sm btn-outline-warning" title="{{ __('app.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('medical-records.destroy', $record) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.confirm') }}?')" title="{{ __('app.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('app.no_data_available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $records->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection