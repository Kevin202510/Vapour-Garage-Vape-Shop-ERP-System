<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1714; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { font-size: 9px; color: #6b6259; margin-bottom: 16px; }
        h2 {
            font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em;
            color: #6b6259; margin: 18px 0 6px; border-bottom: 1px solid #d8d0c2; padding-bottom: 3px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { text-align: left; padding: 4px 6px; border-bottom: 1px solid #ece7dc; }
        th { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #8a8072; }
        td { font-size: 9.5px; }
        .num { text-align: right; }
        .muted { color: #8a8072; }
    </style>
</head>
<body>
    <h1>Vapour Garage — Dashboard Report ({{ ucfirst($range) }}: {{ $dateFrom }} to {{ $dateTo }})</h1>
    <p class="meta">Branch: {{ $branchLabel }} &nbsp;·&nbsp; Generated {{ now()->format('M d, Y g:i A') }}</p>

    <h2>Summary</h2>
    <table>
        <thead><tr><th>Metric</th><th class="num">Value</th><th class="num">Change</th></tr></thead>
        <tbody>
            @foreach($summary as $row)
                <tr><td>{{ $row[0] }}</td><td class="num">{{ $row[1] }}</td><td class="num">{{ $row[2] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Revenue by Branch</h2>
    <table>
        <thead><tr><th>Branch</th><th class="num">Revenue</th></tr></thead>
        <tbody>
            @forelse($branchRevenue as $branch)
                <tr><td>{{ $branch->name }}</td><td class="num">₱{{ number_format($branch->revenue, 2) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">No data for this range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Sales Mix by Category</h2>
    <table>
        <thead><tr><th>Category</th><th class="num">Revenue</th><th class="num">% of Total</th></tr></thead>
        <tbody>
            @forelse($categorySales as $cat)
                <tr>
                    <td>{{ $cat->name }}</td>
                    <td class="num">₱{{ number_format($cat->total, 2) }}</td>
                    <td class="num">{{ round(($cat->total / $totalCategoryRevenue) * 100) }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No data for this range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Transaction Detail ({{ $transactions->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>TXN #</th><th>Date</th><th>Branch</th><th>Cashier</th><th>Customer</th>
                <th>Payment</th><th class="num">Subtotal</th><th class="num">Tax</th><th class="num">Discount</th><th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td>{{ $txn->transaction_number }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($txn->transaction_date)->format('M d, g:i A') }}</td>
                    <td>{{ $txn->branch }}</td>
                    <td>{{ $txn->cashier ?: 'N/A' }}</td>
                    <td>{{ $txn->customer }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $txn->payment_method)) }}</td>
                    <td class="num">{{ number_format($txn->subtotal, 2) }}</td>
                    <td class="num">{{ number_format($txn->tax_amount, 2) }}</td>
                    <td class="num">{{ number_format($txn->discount_amount, 2) }}</td>
                    <td class="num">{{ number_format($txn->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="muted">No transactions for this range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
