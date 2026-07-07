<?php
/**
 * app/Controllers/Admin/DownloadController.php
 * --------------------------------------------
 * จัดการเอกสารดาวน์โหลด (อัปโหลด/ลบ)
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\Download;
use App\Models\Category;

final class DownloadController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('admin/downloads/index', [
            'pageTitle' => 'จัดการเอกสาร',
            'documents' => Download::all(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::require();
        $this->view('admin/downloads/form', [
            'pageTitle'  => 'เพิ่มเอกสาร',
            'categories' => Category::allDownloads(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::require();
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/downloads');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        if (mb_strlen($title) < 3) {
            $this->flash('error', 'กรุณากรอกชื่อเอกสาร');
            $this->withInput($_POST);
            $this->redirect('/admin/downloads/create');
        }

        try {
            $file = Uploader::document($_FILES['document'] ?? []);
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->withInput($_POST);
            $this->redirect('/admin/downloads/create');
        }

        if ($file === null) {
            $this->flash('error', 'กรุณาเลือกไฟล์เอกสาร');
            $this->withInput($_POST);
            $this->redirect('/admin/downloads/create');
        }

        $catId = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;
        Download::create([
            'category_id' => $catId,
            'title'       => $title,
            'file_path'   => $file['url'],
            'file_ext'    => $file['ext'],
            'file_size'   => $file['size'],
        ]);

        self::clearOld();
        $this->flash('success', 'เพิ่มเอกสารเรียบร้อยแล้ว');
        $this->redirect('/admin/downloads');
    }

    public function delete(string $id): void
    {
        Auth::require();
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/downloads');
        }
        $doc = Download::find((int) $id);
        if ($doc !== null) {
            $abs = dirname(__DIR__, 3) . '/public' . $doc['file_path'];
            if (is_file($abs)) {
                @unlink($abs);
            }
            Download::delete((int) $id);
            $this->flash('success', 'ลบเอกสารเรียบร้อยแล้ว');
        }
        $this->redirect('/admin/downloads');
    }
}
