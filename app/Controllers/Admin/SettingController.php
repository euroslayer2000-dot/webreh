<?php
/**
 * app/Controllers/Admin/SettingController.php
 * -------------------------------------------
 * จัดการตั้งค่าเว็บไซต์ + SEO (ชื่อเว็บ, ข้อมูลติดต่อ, social,
 * meta description เริ่มต้น, รูป OG เริ่มต้นสำหรับแชร์โซเชียล)
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Models\Setting;

final class SettingController extends Controller
{
    /** คีย์ข้อความที่อนุญาตให้แก้ไข */
    private const TEXT_KEYS = [
        'site_name', 'site_tagline', 'contact_phone', 'contact_email',
        'contact_address', 'facebook_url', 'line_url', 'youtube_url',
        'meta_description', 'meta_keywords',
    ];

    public function index(): void
    {
        Auth::requireRole(['super_admin']);
        $this->view('admin/settings/index', [
            'pageTitle' => 'ตั้งค่าเว็บไซต์',
            'settings'  => Setting::all(),
        ], 'layouts/admin');
    }

    public function update(): void
    {
        Auth::requireRole(['super_admin']);
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/settings');
        }

        $pairs = [];
        foreach (self::TEXT_KEYS as $key) {
            $pairs[$key] = trim((string) ($_POST[$key] ?? ''));
        }

        // รูป OG เริ่มต้น (อัปโหลดใหม่เพื่อเปลี่ยน)
        try {
            $og = Uploader::image($_FILES['og_image'] ?? [], 'brand');
            if ($og !== null) {
                $pairs['og_image'] = $og;
            }
        } catch (UploadException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/settings');
        }

        Setting::setMany($pairs);
        $this->flash('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
        $this->redirect('/admin/settings');
    }
}
