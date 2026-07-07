<?php
/**
 * app/Core/Uploader.php
 * ---------------------
 * ตัวช่วยอัปโหลดไฟล์แบบใช้ซ้ำได้ ตรวจสอบความปลอดภัย:
 * - ตรวจ error / ขนาดไฟล์
 * - ตรวจ MIME จากเนื้อไฟล์จริงด้วย finfo (ไม่เชื่อ extension)
 * - สุ่มชื่อไฟล์กันชนและกัน path traversal
 * - สร้างโฟลเดอร์ปลายทางอัตโนมัติ
 *
 * คืน public URL เมื่อสำเร็จ, คืน null เมื่อไม่มีไฟล์,
 * หรือโยน UploadException เมื่อไฟล์ไม่ผ่านเงื่อนไข
 */

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class UploadException extends RuntimeException {}

final class Uploader
{
    /**
     * อัปโหลดรูปภาพลงโฟลเดอร์ย่อยที่กำหนด (news|teachers|gallery|banners)
     */
    public static function image(array $file, string $dirKey): ?string
    {
        if (self::isEmpty($file)) {
            return null;
        }
        self::guardBasics($file, (int) config('upload.max_size'), 'รูปภาพ');

        $mime = self::detectMime($file['tmp_name']);
        if (!in_array($mime, config('upload.mime'), true)) {
            throw new UploadException('อนุญาตเฉพาะไฟล์รูป JPG, PNG, WEBP');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'bin',
        };

        $publicDir = config('upload.dirs')[$dirKey] ?? config('upload.public_url');
        $absDir    = dirname(__DIR__, 2) . '/public' . $publicDir;

        $url = self::store($file['tmp_name'], $absDir, $publicDir, $ext);

        // Phase 3: ย่อขนาด + บีบอัดรูปเพื่อให้เว็บโหลดเร็ว
        self::optimize($absDir . '/' . basename($url), $mime);

        return $url;
    }

    /**
     * อัปโหลดเอกสาร (pdf/doc/docx/xls/xlsx) คืน [public_url, ext, size]
     */
    public static function document(array $file): ?array
    {
        if (self::isEmpty($file)) {
            return null;
        }
        self::guardBasics($file, (int) config('document.max_size'), 'เอกสาร');

        $mime = self::detectMime($file['tmp_name']);
        if (!in_array($mime, config('document.mime'), true)) {
            throw new UploadException('อนุญาตเฉพาะไฟล์ PDF, DOC, DOCX, XLS, XLSX');
        }

        // หา extension จากชื่อไฟล์เดิม แล้วตรวจกับ whitelist
        $origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($origExt, config('document.ext'), true)) {
            throw new UploadException('นามสกุลไฟล์ไม่ถูกต้อง');
        }

        $publicDir = config('document.public_url');
        $absDir    = config('document.path');
        $url = self::store($file['tmp_name'], $absDir, $publicDir, $origExt);

        return ['url' => $url, 'ext' => $origExt, 'size' => (int) $file['size']];
    }

    // ---------- ภายใน ----------

    private static function isEmpty(array $file): bool
    {
        return empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;
    }

    private static function guardBasics(array $file, int $maxSize, string $label): void
    {
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            throw new UploadException("อัปโหลด{$label}ไม่สำเร็จ");
        }
        if ($file['size'] > $maxSize) {
            $mb = round($maxSize / 1024 / 1024);
            throw new UploadException("ไฟล์{$label}ใหญ่เกิน {$mb} MB");
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new UploadException('ไฟล์ไม่ถูกต้อง');
        }
    }

    private static function detectMime(string $tmp): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: 'application/octet-stream';
    }

    private static function store(string $tmp, string $absDir, string $publicDir, string $ext): string
    {
        if (!is_dir($absDir) && !mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            throw new UploadException('สร้างโฟลเดอร์ปลายทางไม่สำเร็จ');
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $absDir . '/' . $name;

        if (!move_uploaded_file($tmp, $dest)) {
            throw new UploadException('บันทึกไฟล์ไม่สำเร็จ');
        }
        return $publicDir . '/' . $name;
    }

    /**
     * Phase 3 — image optimization:
     * ย่อรูปที่กว้างเกิน max_width และ re-encode เพื่อบีบอัด (ตัด metadata ทิ้งด้วย)
     * ถ้าไม่มีส่วนขยาย GD หรือรูปเสีย จะข้ามอย่างเงียบ ๆ (ไม่ทำให้อัปโหลดล้มเหลว)
     */
    private static function optimize(string $absPath, string $mime): void
    {
        if (!extension_loaded('gd') || !is_file($absPath)) {
            return;
        }

        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absPath),
            'image/png'  => @imagecreatefrompng($absPath),
            'image/webp' => @imagecreatefromwebp($absPath),
            default      => false,
        };
        if (!$src) {
            return;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $maxW = (int) config('upload.max_width', 1600);

        // ย่อขนาดถ้ากว้างเกิน
        if ($w > $maxW) {
            $newW = $maxW;
            $newH = (int) round($h * $maxW / $w);
            $dst = imagecreatetruecolor($newW, $newH);

            // รักษาความโปร่งใสสำหรับ PNG/WEBP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        // re-encode (บีบอัด + ตัด metadata)
        match ($mime) {
            'image/jpeg' => imagejpeg($src, $absPath, (int) config('upload.jpeg_quality', 82)),
            'image/png'  => imagepng($src, $absPath, 6),
            'image/webp' => imagewebp($src, $absPath, (int) config('upload.webp_quality', 82)),
            default      => null,
        };
        imagedestroy($src);
    }
}
