// admin-main.js
document.addEventListener('DOMContentLoaded', () => {
    console.log("Admin Loaded Successfully");
});

//dashbord-js

    const ctx = document.getElementById('viewsChart').getContext('2d');
    
    // Gradient for the line
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(255, 0, 0, 0.1)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

    const chartData = {
        weekly: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            data: [1200, 1900, 1500, 2100, 2400, 1800, 2900]
        },
        monthly: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            data: [45000, 52000, 48000, 61000, 59000, 72000]
        }
    };

    let viewsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.weekly.labels,
            datasets: [{
                label: 'Views',
                data: chartData.weekly.data,
                borderColor: '#FF0000',
                borderWidth: 4,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#FF0000',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: true, color: '#f9fafb' },
                    border: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' }, color: '#9ca3af' }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 10, weight: 'bold' }, color: '#9ca3af' }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            }
        }
    });

    function updateChart(period) {
        viewsChart.data.labels = chartData[period].labels;
        viewsChart.data.datasets[0].data = chartData[period].data;
        viewsChart.update();
        
        // Update button UI (Not included for brevity, but you'd toggle classes)
    }
