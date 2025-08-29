@extends('layouts.base')

@section('title', "Ledger - {$supplier->name}")

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">Ledger for {{ $supplier->name }}</h1>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="row mb-3">
        <x-info-box title="Total Purchases" :value="$statistics['total_purchases']" />
        <x-info-box title="Total Purchases" :value="$statistics['total_paid']" />
        <x-info-box title="Total Purchases" :value="$statistics['balance']" />
    </div>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <h3 class="card-title">Transactions</h3>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Reference</th>
              <th>Type</th>
              <th>Details</th>
              <th class="text-right">Amount</th>
              <th class="text-right">Balance</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $t)
              <tr>
                <td>{{ $t->created_at->format('Y-m-d') }}</td>
                <td>{{ $t->reference_no }}</td>
                <td>{{ $t->transaction_type }}</td>
                {{-- <td>{{ $t->details }}</td> --}}
                <td class="text-right">{{ number_format($t->amount, 2) }}</td>
                <td class="text-right">{{ number_format($t->balance, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center">No transactions found</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
@endsection
