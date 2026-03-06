<div class="relative" style="height: 300px;">
    <canvas id="{{ $chartId }}"></canvas>
</div>

@push('scripts')
<script>
    (function() {
        let salesChartInstance = null;
        const chartId = '{{ $chartId }}';
        let initAttempts = 0;
        const maxAttempts = 50;

        function waitForChartJS(callback) {
            if (typeof Chart !== 'undefined') {
                callback();
            } else if (initAttempts < maxAttempts) {
                initAttempts++;
                setTimeout(() => waitForChartJS(callback), 100);
            } else {
                console.error('Chart.js failed to load after multiple attempts');
            }
        }

        function initializeSalesChart() {
            const canvas = document.getElementById(chartId);
            
            if (!canvas) {
                if (initAttempts < maxAttempts) {
                    initAttempts++;
                    setTimeout(initializeSalesChart, 100);
                }
                return;
            }

            waitForChartJS(function() {
                // Destroy existing chart if it exists
                if (salesChartInstance) {
                    try {
                        salesChartInstance.destroy();
                    } catch(e) {
                        console.warn('Error destroying chart:', e);
                    }
                    salesChartInstance = null;
                }

                const chartData = @json($chartData);
                
                if (!chartData || !chartData.labels || !chartData.datasets) {
                    console.error('Invalid chart data:', chartData);
                    return;
                }

                try {
                    const ctx = canvas.getContext('2d');
                    salesChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: chartData.datasets.map(dataset => ({
                                label: dataset.label || 'Total Sales',
                                data: dataset.data || [],
                                backgroundColor: 'rgba(66, 153, 225, 0.1)',
                                borderColor: '#4299E1',
                                borderWidth: 2,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#4299E1',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            const isStaff = @json($isStaff);
                                            const val = context.parsed.y.toLocaleString('en-US', {
                                                minimumFractionDigits: isStaff ? 0 : 2,
                                                maximumFractionDigits: isStaff ? 0 : 2
                                            });
                                            return (isStaff ? '' : 'Rs. ') + val + (isStaff ? ' units' : '');
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            const isStaff = @json($isStaff);
                                            const val = value.toLocaleString('en-US', {
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            });
                                            return (isStaff ? '' : 'Rs. ') + val;
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                } catch(error) {
                    console.error('Error creating chart:', error);
                }
            });
        }

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initAttempts = 0;
                initializeSalesChart();
            });
        } else {
            initAttempts = 0;
            initializeSalesChart();
        }

        // Re-initialize when Livewire updates
        if (typeof Livewire !== 'undefined') {
            document.addEventListener('livewire:init', function () {
                Livewire.hook('morph.updated', ({ el, component }) => {
                    if (el && el.querySelector && el.querySelector('#' + chartId)) {
                        initAttempts = 0;
                        setTimeout(initializeSalesChart, 100);
                    }
                });
            });
        }

        // Also listen for Livewire v2 compatibility
        document.addEventListener('livewire:load', function() {
            initAttempts = 0;
            initializeSalesChart();
        });
        document.addEventListener('livewire:update', function() {
            initAttempts = 0;
            initializeSalesChart();
        });
    })();
</script>
@endpush
