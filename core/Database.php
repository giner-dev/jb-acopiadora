<?php

class Database{
    private static $instance = null;
    private $connection = null;
    private $config = [];

    private function __construct(){
        $this->config = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => 'utf8mb4'
        ];

        $this->connect();
    }

    private function connect(){
        try{
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            if(env('APP_ENV') === 'development'){
                logMessage("Conexión a base de datos establecida correctamente", 'info');
            }

        } catch(PDOException $e){
            
            logMessage("Error de conexión a BD: " . $e->getMessage(), 'error');

            if (env('APP_ENV') === 'production') {
                die("Error: No se pudo conectar a la base de datos. Contacte al administrador.");
            } else {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logMessage("Error en query: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }

    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            logMessage("Error en queryOne: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            logMessage("Error en execute: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error al ejecutar: " . $e->getMessage());
        }
    }

    public function insert($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            logMessage("Error en insert: " . $e->getMessage() . " | SQL: $sql", 'error');
            throw new Exception("Error al insertar: " . $e->getMessage());
        }
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    public function close() {
        $this->connection = null;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton");
    }
}

function db() {
    return Database::getInstance()->getConnection();
}

function database() {
    return Database::getInstance();
}