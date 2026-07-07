<?php
/**
 * app/Models/Category.php
 * -----------------------
 * หมวดหมู่ข่าว (Phase 1 ใช้เฉพาะ type = news)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    public static function allNews(): array
    {
        return Database::connection()
            ->query('SELECT * FROM categories WHERE type = "news" ORDER BY name')
            ->fetchAll();
    }

    public static function allDownloads(): array
    {
        return Database::connection()
            ->query('SELECT * FROM categories WHERE type = "download" ORDER BY name')
            ->fetchAll();
    }
}
