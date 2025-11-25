<?php
header("Content-Type: application/json");

// Database connection settings
$servername = "localhost"; // When running PHP on Hostinger, "localhost" is correct
$username = "u137220217_db_logancacy";
$password = "S@lty123";
$dbname = "u137220217_logancacy";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

// If connection fails
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Query data
$sql = "SELECT value, timestamp FROM sensor_value ORDER BY timestamp ASC";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$conn->close();
?>
