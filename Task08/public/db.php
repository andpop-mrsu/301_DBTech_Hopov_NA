<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dbPath = dirname(__DIR__) . '/data/clinic.db';
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->initializeDatabase();
            
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    private function initializeDatabase() {
    // Проверяем, есть ли таблица doctors
    $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='doctors'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        $sqlFile = __DIR__ . '/db_init.sql';
        
        if (file_exists($sqlFile)) {
            $sqlScript = file_get_contents($sqlFile);
            
            if ($sqlScript) {
                $commands = $this->splitSQLCommands($sqlScript);
                foreach ($commands as $command) {
                    if (!empty(trim($command))) {
                        try {
                            $this->pdo->exec($command);
                        } catch (PDOException $e) {
                            // Пропускаем ошибки DELETE для пустых таблиц
                            if (strpos($e->getMessage(), 'no such table') === false) {
                                throw $e; // Перебрасываем другие ошибки
                            }
                        }
                    }
                }
                
                echo "База данных успешно инициализирована из db_init.sql";
            }
        } else {
            echo "Внимание: Файл db_init.sql не найден!";
        }
    }
}

private function splitSQLCommands($sql) {
    // Удаляем комментарии
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Разбиваем по точке с запятой
    $commands = explode(';', $sql);
    
    // Фильтруем пустые команды
    $commands = array_filter($commands, function($cmd) {
        return !empty(trim($cmd));
    });
    
    // Добавляем точку с запятой обратно к каждой команде
    $commands = array_map(function($cmd) {
        return trim($cmd) . ';';
    }, $commands);
    
    return $commands;
}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

function getPDO() {
    return Database::getInstance();
}
?>