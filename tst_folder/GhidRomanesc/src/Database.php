<?php
class Database {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            self::$instance = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // WAL mode — mai rapid, suportă citiri simultane
            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');
            self::$instance->exec('PRAGMA encoding="UTF-8"');
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function fetchColumn(string $sql, array $params = []): mixed {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function insert(string $table, array $data): int {
        $cols = implode(', ', array_map(fn($k) => "\"$k\"", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO \"$table\" ($cols) VALUES ($placeholders)", array_values($data));
        return (int)self::get()->lastInsertId();
    }

    // Tabele care au coloana updated_at
    private static array $withUpdatedAt = [
        'users', 'articles', 'categories', 'editorial_calendar', 'settings',
    ];

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        // Auto-actualizează updated_at doar pentru tabelele care au această coloană
        if (!isset($data['updated_at']) && in_array($table, self::$withUpdatedAt)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $set = implode(', ', array_map(fn($k) => "\"$k\" = ?", array_keys($data)));
        $stmt = self::query("UPDATE \"$table\" SET $set WHERE $where", [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        return self::query("DELETE FROM \"$table\" WHERE $where", $params)->rowCount();
    }
}
