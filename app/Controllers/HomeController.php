<?php
/**
 * app/Controllers/HomeController.php
 * ----------------------------------
 * หน้าแรก: Banner + Hero + สถิติ + ข่าวล่าสุด + แกลเลอรีล่าสุด + Quick Links
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\News;
use App\Models\Teacher;
use App\Models\Gallery;
use App\Models\Banner;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'pageTitle'    => 'หน้าแรก',
            'banners'      => Banner::activeOrdered(),
            'latestNews'   => News::latest(6),
            'latestAlbums' => Gallery::latest(3),
            'stats'        => [
                'news'     => News::countPublished(),
                'teachers' => Teacher::count() ?: 48,   // ใช้ค่าจริง ถ้ายังไม่มีข้อมูลใช้ค่าตัวอย่าง
                'students' => 1250,                     // ยังเป็นค่าตัวอย่าง (ไม่มีระบบนักเรียนตามสเปก PR)
                'years'    => 42,
            ],
        ]);
    }
}
