<?php
/**
 * app/Models/Download.php
 * -----------------------
 * เอกสารดาวน์โหลด + นับยอดดาวน์โหลด
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Download
{
    /** เอกสารทั้งหมดพร้อมชื่อหมวด (สำหรับหน้าเว็บและ admin) */
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT d.*, c.name AS category_name
             FROM downloads d LEFT JOIN categories c ON c.id = d.category_id
             ORDER BY d.created_at DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM downloads WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO downloads (category_id, title, file_path, file_ext, file_size)
             VALUES (:category_id, :title, :file_path, :file_ext, :file_size)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM downloads WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function incrementCount(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE downloads SET download_count = download_count + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM downloads')->fetchColumn();
    }
}
