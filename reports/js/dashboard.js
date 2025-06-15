document.addEventListener('DOMContentLoaded', function() {
    // Update date display
    function updateDate() {
        const today = new Date();
        const options = { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        document.getElementById('displayDate').innerText = today.toLocaleDateString('en-US', options);
    }
    updateDate();

    // Chart configuration
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            },
            title: {
                display: false
            }
        }
    };

    // Service/Age Distribution Chart
    const serviceChart = new Chart(document.getElementById('serviceChart'), {
        type: 'pie',
        data: {
            labels: ['Children (0-12)', 'Youth (13-24)', 'Adults (25-59)', 'Seniors (60+)'],
            datasets: [{
                data: chartData.age,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
            }]
        },
        options: { ...chartConfig }
    });

    // Gender Distribution Chart
    const genderChart = new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [chartData.gender.male, chartData.gender.female],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        },
        options: { ...chartConfig }
    });

    // Purpose/Service Types Chart
    const purposeChart = new Chart(document.getElementById('purposeChart'), {
        type: 'bar',
        data: {
            labels: chartData.purpose.labels,
            datasets: [{
                label: 'Number of Services',
                data: chartData.purpose.data,
                backgroundColor: '#4BC0C0'
            }]
        },
        options: {
            ...chartConfig,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});