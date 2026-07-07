<?php
/**
 * app/Models/Banner.php
 * ---------------------
 * แบนเนอร์สไลด์หน้าแรก
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Banner
{
    public static function activeOrdered(): array
    {
        return Database::connection()
            ->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order, id')
            ->fetchAll();
    }

    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM banners ORDER BY sort_order, id')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM banners WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO banners (title, image_path, link_url, sort_order, is_active)
             VALUES (:title, :image_path, :link_url, :sort_order, :is_active)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = Database::connection()->prepare(
            'UPDATE banners SET title = :title, image_path = :image_path, link_url = :link_url,
                sort_order = :sort_order, is_active = :is_active WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM banners WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
