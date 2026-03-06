<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            line-height: 1.5;
            background: #fff;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-left h1 {
            margin: 0;
            color: #4338ca;
            font-size: 32px;
            letter-spacing: -1px;
        }
        .header-left p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .header-right {
            text-align: right;
        }
        .header-right .invoice-label {
            font-size: 12px;
            font-weight: bold;
            color: #6366f1;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header-right .invoice-number {
            font-size: 24px;
            font-weight: 900;
            margin: 0;
        }
        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        .details-col {
            display: table-cell;
            width: 50%;
        }
        .details-col h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .details-col p {
            margin: 0;
            font-weight: 600;
        }
        .table-container {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .summary-container {
            display: flex;
            justify-content: flex-end;
        }
        .summary-table {
            width: 250px;
            float: right;
        }
        .summary-table tr td {
            padding: 8px 12px;
            border-bottom: none;
        }
        .grand-total {
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }
        .grand-total td {
            font-weight: 900;
            font-size: 16px;
            color: #4338ca;
            padding-top: 15px !important;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
        
        /* Navigation Bar Styles */
        .nav-bar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-btn {
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn:hover {
            background: #f1f5f9;
            color: #4338ca;
        }
        .nav-btn.primary {
            background: #6366f1;
            color: #fff;
        }
        .nav-btn.primary:hover {
            background: #4f46e5;
        }
        .nav-btn svg {
            width: 16px;
            height: 16px;
        }
    </style>
</head>
<body>
    @if(!isset($isPdf))
    <div class="nav-bar no-print">
        <div class="nav-links">
            <a href="{{ route('dashboard') }}" class="nav-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('sales.index') }}" class="nav-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Sales History
            </a>
            <a href="{{ route('sales.create') }}" class="nav-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Sale
            </a>
        </div>
        <div class="nav-links">
            @if($sale->payment_method === 'khalti' && $sale->payment_status === 'pending')
                <div class="flex items-center gap-4">
                    <span class="text-red-500 text-xs font-bold uppercase py-2 px-4 bg-red-50 rounded-lg">
                        Payment Required
                    </span>
                    <a href="{{ route('khalti.initiate', $sale->id) }}" class="nav-btn primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Pay with Khalti
                    </a>
                </div>
            @else
                <button onclick="window.print()" class="nav-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <a href="{{ route('sales.download-pdf', $sale->id) }}" class="nav-btn primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Download PDF
                </a>
            @endif
        </div>

    </div>
    @endif

    <div class="invoice-box">
        <div class="header">
            <div class="header-left">
                <h1>SalesTracker</h1>
                <p>Catmando Shoppe Craft</p>
            </div>
            <div class="header-right">
                <div class="invoice-label">Invoice</div>
                <p class="invoice-number">{{ $sale->invoice_number }}</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-col">
                <h3>Billed To:</h3>
                <p>{{ $sale->customer->name ?? 'Walk-in Customer' }}</p>
                @if($sale->customer && $sale->customer->phone)
                    <div style="font-size: 13px; color: #666;">{{ $sale->customer->phone }}</div>
                @endif
                @if($sale->customer && $sale->customer->address)
                    <div style="font-size: 13px; color: #666;">{{ $sale->customer->address }}</div>
                @endif
            </div>
            <div class="details-col text-right">
                <h3>Date of Issue:</h3>
                <p>{{ $sale->sale_date->format('d M, Y') }}</p>
                <h3 style="margin-top: 15px;">Payment Method:</h3>
                <p>{{ $sale->payment_method === 'khalti' ? 'Online (Khalti)' : 'Cash' }}</p>



                <h3 style="margin-top: 10px;">Payment Status:</h3>
                <p style="color: {{ $sale->payment_status === 'paid' ? '#059669' : ($sale->payment_status === 'pending' ? '#d97706' : '#dc2626') }};">
                    {{ ucfirst($sale->payment_status) }}
                </p>


            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Product Description</th>
                        <th class="text-center" width="80">Qty</th>
                        <th class="text-right" width="120">Unit Price</th>
                        <th class="text-right" width="120">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->saleItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="font-bold">{{ $item->product->name }}</div>
                            <div style="font-size: 11px; color: #999;">SKU: {{ $item->product->sku }}</div>
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-bold">Rs. {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr>
                    <td style="color: #ef4444;">Discount</td>
                    <td class="text-right" style="color: #ef4444;">- Rs. {{ number_format($sale->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tax (0%)</td>
                    <td class="text-right">Rs. 0.00</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Due</td>
                    <td class="text-right">Rs. {{ number_format($sale->final_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Sold by: {{ $sale->user->name }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    @if(!isset($isPdf))
    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        }
    </script>
    @endif
</body>
</html>
