@extends('layouts.app')

@section('title', "Summary - {$supplier->name}")

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">Summary - {{ $supplier->name }}</h1>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-md-4">
        <div class="info-box bg-light">
          <span class="info-box-text">Total Purchases</span>
          <span class="info-box-number">{{ number_format($statistics['total_purchases'], 2) }}</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box bg-light">
          <span class="info-box-text">Total Paid</span>
          <span class="info-box-number">{{ number_format($statistics['total_paid'], 2) }}</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box bg-light">
          <span class="info-box-text">Balance</span>
          <span class="info-box-number">{{ number_format($statistics['balance'], 2) }}</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-primary text-white">
        <h3 class="card-title">Purchases</h3>
      </div>
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th>
              <th>Reference</th>
              <th>Status</th>
              <th class="text-right">Amount</th>
              <th class="text-right">Paid</th>
              <th class="text-right">Balance</th>
            </tr>
          </thead>
          <tbody>
            @forelse($purchases as $p)
              <tr>
                <td>{{ $p->created_at->format('Y-m-d') }}</td>
                <td>{{ $p->reference_no }}</td>
                <td><span class="badge badge-info">{{ $p->status }}</span></td>
                <td class="text-right">{{ number_format($p->total, 2) }}</td>
                <td class="text-right">{{ number_format($p->paid, 2) }}</td>
                <td class="text-right">{{ number_format($p->balance, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center">No purchases found</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
@endsection
