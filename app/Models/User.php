<?php
/**
 * app/Models/User.php
 * -------------------
 * จัดการข้อมูลผู้ใช้และตรรกะความปลอดภัยการล็อกอิน
 * (นับครั้งผิด, ล็อกบัญชีชั่วคราว)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /** ถูกล็อกอยู่หรือไม่ */
    public static function isLocked(array $user): bool
    {
        return !empty($user['locked_until'])
            && strtotime($user['locked_until']) > time();
    }

    /** นาทีที่เหลือจนกว่าจะปลดล็อก */
    public static function lockRemaining(array $user): int
    {
        if (empty($user['locked_until'])) {
            return 0;
        }
        return max(0, (int) ceil((strtotime($user['locked_until']) - time()) / 60));
    }

    /** ล็อกอินผิด: เพิ่มตัวนับ และล็อกเมื่อถึงเพดาน */
    public static function registerFailure(array $user): void
    {
        $attempts = (int) $user['failed_attempts'] + 1;
        $lockedUntil = null;

        if ($attempts >= (int) config('auth.max_attempts')) {
            $minutes = (int) config('auth.lockout_minutes');
            $lockedUntil = date('Y-m-d H:i:s', time() + $minutes * 60);
            $attempts = 0; // รีเซ็ตตัวนับหลังล็อก
        }

        $stmt = Database::connection()->prepare(
            'UPDATE users SET failed_attempts = :a, locked_until = :l WHERE id = :id'
        );
        $stmt->execute(['a' => $attempts, 'l' => $lockedUntil, 'id' => $user['id']]);
    }

    /** ล็อกอินสำเร็จ: ล้างตัวนับและการล็อก */
    public static function registerSuccess(int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $stmt->execute(['id' => $userId]);
    }

    // ---------- รีเซ็ตรหัสผ่าน (Phase 3) ----------

    /** ตั้ง token รีเซ็ต (หมดอายุใน 1 ชม.) */
    public static function setResetToken(int $userId, string $token, string $expires): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET reset_token = :t, reset_expires = :e WHERE id = :id'
        );
        $stmt->execute(['t' => $token, 'e' => $expires, 'id' => $userId]);
    }

    /** หา user จาก token ที่ยังไม่หมดอายุ */
    public static function findByValidResetToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE reset_token = :t AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute(['t' => $token]);
        return $stmt->fetch() ?: null;
    }

    /** ตั้งรหัสผ่านใหม่ + ล้าง token */
    public static function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET password_hash = :p, reset_token = NULL, reset_expires = NULL,
                failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $stmt->execute(['p' => $passwordHash, 'id' => $userId]);
    }
}
