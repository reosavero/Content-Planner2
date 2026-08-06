<?php





class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $searchable = [];
    protected static bool $softDelete = true;
    protected static bool $timestamps = true;

    


    public static function find(int $id): ?array
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $softDelete = static::$softDelete ? 'AND deleted_at IS NULL' : '';
        
        return Database::fetch(
            "SELECT * FROM {$table} WHERE {$pk} = ? {$softDelete}",
            [$id]
        );
    }

    


    public static function all(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $table = static::$table;
        $softDelete = static::$softDelete ? 'WHERE deleted_at IS NULL' : '';
        
        return Database::fetchAll(
            "SELECT * FROM {$table} {$softDelete} ORDER BY {$orderBy} {$direction}"
        );
    }

    


    public static function paginate(int $perPage = 20, string $where = '1=1', array $params = []): array
    {
        $table = static::$table;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $softDelete = static::$softDelete ? 'AND deleted_at IS NULL' : '';
        
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM {$table} WHERE {$where} {$softDelete}",
            $params
        );
        
        $data = Database::fetchAll(
            "SELECT * FROM {$table} WHERE {$where} {$softDelete} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $count,
            'total_pages' => max(1, ceil($count / $perPage)),
        ];
    }

    


    public static function create(array $data): int
    {
        $table = static::$table;
        
        
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }
        
        if (static::$timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        return Database::insert(
            "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    


    public static function update(int $id, array $data): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        
        
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }
        
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        
        return Database::execute(
            "UPDATE {$table} SET {$sets} WHERE {$pk} = ?",
            array_merge(array_values($data), [$id])
        );
    }

    


    public static function delete(int $id): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        
        if (static::$softDelete) {
            return Database::execute(
                "UPDATE {$table} SET deleted_at = NOW(), updated_at = NOW() WHERE {$pk} = ?",
                [$id]
            );
        }
        
        return Database::execute(
            "DELETE FROM {$table} WHERE {$pk} = ?",
            [$id]
        );
    }

    


    public static function forceDelete(int $id): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        
        return Database::execute(
            "DELETE FROM {$table} WHERE {$pk} = ?",
            [$id]
        );
    }

    


    public static function count(string $where = '1=1', array $params = []): int
    {
        $table = static::$table;
        $softDelete = static::$softDelete ? 'AND deleted_at IS NULL' : '';
        
        return (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM {$table} WHERE {$where} {$softDelete}",
            $params
        );
    }

    


    public static function search(string $query, int $limit = 20): array
    {
        $table = static::$table;
        $softDelete = static::$softDelete ? 'AND deleted_at IS NULL' : '';
        
        if (empty(static::$searchable)) {
            return [];
        }
        
        $conditions = [];
        $params = [];
        foreach (static::$searchable as $field) {
            $conditions[] = "{$field} LIKE ?";
            $params[] = "%{$query}%";
        }
        
        $where = implode(' OR ', $conditions);
        
        return Database::fetchAll(
            "SELECT * FROM {$table} WHERE ({$where}) {$softDelete} ORDER BY created_at DESC LIMIT {$limit}",
            $params
        );
    }

    


    public static function latest(int $limit = 10): array
    {
        $table = static::$table;
        $softDelete = static::$softDelete ? 'WHERE deleted_at IS NULL' : '';
        
        return Database::fetchAll(
            "SELECT * FROM {$table} {$softDelete} ORDER BY created_at DESC LIMIT {$limit}"
        );
    }

    


    public static function beginTransaction(): void
    {
        Database::beginTransaction();
    }

    


    public static function commit(): void
    {
        Database::commit();
    }

    


    public static function rollback(): void
    {
        Database::rollback();
    }

    


    protected static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }
}
