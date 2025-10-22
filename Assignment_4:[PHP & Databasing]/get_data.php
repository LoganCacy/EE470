<?php
header('Content-Type: application/json');

// Step 1: Database connection details
$servername = "localhost";  
$username = ;  
$password = ;  
$dbname = ;  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only get data for one node (e.g., node_1)
$node = 'node_1';
$sql = "SELECT time_received, temperature FROM sensor_data WHERE node_name = '$node' ORDER BY time_received ASC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
