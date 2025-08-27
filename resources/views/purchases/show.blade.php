{{-- resources/views/purchases/show.blade.php --}}
@extends('layouts.base')

@section('title', 'Purchase Details')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Purchase Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchases</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            {{-- Purchase Summary --}}
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Purchase #{{ $purchase->reference_no }}</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Supplier</dt>
                            <dd class="col-sm-9">{{ $purchase->supplier->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">Vehicle Number</dt>
                            <dd class="col-sm-9">{{ $purchase->vehicle_number }}</dd>

                            <dt class="col-sm-3">Weight (Ton)</dt>
                            <dd class="col-sm-9">{{ number_format($purchase->weight_ton, 2) }}</dd>

                            <dt class="col-sm-3">Total KG</dt>
                            <dd class="col-sm-9">{{ number_format($purchase->total_kg, 2) }}</dd>

                            {{-- <dt class="col-sm-3">Total Cylinders</dt>
                            <dd class="col-sm-9">{{ $purchase->total_cylinders }}</dd> --}}

                            <dt class="col-sm-3">Rate (11.8kg)</dt>
                            <dd class="col-sm-9">{{ number_format($purchase->rate_11_8_kg, 2) }}</dd>

                            <dt class="col-sm-3">Total Amount</dt>
                            <dd class="col-sm-9"><strong>PKR {{ number_format($purchase->total_amount, 2) }}</strong></dd>

                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9">
                                <span class="badge 
                                    @if($purchase->status == 'paid') badge-success 
                                    @elseif($purchase->status == 'partially_paid') badge-warning 
                                    @else badge-secondary @endif">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </dd>

                            <dt class="col-sm-3">Notes</dt>
                            <dd class="col-sm-9">{{ $purchase->notes ?? '-' }}</dd>

                            <dt class="col-sm-3">Date</dt>
                            <dd class="col-sm-9">{{ $purchase->created_at->format('d M Y h:i A') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Supplier Statistics</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-6">Total Purchases</dt>
                            <dd class="col-sm-6">{{ number_format($statistics['total_purchases'] ?? 0, 2) }}</dd>
        
                            <dt class="col-sm-6">Total Paid</dt>
                            <dd class="col-sm-6">{{ number_format($statistics['total_paid'] ?? 0, 2) }}</dd>
        
                            <dt class="col-sm-6">Balance</dt>
                            <dd class="col-sm-6"><strong>{{ number_format($statistics['balance'] ?? 0, 2) }}</strong></dd>
                        </dl>
                    </div>
                </div>
            </div>
            {{-- Supplier Statistics --}}
        </div>

        

        {{-- Transactions --}}
        {{-- <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Transactions</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
                                <td>
                                    <span class="badge 
                                        @if($transaction->transaction_type == 'purchase') badge-primary
                                        @elseif(str_contains($transaction->transaction_type, 'payment')) badge-warning
                                        @else badge-secondary @endif">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                    </span>
                                </td>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                                <td>{{ number_format($transaction->balance, 2) }}</td>
                                <td>
                                    @if($transaction->details)
                                        <pre class="mb-0">{{ json_encode($transaction->details, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('d M Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}

        {{-- Back Button --}}
        {{-- <div class="mt-3">
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Purchases
            </a>
        </div> --}}
    </div>
</section>
@stop
