<?php
/**
 * app/Controllers/NewsController.php
 * ----------------------------------
 * หน้าข่าวสาธารณะ: รายการข่าว (ค้นหา + แบ่งหน้า) และหน้าอ่านข่าว
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\News;

final class NewsController extends Controller
{
    private const PER_PAGE = 6;

    public function index(): void
    {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim((string) ($_GET['q'] ?? ''));

        $result = News::paginatePublished($page, self::PER_PAGE, $search);

        $this->view('news/index', [
            'pageTitle' => 'ข่าวประชาสัมพันธ์',
            'result'    => $result,
            'search'    => $search,
        ]);
    }

    public function show(string $slug): void
    {
        $news = News::findPublishedBySlug($slug);
        if ($news === null) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'ไม่พบหน้า'], null);
            return;
        }
        News::incrementViews((int) $news['id']);

        $this->view('news/show', [
            'pageTitle'   => $news['title'],
            'metaDesc'    => $news['meta_desc'] ?: $news['excerpt'],
            'ogImage'     => $news['og_image'] ?: $news['cover_image'],
            'news'        => $news,
        ]);
    }
}
