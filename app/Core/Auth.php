<?php
/**
 * app/Core/Auth.php
 * -----------------
 * จัดการสถานะการล็อกอินผ่าน session
 * - login()  : บันทึกผู้ใช้ลง session พร้อม regenerate id (กัน session fixation)
 * - check()  : เช็คว่าล็อกอินอยู่ไหม
 * - user()   : คืนข้อมูลผู้ใช้ปัจจุบัน
 * - logout() : ล้าง session
 * - require(): บังคับให้ล็อกอินก่อน ไม่งั้น redirect ไปหน้า login
 */

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'                   => (int) $user['id'],
            'name'                 => $user['name'],
            'email'                => $user['email'],
            'role'                 => $user['role'],
            'must_change_password' => !empty($user['must_change_password']),
        ];
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** ใช้ต้นเมธอดของ Admin controller ทุกตัว */
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: ' . config('app.url') . '/admin/login');
            exit;
        }
        if (self::mustChangePassword() && !self::isChangePasswordRequest()) {
            header('Location: ' . config('app.url') . '/admin/change-password');
            exit;
        }
    }

    /** ต้องเปลี่ยนรหัสผ่านก่อนใช้งานหน้าอื่นหรือไม่ (super_admin ตั้งรหัสผ่านเริ่มต้นให้) */
    public static function mustChangePassword(): bool
    {
        return !empty(self::user()['must_change_password']);
    }

    /** ล้างสถานะบังคับเปลี่ยนรหัสผ่านใน session (เรียกหลังเปลี่ยนรหัสผ่านสำเร็จ) */
    public static function clearMustChangePassword(): void
    {
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['must_change_password'] = false;
        }
    }

    private static function isChangePasswordRequest(): bool
    {
        $path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
        return str_ends_with(rtrim($path, '/'), '/admin/change-password');
    }

    /** เช็คว่า role ปัจจุบันอยู่ในรายการที่อนุญาตหรือไม่ */
    public static function hasRole(array $roles): bool
    {
        $role = self::user()['role'] ?? null;
        return $role !== null && in_array($role, $roles, true);
    }

    /** บังคับทั้งล็อกอินและจำกัด role — ใช้ในหน้าที่สงวนไว้ เช่น ตั้งค่าเว็บไซต์ / จัดการผู้ใช้งาน */
    public static function requireRole(array $roles): void
    {
        self::require();
        if (!self::hasRole($roles)) {
            $_SESSION['flash'][] = ['type' => 'error', 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
            header('Location: ' . config('app.url') . '/admin/dashboard');
            exit;
        }
    }
}
