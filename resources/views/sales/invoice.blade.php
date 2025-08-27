<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .company-info {
            color: #666;
            font-size: 14px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info, .customer-info {
            flex: 1;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            color: #333;
        }
        .value {
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .totals {
            margin-left: auto;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 18px;
            border-bottom: 2px solid #333;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-confirmed { background-color: #17a2b8; color: white; }
        .status-partially_paid { background-color: #ffc107; color: black; }
        .status-paid { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        @media print {
            body { background-color: white; }
            .invoice-container { box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">GAS SALES COMPANY</div>
            <div class="company-info">
                123 Gas Street, City, State 12345<br>
                Phone: (123) 456-7890 | Email: info@gassales.com<br>
                Website: www.gassales.com
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="info-row">
                    <span class="label">Invoice #:</span>
                    <span class="value">{{ $sale->invoice_no }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ $sale->created_at->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value">
                        <span class="status-badge status-{{ $sale->status }}">
                            {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                        </span>
                    </span>
                </div>
            </div>
            <div class="customer-info">
                <div class="invoice-title">BILL TO</div>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">{{ $sale->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $sale->user->email }}</span>
                </div>
                @if($sale->user->phone)
                <div class="info-row">
                    <span class="label">Phone:</span>
                    <span class="value">{{ $sale->user->phone }}</span>
                </div>
                @endif
                @if($sale->user->address)
                <div class="info-row">
                    <span class="label">Address:</span>
                    <span class="value">{{ $sale->user->address }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->size_kg }}kg Gas Cylinder</td>
                    <td>{{ $item->quantity }}</td>
                    <td>PKR{{ number_format($item->unit_price, 2) }}</td>
                    <td>PKR{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Sub Total:</span>
                <span>PKR{{ number_format($sale->sub_total, 2) }}</span>
            </div>
            @if($sale->discount_amount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span>-PKR{{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>Grand Total:</span>
                <span>PKR{{ number_format($sale->grand_total, 2) }}</span>
            </div>
            @if($sale->balance > 0)
            <div class="total-row">
                <span>Balance Due:</span>
                <span style="color: #dc3545;">PKR{{ number_format($sale->balance, 2) }}</span>
            </div>
            @elseif($sale->balance < 0)
            <div class="total-row">
                <span>Overpayment:</span>
                <span style="color: #28a745;">PKR{{ number_format(abs($sale->balance), 2) }}</span>
            </div>
            @else
            <div class="total-row">
                <span>Balance:</span>
                <span style="color: #28a745;">PKR0.00</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Payment Terms: Net 30 days | Late payment may result in additional charges</p>
            <p>For questions about this invoice, please contact us at (123) 456-7890</p>
        </div>

        <!-- Print Button (hidden when printing) -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Print Invoice
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Close
            </button>
        </div>
    </div>
</body>
</html>
