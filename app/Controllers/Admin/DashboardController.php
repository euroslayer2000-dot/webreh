<?php
/**
 * app/Controllers/Admin/DashboardController.php
 * ---------------------------------------------
 * หน้าแดชบอร์ดหลังบ้าน แสดงสรุปตัวเลขทุกระบบ
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\News;
use App\Models\Teacher;
use App\Models\Gallery;
use App\Models\Download;
use App\Models\Contact;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::require();

        $this->view('admin/dashboard/index', [
            'pageTitle' => 'แดชบอร์ด',
            'stats' => [
                'news_total'     => News::countAll(),
                'news_published' => News::countPublished(),
                'teachers'       => Teacher::count(),
                'galleries'      => Gallery::count(),
                'documents'      => Download::count(),
                'unread'         => Contact::unreadCount(),
            ],
        ], 'layouts/admin');
    }
}
