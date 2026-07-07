<?php
/**
 * app/Controllers/Admin/GalleryController.php
 * -------------------------------------------
 * จัดการอัลบั้ม (CRUD) และรูปในอัลบั้ม (เพิ่ม/ลบหลายรูป)
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\Gallery;

final class GalleryController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('admin/gallery/index', [
            'pageTitle' => 'จัดการแกลเลอรี',
            'albums'    => Gallery::allWithCount(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::require();
        $this->view('admin/gallery/form', [
            'pageTitle' => 'สร้างอัลบั้มใหม่',
            'album'     => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::require();
        $this->guardCsrf('/admin/gallery');

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect('/admin/gallery/create');
        }

        try {
            $cover = Uploader::image($_FILES['cover_image'] ?? [], 'gallery');
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/gallery/create');
        }

        $id = Gallery::create([
            'title'       => $data['title'],
            'slug'        => Gallery::makeSlug($data['title']),
            'description' => $data['description'],
            'cover_image' => $cover,
            'event_date'  => $data['event_date'],
        ]);

        self::clearOld();
        $this->flash('success', 'สร้างอัลบั้มแล้ว — เพิ่มรูปได้เลย');
        $this->redirect("/admin/gallery/edit/{$id}");
    }

    public function edit(string $id): void
    {
        Auth::require();
        $album = Gallery::find((int) $id);
        if ($album === null) {
            $this->flash('error', 'ไม่พบอัลบั้ม');
            $this->redirect('/admin/gallery');
        }
        $this->view('admin/gallery/form', [
            'pageTitle' => 'แก้ไขอัลบั้ม',
            'album'     => $album,
            'images'    => Gallery::images((int) $album['id']),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        Auth::require();
        $this->guardCsrf('/admin/gallery');
        $albumId = (int) $id;
        $existing = Gallery::find($albumId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบอัลบั้ม');
            $this->redirect('/admin/gallery');
        }

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->redirect("/admin/gallery/edit/{$albumId}");
        }

        try {
            $cover = Uploader::image($_FILES['cover_image'] ?? [], 'gallery') ?? $existing['cover_image'];
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect("/admin/gallery/edit/{$albumId}");
        }

        $slug = $existing['title'] === $data['title']
            ? $existing['slug']
            : Gallery::makeSlug($data['title'], $albumId);

        Gallery::update($albumId, [
            'title'       => $data['title'],
            'slug'        => $slug,
            'description' => $data['description'],
            'cover_image' => $cover,
            'event_date'  => $data['event_date'],
        ]);

        $this->flash('success', 'บันทึกอัลบั้มแล้ว');
        $this->redirect("/admin/gallery/edit/{$albumId}");
    }

    /** อัปโหลดรูปเข้าอัลบั้ม (รองรับหลายไฟล์) */
    public function addImages(string $id): void
    {
        Auth::require();
        $this->guardCsrf('/admin/gallery');
        $albumId = (int) $id;
        if (Gallery::find($albumId) === null) {
            $this->flash('error', 'ไม่พบอัลบั้ม');
            $this->redirect('/admin/gallery');
        }

        $files = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'][0])) {
            $this->flash('error', 'ยังไม่ได้เลือกรูป');
            $this->redirect("/admin/gallery/edit/{$albumId}");
        }

        $added = 0;
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $single = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            try {
                $path = Uploader::image($single, 'gallery');
                if ($path !== null) {
                    Gallery::addImage($albumId, $path);
                    $added++;
                }
            } catch (UploadException $e) {
                $this->flash('error', "รูปที่ " . ($i + 1) . ": " . $e->getMessage());
            }
        }

        if ($added > 0) {
            $this->flash('success', "เพิ่มรูปสำเร็จ {$added} รูป");
        }
        $this->redirect("/admin/gallery/edit/{$albumId}");
    }

    public function deleteImage(string $imageId): void
    {
        Auth::require();
        $this->guardCsrf('/admin/gallery');
        $image = Gallery::findImage((int) $imageId);
        if ($image !== null) {
            // ลบไฟล์จริงบนดิสก์ด้วย
            $abs = dirname(__DIR__, 3) . '/public' . $image['image_path'];
            if (is_file($abs)) {
                @unlink($abs);
            }
            Gallery::deleteImage((int) $imageId);
            $this->flash('success', 'ลบรูปแล้ว');
            $this->redirect("/admin/gallery/edit/{$image['gallery_id']}");
        }
        $this->redirect('/admin/gallery');
    }

    public function delete(string $id): void
    {
        Auth::require();
        $this->guardCsrf('/admin/gallery');
        Gallery::delete((int) $id);
        $this->flash('success', 'ลบอัลบั้มเรียบร้อยแล้ว');
        $this->redirect('/admin/gallery');
    }

    // ---------- ภายใน ----------

    private function guardCsrf(string $redirect): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect($redirect);
        }
    }

    private function validate(array $input): ?array
    {
        $title = trim((string) ($input['title'] ?? ''));
        if (mb_strlen($title) < 3) {
            $this->flash('error', 'ชื่ออัลบั้มต้องมีอย่างน้อย 3 ตัวอักษร');
            return null;
        }
        $eventDate = trim((string) ($input['event_date'] ?? ''));
        return [
            'title'       => $title,
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'event_date'  => $eventDate !== '' ? $eventDate : null,
        ];
    }
}
