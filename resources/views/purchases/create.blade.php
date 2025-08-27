@extends('layouts.base')

@section('title', 'Create Purchase')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Purchase</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Purchases</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Create Supplier Purchase</h3>
                    </div>
                    <form action="{{ route('purchases.store') }}" method="POST">
                        <div class="card-body">
                            @csrf
            
                            {{-- Supplier / User --}}
                            <div class="form-group">
                                <label for="user_id">Supplier</label>
                                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('user_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            {{-- Vehicle Number --}}
                            <div class="form-group">
                                <label for="vehicle_number">Vehicle Number</label>
                                <input type="text" name="vehicle_number" id="vehicle_number"
                                        class="form-control @error('vehicle_number') is-invalid @enderror"
                                        value="{{ old('vehicle_number') }}" required>
                                @error('vehicle_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            {{-- Weight (in Tons) --}}
                            <div class="form-group">
                                <label for="weight_ton">Weight (Ton)</label>
                                <input type="number" step="0.01" name="weight_ton" id="weight_ton"
                                        class="form-control @error('weight_ton') is-invalid @enderror"
                                        value="{{ old('weight_ton') }}" required>
                                @error('weight_ton') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            {{-- Rate per 11.8kg --}}
                            <div class="form-group">
                                <label for="rate_11_8_kg">Rate per 11.8kg</label>
                                <input type="number" step="0.01" name="rate_11_8_kg" id="rate_11_8_kg"
                                        class="form-control @error('rate_11_8_kg') is-invalid @enderror"
                                        value="{{ old('rate_11_8_kg') }}" required>
                                @error('rate_11_8_kg') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            {{-- Notes --}}
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                            rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            {{-- Status --}}
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="partially_paid" {{ old('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                                @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
            
                            
                        </div>
                        {{-- Submit --}}
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Purchase
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@stop