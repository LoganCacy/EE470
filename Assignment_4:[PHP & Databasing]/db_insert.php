<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: Database connection details
$servername = "localhost";  // On Hostinger, this may look like "mysql.hostinger.com"
$username = ;  // <-- replace with your Hostinger database username
$password = ;  // <-- replace with your Hostinger database password
$dbname = ;  // <-- replace with your Hostinger database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Step 2: Get parameters from URL ---
$node_name = $_GET['nodeId'] ?? null;
$time_received = $_GET['timeReceived'] ?? date("Y-m-d H:i:s"); // auto timestamp if not included
$temperature = $_GET['nodeTemp'] ?? null;
$humidity = $_GET['nodeHumidity'] ?? null;

// --- Step 3: Validate input ---
if (!$node_name || !$temperature || !$humidity) {
    die("Error: Missing required parameters. Use ?nodeId=node_1&nodeTemp=25&nodeHumidity=50");
}

// --- Step 4: Verify node exists in sensor_register ---
$checkNode = $conn->prepare("SELECT * FROM sensor_register WHERE node_name = ?");
$checkNode->bind_param("s", $node_name);
$checkNode->execute();
$result = $checkNode->get_result();

if ($result->num_rows == 0) {
    die("Error: Node '$node_name' is not registered. Data not accepted.");
}

// --- Step 5: Validate data range ---
if ($temperature < -10 || $temperature > 100 || $humidity < 0 || $humidity > 100) {
    die("Error: Data out of accepted range. Temp must be -10–100, Humidity 0–100.");
}

// --- Step 6: Prevent duplicate entries for same node and timestamp ---
$checkDup = $conn->prepare("SELECT * FROM sensor_data WHERE node_name=? AND time_received=?");
$checkDup->bind_param("ss", $node_name, $time_received);
$checkDup->execute();
$dupResult = $checkDup->get_result();

if ($dupResult->num_rows > 0) {
    die("Error: Duplicate entry for node '$node_name' at time '$time_received'.");
}

// --- Step 7: Insert the data ---
$insert = $conn->prepare("INSERT INTO sensor_data (node_name, time_received, temperature, humidity)
                          VALUES (?, ?, ?, ?)");
$insert->bind_param("ssdd", $node_name, $time_received, $temperature, $humidity);

if ($insert->execute()) {
    echo "✅ Data inserted successfully for $node_name at $time_received";
} else {
    echo "❌ Error inserting data: " . $conn->error;
}

$conn->close();
?>
