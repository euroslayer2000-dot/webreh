<?php
/**
 * app/Models/Gallery.php
 * ----------------------
 * อัลบั้มภาพ + รูปในอัลบั้ม
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Gallery
{
    /** อัลบั้มทั้งหมด พร้อมจำนวนรูปในแต่ละอัลบั้ม */
    public static function allWithCount(): array
    {
        return Database::connection()->query(
            'SELECT g.*, (SELECT COUNT(*) FROM gallery_images gi WHERE gi.gallery_id = g.id) AS image_count
             FROM galleries g ORDER BY g.event_date DESC, g.id DESC'
        )->fetchAll();
    }

    public static function latest(int $limit = 3): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT g.*, (SELECT COUNT(*) FROM gallery_images gi WHERE gi.gallery_id = g.id) AS image_count
             FROM galleries g ORDER BY g.event_date DESC, g.id DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM galleries WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM galleries WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function images(int $galleryId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM gallery_images WHERE gallery_id = :id ORDER BY sort_order, id'
        );
        $stmt->execute(['id' => $galleryId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO galleries (title, slug, description, cover_image, event_date)
             VALUES (:title, :slug, :description, :cover_image, :event_date)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = Database::connection()->prepare(
            'UPDATE galleries SET title = :title, slug = :slug, description = :description,
                cover_image = :cover_image, event_date = :event_date WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        // gallery_images ถูกลบตาม ON DELETE CASCADE
        $stmt = Database::connection()->prepare('DELETE FROM galleries WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function addImage(int $galleryId, string $path, ?string $caption = null): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO gallery_images (gallery_id, image_path, caption) VALUES (:g, :p, :c)'
        );
        $stmt->execute(['g' => $galleryId, 'p' => $path, 'c' => $caption]);
    }

    public static function findImage(int $imageId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM gallery_images WHERE id = :id');
        $stmt->execute(['id' => $imageId]);
        return $stmt->fetch() ?: null;
    }

    public static function deleteImage(int $imageId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM gallery_images WHERE id = :id');
        $stmt->execute(['id' => $imageId]);
    }

    /** สร้าง slug กันซ้ำ (รองรับภาษาไทย) */
    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = trim(preg_replace('/\s+/u', '-', trim($title)));
        $slug = preg_replace('/[^\p{Thai}\p{L}\p{N}\-]/u', '', $slug);
        $slug = mb_strtolower($slug) ?: 'album';
        $base = $slug;
        $i = 1;
        while (self::slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId): bool
    {
        $sql = 'SELECT COUNT(*) FROM galleries WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM galleries')->fetchColumn();
    }
}
