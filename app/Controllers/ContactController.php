<?php
/**
 * app/Controllers/ContactController.php
 * -------------------------------------
 * ฟอร์มติดต่อสาธารณะ:
 * - CSRF
 * - honeypot กันบอท
 * - บังคับติ๊กยินยอม PDPA ก่อนส่ง (บันทึก consent_at + ip)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setting;
use App\Models\Contact;

final class ContactController extends Controller
{
    public function index(): void
    {
        $this->view('contact/index', [
            'pageTitle' => 'ติดต่อเรา',
        ]);
    }

    public function store(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/contact');
        }

        // honeypot: ช่องซ่อนที่มนุษย์จะไม่กรอก ถ้ามีค่า = บอท
        if (!empty($_POST['website'])) {
            $this->redirect('/contact');  // เงียบ ๆ ไม่บอกบอท
        }

        $name    = trim((string) ($_POST['name'] ?? ''));
        $email   = trim((string) ($_POST['email'] ?? ''));
        $phone   = trim((string) ($_POST['phone'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $consent = !empty($_POST['consent']);

        $errors = [];
        if (mb_strlen($name) < 2)    $errors[] = 'กรุณากรอกชื่อ';
        if (mb_strlen($message) < 10) $errors[] = 'ข้อความต้องมีอย่างน้อย 10 ตัวอักษร';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'อีเมลไม่ถูกต้อง';
        }
        if (!$consent) {
            $errors[] = 'กรุณายินยอมให้จัดเก็บข้อมูลก่อนส่ง';
        }

        if ($errors) {
            foreach ($errors as $e) $this->flash('error', $e);
            $this->withInput(compact('name', 'email', 'phone', 'subject', 'message'));
            $this->redirect('/contact');
        }

        Contact::create([
            'name'       => $name,
            'email'      => $email ?: null,
            'phone'      => $phone ?: null,
            'subject'    => $subject ?: null,
            'message'    => $message,
            'consent_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        self::clearOld();
        $this->flash('success', 'ส่งข้อความเรียบร้อยแล้ว ทางโรงเรียนจะติดต่อกลับโดยเร็ว');
        $this->redirect('/contact');
    }
}
