<?php
/**
 * app/Controllers/SitemapController.php
 * -------------------------------------
 * สร้าง sitemap.xml แบบไดนามิกจากเนื้อหาจริง (ช่วยให้ Google เก็บ index ครบ)
 * เข้าถึงที่ /sitemap.xml
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

final class SitemapController
{
    public function index(): void
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        // หน้าหลัก (static)
        foreach (['/', '/news', '/personnel', '/gallery', '/downloads', '/contact', '/privacy'] as $p) {
            $urls[] = ['loc' => $base . $p, 'priority' => $p === '/' ? '1.0' : '0.7'];
        }

        $db = Database::connection();

        // ข่าวที่เผยแพร่แล้ว
        $news = $db->query(
            'SELECT slug, updated_at FROM news WHERE status = "published" ORDER BY published_at DESC'
        )->fetchAll();
        foreach ($news as $n) {
            $urls[] = [
                'loc'     => $base . '/news/' . rawurlencode($n['slug']),
                'lastmod' => date('Y-m-d', strtotime($n['updated_at'])),
                'priority'=> '0.8',
            ];
        }

        // อัลบั้มแกลเลอรี
        $albums = $db->query('SELECT slug FROM galleries ORDER BY id DESC')->fetchAll();
        foreach ($albums as $a) {
            $urls[] = ['loc' => $base . '/gallery/' . rawurlencode($a['slug']), 'priority' => '0.6'];
        }

        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo '  <url>' . "\n";
            echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc>' . "\n";
            if (!empty($u['lastmod'])) {
                echo '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            }
            echo '    <priority>' . $u['priority'] . '</priority>' . "\n";
            echo '  </url>' . "\n";
        }
        echo '</urlset>';
    }
}
