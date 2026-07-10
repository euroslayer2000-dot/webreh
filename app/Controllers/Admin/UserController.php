<?php
/**
 * app/Controllers/Admin/UserController.php
 * -----------------------------------------
 * จัดการผู้ใช้งานหลังบ้าน (super_admin เท่านั้น)
 * - สร้าง/ตั้งรหัสผ่านใหม่: super_admin ตั้งรหัสผ่านเริ่มต้นให้โดยตรง
 *   ระบบจะบังคับให้ผู้ใช้เปลี่ยนรหัสผ่านทันทีตอนล็อกอินครั้งถัดไป
 * - กันไม่ให้แก้ไข/ปิดใช้งาน/ลบบัญชีตนเอง และกันไม่ให้เหลือ super_admin ที่ active 0 คน
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\User;

final class UserController extends Controller
{
    private const ROLES = ['super_admin', 'editor', 'teacher'];

    public function index(): void
    {
        Auth::requireRole(['super_admin']);
        $this->view('admin/users/index', [
            'pageTitle' => 'จัดการผู้ใช้งาน',
            'users'     => User::all(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::requireRole(['super_admin']);
        $this->view('admin/users/form', [
            'pageTitle' => 'เพิ่มผู้ใช้งาน',
            'user'      => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireRole(['super_admin']);
        $this->guardCsrf('/admin/users');

        $data = $this->validate($_POST, null);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect('/admin/users/create');
        }

        $password = $this->validatePassword($_POST, true);
        if ($password === null) {
            $this->withInput($_POST);
            $this->redirect('/admin/users/create');
        }

        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        User::create($data);
        self::clearOld();

        $this->flash('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว ระบบจะบังคับให้ตั้งรหัสผ่านใหม่ตอนล็อกอินครั้งแรก');
        $this->redirect('/admin/users');
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['super_admin']);
        $user = User::find((int) $id);
        if ($user === null) {
            $this->flash('error', 'ไม่พบข้อมูลผู้ใช้งาน');
            $this->redirect('/admin/users');
        }
        $this->view('admin/users/form', [
            'pageTitle' => 'แก้ไขผู้ใช้งาน',
            'user'      => $user,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        Auth::requireRole(['super_admin']);
        $this->guardCsrf('/admin/users');
        $targetId = (int) $id;
        $existing = User::find($targetId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบข้อมูลผู้ใช้งาน');
            $this->redirect('/admin/users');
        }

        $data = $this->validate($_POST, $targetId);
        if ($data === null) {
            $this->withInput($_POST);
            $this->redirect("/admin/users/edit/{$targetId}");
        }

        $passwordProvided = trim((string) ($_POST['password'] ?? '')) !== '';
        $password = null;
        if ($passwordProvided) {
            $password = $this->validatePassword($_POST, false);
            if ($password === null) {
                $this->withInput($_POST);
                $this->redirect("/admin/users/edit/{$targetId}");
            }
        }

        $this->guardSelfAndLastAdmin($targetId, $existing, $data['role'], (bool) $data['is_active'], "/admin/users/edit/{$targetId}");

        User::update($targetId, $data);
        if ($password !== null) {
            User::setPasswordByAdmin($targetId, password_hash($password, PASSWORD_DEFAULT));
        }
        self::clearOld();
        $this->flash('success', 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
        $this->redirect('/admin/users');
    }

    public function toggleActive(string $id): void
    {
        Auth::requireRole(['super_admin']);
        $this->guardCsrf('/admin/users');
        $targetId = (int) $id;
        $existing = User::find($targetId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบข้อมูลผู้ใช้งาน');
            $this->redirect('/admin/users');
        }

        $newActive = (int) $existing['is_active'] === 1 ? false : true;
        $this->guardSelfAndLastAdmin($targetId, $existing, $existing['role'], $newActive, '/admin/users');

        User::setActive($targetId, $newActive);
        $this->flash('success', $newActive ? 'เปิดใช้งานบัญชีเรียบร้อยแล้ว' : 'ปิดใช้งานบัญชีเรียบร้อยแล้ว');
        $this->redirect('/admin/users');
    }

    public function delete(string $id): void
    {
        Auth::requireRole(['super_admin']);
        $this->guardCsrf('/admin/users');
        $targetId = (int) $id;
        $existing = User::find($targetId);
        if ($existing === null) {
            $this->flash('error', 'ไม่พบข้อมูลผู้ใช้งาน');
            $this->redirect('/admin/users');
        }

        if ($targetId === Auth::id()) {
            $this->flash('error', 'ไม่สามารถลบบัญชีของตนเองได้');
            $this->redirect('/admin/users');
        }
        if ($existing['role'] === 'super_admin'
            && (int) $existing['is_active'] === 1
            && User::countActiveSuperAdmins($targetId) === 0
        ) {
            $this->flash('error', 'ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายได้');
            $this->redirect('/admin/users');
        }

        User::delete($targetId);
        $this->flash('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
        $this->redirect('/admin/users');
    }

    // ---------- ภายใน ----------

    private function guardCsrf(string $redirect): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect($redirect);
        }
    }

    private function validate(array $input, ?int $excludeId): ?array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if (mb_strlen($name) < 2) {
            $this->flash('error', 'กรุณากรอกชื่อผู้ใช้งาน');
            return null;
        }

        $email = trim((string) ($input['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'กรุณากรอกอีเมลให้ถูกต้อง');
            return null;
        }
        if (User::emailExists($email, $excludeId)) {
            $this->flash('error', 'อีเมลนี้ถูกใช้งานแล้ว');
            return null;
        }

        $role = (string) ($input['role'] ?? '');
        if (!in_array($role, self::ROLES, true)) {
            $this->flash('error', 'กรุณาเลือกบทบาทให้ถูกต้อง');
            return null;
        }

        return [
            'name'      => $name,
            'email'     => $email,
            'role'      => $role,
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    /** ตรวจรหัสผ่าน + ยืนยันรหัสผ่าน คืนรหัสผ่านที่ผ่านการตรวจ หรือ null ถ้าไม่ผ่าน */
    private function validatePassword(array $input, bool $required): ?string
    {
        $password = (string) ($input['password'] ?? '');
        $confirm  = (string) ($input['password_confirm'] ?? '');

        if (!$required && $password === '' && $confirm === '') {
            return null;
        }
        if (mb_strlen($password) < 8) {
            $this->flash('error', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
            return null;
        }
        if ($password !== $confirm) {
            $this->flash('error', 'รหัสผ่านทั้งสองช่องไม่ตรงกัน');
            return null;
        }
        return $password;
    }

    /** กันแก้ไข/ปิดใช้งานบัญชีตนเอง และกันไม่ให้เหลือ super_admin ที่ active 0 คน (redirect หากถูกบล็อก) */
    private function guardSelfAndLastAdmin(int $targetId, array $existing, string $newRole, bool $newActive, string $redirect): void
    {
        $isSelf = $targetId === Auth::id();
        if ($isSelf && ($newRole !== 'super_admin' || !$newActive)) {
            $this->flash('error', 'ไม่สามารถลดสิทธิ์หรือปิดใช้งานบัญชีของตนเองได้');
            $this->redirect($redirect);
        }

        $wasActiveSuperAdmin = $existing['role'] === 'super_admin' && (int) $existing['is_active'] === 1;
        $willStillBeActiveSuperAdmin = $newRole === 'super_admin' && $newActive;
        if ($wasActiveSuperAdmin && !$willStillBeActiveSuperAdmin && User::countActiveSuperAdmins($targetId) === 0) {
            $this->flash('error', 'ไม่สามารถลดสิทธิ์หรือปิดใช้งานผู้ดูแลระบบคนสุดท้ายได้');
            $this->redirect($redirect);
        }
    }

}
