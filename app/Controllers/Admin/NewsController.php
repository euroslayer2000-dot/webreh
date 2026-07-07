<?php
/**
 * app/Controllers/Admin/NewsController.php
 * ----------------------------------------
 * CRUD ข่าวประชาสัมพันธ์ (หลังบ้าน)
 * ครอบคลุม: list + pagination, create, store, edit, update, delete
 * ความปลอดภัย: Auth::require ทุก action, CSRF ทุก POST,
 *              validation, อัปโหลดรูปแบบตรวจ MIME + ขนาด + สุ่มชื่อไฟล์
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\News;
use App\Models\Category;

final class NewsController extends Controller
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        Auth::require();
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $this->view('admin/news/index', [
            'pageTitle' => 'จัดการข่าว',
            'result'    => News::paginateAll($page, self::PER_PAGE),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::require();
        $this->view('admin/news/form', [
            'pageTitle'  => 'เพิ่มข่าวใหม่',
            'news'       => null,
            'categories' => Category::allNews(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::require();
        $this->guardCsrf();

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect('/admin/news/create');
        }

        // อัปโหลดรูปปก + รูป OG (ผ่าน Uploader ซึ่งย่อ/บีบอัดให้อัตโนมัติ)
        try {
            $cover = Uploader::image($_FILES['cover_image'] ?? [], 'news');
            $og    = Uploader::image($_FILES['og_image'] ?? [], 'news');
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->withInput($_POST);
            $this->redirect('/admin/news/create');
        }

        $slug = News::makeSlug($data['title']);
        News::create([
            'category_id'  => $data['category_id'],
            'title'        => $data['title'],
            'slug'         => $slug,
            'excerpt'      => $data['excerpt'],
            'content'      => $data['content'],
            'cover_image'  => $cover,
            'meta_title'   => $data['meta_title'],
            'meta_desc'    => $data['meta_desc'],
            'og_image'     => $og,
            'status'       => $data['status'],
            'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
            'author_id'    => Auth::id(),
        ]);

        self::clearOld();
        $this->flash('success', 'บันทึกข่าวเรียบร้อยแล้ว');
        $this->redirect('/admin/news');
    }

    public function edit(string $id): void
    {
        Auth::require();
        $news = News::find((int) $id);
        if ($news === null) {
            $this->flash('error', 'ไม่พบข่าวที่ต้องการแก้ไข');
            $this->redirect('/admin/news');
        }
        $this->view('admin/news/form', [
            'pageTitle'  => 'แก้ไขข่าว',
            'news'       => $news,
            'categories' => Category::allNews(),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        Auth::require();
        $this->guardCsrf();

        $newsId = (int) $id;
        $existing = News::find($newsId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบข่าวที่ต้องการแก้ไข');
            $this->redirect('/admin/news');
        }

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect("/admin/news/edit/{$newsId}");
        }

        // ถ้าอัปโหลดรูปใหม่ ใช้ของใหม่ ไม่งั้นคงของเดิม (Uploader ย่อ/บีบอัดให้)
        try {
            $cover = Uploader::image($_FILES['cover_image'] ?? [], 'news') ?? $existing['cover_image'];
            $og    = Uploader::image($_FILES['og_image'] ?? [], 'news') ?? ($existing['og_image'] ?? null);
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->withInput($_POST);
            $this->redirect("/admin/news/edit/{$newsId}");
        }

        // slug คงเดิมถ้าหัวข้อไม่เปลี่ยน มิฉะนั้นสร้างใหม่
        $slug = $existing['title'] === $data['title']
            ? $existing['slug']
            : News::makeSlug($data['title'], $newsId);

        News::update($newsId, [
            'category_id'  => $data['category_id'],
            'title'        => $data['title'],
            'slug'         => $slug,
            'excerpt'      => $data['excerpt'],
            'content'      => $data['content'],
            'cover_image'  => $cover,
            'meta_title'   => $data['meta_title'],
            'meta_desc'    => $data['meta_desc'],
            'og_image'     => $og,
            'status'       => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($existing['published_at'] ?: date('Y-m-d H:i:s'))
                : null,
        ]);

        self::clearOld();
        $this->flash('success', 'อัปเดตข่าวเรียบร้อยแล้ว');
        $this->redirect('/admin/news');
    }

    public function delete(string $id): void
    {
        Auth::require();
        $this->guardCsrf();
        News::delete((int) $id);
        $this->flash('success', 'ลบข่าวเรียบร้อยแล้ว');
        $this->redirect('/admin/news');
    }

    // ---------- ตัวช่วยภายใน ----------

    private function guardCsrf(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/news');
        }
    }

    /**
     * ตรวจความถูกต้องของข้อมูล คืน array ที่สะอาดแล้ว หรือ null ถ้าไม่ผ่าน
     */
    private function validate(array $input): ?array
    {
        $errors = [];

        $title   = trim((string) ($input['title'] ?? ''));
        $content = trim((string) ($input['content'] ?? ''));
        $status  = ($input['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $catId   = ($input['category_id'] ?? '') !== '' ? (int) $input['category_id'] : null;
        $excerpt = mb_substr(trim((string) ($input['excerpt'] ?? '')), 0, 500);

        if (mb_strlen($title) < 5) {
            $errors[] = 'หัวข้อข่าวต้องมีอย่างน้อย 5 ตัวอักษร';
        }
        if ($content === '') {
            $errors[] = 'กรุณากรอกเนื้อหาข่าว';
        }

        if ($errors) {
            foreach ($errors as $e) {
                $this->flash('error', $e);
            }
            return null;
        }

        // meta อัตโนมัติถ้าไม่กรอก (ช่วย SEO/แชร์โซเชียล)
        $metaTitle = trim((string) ($input['meta_title'] ?? '')) ?: $title;
        $metaDesc  = trim((string) ($input['meta_desc'] ?? '')) ?: $excerpt;

        return [
            'title'       => $title,
            'content'     => $content,   // หมายเหตุ: ควร sanitize HTML เพิ่มด้วย HTMLPurifier ใน production
            'status'      => $status,
            'category_id' => $catId,
            'excerpt'     => $excerpt,
            'meta_title'  => mb_substr($metaTitle, 0, 255),
            'meta_desc'   => mb_substr($metaDesc, 0, 300),
        ];
    }

    /**
     * จัดการอัปโหลดรูป: ตรวจ error, ขนาด, MIME จริง (finfo), สุ่มชื่อไฟล์
     * คืน public path หรือ null ถ้าไม่มีไฟล์ / ไม่ผ่าน
     */
    private function handleUpload(string $field): ?string
    {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'อัปโหลดรูปไม่สำเร็จ');
            return null;
        }
        if ($file['size'] > (int) config('upload.max_size')) {
            $this->flash('error', 'ไฟล์รูปใหญ่เกิน 3 MB');
            return null;
        }

        // ตรวจ MIME จากเนื้อไฟล์จริง ไม่เชื่อ extension ที่ผู้ใช้ส่งมา
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = config('upload.mime');
        if (!in_array($mime, $allowed, true)) {
            $this->flash('error', 'อนุญาตเฉพาะไฟล์ JPG, PNG, WEBP');
            return null;
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        };
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = config('upload.path') . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->flash('error', 'บันทึกไฟล์รูปไม่สำเร็จ');
            return null;
        }

        return config('upload.public_url') . '/' . $name;
    }
}
