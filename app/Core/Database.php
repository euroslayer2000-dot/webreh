<?php
/**
 * app/Core/Database.php
 * ---------------------
 * เชื่อมต่อ MySQL ด้วย PDO แบบ Singleton (สร้างการเชื่อมต่อครั้งเดียว)
 * ตั้งค่า error mode เป็น exception และปิด emulate prepares
 * เพื่อให้ Prepared Statements ทำงานจริง (กัน SQL Injection)
 */

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    /** ห้ามสร้าง instance ตรง ๆ */
    private function __construct() {}

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            config('db.host'),
            config('db.port'),
            config('db.name')
        );

        try {
            self::$instance = new PDO($dsn, config('db.user'), config('db.pass'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // ไม่โชว์รายละเอียด DB บน production
            if (config('app.debug')) {
                throw $e;
            }
            http_response_code(500);
            exit('เชื่อมต่อฐานข้อมูลไม่สำเร็จ');
        }

        return self::$instance;
    }
}
