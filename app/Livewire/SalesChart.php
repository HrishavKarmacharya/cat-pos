<?php

namespace App\Livewire;

use App\Models\Sale;
use Carbon\Carbon;
use Livewire\Component;

class SalesChart extends Component
{
    public $chartId;
    public $startDate;
    public $endDate;

    public function mount($startDate = null, $endDate = null)
    {
        $this->chartId = 'salesChart-' . uniqid();
        $this->startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(6)->startOfDay();
        $this->endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfDay();
    }

    public function render()
    {
        $startDate = $this->startDate instanceof Carbon ? $this->startDate : Carbon::parse($this->startDate);
        $endDate = $this->endDate instanceof Carbon ? $this->endDate : Carbon::parse($this->endDate);
        
        // Adjust to ensure full day coverage if strings passed
        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->endOfDay();

        $isStaff = auth()->user()->role === 'staff';

        // Get sales data grouped by date
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate]);

        if ($isStaff) {
            $query->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
                  ->selectRaw('DATE(sale_date) as date, SUM(sale_items.quantity) as metric_value');
        } else {
            $query->selectRaw('DATE(sale_date) as date, SUM(final_amount) as metric_value');
        }

        $sales = $query->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                // Ensure date is in Y-m-d format for matching
                $date = is_string($item->date) ? $item->date : Carbon::parse($item->date)->format('Y-m-d');
                return [$date => (float) $item->metric_value];
            });

        // Generate dates for the selected range
        $dates = collect();
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dates->push($current->format('Y-m-d'));
            $current->addDay();
        }

        // Create chart labels (formatted dates)
        $chartLabels = $dates->map(function ($date) {
            return Carbon::parse($date)->format('M d');
        });

        // Map sales data to dates (fill with 0 if no sales)
        $chartData = $dates->map(function ($date) use ($sales) {
            return $sales->get($date, 0);
        });

        $chartDataset = [
            'labels' => $chartLabels->values()->all(),
            'datasets' => [
                [
                    'label' => $isStaff ? 'Units Sold' : 'Total Sales',
                    'backgroundColor' => '#4299E1',
                    'borderColor' => '#4299E1',
                    'data' => $chartData->values()->all(),
                    'fill' => false,
                ],
            ],
        ];

        return view('livewire.sales-chart', [
            'chartData' => $chartDataset,
            'chartId' => $this->chartId,
            'isStaff' => auth()->user()->role === 'staff',
        ]);
    }
}
