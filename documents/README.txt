BONTEN - Event Management System

A comprehensive web-based event management platform built with PHP,
MySQL, and JavaScript. BONTEN allows users to discover, RSVP, and
purchase tickets for events, while providing event managers with tools
to create, manage, and track their events.

Table of Contents

-   Features
-   Prerequisites
-   Installation
-   Database Setup
-   Configuration
-   Running the Server
-   Testing
-   Project Structure
-   Troubleshooting

Features

User Features

-   User registration and authentication
-   Event discovery and exploration
-   RSVP functionality
-   Ticket purchasing with Paystack payment integration
-   Event history tracking
-   Review and comment system
-   Profile management

Manager Features

-   Event creation and management
-   Dashboard with analytics (revenue, tickets sold, ratings)
-   Event history and statistics
-   Profile and payment settings

Security Features

-   CSRF protection
-   Rate limiting for login attempts
-   Password hashing (bcrypt)
-   Input sanitization and validation
-   Session management
-   SQL injection prevention (prepared statements)

Prerequisites

Before setting up BONTEN, ensure you have the following installed:

1.  PHP 7.4 or higher

    -   Required extensions: mysqli, curl, fileinfo, mbstring, json
    -   Check PHP version: php -v

2.  MySQL 5.7 or higher / MariaDB 10.2 or higher

    -   Check MySQL version: mysql --version

3.  Web Server

    -   Apache (with mod_rewrite enabled) OR
    -   Nginx OR
    -   PHP Built-in Server (for development)

4.  Composer (optional, if using package management)

5.  Paystack Account (for payment functionality)

    -   Sign up at https://paystack.com
    -   Get your test API keys from the dashboard

Installation

Step 1: Clone or Download the Project

    # If using git
    git clone <repository-url>
    cd Project-BONTEN

    # Or extract the project ZIP file to your desired location

Step 2: Set Up Web Server Root

Option A: Using Apache/Nginx

1.  Point your web server document root to the Project-BONTEN directory

2.  Or create a symbolic link:

        # Example for Apache (adjust path as needed)
        sudo ln -s /path/to/Project-BONTEN /var/www/html/bonten

Option B: Using PHP Built-in Server (Development)

Navigate to the project directory:

    cd /path/to/Project-BONTEN

Step 3: Set Permissions

Ensure the following directories are writable:

    # Create logs directory if it doesn't exist
    mkdir -p logs
    mkdir -p public/assets/events

    # Set permissions (adjust based on your server setup)
    chmod 755 logs
    chmod 755 public/assets
    chmod 755 public/assets/events

    # For image uploads
    chmod 777 public/assets/events  # Use more restrictive permissions in production

Database Setup

Step 1: Create MySQL Database

1.  Log in to MySQL:

        mysql -u root -p

2.  Create the database:

        CREATE DATABASE webtech_2025A_marc_sossou CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    Or use your preferred database name.

3.  Create a database user (recommended):

        CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
        GRANT ALL PRIVILEGES ON webtech_2025A_marc_sossou.* TO 'your_username'@'localhost';
        FLUSH PRIVILEGES;
        EXIT;

Step 2: Import Database Schema

1.  Import the database schema:

        mysql -u your_username -p webtech_2025A_marc_sossou < database/database_schema.sql

    Or from MySQL prompt:

        USE webtech_2025A_marc_sossou;
        SOURCE /path/to/Project-BONTEN/database/database_schema.sql;

Step 3: Populate Sample Data (Optional)

Import sample data for testing:

    mysql -u your_username -p webtech_2025A_marc_sossou < database/populate_events.sql

This will create:

-   1 Manager account (eldad.opare@bonten.com)
-   5 Sample users (user1@example.com to user5@example.com)
-   22 Sample events
-   Sample tickets, RSVPs, reviews, and comments

Default Password for Test Accounts: password (hashed with bcrypt in the
database)

Configuration

Step 1: Database Configuration

Edit config/Database.php and update the database credentials:

    private $host = "localhost";          // Database host
    private $db_name = "webtech_2025A_marc_sossou";  // Database name
    private $username = "your_username";   // Database username
    private $password = "your_password";   // Database password

Step 2: Paystack Configuration

Edit src/Controllers/initialize_transaction.php and update Paystack
keys:

    $secret_key = "sk_test_your_secret_key_here";  // Your Paystack Secret Key
    $public_key = "pk_test_your_public_key_here";  // Your Paystack Public Key

To get your Paystack keys:

1.  Log in to Paystack Dashboard
2.  Go to Settings → API Keys & Webhooks
3.  Copy your Test Secret Key and Test Public Key
4.  For production, use Live keys

Step 3: Session Configuration (Optional)

Edit config/security.php if you need to customize session settings:

    ini_set('session.cookie_secure', 1);  // Set to 1 for HTTPS only

Step 4: File Upload Configuration (Optional)

Check PHP upload settings in php.ini:

    upload_max_filesize = 5M
    post_max_size = 5M
    max_file_uploads = 20

Running the Server

Option 1: PHP Built-in Server (Development)

Run from the project root directory:

    # For Unix/Linux/Mac
    php -S localhost:8000 -t .

    # For Windows
    php -S localhost:8000 -t .

Then access the application at: http://localhost:8000/views/index.php

Option 2: Apache

1.  Enable mod_rewrite (if using .htaccess):

        sudo a2enmod rewrite
        sudo systemctl restart apache2

2.  Configure virtual host (example):

        <VirtualHost *:80>
            ServerName bonten.local
            DocumentRoot /path/to/Project-BONTEN

            <Directory /path/to/Project-BONTEN>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
            </Directory>
        </VirtualHost>

3.  Add to /etc/hosts (Linux/Mac) or
    C:\Windows\System32\drivers\etc\hosts (Windows):

        127.0.0.1    bonten.local

4.  Restart Apache:

        sudo systemctl restart apache2

5.  Access: http://bonten.local/views/index.php

Option 3: Nginx

Example Nginx configuration:

    server {
        listen 80;
        server_name bonten.local;
        root /path/to/Project-BONTEN;
        index index.php;

        location / {
            try_files $uri $uri/ /views/index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

Accessing the Application

1.  Home/Login Page: http://localhost:8000/views/index.php
2.  User Homepage: http://localhost:8000/views/user_homepage.php
    (requires login)
3.  Manager Dashboard: http://localhost:8000/views/manager_dashboard.php
    (requires manager login)

Testing

Test Accounts

After importing populate_events.sql, you can use these test accounts:

Manager Account:

-   Email: eldad.opare@bonten.com
-   Password: 23May@2005

Regular User Accounts:

-   Email: user1@example.com through user5@example.com
-   Password: password (for all test users)

Testing Features

1.  User Registration:

    -   Visit views/index.php
    -   Click “Sign Up”
    -   Create a new account (password must meet criteria: 8+ chars,
        uppercase, lowercase, number)

2.  Event Creation (Manager):

    -   Log in as manager
    -   Go to “Create Event”
    -   Fill out the multi-step form
    -   Upload an event image
    -   Create tickets (free or paid)

3.  Payment Testing:

    -   Use Paystack test cards:
        -   Card: 4084 0840 8408 4081
        -   CVV: 408
        -   Expiry: Any future date
        -   PIN: 0000 (when prompted)

4.  Event Exploration:

    -   Browse events on the explore page
    -   Filter by category
    -   View event details
    -   RSVP or purchase tickets

Project Structure

    Project-BONTEN/
    ├── api/                      # API endpoints (REST)
    │   ├── cancel_rsvp.php
    │   ├── comments.php
    │   ├── reviews.php
    │   └── ...
    ├── config/                   # Configuration files
    │   ├── Database.php          # Database connection
    │   ├── security.php          # Security functions
    │   └── image_helpers.php
    ├── database/                 # Database scripts
    │   ├── database_schema.sql   # Database structure
    │   └── populate_events.sql   # Sample data
    ├── public/                   # Public assets
    │   ├── assets/              # Images, icons
    │   ├── css/                 # Stylesheets
    │   └── js/                  # JavaScript files
    ├── src/                     # Source code
    │   └── Controllers/         # Server-side controllers
    │       ├── handle_create_event.php
    │       ├── initialize_transaction.php
    │       └── verify_transaction.php
    ├── views/                   # View templates (PHP)
    │   ├── index.php           # Login/Signup
    │   ├── user_homepage.php
    │   ├── manager_dashboard.php
    │   └── ...
    └── README.md               # This file

Troubleshooting

Database Connection Issues

Error: “Connection failed: Access denied”

-   Solution: Check database credentials in config/Database.php
-   Verify database user has proper permissions

Error: “Unknown database”

-   Solution: Ensure database is created and name matches in
    Database.php

Session Issues

Error: “Headers already sent”

-   Solution: Ensure config/security.php is included before any output
-   Check for whitespace before <?php tags

Error: “Session not working”

-   Solution: Check session.save_path in php.ini
-   Ensure logs/ directory exists and is writable

File Upload Issues

Error: “Failed to upload image”

-   Solution: Check directory permissions (public/assets/events)
-   Verify PHP upload limits in php.ini
-   Ensure public/assets/events directory exists

Payment Issues

Error: “Payment system not loaded”

-   Solution: Ensure Paystack script loads before event_modals.js
-   Check internet connection for Paystack CDN
-   Verify Paystack keys are correct

404 Errors

Error: “Page not found”

-   Solution: Ensure you’re accessing files from views/ directory
-   Check web server document root configuration
-   Verify .htaccess is configured (if using Apache)

Permission Errors

Error: “Permission denied”

-   Solution:

        chmod 755 logs
        chmod 755 public/assets
        chmod 777 public/assets/events  # Development only

Security Notes

1.  Production Deployment:

    -   Change default passwords
    -   Use environment variables for sensitive data
    -   Enable HTTPS
    -   Set session.cookie_secure = 1 in security.php
    -   Use strong database passwords
    -   Restrict file upload permissions

2.  Database Security:

    -   Use prepared statements (already implemented)
    -   Limit database user privileges
    -   Regular backups

3.  Paystack Keys:

    -   Never commit keys to version control
    -   Use test keys for development
    -   Use live keys only in production

Additional Resources

-   PHP Documentation: https://www.php.net/docs.php
-   MySQL Documentation: https://dev.mysql.com/doc/
-   Paystack API: https://paystack.com/docs/api

Support

For issues or questions:

1.  Check the Troubleshooting section
2.  Review error logs in logs/security.log
3.  Check PHP error logs
4.  Verify all prerequisites are installed

License

[Specify your license here]

------------------------------------------------------------------------

Last Updated: 2025

Version: 1.0.0
