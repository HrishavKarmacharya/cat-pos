<?php

namespace App\Http\Livewire;

use App\Models\Sale;
use Carbon\Carbon;
use Livewire\Component;

class SalesChart extends Component
{
    public $chartData = [];

    public function mount()
    {
        $this->prepareChartData();
    }

    public function prepareChartData()
    {
        $sales = Sale::where('sale_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('sale_date')
            ->get()
            ->groupBy(function ($sale) {
                return Carbon::parse($sale->sale_date)->format('Y-m-d');
            });

        $labels = [];
        $data = [];
        $date = Carbon::now()->subDays(6);

        for ($i = 0; $i < 7; $i++) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('D');
            $data[] = $sales->has($dateString) ? $sales[$dateString]->sum('total_amount') : 0;
            $date->addDay();
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sales',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'data' => $data,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.sales-chart');
    }
}
