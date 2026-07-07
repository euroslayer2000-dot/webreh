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
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
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
    }
}
