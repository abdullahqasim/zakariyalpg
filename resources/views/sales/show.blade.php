@extends('layouts.base')

@section('title', 'Sale Details - ' . $sale->invoice_no)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Sale Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">{{ $sale->invoice_no }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Status Alert -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <!-- Sale Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sale Information</h3>
                        <div class="card-tools">
                            @switch($sale->status)
                                @case('draft')
                                    <span class="badge badge-secondary">Draft</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge badge-info">Confirmed</span>
                                    @break
                                @case('partially_paid')
                                    <span class="badge badge-warning">Partially Paid</span>
                                    @break
                                @case('paid')
                                    <span class="badge badge-success">Paid</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge badge-danger">Cancelled</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    {{-- <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
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
                                        <td><strong>Date:</strong></td>
                                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Price (11.8kg):</strong></td>
                                        <td>PKR{{ number_format($sale->base_price_11_8, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Sub Total:</strong></td>
                                        <td>PKR{{ number_format($sale->sub_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Discount:</strong></td>
                                        <td>PKR{{ number_format($sale->discount_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total:</strong></td>
                                        <td><strong>PKR{{ number_format($sale->grand_total, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Balance:</strong></td>
                                        <td>
                                            @if($sale->balance > 0)
                                                <span class="text-danger"><strong>PKR{{ number_format($sale->balance, 2) }}</strong></span>
                                            @elseif($sale->balance < 0)
                                                <span class="text-success"><strong>PKR{{ number_format(abs($sale->balance), 2) }}</strong></span>
                                            @else
                                                <span class="text-success"><strong>PKR0.00</strong></span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <!-- Sale Items -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sale Items</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cylinder Size</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $item)
                                <tr>
                                    <td>{{ $item->formatted_size }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->formatted_unit_price }}</td>
                                    <td>{{ $item->formatted_line_total }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No items found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transactions</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        @switch($transaction->transaction_type)
                                            @case('sale')
                                                <span class="badge badge-primary">Sale</span>
                                                @break
                                            @case('payment_in')
                                                <span class="badge badge-success">Payment Received</span>
                                                @break
                                            @case('refund')
                                                <span class="badge badge-warning">Refund</span>
                                                @break
                                            @case('adjustment')
                                                <span class="badge badge-info">Adjustment</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $transaction->formatted_amount }}</td>
                                    <td>{{ $transaction->paymentMethod?->name ?? '-' }}</td>
                                    <td>{{ isset($transaction->details['reference'])? $transaction->details['reference']: '-' }}</td>
                                    <td>{{ isset($transaction->details['note'])? $transaction->details['note'] : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group-vertical w-100">
                            @if($sale->status === 'draft')
                                {{-- <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning mb-2">
                                    <i class="fas fa-edit"></i> Edit Sale
                                </a> --}}
                                <a href="{{ route('sales.confirm', $sale) }}" class="btn btn-success mb-2"
                                   onclick="return confirm('Confirm this sale?')">
                                    <i class="fas fa-check"></i> Confirm Sale
                                </a>
                            @endif

                            @if(in_array($sale->status, ['draft', 'confirmed']))
                                <a href="{{ route('sales.cancel', $sale) }}" class="btn btn-danger mb-2"
                                   onclick="return confirm('Cancel this sale?')">
                                    <i class="fas fa-times"></i> Cancel Sale
                                </a>
                            @endif

                            @if($sale->status !== 'draft' && $sale->status !== 'cancelled')
                                <a href="{{ route('transactions.create-payment', $sale) }}" class="btn btn-success mb-2">
                                    <i class="fas fa-money-bill-wave"></i> Record Payment
                                </a>
                                
                                {{-- @if($sale->balance > 0)
                                    <a href="{{ route('transactions.pay-remaining', $sale) }}" class="btn btn-info mb-2"
                                       onclick="return confirm('Pay remaining balance of PKR{{ number_format($sale->balance, 2) }}?')">
                                        <i class="fas fa-credit-card"></i> Pay Remaining
                                    </a>
                                @endif --}}

                                {{-- <a href="{{ route('transactions.create-refund', $sale) }}" class="btn btn-warning mb-2">
                                    <i class="fas fa-undo"></i> Record Refund
                                </a> --}}

                                {{-- <a href="{{ route('transactions.create-adjustment', $sale) }}" class="btn btn-secondary mb-2">
                                    <i class="fas fa-cog"></i> Record Adjustment
                                </a> --}}
                            @endif

                            <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-primary mb-2" target="_blank">
                                <i class="fas fa-print"></i> Print Invoice
                            </a>

                            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Sales
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Customer Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Sales Invoice:</strong> {{ $sale->invoice_no }}</p>
                        <p><strong>Base Price (11.8 kg):</strong> {{ $sale->base_price_11_8 }}</p>
                        <p><strong>Name:</strong> {{ $sale->user->name }}</p>
                        {{-- <p><strong>Email:</strong> {{ $sale->user->email }}</p> --}}
                        <p><strong>Total:</strong> {{ $sale->grand_total }}</p>
                        <p><strong>Date:</strong> {{ $sale->created_at->format(config('app.date_format_2')) }}</p>
                        
                        <a href="{{ route('sales.customer', $sale->user_id) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-history"></i> View Customer Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
