<?php
/**
 * app/Models/Contact.php
 * ----------------------
 * ข้อความจากฟอร์มติดต่อ พร้อมสถานะอ่าน/ยังไม่อ่าน และการยินยอม PDPA
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Contact
{
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO contacts (name, email, phone, subject, message, consent_at, ip_address)
             VALUES (:name, :email, :phone, :subject, :message, :consent_at, :ip_address)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM contacts ORDER BY is_read ASC, created_at DESC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function markRead(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE contacts SET is_read = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function unreadCount(): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM contacts WHERE is_read = 0')->fetchColumn();
    }
}
