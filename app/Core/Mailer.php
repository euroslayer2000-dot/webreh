<?php
/**
 * app/Core/Mailer.php
 * -------------------
 * ตัวช่วยส่งอีเมลแบบเรียบง่าย
 * - local/dev: เขียนอีเมลลงไฟล์ storage/mail/*.html (จะได้เปิดดูลิงก์รีเซ็ตได้)
 * - production: ใช้ mail() ของ PHP
 *
 * ต้องการ SMTP จริง แนะนำเปลี่ยนมาใช้ PHPMailer:
 *   composer require phpmailer/phpmailer
 * แล้วแทนที่เมธอด deliver() ด้านล่าง
 */

declare(strict_types=1);

namespace App\Core;

final class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $fromEmail = (string) config('mail.from_email');
        $fromName  = (string) config('mail.from_name');

        if (config('mail.log_to_file')) {
            return self::logToFile($to, $subject, $htmlBody, $fromEmail, $fromName);
        }

        // encode หัวข้อภาษาไทยให้ถูกต้อง
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($fromName), $fromEmail),
        ]);

        return @mail($to, $encodedSubject, $htmlBody, $headers);
    }

    /** โหมด dev: บันทึกอีเมลเป็นไฟล์ */
    private static function logToFile(string $to, string $subject, string $body, string $fromEmail, string $fromName): bool
    {
        $dir = (string) config('mail.log_path');
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }
        $file = $dir . '/' . date('Ymd_His') . '_' . substr(md5($to . microtime()), 0, 8) . '.html';
        $content = "<!-- To: {$to} | From: {$fromName} <{$fromEmail}> | Subject: {$subject} -->\n" . $body;
        return file_put_contents($file, $content) !== false;
    }
}
