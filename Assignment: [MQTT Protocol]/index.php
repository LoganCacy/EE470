<!DOCTYPE html>
<html>
<head>
    <title>Sensor Data Visualization</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            padding: 40px;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.12);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Real-Time Potentiometer Graph</h2>
    <canvas id="myChart" height="120"></canvas>
</div>

<script>
async function fetchData() {
    const response = await fetch("data.php");
    const data = await response.json();

    const timestamps = data.map(entry => entry.timestamp);
    const values = data.map(entry => entry.value);

    return { timestamps, values };
}

async function renderChart() {
    const data = await fetchData();

    const ctx = document.getElementById('myChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.timestamps,
            datasets: [{
                label: 'Potentiometer Value',
                data: data.values,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.2)',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: "Time" }
                },
                y: {
                    title: { display: true, text: "Value" }
                }
            }
        }
    });
}

renderChart();
</script>

</body>
</html>
