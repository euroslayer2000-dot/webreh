<?php
/**
 * app/Controllers/Admin/BannerController.php
 * ------------------------------------------
 * จัดการแบนเนอร์หน้าแรก (CRUD)
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\Banner;

final class BannerController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('admin/banners/index', [
            'pageTitle' => 'จัดการแบนเนอร์',
            'banners'   => Banner::all(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::require();
        $this->view('admin/banners/form', [
            'pageTitle' => 'เพิ่มแบนเนอร์',
            'banner'    => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::require();
        $this->guardCsrf();

        try {
            $image = Uploader::image($_FILES['image'] ?? [], 'banners');
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/banners/create');
        }
        if ($image === null) {
            $this->flash('error', 'กรุณาเลือกรูปแบนเนอร์');
            $this->redirect('/admin/banners/create');
        }

        Banner::create($this->fields($_POST) + ['image_path' => $image]);
        $this->flash('success', 'เพิ่มแบนเนอร์เรียบร้อยแล้ว');
        $this->redirect('/admin/banners');
    }

    public function edit(string $id): void
    {
        Auth::require();
        $banner = Banner::find((int) $id);
        if ($banner === null) {
            $this->flash('error', 'ไม่พบแบนเนอร์');
            $this->redirect('/admin/banners');
        }
        $this->view('admin/banners/form', [
            'pageTitle' => 'แก้ไขแบนเนอร์',
            'banner'    => $banner,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        Auth::require();
        $this->guardCsrf();
        $bannerId = (int) $id;
        $existing = Banner::find($bannerId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบแบนเนอร์');
            $this->redirect('/admin/banners');
        }

        try {
            $image = Uploader::image($_FILES['image'] ?? [], 'banners') ?? $existing['image_path'];
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect("/admin/banners/edit/{$bannerId}");
        }

        Banner::update($bannerId, $this->fields($_POST) + ['image_path' => $image]);
        $this->flash('success', 'อัปเดตแบนเนอร์เรียบร้อยแล้ว');
        $this->redirect('/admin/banners');
    }

    public function delete(string $id): void
    {
        Auth::require();
        $this->guardCsrf();
        $banner = Banner::find((int) $id);
        if ($banner !== null) {
            $abs = dirname(__DIR__, 3) . '/public' . $banner['image_path'];
            if (is_file($abs)) {
                @unlink($abs);
            }
            Banner::delete((int) $id);
            $this->flash('success', 'ลบแบนเนอร์เรียบร้อยแล้ว');
        }
        $this->redirect('/admin/banners');
    }

    // ---------- ภายใน ----------

    private function guardCsrf(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/banners');
        }
    }

    private function fields(array $input): array
    {
        return [
            'title'      => trim((string) ($input['title'] ?? '')) ?: null,
            'link_url'   => trim((string) ($input['link_url'] ?? '')) ?: null,
            'sort_order' => (int) ($input['sort_order'] ?? 0),
            'is_active'  => isset($input['is_active']) ? 1 : 0,
        ];
    }
}
