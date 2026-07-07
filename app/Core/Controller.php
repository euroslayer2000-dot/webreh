<?php
/**
 * app/Core/Controller.php
 * -----------------------
 * คลาสแม่ของทุก Controller รวมตัวช่วยที่ใช้บ่อย:
 * - view()     : เรนเดอร์ View พร้อมส่งตัวแปร และห่อด้วย layout
 * - redirect() : ย้ายหน้า
 * - flash()/getFlash() : ข้อความแจ้งเตือนข้ามหน้า (toast)
 * - old()      : ค่าที่กรอกไว้เดิม เมื่อ validation ไม่ผ่าน
 * - e()        : escape HTML กัน XSS
 */

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * เรนเดอร์ view จาก app/Views/{$view}.php
     * $layout : layouts/public หรือ layouts/admin (null = ไม่ใช้ layout)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/public'): void
    {
        extract($data, EXTR_SKIP);

        // จับ output ของ view ไว้ในตัวแปร $content เพื่อยัดเข้า layout
        ob_start();
        require dirname(__DIR__) . "/Views/{$view}.php";
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }
        require dirname(__DIR__) . "/Views/{$layout}.php";
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . config('app.url') . $path);
        exit;
    }

    /** เก็บข้อความ flash ลง session */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /** ดึงและล้าง flash ทั้งหมด (เรียกใน layout) */
    public static function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /** เก็บค่าเดิมของฟอร์มไว้ใน session ชั่วคราว */
    protected function withInput(array $input): void
    {
        $_SESSION['old'] = $input;
    }

    /** อ่านค่าเดิม (ใช้ใน view) */
    public static function old(string $key, string $default = ''): string
    {
        $value = $_SESSION['old'][$key] ?? $default;
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function clearOld(): void
    {
        unset($_SESSION['old']);
    }

    /** escape HTML — ใช้ทุกครั้งที่พ่นข้อมูลผู้ใช้ลง HTML */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
