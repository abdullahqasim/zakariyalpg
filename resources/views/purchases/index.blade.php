@extends('layouts.base')

@section('title', 'Gas Purchase')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gas Purchase</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Gas Purchase</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        {{-- <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $statistics['total_sales'] }}</h3>
                        <p>Total Sales</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>PKR{{ number_format($statistics['total_revenue'], 2) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>PKR{{ number_format($statistics['pending_amount'], 2) }}</h3>
                        <p>Pending Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $sales->total() }}</h3>
                        <p>Records</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Filters and Actions -->
        <div class="row mb-3">
            <div class="col-md-8">
                <form method="GET" action="{{ route('purchases.index') }}" class="form-inline">
                    <div class="form-group mr-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <select name="per_page" class="form-control">
                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Clear</a>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('purchases.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Purchase
                </a>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales List</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference #</th>
                            <th>Supplier</th>
                            <th>Vehicle #</th>
                            <th>Weight(TON)</th>
                            <th>Total Weight(KG)</th>
                            <th>Total Amount</th>
                            <th>Rate(11.8)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}" class="font-weight-bold">
                                    {{ $purchase->reference_no }}
                                </a>
                            </td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>{{ $purchase->vehicle_number }}</td>
                            <td>{{ $purchase->weight_ton }}</td>
                            <td>{{ $purchase->total_kg }}</td>
                            <td>PKR {{ number_format($purchase->total_amount, 2) }}</td>
                            <td>{{ $purchase->rate_11_8_kg }}</td>
                            {{-- <td>
                                <div class="btn-group">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($sale->status === 'draft')
                                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('sales.confirm', $sale) }}" class="btn btn-sm btn-success" 
                                           onclick="return confirm('Confirm this sale?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                    @if(in_array($sale->status, ['draft', 'confirmed']))
                                        <a href="{{ route('sales.cancel', $sale) }}" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Cancel this sale?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td> --}}
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $purchases->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
