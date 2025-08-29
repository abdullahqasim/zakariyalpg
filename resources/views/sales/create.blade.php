@extends('layouts.base')

@section('title', 'Create New Sale')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create New Sale</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
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
        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sale Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_id">Customer *</label>
                                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="base_price_11_8">Base Price (11.8kg) *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="number" name="base_price_11_8" id="base_price_11_8" 
                                                   class="form-control @error('base_price_11_8') is-invalid @enderror" 
                                                   step="0.01" min="0" value="{{ old('base_price_11_8') }}" required>
                                        </div>
                                        @error('base_price_11_8')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_amount">Discount Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="number" name="discount_amount" id="discount_amount" 
                                                   class="form-control @error('discount_amount') is-invalid @enderror" 
                                                   step="0.01" min="0" value="{{ old('discount_amount', 0) }}">
                                        </div>
                                        @error('discount_amount')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($errors->has('items'))
                    <div class="alert alert-danger">
                        {{ $errors->first('items') }}
                    </div>
                    @endif
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sale Items</h3>
                        </div>
                        <div class="card-body">
                            <div id="saleItems">
                                @foreach($availableSizes as $index => $size)
                                <div class="row item-row mb-3">
                                    <div class="col-md-3">
                                        <label>{{ $size }}kg Cylinder</label>
                                        <input class="item-size" type="hidden" name="items[{{ $index }}][size_kg]" value="{{ $size }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Quantity</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control item-quantity" min="0" value="0">
                                        @error("items.$index.quantity")
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label>Unit Price</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="number" name="items[{{ $index }}][unit_price]" 
                                                   class="form-control item-unit-price" step="0.01" min="0" readonly>
                                            @error("items.$index.unit_price")
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Line Total</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="text" class="form-control item-line-total" readonly>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Sub Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">PKR</span>
                                    </div>
                                    <input type="text" id="sub_total" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Discount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">PKR</span>
                                    </div>
                                    <input type="text" id="discount_display" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong>Grand Total</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">PKR</span>
                                    </div>
                                    <input type="text" id="grand_total" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Create Sale
                            </button>
                            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const basePriceInput = $('#base_price_11_8');
    const discountInput = $('#discount_amount');
    
    // Calculate prices when base price changes
    basePriceInput.on('input', calculatePrices);
    discountInput.on('input', calculateTotals);
    
    // Calculate line totals when quantity changes
    $(document).on('change', '.item-quantity', function() {
        calculateLineTotal($(this));
        calculateTotals();
    });
    
    // Initial calculation
    calculatePrices();
    calculateTotals();
    
    function calculatePrices() {
        const basePrice = parseFloat(basePriceInput.val()) || 0;
        
        if (basePrice > 0) {
            $.ajax({
                url: '{{ route("sales.calculate-prices") }}',
                method: 'POST',
                data: {
                    base_price_11_8: basePrice,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('.item-row').each(function(index) {
                        const size = $(this).find('input[name*="[size_kg]"]').val();
                        const unitPrice = response[size] || 0;
                        $(this).find('.item-unit-price').val(unitPrice.toFixed(2));
                        calculateLineTotal($(this).find('.item-quantity'));
                    });
                    calculateTotals();
                },
                error: function() {
                    alert('Error calculating prices');
                }
            });
        }else{
            $('.item-row').each(function(index) {
                $(this).find('.item-unit-price').val(0);
                calculateLineTotal($(this).find('.item-quantity'));
            });
        }
    }
    
    function calculateLineTotal(quantityInput) {
        const row = quantityInput.closest('.item-row');
        const quantity = parseFloat(quantityInput.val()) || 0;
        const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
        const lineTotal = quantity * unitPrice;
        row.find('.item-line-total').val(lineTotal.toFixed(2));
    }
    
    function calculateTotals() {
        let subTotal = 0;
        $('.item-line-total').each(function() {
            subTotal += parseFloat($(this).val()) || 0;
        });
        
        const discount = parseFloat(discountInput.val()) || 0;
        const grandTotal = subTotal - discount;
        
        $('#sub_total').val(subTotal.toFixed(2));
        $('#discount_display').val(discount.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));
    }
});
</script>
@endpush
