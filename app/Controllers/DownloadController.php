<?php
/**
 * app/Controllers/DownloadController.php
 * --------------------------------------
 * หน้าเอกสารดาวน์โหลด + จัดการดาวน์โหลดไฟล์ (นับยอด + ส่งไฟล์อย่างปลอดภัย)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Download;

final class DownloadController extends Controller
{
    public function index(): void
    {
        $all = Download::all();

        // จัดกลุ่มตามหมวดหมู่เพื่อแสดงผล
        $grouped = [];
        foreach ($all as $doc) {
            $cat = $doc['category_name'] ?: 'ทั่วไป';
            $grouped[$cat][] = $doc;
        }

        $this->view('downloads/index', [
            'pageTitle' => 'ดาวน์โหลดเอกสาร',
            'grouped'   => $grouped,
        ]);
    }

    /** ส่งไฟล์ให้ดาวน์โหลด พร้อมนับยอด (ไม่เปิดเผย path จริง) */
    public function download(string $id): void
    {
        $doc = Download::find((int) $id);
        if ($doc === null) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'ไม่พบไฟล์'], null);
            return;
        }

        // แปลง public path เป็น path จริงบนดิสก์
        $absolute = dirname(__DIR__, 2) . '/public' . $doc['file_path'];

        // ป้องกัน path traversal: ไฟล์ต้องอยู่ในโฟลเดอร์เอกสารเท่านั้น
        $realBase = realpath(config('document.path'));
        $realFile = realpath($absolute);
        if ($realFile === false || $realBase === false || !str_starts_with($realFile, $realBase)) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'ไม่พบไฟล์'], null);
            return;
        }

        Download::incrementCount((int) $doc['id']);

        // ตั้งชื่อไฟล์ที่ดาวน์โหลดให้อ่านง่าย
        $safeName = preg_replace('/[^\p{Thai}\p{L}\p{N}\.\-_ ]/u', '', $doc['title']);
        $filename = $safeName . '.' . $doc['file_ext'];

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($realFile));
        header('X-Content-Type-Options: nosniff');
        readfile($realFile);
        exit;
    }
}
