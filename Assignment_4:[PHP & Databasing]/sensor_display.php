<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: Database connection details
$servername = "localhost";  // On Hostinger, this may look like "mysql.hostinger.com"
$username = ;  
$password = ;  
$dbname = ; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Fetch data from both tables
$sql1 = "SELECT * FROM sensor_register ORDER BY node_name";
$sql2 = "SELECT * FROM sensor_data ORDER BY node_name, time_received";

$result1 = $conn->query($sql1);
$result2 = $conn->query($sql2);

// ✅ Calculate average temperature and humidity for Node 1
$node = 'node_1';  // You can change this to node_2, node_3, etc.
$avgQuery = "SELECT 
                AVG(temperature) AS avg_temp, 
                AVG(humidity) AS avg_humidity 
            FROM sensor_data 
            WHERE node_name = '$node'";

$avgResult = $conn->query($avgQuery);
$avg_temp = $avg_humidity = null;

if ($avgResult && $avgResult->num_rows > 0) {
    $row = $avgResult->fetch_assoc();
    $avg_temp = round($row['avg_temp'], 2);
    $avg_humidity = round($row['avg_humidity'], 2);
}

// HTML + CSS output
echo "<!DOCTYPE html>
<html>
<head>
<title>Sensor Data Display</title>
<style>
    body { 
        font-family: Arial, sans-serif; 
        background-color: #f4f6f8; 
        text-align: center; 
        padding: 20px; 
    }
    h1 { color: #222; }
    h2 { color: #333; margin-top: 40px; }
    table { 
        border-collapse: collapse; 
        width: 85%; 
        margin: 20px auto; 
        background: #fff; 
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td { 
        border: 1px solid #ccc; 
        padding: 8px; 
        text-align: center; 
    }
    th { 
        background-color: #007bff; 
        color: white; 
        text-transform: capitalize; 
    }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .average-box {
        background: #eaf2ff;
        border: 1px solid #007bff;
        border-radius: 6px;
        width: 50%;
        margin: 30px auto;
        padding: 15px;
        text-align: center;
    }
</style>
</head>
<body>
<h1>Sensor Data Display</h1>";

// --- Display sensor_register table ---
echo "<h2>Sensor Register Table</h2>";
if ($result1 && $result1->num_rows > 0) {
    echo "<table><tr>";
    while ($fieldinfo = $result1->fetch_field()) {
        echo "<th>{$fieldinfo->name}</th>";
    }
    echo "</tr>";
    while ($row = $result1->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) echo "<td>$value</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in sensor_register or query failed.</p>";
}

// --- Display sensor_data table ---
echo "<h2>Sensor Data Table</h2>";
if ($result2 && $result2->num_rows > 0) {
    echo "<table><tr>";
    while ($fieldinfo = $result2->fetch_field()) {
        echo "<th>{$fieldinfo->name}</th>";
    }
    echo "</tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) echo "<td>$value</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in sensor_data or query failed.</p>";
}

// ✅ Display the averages for Node 1
if ($avg_temp !== null && $avg_humidity !== null) {
    echo "<div class='average-box'>
            <h3>Average Data for $node</h3>
            <p><strong>Average Temperature:</strong> $avg_temp °C</p>
            <p><strong>Average Humidity:</strong> $avg_humidity %</p>
          </div>";
} else {
    echo "<p>Could not calculate averages for $node.</p>";
}

echo "</body></html>";

echo "
<h2>Temperature Chart for $node</h2>
<canvas id='tempChart' width='300' height='150'></canvas>

<!-- Load Chart.js -->
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

<script>
fetch('get_data.php')
  .then(response => response.json())
  .then(data => {
    const times = data.map(row => row.time_received);
    const temps = data.map(row => row.temperature);

    const ctx = document.getElementById('tempChart').getContext('2d');
    new Chart(ctx, {
      type: 'line', // You can change this to 'line' later for Q1
      data: {
        labels: times,
        datasets: [{
          label: 'Temperature (°C)',
          data: temps,
          backgroundColor: 'rgba(0, 255, 0, 0.6)', // You’ll change this to green for Q2
          borderColor: 'rgba(0, 255, 0, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          x: { title: { display: true, text: 'Time' } },
          y: { title: { display: true, text: 'Temperature (°C)' } }
        },
        plugins: {
          title: {
            display: true,
            text: 'Sensor Node 1 Temperature Readings'
          }
        }
      }
    });
  });
</script>
";

$conn->close();
?>
