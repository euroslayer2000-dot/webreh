<?php
/**
 * app/Models/News.php
 * -------------------
 * CRUD ข่าว + ดึงข่าวหน้าเว็บ + pagination + นับยอดวิว
 * ทุก query ใช้ Prepared Statements
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class News
{
    // ---------- ฝั่งสาธารณะ ----------

    /** ข่าวล่าสุดที่เผยแพร่แล้ว (หน้าแรก) */
    public static function latest(int $limit = 6): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, c.name AS category_name
             FROM news n LEFT JOIN categories c ON c.id = n.category_id
             WHERE n.status = "published"
             ORDER BY n.published_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** รายการข่าวหน้าเว็บ พร้อมค้นหา + แบ่งหน้า */
    public static function paginatePublished(int $page, int $perPage, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = 'WHERE n.status = "published"';
        $params = [];

        if ($search !== '') {
            $where .= ' AND (n.title LIKE :s OR n.excerpt LIKE :s)';
            $params['s'] = '%' . $search . '%';
        }

        $db = Database::connection();

        // นับทั้งหมดเพื่อคำนวณจำนวนหน้า
        $countStmt = $db->prepare("SELECT COUNT(*) FROM news n $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT n.*, c.name AS category_name
             FROM news n LEFT JOIN categories c ON c.id = n.category_id
             $where
             ORDER BY n.published_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'       => $stmt->fetchAll(),
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
            'page'        => $page,
        ];
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, c.name AS category_name
             FROM news n LEFT JOIN categories c ON c.id = n.category_id
             WHERE n.slug = :slug AND n.status = "published" LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function incrementViews(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE news SET views = views + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    // ---------- ฝั่งหลังบ้าน ----------

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM news WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** รายการข่าวทั้งหมด (draft + published) พร้อมแบ่งหน้าสำหรับ admin */
    public static function paginateAll(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $db = Database::connection();

        $total = (int) $db->query('SELECT COUNT(*) FROM news')->fetchColumn();

        $stmt = $db->prepare(
            'SELECT n.*, c.name AS category_name
             FROM news n LEFT JOIN categories c ON c.id = n.category_id
             ORDER BY n.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'       => $stmt->fetchAll(),
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
            'page'        => $page,
        ];
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO news
                (category_id, title, slug, excerpt, content, cover_image,
                 meta_title, meta_desc, og_image, status, published_at, author_id)
             VALUES
                (:category_id, :title, :slug, :excerpt, :content, :cover_image,
                 :meta_title, :meta_desc, :og_image, :status, :published_at, :author_id)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = Database::connection()->prepare(
            'UPDATE news SET
                category_id = :category_id, title = :title, slug = :slug,
                excerpt = :excerpt, content = :content, cover_image = :cover_image,
                meta_title = :meta_title, meta_desc = :meta_desc, og_image = :og_image,
                status = :status, published_at = :published_at
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM news WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** สร้าง slug จากหัวข้อ รองรับภาษาไทย และกันซ้ำ */
    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = trim(preg_replace('/[\s]+/u', '-', trim($title)));
        $slug = preg_replace('/[^\p{Thai}\p{L}\p{N}\-]/u', '', $slug);
        $slug = mb_strtolower($slug) ?: 'news';

        $base = $slug;
        $i = 1;
        while (self::slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId): bool
    {
        $sql = 'SELECT COUNT(*) FROM news WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function countPublished(): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM news WHERE status = "published"')
            ->fetchColumn();
    }

    public static function countAll(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM news')->fetchColumn();
    }
}
