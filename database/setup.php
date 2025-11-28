<?php
/**
 * Database Setup Script
 * BONTEN Event Management System
 *
 * This script sets up the database schema and performs initial setup.
 * Run this file once to create all necessary tables, views, and triggers.
 *
 * Usage: Access this file via browser (e.g., http://localhost/database/setup.php)
 *        or run via CLI: php setup.php
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../config/Database.php';

// HTML Header for browser output
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup - BONTEN</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 40px 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 { font-size: 28px; margin-bottom: 10px; }
            .header p { opacity: 0.9; }
            .content {
                padding: 30px;
            }
            .step {
                margin-bottom: 20px;
                padding: 15px;
                border-left: 4px solid #667eea;
                background: #f8f9fa;
                border-radius: 4px;
            }
            .step.success {
                border-left-color: #4caf50;
                background: #e8f5e9;
            }
            .step.error {
                border-left-color: #f44336;
                background: #ffebee;
            }
            .step.warning {
                border-left-color: #ff9800;
                background: #fff3e0;
            }
            .step-title {
                font-weight: 600;
                margin-bottom: 5px;
                color: #333;
            }
            .step-message {
                color: #666;
                font-size: 14px;
            }
            .summary {
                margin-top: 30px;
                padding: 20px;
                background: #667eea;
                color: white;
                border-radius: 8px;
                text-align: center;
            }
            .summary h2 { margin-bottom: 10px; }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: white;
                color: #667eea;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin-top: 15px;
                transition: transform 0.2s;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
            .code {
                background: #2d3748;
                color: #48bb78;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: monospace;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎉 BONTEN Database Setup</h1>
                <p>Event Management System - Database Installation</p>
            </div>
            <div class="content">';
}

/**
 * Output a message
 */
function outputMessage($title, $message, $type = 'info') {
    global $isCLI;

    if ($isCLI) {
        $prefix = match($type) {
            'success' => '✓',
            'error' => '✗',
            'warning' => '⚠',
            default => 'ℹ'
        };
        echo "$prefix $title: $message\n";
    } else {
        echo "<div class='step $type'>
                <div class='step-title'>$title</div>
                <div class='step-message'>$message</div>
              </div>";
    }
}

/**
 * Read and execute SQL file
 */
function executeSQLFile($conn, $filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("SQL file not found: $filePath");
    }

    $sql = file_get_contents($filePath);

    // Remove comments and split by delimiter
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt); }
    );

    $executed = 0;
    $errors = [];

    foreach ($statements as $statement) {
        // Skip empty statements and delimiter changes
        if (empty(trim($statement)) || stripos($statement, 'DELIMITER') === 0) {
            continue;
        }

        try {
            if ($conn->query($statement) === false) {
                $errors[] = "Error: " . $conn->error . "\nStatement: " . substr($statement, 0, 100);
            } else {
                $executed++;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    return [
        'executed' => $executed,
        'errors' => $errors
    ];
}

// ============================================================================
// MAIN SETUP PROCESS
// ============================================================================

try {
    outputMessage('Starting Setup', 'Initializing database setup process...', 'info');

    // Step 1: Test database connection
    outputMessage('Step 1', 'Testing database connection...', 'info');
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }

    $testResult = $db->testConnection();
    if ($testResult['success']) {
        outputMessage(
            'Connection Successful',
            "Connected to database: {$testResult['database']} on {$testResult['host']}",
            'success'
        );
    } else {
        throw new Exception($testResult['message']);
    }

    // Step 2: Check if tables already exist
    outputMessage('Step 2', 'Checking existing tables...', 'info');
    $existingTables = [];
    $tablesToCheck = ['users', 'events', 'tickets', 'rsvps', 'reviews', 'comments'];

    foreach ($tablesToCheck as $table) {
        if ($db->tableExists($table)) {
            $existingTables[] = $table;
        }
    }

    if (!empty($existingTables)) {
        outputMessage(
            'Existing Tables Found',
            'The following tables already exist: ' . implode(', ', $existingTables) . '. They will be dropped and recreated.',
            'warning'
        );
    } else {
        outputMessage('Clean Installation', 'No existing tables found. Proceeding with fresh installation.', 'success');
    }

    // Step 3: Execute schema file
    outputMessage('Step 3', 'Creating database schema...', 'info');
    $schemaFile = __DIR__ . '/schema.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found at: $schemaFile");
    }

    $result = executeSQLFile($conn, $schemaFile);

    if (!empty($result['errors'])) {
        outputMessage(
            'Schema Creation Warnings',
            count($result['errors']) . ' warnings occurred during schema creation. Check logs for details.',
            'warning'
        );
        foreach ($result['errors'] as $error) {
            error_log("Schema warning: $error");
        }
    }

    outputMessage(
        'Schema Created',
        "Successfully executed {$result['executed']} SQL statements",
        'success'
    );

    // Step 4: Verify table creation
    outputMessage('Step 4', 'Verifying table creation...', 'info');
    $createdTables = [];
    $requiredTables = [
        'users', 'categories', 'events', 'tags', 'event_tags',
        'tickets', 'ticket_purchases', 'rsvps', 'reviews', 'comments', 'bookmarks'
    ];

    foreach ($requiredTables as $table) {
        if ($db->tableExists($table)) {
            $createdTables[] = $table;
        }
    }

    if (count($createdTables) === count($requiredTables)) {
        outputMessage(
            'All Tables Created',
            'Successfully created ' . count($createdTables) . ' tables',
            'success'
        );
    } else {
        $missing = array_diff($requiredTables, $createdTables);
        throw new Exception('Missing tables: ' . implode(', ', $missing));
    }

    // Step 5: Insert default categories
    outputMessage('Step 5', 'Inserting default categories...', 'info');

    $defaultCategories = [
        ['Concert', 'concert', 'Live music performances and concerts'],
        ['Festival', 'festival', 'Music and cultural festivals'],
        ['Conference', 'conference', 'Professional conferences and seminars'],
        ['Workshop', 'workshop', 'Educational workshops and training'],
        ['Sports', 'sports', 'Sports events and competitions'],
        ['Fashion', 'fashion', 'Fashion shows and events'],
        ['Food & Drinks', 'food', 'Food festivals and culinary events'],
        ['Networking', 'networking', 'Professional networking events'],
        ['Party', 'party', 'Social parties and celebrations'],
        ['Other', 'other', 'Other types of events']
    ];

    $insertedCategories = 0;
    foreach ($defaultCategories as $category) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $category[0], $category[1], $category[2]);
        if ($stmt->execute()) {
            $insertedCategories++;
        }
        $stmt->close();
    }

    outputMessage(
        'Categories Added',
        "Inserted $insertedCategories default categories",
        'success'
    );

    // Step 6: Create admin/manager user (optional)
    outputMessage('Step 6', 'Creating default manager account...', 'info');

    $defaultEmail = 'manager@bonten.com';
    $defaultPassword = password_hash('manager123', PASSWORD_DEFAULT);
    $defaultUsername = 'admin';

    $stmt = $conn->prepare("INSERT IGNORE INTO users (email, password_hash, username, user_type, full_name, is_active, email_verified) VALUES (?, ?, ?, 'manager', 'Default Manager', 1, 1)");
    $stmt->bind_param('sss', $defaultEmail, $defaultPassword, $defaultUsername);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        outputMessage(
            'Manager Account Created',
            "Email: $defaultEmail | Password: manager123 (Please change this after first login!)",
            'success'
        );
    } else {
        outputMessage(
            'Manager Account',
            'Default manager account already exists or could not be created',
            'warning'
        );
    }
    $stmt->close();

    // Final success message
    if (!$isCLI) {
        echo '<div class="summary">
                <h2> Setup Complete!</h2>
                <p>Your database has been successfully set up and is ready to use.</p>
                <p><strong>Tables Created:</strong> ' . count($createdTables) . '</p>
                <p><strong>Categories Added:</strong> ' . $insertedCategories . '</p>
                <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3);">
                    Default Manager Login:<br>
                    Email: <span class="code">manager@bonten.com</span><br>
                    Password: <span class="code">manager123</span>
                </p>
                <a href="../views/index.html" class="btn">Go to Application</a>
              </div>';
    } else {
        echo "\n Setup Complete!\n";
        echo "Tables Created: " . count($createdTables) . "\n";
        echo "Categories Added: $insertedCategories\n";
        echo "\nDefault Manager Login:\n";
        echo "Email: manager@bonten.com\n";
        echo "Password: manager123\n";
    }

} catch (Exception $e) {
    outputMessage('Setup Failed', $e->getMessage(), 'error');

    if (!$isCLI) {
        echo '<div class="summary" style="background: #f44336;">
                <h2>Setup Failed</h2>
                <p>An error occurred during setup. Please check the error message above and try again.</p>
              </div>';
    }

    error_log("Database setup error: " . $e->getMessage());
    exit(1);
}

// HTML Footer
if (!$isCLI) {
    echo '      </div>
        </div>
    </body>
    </html>';
}
?>