<?php
    class Database{
    private static ?Database $instance  = null;
    private ?PDO $connection = null;

    private string $host = 'localhost';
    private string $db_name = 'shared_lists_db';
    private string $username = 'root';
    private string $password = '';

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES      => false,
            ];
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

        }catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: '. $e->getMessage()]);
            exit;
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
    }

?>