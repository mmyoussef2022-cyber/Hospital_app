@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ __('app.financial_management') }} - {{ $doctor->full_name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('financial.index') }}">{{ __('app.financial_management') }}</a></li>
                    <li class="breadcrumb-item active">{{ $doctor->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTransactionModal">
                <i class="fas fa-plus"></i> {{ __('app.add_new') }} {{ __('app.transactions') }}
            </button>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                <i class="fas fa-money-bill-wave"></i> {{ __('app.request_withdrawal') }}
            </button>
        </div>
    </div>

    <!-- Doctor Info Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="{{ $doctor->profile_photo_url }}" alt="{{ $doctor->full_name }}" 
                         class="rounded-circle img-fluid" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <div class="col-md-10">
                    <h4 class="mb-1">{{ $doctor->full_name }}</h4>
                    <p class="text-muted mb-2">{{ $doctor->specialization }}</p>
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('app.account_number') }}</small>
                            <div class="font-weight-bold">
                                @if($doctor->financialAccount)
                                    <code>{{ $doctor->financialAccount->account_number }}</code>
                                @else
                                    <span class="text-muted">{{ __('app.not_created') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('app.commission_rate') }}</small>
                            <div class="font-weight-bold">
                                {{ $doctor->financialAccount->commission_rate ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('app.status') }}</small>
                            <div>
                                @if($doctor->financialAccount && $doctor->financialAccount->status === 'active')
                                    <span class="badge badge-success">{{ __('app.active') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('app.inactive') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('app.created_at') }}</small>
                            <div class="font-weight-bold">
                                {{ $doctor->financialAccount ? $doctor->financialAccount->created_at->format('Y-m-d') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('app.available_balance') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($doctor->available_balance, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
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
                                {{ __('app.pending_balance') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($doctor->financialAccount->pending_balance ?? 0, 2) }} {{ __('app.currency') }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('app.monthly_earnings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($monthlyEarnings, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                {{ __('app.total_earnings') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($yearlyEarnings, 2) }} {{ __('app.currency') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Earnings Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.monthly_earnings') }} - {{ __('app.this_year') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('app.quick_actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('financial.transactions', $doctor) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-list text-primary"></i> {{ __('app.view') }} {{ __('app.transactions') }}</h6>
                                <small>{{ $doctor->transactions->count() }}</small>
                            </div>
                            <p class="mb-1">{{ __('app.view_all_transactions') }}</p>
                        </a>
                        
                        <a href="{{ route('financial.commissions', $doctor) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-percentage text-warning"></i> {{ __('app.commission_settings') }}</h6>
                                <small>{{ $doctor->financialAccount->commission_rate ?? 0 }}%</small>
                            </div>
                            <p class="mb-1">{{ __('app.manage_commission_settings') }}</p>
                        </a>
                        
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-chart-bar text-info"></i> {{ __('app.financial_reports') }}</h6>
                            </div>
                            <p class="mb-1">{{ __('app.generate_financial_reports') }}</p>
                        </button>
                        
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="fas fa-money-bill-wave text-success"></i> {{ __('app.request_withdrawal') }}</h6>
                            </div>
                            <p class="mb-1">{{ __('app.request_new_withdrawal') }}</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('app.recent_transactions') }}</h6>
            <a href="{{ route('financial.transactions', $doctor) }}" class="btn btn-sm btn-primary">
                {{ __('app.view_all') }}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('app.transaction_date') }}</th>
                            <th>{{ __('app.transaction_type') }}</th>
                            <th>{{ __('app.transaction_amount') }}</th>
                            <th>{{ __('app.transaction_status') }}</th>
                            <th>{{ __('app.transaction_description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @switch($transaction->type)
                                    @case('earning')
                                        <span class="badge badge-success">{{ __('app.earning_from_appointment') }}</span>
                                        @break
                                    @case('withdrawal')
                                        <span class="badge badge-info">{{ __('app.withdrawal_request') }}</span>
                                        @break
                                    @case('commission')
                                        <span class="badge badge-warning">{{ __('app.commission_deduction') }}</span>
                                        @break
                                    @case('bonus')
                                        <span class="badge badge-primary">{{ __('app.bonus_payment') }}</span>
                                        @break
                                    @case('refund')
                                        <span class="badge badge-danger">{{ __('app.refund_payment') }}</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if(in_array($transaction->type, ['earning', 'bonus']))
                                    <span class="text-success">+{{ number_format($transaction->amount, 2) }}</span>
                                @else
                                    <span class="text-danger">-{{ number_format($transaction->amount, 2) }}</span>
                                @endif
                                {{ __('app.currency') }}
                            </td>
                            <td>
                                @switch($transaction->status)
                                    @case('completed')
                                        <span class="badge badge-success">{{ __('app.completed') }}</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-warning">{{ __('app.pending') }}</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge badge-danger">{{ __('app.rejected') }}</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ Str::limit($transaction->description, 50) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                    <p>{{ __('app.no_transactions_found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
            <form action="{{ route('financial.create-transaction', $doctor) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.transaction_amount') }}</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('app.reference_number') }}</label>
                                <input type="text" name="reference_number" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.transaction_description') }}</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('app.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Withdrawal Request Modal -->
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('app.request_withdrawal') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('financial.create-transaction', $doctor) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="withdrawal">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>{{ __('app.available_balance') }}:</strong> 
                        {{ number_format($doctor->available_balance, 2) }} {{ __('app.currency') }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.withdrawal_amount') }}</label>
                        <input type="number" name="amount" class="form-control" 
                               step="0.01" min="0.01" max="{{ $doctor->available_balance }}" required>
                        <div class="form-text">{{ __('app.maximum_withdrawal') }}: {{ number_format($doctor->available_balance, 2) }} {{ __('app.currency') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.payment_method') }}</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">{{ __('app.select') }}</option>
                            <option value="bank_transfer">{{ __('app.bank_transfer') }}</option>
                            <option value="check">{{ __('app.check') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.withdrawal_reason') }}</label>
                        <textarea name="description" class="form-control" rows="3" required 
                                  placeholder="{{ __('app.describe_withdrawal_reason') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('app.request_withdrawal') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Earnings Chart
const ctx = document.getElementById('earningsChart').getContext('2d');
const earningsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($monthlyData, 'month')) !!},
        datasets: [{
            label: '{{ __("app.monthly_earnings") }}',
            data: {!! json_encode(array_column($monthlyData, 'earnings')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: '{{ __("app.monthly_earnings") }} - {{ __("app.this_year") }}'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return value.toLocaleString() + ' {{ __("app.currency") }}';
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection