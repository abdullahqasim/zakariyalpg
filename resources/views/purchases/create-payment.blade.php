@extends('layouts.base')

@section('title', 'Record Payment')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Record Payment</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          {{-- <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li> --}}
          <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchases</a></li>
          <li class="breadcrumb-item active">Record Payment</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    {{-- Alerts --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Payment for Purchase #{{ $purchase->reference_no }}</h3>
      </div>

      <form method="POST" action="{{ route('purchases.record-payment', $purchase) }}">
        @csrf

        <div class="card-body">
          <div class="form-group">
            <label>Outstanding Balance</label>
            <input type="text" class="form-control" value="{{ number_format($purchase->balance, 2) }}" disabled>
          </div>

          <div class="form-group">
            <label for="amount">Payment Amount <span class="text-danger">*</span></label>
            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $purchase->balance }}"
                   value="{{ old('amount') }}"
                   class="form-control @error('amount') is-invalid @enderror" required>
            @error('amount')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="details">Payment Details</label>
            <textarea name="details" class="form-control @error('details') is-invalid @enderror" rows="3"
                      placeholder="Optional">{{ old('details') }}</textarea>
            @error('details')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn btn-primary">Record Payment</button>
          <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>

  </div>
</section>
@endsection
