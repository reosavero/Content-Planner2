<?php







class Notification
{
    












    public static function create(
        int $userId,
        string $title,
        string $message = '',
        string $type = 'info',
        ?string $link = null,
        ?string $icon = null,
        ?string $groupKey = null,
        ?string $groupLabel = null
    ): int|false {
        $allowedTypes = ['info', 'success', 'warning', 'error'];
        if (!in_array($type, $allowedTypes)) {
            $type = 'info';
        }

        return Database::insert(
            "INSERT INTO notifications (user_id, type, title, message, link, icon, is_read, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, 0, ?)",
            [
                $userId,
                $type,
                $title,
                $message,
                $link,
                $icon,
                date('Y-m-d H:i:s'),
            ]
        );
    }

    











    public static function createBulk(
        array $userIds,
        string $title,
        string $message = '',
        string $type = 'info',
        ?string $link = null,
        ?string $groupKey = null,
        ?string $groupLabel = null
    ): bool {
        if (empty($userIds)) return false;

        $now = date('Y-m-d H:i:s');
        $allowedTypes = ['info', 'success', 'warning', 'error'];
        if (!in_array($type, $allowedTypes)) {
            $type = 'info';
        }

        $values = [];
        $params = [];
        foreach ($userIds as $userId) {
            $values[] = "(?, ?, ?, ?, ?, ?, 0, ?)";
            $params[] = $userId;
            $params[] = $type;
            $params[] = $title;
            $params[] = $message;
            $params[] = $link;
            $params[] = null; 
            $params[] = $now;
        }

        $sql = "INSERT INTO notifications (user_id, type, title, message, link, icon, is_read, created_at) VALUES " 
             . implode(', ', $values);

        return Database::execute($sql, $params);
    }

    





    public static function cleanOldRead(int $days = 30): int
    {
        Database::execute(
            "DELETE FROM notifications WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        
        
        return 0;
    }
}
