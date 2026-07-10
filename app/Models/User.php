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

    /** ตั้งรหัสผ่านใหม่ + ล้าง token (ผู้ใช้ตั้งเอง ไม่บังคับเปลี่ยนอีก) */
    public static function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET password_hash = :p, must_change_password = 0, reset_token = NULL, reset_expires = NULL,
                failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $stmt->execute(['p' => $passwordHash, 'id' => $userId]);
    }

    // ---------- จัดการผู้ใช้งาน (Phase 4) ----------

    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, name, email, role, is_active, must_change_password, created_at FROM users ORDER BY name'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /** สร้างผู้ใช้ใหม่ (super_admin ตั้งรหัสผ่านเริ่มต้นให้ บังคับเปลี่ยนตอนล็อกอินครั้งแรก) */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_active, must_change_password)
             VALUES (:name, :email, :password_hash, :role, :is_active, 1)'
        );
        $stmt->execute([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'],
            'is_active'     => $data['is_active'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    /** super_admin ตั้งรหัสผ่านใหม่ให้ผู้ใช้คนอื่น — บังคับเปลี่ยนตอนล็อกอินครั้งถัดไป */
    public static function setPasswordByAdmin(int $userId, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET password_hash = :p, must_change_password = 1,
                failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $stmt->execute(['p' => $passwordHash, 'id' => $userId]);
    }

    /** แก้ไขข้อมูลผู้ใช้ (ไม่แตะ password_hash) */
    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET name = :name, email = :email, role = :role, is_active = :is_active WHERE id = :id'
        );
        $stmt->execute([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $data['is_active'],
            'id'        => $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET is_active = :a WHERE id = :id');
        $stmt->execute(['a' => $active ? 1 : 0, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** จำนวน super_admin ที่ active อยู่ (ใช้กันไม่ให้เหลือ 0 คน) */
    public static function countActiveSuperAdmins(?int $excludeId = null): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND is_active = 1";
        $params = [];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
