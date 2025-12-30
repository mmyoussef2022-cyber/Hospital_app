@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ __('app.transactions') }} - {{ $doctor->full_name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('financial.index') }}">{{ __('app.financial_management') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.show', $doctor) }}">{{ $doctor->full_name }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.transactions') }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTransactionModal">
                <i class="fas fa-plus"></i> {{ __('app.add_new') }} {{ __('app.transactions') }}
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('app.filters') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('financial.transactions', $doctor) }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __('app.transaction_type') }}</label>
                            <select name="type" class="form-select">
                                <option value="">{{ __('app.all') }}</option>
                                <option value="earning" {{ request('type') === 'earning' ? 'selected' : '' }}>{{ __('app.earnings') }}</option>
                                <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>{{ __('app.withdrawals') }}</option>
                                <option value="commission" {{ request('type') === 'commission' ? 'selected' : '' }}>{{ __('app.commissions') }}</option>
                                <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>{{ __('app.bonus_payment') }}</option>
                                <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>{{ __('app.refund_payment') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __('app.transaction_status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('app.all') }}</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __('app.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __('app.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ __('app.filter') }}
                        </button>
                        <a href="{{ route('financial.transactions', $doctor) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('app.clear') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('app.transactions') }}</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">{{ __('app.actions') }}:</div>
                    <a class="dropdown-item" href="#" onclick="exportTransactions()">
                        <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                        {{ __('app.export') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="transactionsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('app.transaction_date') }}</th>
                            <th>{{ __('app.transaction_type') }}</th>
                            <th>{{ __('app.transaction_amount') }}</th>
                            <th>{{ __('app.transaction_status') }}</th>
                            <th>{{ __('app.payment_method') }}</th>
                            <th>{{ __('app.reference_number') }}</th>
                            <th>{{ __('app.transaction_description') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                            </td>
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
                                    <span class="text-success font-weight-bold">+{{ number_format($transaction->amount, 2) }}</span>
                                @else
                                    <span class="text-danger font-weight-bold">-{{ number_format($transaction->amount, 2) }}</span>
                                @endif
                                <small class="text-muted">{{ __('app.currency') }}</small>
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
                            <td>
                                @if($transaction->payment_method)
                                    @switch($transaction->payment_method)
                                        @case('cash')
                                            <span class="badge badge-secondary">{{ __('app.cash') }}</span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge badge-info">{{ __('app.bank_transfer') }}</span>
                                            @break
                                        @case('check')
                                            <span class="badge badge-warning">{{ __('app.check') }}</span>
                                            @break
                                        @case('online_payment')
                                            <span class="badge badge-primary">{{ __('app.online_payment') }}</span>
                                            @break
                                    @endswitch
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->reference_number)
                                    <code>{{ $transaction->reference_number }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $transaction->description }}">
                                    {{ Str::limit($transaction->description, 50) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" data-bs-target="#viewTransactionModal{{ $transaction->id }}"
                                            title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @if($transaction->type === 'withdrawal' && $transaction->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            data-bs-toggle="modal" data-bs-target="#processWithdrawalModal{{ $transaction->id }}"
                                            title="{{ __('app.process') }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- View Transaction Modal -->
                        <div class="modal fade" id="viewTransactionModal{{ $transaction->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('app.transaction_details') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.transaction_date') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ $transaction->created_at->format('Y-m-d H:i') }}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.transaction_type') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ ucfirst($transaction->type) }}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.transaction_amount') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ number_format($transaction->amount, 2) }} {{ __('app.currency') }}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.transaction_status') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ ucfirst($transaction->status) }}
                                            </div>
                                        </div>
                                        @if($transaction->payment_method)
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.payment_method') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                            </div>
                                        </div>
                                        @endif
                                        @if($transaction->reference_number)
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{ __('app.reference_number') }}:</strong>
                                            </div>
                                            <div class="col-6">
                                                <code>{{ $transaction->reference_number }}</code>
                                            </div>
                                        </div>
                                        @endif
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>{{ __('app.transaction_description') }}:</strong>
                                                <p class="mt-2">{{ $transaction->description }}</p>
                                            </div>
                                        </div>
                                        @if($transaction->notes)
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>{{ __('app.notes') }}:</strong>
                                                <p class="mt-2">{{ $transaction->notes }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Process Withdrawal Modal -->
                        @if($transaction->type === 'withdrawal' && $transaction->status === 'pending')
                        <div class="modal fade" id="processWithdrawalModal{{ $transaction->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('app.process') }} {{ __('app.withdrawal_request') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('financial.process-withdrawal', $transaction) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <strong>{{ __('app.withdrawal_amount') }}:</strong> 
                                                {{ number_format($transaction->amount, 2) }} {{ __('app.currency') }}
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('app.action') }}</label>
                                                <select name="action" class="form-select" required>
                                                    <option value="">{{ __('app.select') }}</option>
                                                    <option value="approve">{{ __('app.approve_withdrawal') }}</option>
                                                    <option value="reject">{{ __('app.reject_withdrawal') }}</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('app.notes') }}</label>
                                                <textarea name="notes" class="form-control" rows="3" 
                                                          placeholder="{{ __('app.add_processing_notes') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('app.process') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>{{ __('app.no_transactions_found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->appends(request()->query())->links() }}
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

@push('scripts')
<script>
function exportTransactions() {
    window.print();
}

// Initialize DataTable
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        "language": {
            "url": "{{ app()->getLocale() === 'ar' ? '//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json' : '' }}"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});
</script>
@endpush
@endsection