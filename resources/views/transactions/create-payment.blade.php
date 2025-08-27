@extends('layouts.base')

@section('title', 'Record Payment - ' . $sale->invoice_no)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Record Payment</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('sales.show', $sale) }}">Sale</a></li>
                    <li class="breadcrumb-item active">Record Payment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payment Details</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transactions.store-payment', $sale) }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Payment Amount *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="number" name="amount" id="amount" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   step="0.01" min="0.01" max="{{ $sale->balance }}" 
                                                   value="{{ old('amount') }}" required>
                                        </div>
                                        <small class="form-text text-muted">
                                            Outstanding balance: PKR{{ number_format($sale->balance, 2) }}
                                        </small>
                                        @error('amount')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="method">Payment Method *</label>
                                        <select name="method" id="method" class="form-control @error('method') is-invalid @enderror" required>
                                            <option value="">Select Method</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}" {{ old('method') == $method->id ? 'selected' : '' }}>
                                                    {{ ucfirst($method->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('method')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reference">Reference Number</label>
                                        <input type="text" name="reference" id="reference" 
                                               class="form-control @error('reference') is-invalid @enderror" 
                                               value="{{ old('reference') }}" placeholder="Transaction reference">
                                        @error('reference')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="note">Note</label>
                                        <textarea name="note" id="note" rows="3" 
                                                  class="form-control @error('note') is-invalid @enderror" 
                                                  placeholder="Optional note">{{ old('note') }}</textarea>
                                        @error('note')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Record Payment
                                </button>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sale Information</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Invoice #:</strong></td>
                                <td>{{ $sale->invoice_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer:</strong></td>
                                <td>{{ $sale->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total:</strong></td>
                                <td>PKR{{ number_format($sale->grand_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Balance Due:</strong></td>
                                <td><span class="text-danger">PKR{{ number_format($sale->balance, 2) }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group-vertical w-100">
                            <a href="{{ route('transactions.pay-remaining', $sale) }}" 
                               class="btn btn-info mb-2"
                               onclick="return confirm('Pay remaining balance of PKR{{ number_format($sale->balance, 2) }}?')">
                                <i class="fas fa-credit-card"></i> Pay Remaining Balance
                            </a>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Sale
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill amount with remaining balance
    $('#amount').on('focus', function() {
        if (!$(this).val()) {
            $(this).val('{{ $sale->balance }}');
        }
    });
});
</script>
@endpush
