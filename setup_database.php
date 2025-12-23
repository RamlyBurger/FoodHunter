<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password
$db = 'university_canteen';

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Create Database
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS $db")) {
    die("Error creating database: " . $mysqli->error);
}

echo "Database '$db' created or already exists.\n";

$mysqli->select_db($db);

// Import SQL file
$sqlFile = 'university_canteen.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found.");
}

$sql = file_get_contents($sqlFile);

// Simple split by semicolon. NOTE: This might fail if semicolon is inside string.
// For a simple dump, this is often sufficient.
// Better regex or parser is preferred but this is a quick fix for agentic context.
$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if (!$mysqli->query($query)) {
            echo "Error executing query: " . substr($query, 0, 50) . "... Error: " . $mysqli->error . "\n";
        }
    }
}

echo "Database setup completed.\n";
$mysqli->close();
?>
