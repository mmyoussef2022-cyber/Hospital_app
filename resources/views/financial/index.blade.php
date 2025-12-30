@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ __('app.financial_management') }}</h1>
            <p class="mb-0 text-muted">{{ __('app.doctor_finances') }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTransactionModal">
                <i class="fas fa-plus"></i> {{ __('app.add_new') }} {{ __('app.transactions') }}
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('app.total_earnings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalEarnings, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('app.total_withdrawals') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalWithdrawals, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('app.pending_withdrawals') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($pendingWithdrawals, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('app.monthly_earnings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($monthlyEarnings, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctors Financial Accounts -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('app.financial_accounts') }}</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">{{ __('app.actions') }}:</div>
                    <a class="dropdown-item" href="#" onclick="exportData()">
                        <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                        {{ __('app.export') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('app.doctors') }}</th>
                            <th>{{ __('app.account_number') }}</th>
                            <th>{{ __('app.available_balance') }}</th>
                            <th>{{ __('app.pending_balance') }}</th>
                            <th>{{ __('app.total_earnings') }}</th>
                            <th>{{ __('app.commission_rate') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ $doctor->profile_photo_url }}" 
                                         alt="{{ $doctor->full_name }}" width="40" height="40">
                                    <div>
                                        <div class="font-weight-bold">{{ $doctor->full_name }}</div>
                                        <div class="text-muted small">{{ $doctor->specialization }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($doctor->financialAccount)
                                    <code>{{ $doctor->financialAccount->account_number }}</code>
                                @else
                                    <span class="text-muted">{{ __('app.not_created') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success font-weight-bold">
                                    {{ number_format($doctor->available_balance, 2) }} {{ __('app.currency') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-warning">
                                    {{ number_format($doctor->financialAccount->pending_balance ?? 0, 2) }} {{ __('app.currency') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-primary">
                                    {{ number_format($doctor->total_earnings, 2) }} {{ __('app.currency') }}
                                </span>
                            </td>
                            <td>
                                @if($doctor->financialAccount)
                                    {{ $doctor->financialAccount->commission_rate }}%
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($doctor->financialAccount)
                                    @if($doctor->financialAccount->status === 'active')
                                        <span class="badge badge-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ __('app.inactive') }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-warning">{{ __('app.not_created') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('financial.show', $doctor) }}" 
                                       class="btn btn-sm btn-outline-primary" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('financial.transactions', $doctor) }}" 
                                       class="btn btn-sm btn-outline-info" title="{{ __('app.transactions') }}">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="{{ route('financial.commissions', $doctor) }}" 
                                       class="btn btn-sm btn-outline-warning" title="{{ __('app.commissions') }}">
                                        <i class="fas fa-percentage"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>{{ __('app.no_data_available') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($doctors->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $doctors->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Transaction Modal -->
<div class="modal fade" id="createTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('app.add_new') }} {{ __('app.transactions') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTransactionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.doctors') }}</label>
                                <select name="doctor_id" class="form-select" required onchange="updateFormAction()">
                                    <option value="">{{ __('app.select') }} {{ __('app.doctors') }}</option>
                                    @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.transaction_type') }}</label>
                                <select name="type" class="form-select" required>
                                    <option value="earning">{{ __('app.earning_from_appointment') }}</option>
                                    <option value="bonus">{{ __('app.bonus_payment') }}</option>
                                    <option value="commission">{{ __('app.commission_deduction') }}</option>
                                    <option value="refund">{{ __('app.refund_payment') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.transaction_amount') }}</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.payment_method') }}</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">{{ __('app.select') }}</option>
                                    <option value="cash">{{ __('app.cash') }}</option>
                                    <option value="bank_transfer">{{ __('app.bank_transfer') }}</option>
                                    <option value="check">{{ __('app.check') }}</option>
                                    <option value="online_payment">{{ __('app.online_payment') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.transaction_description') }}</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.reference_number') }}</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" onclick="return validateForm()">{{ __('app.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportData() {
    // Export functionality
    window.print();
}

// Update form action when doctor is selected
function updateFormAction() {
    const doctorSelect = document.querySelector('select[name="doctor_id"]');
    const form = document.getElementById('createTransactionForm');
    
    if (doctorSelect.value) {
        form.action = `/financial/doctors/${doctorSelect.value}/transactions`;
    } else {
        form.action = '';
    }
}

// Validate form before submission
function validateForm() {
    const doctorSelect = document.querySelector('select[name="doctor_id"]');
    
    if (!doctorSelect.value) {
        alert('{{ __("app.please_select_doctor") }}');
        return false;
    }
    
    return true;
}

// Initialize DataTable
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "{{ app()->getLocale() === 'ar' ? '//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json' : '' }}"
        },
        "order": [[ 0, "asc" ]],
        "pageLength": 25,
        "responsive": true
    });
});
</script>
@endpush
@endsection