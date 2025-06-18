<?php
class AdminController {
    private $model;
    private $maxLoginAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds

    public function __construct($model) {
        $this->model = $model;
    }

    public function login($username, $password) {
        // Check if IP is blocked
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if ($this->isIPBlocked($ipAddress)) {
            return false;
        }
        
        // In a real system, get admin from database
        // For now, using the hardcoded credentials but with better security
        if ($username === "admin" && password_verify($password, $this->getAdminPasswordHash())) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_last_activity'] = time();
            
            // Reset failed attempts for this IP
            $this->resetFailedAttempts($ipAddress);
            
            // Log successful login
            $this->logLoginAttempt($username, true);
            
            return true;
        }
        
        // Log failed attempt
        $this->logLoginAttempt($username, false);
        
        // Increment failed attempts
        $this->incrementFailedAttempts($ipAddress);
        
        return false;
    }
    
    private function getAdminPasswordHash() {
        // In production, this would come from a database
        // This is the hash for "admin123" using PASSWORD_DEFAULT
        return '$2y$10$rBDqrKqQHKkFxQUVJ6BgXu.T.yZcHRYX0SuQP5wjBUWNbxNNHvjZW';
    }
    
    private function isIPBlocked($ip) {
        // Check if IP has too many failed attempts
        $failedAttempts = $this->getFailedAttempts($ip);
        if ($failedAttempts >= $this->maxLoginAttempts) {
            $lastAttemptTime = $this->getLastAttemptTime($ip);
            if (time() - $lastAttemptTime < $this->lockoutTime) {
                return true;
            } else {
                // Reset if lockout time has passed
                $this->resetFailedAttempts($ip);
            }
        }
        return false;
    }
    
    private function getFailedAttempts($ip) {
        // In production, this would check a database
        // For now, using session as a simple storage
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = [];
        }
        return isset($_SESSION['failed_attempts'][$ip]) ? $_SESSION['failed_attempts'][$ip] : 0;
    }
    
    private function incrementFailedAttempts($ip) {
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = [];
        }
        if (!isset($_SESSION['failed_attempts'][$ip])) {
            $_SESSION['failed_attempts'][$ip] = 0;
        }
        $_SESSION['failed_attempts'][$ip]++;
        $_SESSION['last_attempt_time'][$ip] = time();
    }
    
    private function resetFailedAttempts($ip) {
        if (isset($_SESSION['failed_attempts'][$ip])) {
            $_SESSION['failed_attempts'][$ip] = 0;
        }
    }
    
    private function getLastAttemptTime($ip) {
        if (!isset($_SESSION['last_attempt_time']) || !isset($_SESSION['last_attempt_time'][$ip])) {
            return 0;
        }
        return $_SESSION['last_attempt_time'][$ip];
    }
    
    private function logLoginAttempt($username, $success) {
        // In production, log to database or file
        $logMessage = date('Y-m-d H:i:s') . " - Admin login attempt: " . 
                        "Username: " . $username . ", " .
                        "IP: " . $_SERVER['REMOTE_ADDR'] . ", " .
                        "Status: " . ($success ? "Success" : "Failed");
        
        error_log($logMessage);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        // Check if logged in
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }
        
        // Check for session timeout (30 minutes)
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 1800)) {
            $this->logout();
            return false;
        }
        
        // Update last activity time
        $_SESSION['admin_last_activity'] = time();
        
        return true;
    }
}
