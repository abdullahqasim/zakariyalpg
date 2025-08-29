@extends('layouts.base')

@section('title', 'Gas Sales')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gas Sales</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Gas Sales</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
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
        </div>

        <!-- Filters and Actions -->
        <div class="row mb-3">
            <div class="col-md-8">
                <form method="GET" action="{{ route('sales.index') }}" class="form-inline">
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
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Clear</a>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('sales.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Sale
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
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Balance</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        {{-- @dd($sale) --}}
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" class="font-weight-bold">
                                    {{ $sale->invoice_no }}
                                </a>
                            </td>
                            <td>{{ $sale->user->name }}</td>
                            <td>
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
                            </td>
                            <td>PKR{{ number_format($sale->grand_total, 2) }}</td>
                            <td>
                                aaa
                                {{-- @if($sale->balance > 0)
                                    <span class="text-danger">PKR{{ number_format($sale->balance, 2) }}</span>
                                @elseif($sale->balance < 0)
                                    <span class="text-success">PKR{{ number_format(abs($sale->balance), 2) }}</span>
                                @else
                                    <span class="text-success">PKR0.00</span>
                                @endif --}}
                            </td>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($sale->status === 'draft')
                                        {{-- <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a> --}}
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
                            </td>
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
                {{ $sales->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
