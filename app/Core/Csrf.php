<?php
/**
 * app/Core/Csrf.php
 * -----------------
 * ป้องกัน CSRF: สร้าง token เก็บใน session แล้วแนบไปกับทุกฟอร์ม
 * ตรวจสอบด้วย hash_equals เพื่อกัน timing attack
 */

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    /** คืน token ปัจจุบัน (สร้างใหม่ถ้ายังไม่มี) */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** สร้าง hidden input พร้อมใช้ในฟอร์ม */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /** ตรวจสอบ token ที่ส่งมา */
    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
