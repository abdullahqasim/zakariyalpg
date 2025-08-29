@extends('layouts.base')

@section('title', 'Supplier Ledger')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Supplier Ledger</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Supplier Ledger</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    {{-- Filter Form --}}
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Filter Ledger</h3>
      </div>
      <form method="GET" action="{{ route('supplier-ledger.index') }}">
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="supplier_id">Supplier</label>
              <select name="supplier_id" class="form-control">
                <option value="">-- Select Supplier --</option>
                @foreach($suppliers as $sup)
                  <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
                    {{ $sup->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Start Date</label>
              <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
              <label>End Date</label>
              <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    @if($supplier)
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title">Ledger for {{ $supplier->name }}</h3>
        </div>
        <div class="card-body">
          <div class="row text-center mb-3">
            <x-info-box title="Total Purchases" :value="$statistics['total_purchases']" />
            <x-info-box title="Total Paid" :value="$statistics['total_paid']" />
            <x-info-box title="Balance" :value="$statistics['balance']" />
            <x-info-box title="Transactions" :value="$statistics['purchase_count'] + $statistics['payment_count']" />
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Reference #</th>
                        <th>Details</th>
                        <th>Particulars</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center font-weight-bold">
                            OPENING  BALANCE
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format(abs($openingBalance), 2) }}
                            {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}
                        </td>
                    </tr>
                    @php
                        $closingBalance = 0;
                    @endphp
                    @forelse($transactions as $t)
                        @php
                            // Update balance (amount can be + for debit, - for credit)
                            $closingBalance += $t->amount;
                        @endphp
                        <tr>
                            <td>{{ $t->transactionable->reference_no ?? '-' }}</td>
                            <td>
                                {{ $t->created_at->format('d-m-Y') }} <br>
                                @if ($t->transaction_type == 'purchase')
                                    {{ $t->transactionable->vehicle_number ?? '' }} <br>
                                    <b>WGT:</b> {{ $t->transactionable->weight_ton ?? '' }} <br>
                                    @ {{ $t->transactionable->rate_11_8_kg ?? '' }}
                                @endif
                            </td>
                            <td>
                                @if ($t->transaction_type == 'purchase')
                                    <span class="badge badge-warning" style="font-size:100%">
                                        Gas Purchased
                                    </span>
                                @elseif ($t->transaction_type == 'payment_out')
                                    <span class="badge badge-success" style="font-size:100%">
                                        Payment Made
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                {{-- Show only if amount > 0 (Debit) --}}
                                @if ($t->amount > 0)
                                    {{ number_format($t->amount, 2) }}
                                @endif
                            </td>
                            <td class="text-right">
                                {{-- Show only if amount < 0 (Credit) --}}
                                @if ($t->amount < 0)
                                    {{ number_format(abs($t->amount), 2) }}
                                @endif
                            </td>
                            <td class="text-right">
                                {{ number_format(abs($closingBalance), 2) }}
                                {{ $closingBalance >= 0 ? 'Dr' : 'Cr' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No transactions found</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="5" class="text-center font-weight-bold">
                            CLOSING BALANCE
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format(abs($closingBalance), 2) }}
                            {{ $closingBalance >= 0 ? 'Dr' : 'Cr' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
    @endif
  </div>
</section>
@endsection
