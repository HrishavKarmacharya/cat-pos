<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;


class PaymentDashboard extends Component
{
    use WithPagination;

    public $status = '';
    public $date_from;
    public $date_to;
    public $search = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Payment::with(['sale.customer'])
            ->when($this->status, function ($q) {
                return $q->where('status', $this->status);
            })
            ->when($this->date_from, function ($q) {
                return $q->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($q) {
                return $q->whereDate('created_at', '<=', $this->date_to);
            })
            ->when($this->search, function ($q) {
                return $q->where(function($sub) {
                    $sub->where('order_id', 'like', '%' . $this->search . '%')
                        ->orWhere('khalti_transaction_id', 'like', '%' . $this->search . '%')
                        ->orWhere('khalti_pidx', 'like', '%' . $this->search . '%')
                        ->orWhereHas('sale', function($sq) {
                            $sq->where('invoice_number', 'like', '%' . $this->search . '%')
                               ->orWhereHas('customer', function($cq) {
                                   $cq->where('name', 'like', '%' . $this->search . '%');
                               });
                        });
                });
            })
            ->orderBy('created_at', 'desc');

        $payments = $query->paginate(10);
        
        // --- Calculate Stats for the Selected Period ---
        $from = $this->date_from . ' 00:00:00';
        $to = $this->date_to . ' 23:59:59';

        // 1. Online Revenue (Successful Payments)
        $onlineRevenue = Payment::where('status', 'PAID')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // 2. Cash Revenue (Completed Sales with Cash Method)
        $cashRevenue = \App\Models\Sale::where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->whereBetween('sale_date', [$from, $to])
            ->sum('final_amount');

        // 3. Total Expenditure (Purchases)
        $totalExp = \App\Models\Purchase::whereBetween('purchase_date', [$this->date_from, $this->date_to])
            ->sum('total_amount');

        // 4. Counts
        $pendingCount = Payment::where('status', 'PENDING')
            ->whereBetween('created_at', [$from, $to])
            ->count();
            
        $paidCount = Payment::where('status', 'PAID')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $stats = [
            'total_revenue' => $onlineRevenue + $cashRevenue,
            'online_revenue' => $onlineRevenue,
            'cash_revenue' => $cashRevenue,
            'total_expenses' => $totalExp,
            'net_balance' => ($onlineRevenue + $cashRevenue) - $totalExp,
            'pending_count' => $pendingCount,
            'paid_count' => $paidCount,
        ];

        return view('livewire.payment-dashboard', [
            'payments' => $payments,
            'stats' => $stats,
        ])->layout('layouts.app');
    }

    public function resetFilters()
    {
        $this->reset(['status', 'search']);
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function exportCsv()
    {
        $query = Payment::with(['sale.customer'])
            ->when($this->status, function ($q) {
                return $q->where('status', $this->status);
            })
            ->when($this->date_from, function ($q) {
                return $q->whereDate('created_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($q) {
                return $q->whereDate('created_at', '<=', $this->date_to);
            })
            ->when($this->search, function ($q) {
                return $q->where(function($sub) {
                    $sub->where('order_id', 'like', '%' . $this->search . '%')
                        ->orWhere('khalti_transaction_id', 'like', '%' . $this->search . '%')
                        ->orWhere('khalti_pidx', 'like', '%' . $this->search . '%')
                        ->orWhereHas('sale', function($sq) {
                            $sq->where('invoice_number', 'like', '%' . $this->search . '%')
                               ->orWhereHas('customer', function($cq) {
                                   $cq->where('name', 'like', '%' . $this->search . '%');
                               });
                        });
                });
            })
            ->orderBy('created_at', 'desc');

        $payments = $query->get();
        
        $filename = "payments_export_" . now()->format('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Gateway', 'Invoice Number', 'UUID', 'Customer', 'Amount', 'Status', 'Ref ID'];

        $callback = function() use($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at,
                    $payment->gateway,
                    $payment->sale->invoice_number ?? 'N/A',
                    $payment->order_id,
                    $payment->sale->customer->name ?? 'N/A',
                    $payment->amount,
                    $payment->status,
                    $payment->khalti_transaction_id ?? $payment->pidx ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
