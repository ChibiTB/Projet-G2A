<?php
// Application Configuration
// Set environment mode
define('DEV_MODE', true); // Set to false in production
define('APP_NAME', 'DBreeze');
define('APP_VERSION', '1.0.0');

// Database Configuration (use environment variables in production)
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'dbreeze_db');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
}

// Email Configuration
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
    define('SMTP_USER', $_ENV['SMTP_USER'] ?? 'your-email@gmail.com');
    define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? 'your-app-password');
    define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
}

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Application Settings
define('TIMEZONE', 'Europe/Paris');
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('CACHE_DURATION', 300); // 5 minutes

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting based on environment
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
