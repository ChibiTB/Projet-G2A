<?php
// Include configuration
require_once __DIR__ . '/../config/config.php';

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logMessage = date('Y-m-d H:i:s') . " - Error: [$errno] $errstr in $errfile on line $errline";
    
    // Log to file
    error_log($logMessage, 3, __DIR__ . '/../logs/error.log');
    
    // For development environment only
    if (defined('DEV_MODE') && DEV_MODE === true) {
        echo "<div style='color:red; background-color:#ffeeee; padding:10px; margin:10px; border:1px solid #ffaaaa;'>";
        echo "<h3>Error Occurred</h3>";
        echo "<p><strong>Type:</strong> $errno</p>";
        echo "<p><strong>Message:</strong> $errstr</p>";
        echo "<p><strong>File:</strong> $errfile</p>";
        echo "<p><strong>Line:</strong> $errline</p>";
        echo "</div>";
    } else {
        // In production, show a user-friendly message
        if ($errno == E_USER_ERROR) {
            echo "<div style='text-align:center; padding:20px;'>";
            echo "<h2>Oops! Something went wrong.</h2>";
            echo "<p>We're sorry, but there was an error processing your request.</p>";
            echo "<p>Please try again later or contact support if the problem persists.</p>";
            echo "</div>";
        }
    }
    
    // Don't execute PHP's internal error handler
    return true;
}

// Set custom error handler
set_error_handler("customErrorHandler");

// Custom exception handler
function customExceptionHandler($exception) {
    $logMessage = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . " on line " . $exception->getLine() . 
                    "\nStack trace: " . $exception->getTraceAsString();
    
    // Log to file
    error_log($logMessage, 3, __DIR__ . '/../logs/exceptions.log');
    
    // For development environment only
    if (defined('DEV_MODE') && DEV_MODE === true) {
        echo "<div style='color:red; background-color:#ffeeee; padding:10px; margin:10px; border:1px solid #ffaaaa;'>";
        echo "<h3>Exception Occurred</h3>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<p><strong>Stack Trace:</strong> <pre>" . $exception->getTraceAsString() . "</pre></p>";
        echo "</div>";
    } else {
        // In production, show a user-friendly message
        echo "<div style='text-align:center; padding:20px;'>";
        echo "<h2>Oops! Something went wrong.</h2>";
        echo "<p>We're sorry, but there was an error processing your request.</p>";
        echo "<p>Please try again later or contact support if the problem persists.</p>";
        echo "</div>";
    }
}

// Set custom exception handler
set_exception_handler("customExceptionHandler");

// Function to log application events
function logEvent($type, $message, $data = []) {
    $logMessage = date('Y-m-d H:i:s') . " - $type: $message";
    
    if (!empty($data)) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    
    error_log($logMessage, 3, __DIR__ . '/../logs/application.log');
}
