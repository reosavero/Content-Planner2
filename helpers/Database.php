<?php





class Database
{
    private static ?PDO $instance = null;
    private static int $queryCount = 0;
    private static array $queryLog = [];

    


    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('Database Connection Error: ' . $e->getMessage());
                }
                die('Maaf, terjadi kesalahan koneksi database. Silakan coba lagi nanti.');
            }
        }
        return self::$instance;
    }

    


    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        self::logQuery($sql, $params);
        return $stmt;
    }

    


    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    


    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    


    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    


    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    


    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::getInstance()->lastInsertId();
    }

    


    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    


    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    


    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    


    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    


    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    


    private static function logQuery(string $sql, array $params): void
    {
        self::$queryCount++;
        if (APP_ENV === 'development') {
            self::$queryLog[] = [
                'sql' => $sql,
                'params' => $params,
                'time' => microtime(true),
            ];
        }
    }

    


    public static function escapeLike(string $value): string
    {
        return str_replace(['%', '_'], ['\%', '\_'], $value);
    }
}
