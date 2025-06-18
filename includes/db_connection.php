<?php
class DatabaseConnection {
    private static $instance = null;
    private $pdo;
    private $isTransactionActive = false;
    
    private function __construct() {
        require_once __DIR__ . '/../database.php';
        $this->pdo = getPDO();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function beginTransaction() {
        if (!$this->isTransactionActive) {
            $this->pdo->beginTransaction();
            $this->isTransactionActive = true;
            return true;
        }
        return false;
    }
    
    public function commit() {
        if ($this->isTransactionActive) {
            $this->pdo->commit();
            $this->isTransactionActive = false;
            return true;
        }
        return false;
    }
    
    public function rollback() {
        if ($this->isTransactionActive) {
            $this->pdo->rollBack();
            $this->isTransactionActive = false;
            return true;
        }
        return false;
    }
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function execute($stmt, $params = []) {
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            // Log error
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function query($sql) {
        try {
            return $this->pdo->query($sql);
        } catch (PDOException $e) {
            // Log error
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
}
