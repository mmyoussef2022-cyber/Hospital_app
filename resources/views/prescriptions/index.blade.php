@extends('layouts.app')

@section('title', __('app.prescriptions'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('app.prescriptions') }}</h3>
                    <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">
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
                                <select name="status" class="form-control">
                                    <option value="">{{ __('app.all') }} {{ __('app.status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('app.expired') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="{{ __('app.date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">{{ __('app.filter') }}</button>
                                <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">{{ __('app.clear') }}</a>
                            </div>
                        </div>
                    </form>

                    <!-- Prescriptions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('app.patient') }}</th>
                                    <th>{{ __('app.doctor') }}</th>
                                    <th>{{ __('app.medication_name') }}</th>
                                    <th>{{ __('app.dosage') }}</th>
                                    <th>{{ __('app.frequency') }}</th>
                                    <th>{{ __('app.duration_days') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.remaining_days') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prescriptions as $prescription)
                                    <tr>
                                        <td>
                                            <strong>{{ $prescription->patient->name }}</strong><br>
                                            <small class="text-muted">{{ $prescription->patient->phone }}</small>
                                        </td>
                                        <td>{{ $prescription->doctor->name }}</td>
                                        <td>
                                            <strong>{{ $prescription->medication_name_localized }}</strong>
                                            @if($prescription->is_controlled_substance)
                                                <span class="badge badge-warning ml-1">{{ __('app.is_controlled_substance') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $prescription->dosage }}</td>
                                        <td>{{ $prescription->frequency_localized }}</td>
                                        <td>{{ $prescription->duration_days }} {{ __('app.days') }}</td>
                                        <td>
                                            @switch($prescription->status)
                                                @case('active')
                                                    <span class="badge badge-success">{{ __('app.active') }}</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-primary">{{ __('app.completed') }}</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">{{ __('app.cancelled') }}</span>
                                                    @break
                                                @case('expired')
                                                    <span class="badge badge-secondary">{{ __('app.expired') }}</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($prescription->status == 'active')
                                                @if($prescription->remaining_days > 0)
                                                    <span class="text-success">{{ $prescription->remaining_days }} {{ __('app.days') }}</span>
                                                @else
                                                    <span class="text-danger">{{ __('app.expired') }}</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('prescriptions.print', $prescription) }}" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if($prescription->status == 'active')
                                                    <form action="{{ route('prescriptions.complete', $prescription) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('app.prescription_marked_completed') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('prescriptions.destroy', $prescription) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('app.confirm') }}?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('app.no_data_available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $prescriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection