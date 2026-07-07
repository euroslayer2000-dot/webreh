<?php
/**
 * app/Controllers/Admin/TeacherController.php
 * -------------------------------------------
 * จัดการบุคลากร (CRUD) + อัปโหลดรูป
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\Teacher;

final class TeacherController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('admin/teachers/index', [
            'pageTitle' => 'จัดการบุคลากร',
            'teachers'  => Teacher::all(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::require();
        $this->view('admin/teachers/form', [
            'pageTitle' => 'เพิ่มบุคลากร',
            'teacher'   => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::require();
        $this->guardCsrf('/admin/teachers');

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect('/admin/teachers/create');
        }

        try {
            $photo = Uploader::image($_FILES['photo'] ?? [], 'teachers');
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->withInput($_POST);
            $this->redirect('/admin/teachers/create');
        }

        Teacher::create($data + ['photo' => $photo]);
        self::clearOld();
        $this->flash('success', 'เพิ่มบุคลากรเรียบร้อยแล้ว');
        $this->redirect('/admin/teachers');
    }

    public function edit(string $id): void
    {
        Auth::require();
        $teacher = Teacher::find((int) $id);
        if ($teacher === null) {
            $this->flash('error', 'ไม่พบข้อมูลบุคลากร');
            $this->redirect('/admin/teachers');
        }
        $this->view('admin/teachers/form', [
            'pageTitle' => 'แก้ไขบุคลากร',
            'teacher'   => $teacher,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        Auth::require();
        $this->guardCsrf('/admin/teachers');
        $teacherId = (int) $id;
        $existing = Teacher::find($teacherId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบข้อมูลบุคลากร');
            $this->redirect('/admin/teachers');
        }

        $data = $this->validate($_POST);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect("/admin/teachers/edit/{$teacherId}");
        }

        try {
            $photo = Uploader::image($_FILES['photo'] ?? [], 'teachers') ?? $existing['photo'];
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect("/admin/teachers/edit/{$teacherId}");
        }

        Teacher::update($teacherId, $data + ['photo' => $photo]);
        self::clearOld();
        $this->flash('success', 'อัปเดตข้อมูลบุคลากรเรียบร้อยแล้ว');
        $this->redirect('/admin/teachers');
    }

    public function delete(string $id): void
    {
        Auth::require();
        $this->guardCsrf('/admin/teachers');
        Teacher::delete((int) $id);
        $this->flash('success', 'ลบบุคลากรเรียบร้อยแล้ว');
        $this->redirect('/admin/teachers');
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
        $name = trim((string) ($input['name'] ?? ''));
        if (mb_strlen($name) < 2) {
            $this->flash('error', 'กรุณากรอกชื่อบุคลากร');
            return null;
        }
        return [
            'name'       => $name,
            'position'   => trim((string) ($input['position'] ?? '')) ?: null,
            'department' => trim((string) ($input['department'] ?? '')) ?: null,
            'email'      => trim((string) ($input['email'] ?? '')) ?: null,
            'sort_order' => (int) ($input['sort_order'] ?? 0),
            'is_active'  => isset($input['is_active']) ? 1 : 0,
        ];
    }
}
