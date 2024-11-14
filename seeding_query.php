<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Define the expected passkey
$expected_passkey = "make up a passkey here to match on your script that calls the json data"; // Replace with your actual passkey

// Check for the passkey in the 'Authorization' header
$headers = getallheaders();
$received_passkey = isset($headers['Authorization']) ? $headers['Authorization'] : null;

// Validate the passkey
if ($received_passkey !== $expected_passkey) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Path to SQLite database
$dbPath = 'D:\whitelist\sqlite.db';
$db = new SQLite3($dbPath);

// Check if the table `seeding_Users` exists
$checkTableQuery = "SELECT name FROM sqlite_master WHERE type='table' AND name='seeding_Users'";
$tableResult = $db->querySingle($checkTableQuery);

if (!$tableResult) {
    echo json_encode(["status" => "error", "message" => "Table 'seeding_Users' does not exist."]);
    exit;
}

// Query the data from `seeding_Users` using steamID and points
$query = "SELECT steamID, points FROM seeding_Users";
$result = $db->query($query);
if (!$result) {
    echo json_encode(["status" => "error", "message" => "Query execution failed."]);
    exit;
}

// Fetch each row and add it to the data array
$data = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
}

// Final output of data in JSON format
echo json_encode(["status" => "success", "data" => $data]);
?>

