<?php
/**
 * Test Database Connection
 * Run this file to check if database connection works
 * Access: http://localhost/test_connection.php
 */

require_once 'config/Database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        h1 { color: #333; }
        pre {
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>";

echo "<h1>🔌 Database Connection Test</h1>";

try {
    // Create database instance
    $db = new Database();

    // Connect to database
    $conn = $db->connect();

    echo "<div class='success'>";
    echo "<h2>✅ Connection Successful!</h2>";
    echo "<p><strong>Database:</strong> webtech_2025A_marc_sossou</p>";
    echo "<p><strong>Host:</strong> 169.239.251.102:3306</p>";
    echo "<p><strong>Status:</strong> Connected</p>";
    echo "</div>";

    // Test query - check if tables exist
    echo "<div class='info'>";
    echo "<h3>📊 Checking Tables...</h3>";

    $result = $conn->query("SHOW TABLES");

    if ($result && $result->num_rows > 0) {
        echo "<p><strong>Found " . $result->num_rows . " tables:</strong></p>";
        echo "<pre>";
        while ($row = $result->fetch_array()) {
            echo "✓ " . $row[0] . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>⚠️ No tables found. Please run create_tables.sql in phpMyAdmin.</p>";
    }
    echo "</div>";

    // Check categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<div class='info'>";
        echo "<h3>📁 Sample Data Check</h3>";
        echo "<p>Categories: <strong>$count</strong></p>";

        if ($count > 0) {
            $result = $conn->query("SELECT name FROM categories LIMIT 5");
            echo "<p>Sample categories: ";
            $cats = [];
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row['name'];
            }
            echo implode(', ', $cats) . "</p>";
        }
        echo "</div>";
    }

    // Close connection
    $db->close();

    echo "<div class='success'>";
    echo "<h3>🎉 All Tests Passed!</h3>";
    echo "<p>Your database is ready to use. You can now start building PHP pages.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Connection Failed</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check if database 'webtech_2025A_marc_sossou' exists</li>";
    echo "<li>Verify username 'marc.sossou' and password are correct</li>";
    echo "<li>Make sure MySQL server is running</li>";
    echo "<li>Check if host '169.239.251.102' is accessible</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</body></html>";
?>
