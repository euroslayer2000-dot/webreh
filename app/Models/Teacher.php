<?php
/**
 * app/Models/Teacher.php
 * ----------------------
 * บุคลากร: CRUD + ดึงแบบจัดกลุ่มตามกลุ่มสาระสำหรับหน้าเว็บ
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Teacher
{
    /** บุคลากรที่เปิดแสดง เรียงตาม sort_order */
    public static function allActive(): array
    {
        return Database::connection()
            ->query('SELECT * FROM teachers WHERE is_active = 1 ORDER BY sort_order, id')
            ->fetchAll();
    }

    /** จัดกลุ่มตามกลุ่มสาระ (department) เพื่อแสดงหน้าเว็บ */
    public static function groupedByDepartment(): array
    {
        $grouped = [];
        foreach (self::allActive() as $t) {
            $dept = $t['department'] ?: 'อื่น ๆ';
            $grouped[$dept][] = $t;
        }
        return $grouped;
    }

    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM teachers ORDER BY sort_order, id')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO teachers (name, position, department, photo, email, sort_order, is_active)
             VALUES (:name, :position, :department, :photo, :email, :sort_order, :is_active)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = Database::connection()->prepare(
            'UPDATE teachers SET name = :name, position = :position, department = :department,
                photo = :photo, email = :email, sort_order = :sort_order, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM teachers')->fetchColumn();
    }
}
