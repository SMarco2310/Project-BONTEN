# BONTEN Database Setup Guide

This directory contains all the database files needed to set up the BONTEN Event Management System database.

## Files Overview

1. **schema.sql** - Complete database schema with all tables, views, and triggers
2. **setup.php** - Automated installation script with web interface
3. **seed_data.sql** - Sample data for testing and development
4. **README.md** - This file

## Database Structure

### Core Tables

- **users** - User accounts (both regular users and event managers)
- **categories** - Event categories (Concert, Festival, Workshop, etc.)
- **events** - Event information and settings
- **tags** - Custom tags for events
- **event_tags** - Many-to-many relationship between events and tags
- **tickets** - Ticket types for paid events
- **ticket_purchases** - Purchase records and transactions
- **rsvps** - Event registrations and attendance tracking
- **reviews** - User reviews for completed events
- **comments** - Comments on events
- **bookmarks** - User-saved events

### Views

- **event_statistics** - Aggregated statistics per event
- **manager_dashboard_stats** - Dashboard metrics for event managers

## Installation Methods

### Method 1: Automated Setup (Recommended)

1. **Access the setup script via browser:**
   ```
   http://localhost/database/setup.php
   ```
   OR
   ```
   http://yourdomain.com/database/setup.php
   ```

2. The script will automatically:
   - Test database connection
   - Create all tables, views, and triggers
   - Insert default categories
   - Create a default manager account
   - Display a beautiful setup completion page

3. **Default manager credentials:**
   - Email: `manager@bonten.com`
   - Password: `manager123`
   - ⚠️ **IMPORTANT:** Change this password after first login!

### Method 2: Manual Setup via MySQL CLI

1. **Connect to MySQL:**
   ```bash
   mysql -h 169.239.251.102 -u marc.sossou -p webtech_2025A_marc_sossou
   ```

2. **Run the schema file:**
   ```sql
   source /path/to/database/schema.sql
   ```

3. **Insert default categories (optional):**
   ```sql
   INSERT INTO categories (name, slug, description) VALUES
   ('Concert', 'concert', 'Live music performances and concerts'),
   ('Festival', 'festival', 'Music and cultural festivals'),
   ('Conference', 'conference', 'Professional conferences and seminars'),
   -- ... add other categories as needed
   ```

### Method 3: Using phpMyAdmin

1. Log into phpMyAdmin
2. Select database: `webtech_2025A_marc_sossou`
3. Click on "Import" tab
4. Choose file: `schema.sql`
5. Click "Go"
6. Optionally import `seed_data.sql` for test data

## Adding Sample Data

After setting up the schema, you can populate the database with sample data for testing:

### Via Browser
```
http://localhost/database/seed_data.php
```

### Via MySQL CLI
```bash
mysql -h 169.239.251.102 -u marc.sossou -p webtech_2025A_marc_sossou < seed_data.sql
```

### Sample Data Includes:
- 5 regular users
- 3 event managers
- 10 event categories
- 9 events (active, completed, cancelled, and draft)
- Multiple ticket types
- RSVPs and attendance records
- User reviews and comments
- Bookmarks
- Sample ticket purchases

### Sample User Accounts (all with password: `password123`)
- john.doe@example.com (Regular User)
- jane.smith@example.com (Regular User)
- kofi.mensah@example.com (Regular User)
- ama.serwaa@example.com (Regular User)
- kwame.asante@example.com (Regular User)

### Sample Manager Accounts (all with password: `manager123`)
- jerome.adedze@bonten.com
- sarah.johnson@bonten.com
- david.osei@bonten.com

## Database Configuration

The database connection settings are stored in:
```
/config/Database.php
```

Current configuration:
- Host: `169.239.251.102`
- Port: `3306`
- Database: `webtech_2025A_marc_sossou`
- Username: `marc.sossou`
- Password: `Marco2310#`

## Verifying Installation

### Check tables were created:
```sql
SHOW TABLES;
```

You should see 11 tables:
- bookmarks
- categories
- comments
- event_tags
- events
- reviews
- rsvps
- tags
- ticket_purchases
- tickets
- users

### Check views:
```sql
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';
```

You should see:
- event_statistics
- manager_dashboard_stats

### Test with sample queries:
```sql
-- Count events by status
SELECT status, COUNT(*) as count FROM events GROUP BY status;

-- View event statistics
SELECT * FROM event_statistics;

-- Check manager dashboard
SELECT * FROM manager_dashboard_stats;
```

## Troubleshooting

### Connection Failed
- Verify database credentials in `/config/Database.php`
- Check if MySQL server is running
- Verify network connectivity to database host
- Ensure database exists: `webtech_2025A_marc_sossou`

### Permission Denied
- Verify user has CREATE, INSERT, SELECT, UPDATE, DELETE privileges
- Check if user can create triggers and views

### Tables Already Exist
The schema file includes `DROP TABLE IF EXISTS` statements, so running it multiple times is safe. It will recreate all tables from scratch.

⚠️ **WARNING:** This will delete all existing data!

### Setup Script Shows Errors
1. Check PHP error logs
2. Verify PHP has mysqli extension enabled
3. Ensure PHP version is 7.4 or higher
4. Check file permissions on setup.php

## Database Maintenance

### Backup Database
```bash
mysqldump -h 169.239.251.102 -u marc.sossou -p webtech_2025A_marc_sossou > backup.sql
```

### Restore Database
```bash
mysql -h 169.239.251.102 -u marc.sossou -p webtech_2025A_marc_sossou < backup.sql
```

### Clear All Data (keep structure)
```sql
TRUNCATE TABLE bookmarks;
TRUNCATE TABLE comments;
TRUNCATE TABLE reviews;
TRUNCATE TABLE rsvps;
TRUNCATE TABLE ticket_purchases;
TRUNCATE TABLE tickets;
TRUNCATE TABLE event_tags;
TRUNCATE TABLE tags;
TRUNCATE TABLE events;
DELETE FROM users WHERE user_id > 1; -- Keep the default manager
```

## Security Recommendations

1. **Change default passwords immediately**
2. **Never commit database credentials to version control**
3. **Use environment variables for sensitive configuration**
4. **Implement prepared statements for all queries** (already done in Database.php)
5. **Enable SSL for database connections in production**
6. **Regularly backup your database**
7. **Restrict database user permissions to only what's needed**

## Next Steps

After successful database setup:

1. ✅ Verify all tables exist
2. ✅ Test database connection from your PHP application
3. ✅ Change default manager password
4. ✅ (Optional) Load sample data for testing
5. ✅ Begin converting HTML pages to PHP
6. ✅ Implement user authentication
7. ✅ Build API endpoints for event management

## Support

For issues or questions:
- Check the error logs in your PHP error log file
- Review MySQL error logs
- Ensure all prerequisites are met
- Verify database credentials

## Schema Version

**Version:** 1.0
**Last Updated:** 2024-11-28
**Database Engine:** InnoDB
**Character Set:** utf8mb4 (full Unicode support)
