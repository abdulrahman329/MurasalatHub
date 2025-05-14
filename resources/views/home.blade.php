{{-- resources/views/home.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contracts Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-6 font-sans">
    <h1 style="font-size: 24px; margin-bottom: 20px;">Contracts Overview</h1>
    <canvas id="contractsChart" width="600" height="400"></canvas>

    <script>
        const ctx = document.getElementById('contractsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: @json($chartData['datasets']),
            },
        });
    </script>
</body>
</html>
