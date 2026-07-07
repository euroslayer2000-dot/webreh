<?php
/**
 * app/Models/Setting.php
 * ----------------------
 * อ่านค่าตั้งค่าเว็บไซต์แบบ key-value และ cache ไว้ในหน่วยความจำ
 * เรียกใช้ผ่าน Setting::get('site_name')
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            $rows = Database::connection()
                ->query('SELECT setting_key, setting_value FROM settings')
                ->fetchAll();
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::load()[$key] ?? $default;
    }

    /** บันทึกค่าเดียว (สร้างใหม่ถ้ายังไม่มี) */
    public static function set(string $key, ?string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2'
        );
        $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
        self::$cache = null; // ล้าง cache ให้อ่านค่าใหม่
    }

    /** บันทึกหลายค่าในครั้งเดียว (array คีย์ => ค่า) */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set($key, $value === '' ? null : (string) $value);
        }
    }

    /** คืนค่าทั้งหมด (สำหรับหน้าตั้งค่าหลังบ้าน) */
    public static function all(): array
    {
        return self::load();
    }
}
