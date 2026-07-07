<?php
/**
 * app/Controllers/GalleryController.php
 * -------------------------------------
 * หน้าแกลเลอรีสาธารณะ: รายการอัลบั้ม และหน้าดูรูปในอัลบั้ม
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Gallery;

final class GalleryController extends Controller
{
    public function index(): void
    {
        $this->view('gallery/index', [
            'pageTitle' => 'แกลเลอรี',
            'albums'    => Gallery::allWithCount(),
        ]);
    }

    public function show(string $slug): void
    {
        $album = Gallery::findBySlug($slug);
        if ($album === null) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'ไม่พบอัลบั้ม'], null);
            return;
        }
        $this->view('gallery/show', [
            'pageTitle' => $album['title'],
            'album'     => $album,
            'images'    => Gallery::images((int) $album['id']),
        ]);
    }
}
