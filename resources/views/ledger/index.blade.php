@extends('layouts.base')

@section('title', 'Customer Ledger')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    @if($selectedCustomer)
                    Customer Ledger: <strong>{{ $selectedCustomer->name }}</strong>
                    @else
                    Customer Ledger
                    @endif
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Customer Ledger</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Customer Selection -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Select Customer</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('ledger.index') }}" class="form-inline">
                    <div class="form-group mr-2">
                        <select name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">View Ledger</button>
                </form>
            </div>
        </div>

        @if($selectedCustomer)
        <!-- Customer Summary -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>PKR{{ number_format($summary['total_sales'] ?? 0, 2) }}</h3>
                        <p>Total Sales</p>
                    </div>
                    <div class="icon"><i class="fas fa-file-invoice"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>PKR{{ number_format($summary['total_payments'] ?? 0, 2) }}</h3>
                        <p>Total Payments</p>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box {{ ($summary['balance'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' }}">
                    <div class="inner">
                        <h3>PKR{{ number_format($summary['balance'] ?? 0, 2) }}</h3>
                        <p>Balance</p>
                    </div>
                    <div class="icon"><i class="fas fa-balance-scale"></i></div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        {{-- <div class="card">
            <div class="card-header"><h3 class="card-title">Customer Information</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $selectedCustomer->name }}</p>
                        <p><strong>Email:</strong> {{ $selectedCustomer->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Phone:</strong> {{ $selectedCustomer->phone ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $selectedCustomer->address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Ledger Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaction Ledger</h3>
                <div class="card-tools">
                    <a href="{{ route('ledger.customer', $selectedCustomer->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Detailed View
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Detail</th>
                            <th>Payment Method</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Banalce</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $balance = 0
                        @endphp
                        @forelse($ledger['transactions'] ?? [] as $transaction)
                        @php
                            $balance += $transaction->amount;
                            
                        @endphp
                        <tr>
                            {{-- @dd($transaction) --}}
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @switch($transaction->transaction_type)
                                    @case('sale') <span class="badge badge-primary">Sale</span> @break
                                    @case('payment_in') <span class="badge badge-success">Payment</span> @break
                                    @case('refund') <span class="badge badge-warning">Refund</span> @break
                                    @case('adjustment') <span class="badge badge-info">Adjustment</span> @break
                                    @default <span class="badge badge-secondary">{{ ucfirst($transaction->transaction_type) }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'sale')
                                    Invoice # {{ $transaction->transactionable->invoice_no }} <br>
                                    Base Price (11.8): {{ number_format($transaction->transactionable->base_price_11_8) }}
                                @endif
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'payment_in')
                                    {{ $transaction->paymentMethod->name }}
                                @endif
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'sale')
                                    PKR {{ number_format($transaction->amount, 0) }}
                                @endif
                            </td>
                            <td>
                                @if($transaction->transaction_type == 'payment_in')
                                    PKR {{ number_format($transaction->amount, 0) }}
                                @endif
                            </td>
                            <td>
                                PKR {{ number_format($balance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No transactions found.</td>
                        </tr>
                        @endforelse
                        <tr>
                            <td colspan="6"><strong>Closing Balance: </strong></td>
                            <td>
                                <span class="{{ ($ledger['balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    <b>PKR {{ number_format($ledger['balance'] ?? 0, 2) }}</b>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
