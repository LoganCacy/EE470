<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = ;
$password = ;
$dbname = ;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Step 1: Get the Base64 encoded string from URL
if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
    $encodedData = $_SERVER['QUERY_STRING'];
    $decoded = base64_decode($encodedData);

    // ✅ Step 2: Display decoded message (for verification)
    echo "<h3>Decoded message:</h3><pre>$decoded</pre>";

    // ✅ Step 3: Parse variables (format: nodeId=node_1&nodeTemp=23&timeReceived=2025-10-09 12:00:00)
    parse_str($decoded, $data);

    $nodeId = $data['nodeId'] ?? null;
    $temperature = $data['nodeTemp'] ?? null;
    $timeReceived = $data['timeReceived'] ?? date("Y-m-d H:i:s");

    // ✅ Step 4: Validate and insert into database
    if ($nodeId && $temperature && $timeReceived) {
        // Check for registered node
        $checkNode = $conn->prepare("SELECT * FROM sensor_register WHERE node_name = ?");
        $checkNode->bind_param("s", $nodeId);
        $checkNode->execute();
        $result = $checkNode->get_result();

        if ($result->num_rows == 0) {
            die("Error: Node '$nodeId' is not registered.");
        }

        // Insert
        $stmt = $conn->prepare("INSERT INTO sensor_data (node_name, temperature, humidity, time_received)
                                VALUES (?, ?, 0, ?)");
        $stmt->bind_param("sds", $nodeId, $temperature, $timeReceived);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>✅ Data inserted successfully for $nodeId at $timeReceived</p>";
        } else {
            echo "<p style='color:red;'>❌ Insert failed: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Error: Missing one or more required fields.</p>";
    }
} else {
    echo "<p style='color:red;'>❌ No Base64 data found in URL.</p>";
}

$conn->close();
?>
