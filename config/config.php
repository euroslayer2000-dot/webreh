<?php
/**
 * config/config.php
 * -----------------
 * อ่านค่าจากไฟล์ .env (แบบไม่ต้องพึ่ง composer) แล้วคืนเป็น array
 * ทั้งระบบเรียกใช้ผ่าน config('key') ด้านล่าง
 */

declare(strict_types=1);

/**
 * โหลดไฟล์ .env เข้าสู่ getenv()/$_ENV แบบง่าย ๆ
 * รองรับ comment (#) และค่าที่มีเครื่องหมาย =
 */
function load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key   = trim($key);
        // ตัด comment ท้ายบรรทัด และช่องว่าง/เครื่องหมายคำพูด
        $value = trim(preg_replace('/\s+#.*$/', '', $value));
        $value = trim($value, "\"'");
        if ($key !== '') {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

load_env(dirname(__DIR__) . '/.env');

/**
 * ตัวช่วยอ่านค่า env พร้อมค่า default และแปลง true/false เป็น bool
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $value,
    };
}

$config = [
    'app' => [
        'env'   => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', false),
        'url'   => rtrim((string) env('APP_URL', ''), '/'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'school_pr'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    // ความปลอดภัยการล็อกอิน
    'auth' => [
        'max_attempts'   => 5,     // ผิดกี่ครั้งจึงล็อก
        'lockout_minutes'=> 15,    // ล็อกนานกี่นาที
    ],
    // อีเมล (สำหรับรีเซ็ตรหัสผ่าน)
    'mail' => [
        'from_email' => env('MAIL_FROM', 'no-reply@school.ac.th'),
        'from_name'  => env('MAIL_FROM_NAME', 'ระบบเว็บไซต์โรงเรียน'),
        // local/dev: เขียนอีเมลลงไฟล์ storage/mail แทนการส่งจริง (จะได้เห็นลิงก์รีเซ็ต)
        // production: ใช้ฟังก์ชัน mail() ของ PHP (หรือเปลี่ยนไปใช้ PHPMailer/SMTP)
        'log_to_file' => env('APP_ENV', 'production') !== 'production',
        'log_path'    => dirname(__DIR__) . '/storage/mail',
    ],
    // อัปโหลดรูปภาพ (ใช้ร่วมกันทุกส่วน: ข่าว บุคลากร แกลเลอรี แบนเนอร์)
    'upload' => [
        'max_size'   => 3 * 1024 * 1024,   // 3 MB
        'mime'       => ['image/jpeg', 'image/png', 'image/webp'],
        // image optimization ตอนอัปโหลด (Phase 3)
        'max_width'    => 1600,   // ย่อรูปที่กว้างเกินลงเหลือเท่านี้ (px)
        'jpeg_quality' => 82,     // คุณภาพ JPEG หลังบีบอัด
        'webp_quality' => 82,     // คุณภาพ WEBP หลังบีบอัด
        // path ของแต่ละหมวด (โฟลเดอร์จะถูกสร้างอัตโนมัติถ้ายังไม่มี)
        'path'       => dirname(__DIR__) . '/public/assets/uploads/news',
        'public_url' => '/assets/uploads/news',
        'dirs' => [
            'news'     => '/assets/uploads/news',
            'teachers' => '/assets/uploads/teachers',
            'gallery'  => '/assets/uploads/gallery',
            'banners'  => '/assets/uploads/banners',
            'brand'    => '/assets/uploads/brand',
        ],
    ],
    // อัปโหลดเอกสารดาวน์โหลด
    'document' => [
        'max_size'   => 15 * 1024 * 1024,  // 15 MB
        'mime'       => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'ext'        => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'path'       => dirname(__DIR__) . '/public/assets/uploads/docs',
        'public_url' => '/assets/uploads/docs',
    ],
];

/**
 * เข้าถึงค่า config ด้วย dot notation เช่น config('db.host')
 */
function config(string $key, mixed $default = null): mixed
{
    global $config;
    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}
