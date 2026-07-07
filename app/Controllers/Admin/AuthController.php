<?php
/**
 * app/Controllers/Admin/AuthController.php
 * ----------------------------------------
 * ระบบล็อกอินหลังบ้าน:
 * - ป้องกัน CSRF
 * - password_verify (bcrypt)
 * - ล็อกบัญชีชั่วคราวเมื่อกรอกผิดหลายครั้ง (กัน brute force)
 * - ข้อความ error แบบรวม (ไม่บอกว่า email หรือ password ผิด) กัน user enumeration
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Models\User;

final class AuthController extends Controller
{
    /** แสดงฟอร์มล็อกอิน */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
        }
        $this->view('admin/auth/login', ['pageTitle' => 'เข้าสู่ระบบ'], null);
    }

    /** ประมวลผลการล็อกอิน */
    public function login(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/login');
        }

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->flash('error', 'กรุณากรอกอีเมลและรหัสผ่าน');
            $this->withInput(['email' => $email]);
            $this->redirect('/admin/login');
        }

        $user = User::findByEmail($email);

        // บัญชีถูกล็อกอยู่
        if ($user && User::isLocked($user)) {
            $mins = User::lockRemaining($user);
            $this->flash('error', "บัญชีถูกล็อกชั่วคราว กรุณารอ {$mins} นาที");
            $this->redirect('/admin/login');
        }

        // ตรวจรหัสผ่าน (verify แม้ไม่พบ user เพื่อให้เวลาตอบสนองใกล้เคียงกัน)
        $valid = $user
            && (int) $user['is_active'] === 1
            && password_verify($password, $user['password_hash']);

        if (!$valid) {
            if ($user) {
                User::registerFailure($user);
            }
            $this->flash('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            $this->withInput(['email' => $email]);
            $this->redirect('/admin/login');
        }

        User::registerSuccess((int) $user['id']);
        Auth::login($user);
        self::clearOld();
        $this->flash('success', 'ยินดีต้อนรับ ' . $user['name']);
        $this->redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/admin/login');
    }

    // ==================== ลืม/รีเซ็ตรหัสผ่าน (Phase 3) ====================

    public function showForgot(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
        }
        $this->view('admin/auth/forgot', ['pageTitle' => 'ลืมรหัสผ่าน'], null);
    }

    public function sendReset(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/forgot');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $user  = $email !== '' ? User::findByEmail($email) : null;

        // ถ้าพบบัญชี: สร้าง token แล้วส่งอีเมล (ไม่บอกผู้ใช้ว่ามี/ไม่มีบัญชี กัน enumeration)
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 ชั่วโมง
            User::setResetToken((int) $user['id'], $token, $expires);

            $link = config('app.url') . '/admin/reset/' . $token;
            $body = '<div style="font-family:sans-serif;line-height:1.7">'
                  . '<h2>ตั้งรหัสผ่านใหม่</h2>'
                  . '<p>เรียน ' . Controller::e($user['name']) . '</p>'
                  . '<p>มีการขอตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณ คลิกลิงก์ด้านล่างเพื่อดำเนินการ (ลิงก์หมดอายุใน 1 ชั่วโมง):</p>'
                  . '<p><a href="' . $link . '" style="background:#B01E28;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none">ตั้งรหัสผ่านใหม่</a></p>'
                  . '<p>หรือคัดลอกลิงก์นี้: <br>' . $link . '</p>'
                  . '<p style="color:#888;font-size:.9em">หากคุณไม่ได้ร้องขอ กรุณาเพิกเฉยต่ออีเมลนี้</p></div>';

            Mailer::send($user['email'], 'ตั้งรหัสผ่านใหม่ — ระบบเว็บไซต์โรงเรียน', $body);
        }

        $this->flash('success', 'หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์ตั้งรหัสผ่านใหม่ไปให้แล้ว');
        $this->redirect('/admin/login');
    }

    public function showReset(string $token): void
    {
        $user = User::findByValidResetToken($token);
        if ($user === null) {
            $this->flash('error', 'ลิงก์รีเซ็ตไม่ถูกต้องหรือหมดอายุแล้ว');
            $this->redirect('/admin/forgot');
        }
        $this->view('admin/auth/reset', [
            'pageTitle' => 'ตั้งรหัสผ่านใหม่',
            'token'     => $token,
        ], null);
    }

    public function resetPassword(string $token): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/reset/' . $token);
        }

        $user = User::findByValidResetToken($token);
        if ($user === null) {
            $this->flash('error', 'ลิงก์รีเซ็ตไม่ถูกต้องหรือหมดอายุแล้ว');
            $this->redirect('/admin/forgot');
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if (mb_strlen($password) < 8) {
            $this->flash('error', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
            $this->redirect('/admin/reset/' . $token);
        }
        if ($password !== $confirm) {
            $this->flash('error', 'รหัสผ่านทั้งสองช่องไม่ตรงกัน');
            $this->redirect('/admin/reset/' . $token);
        }

        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $this->flash('success', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว กรุณาเข้าสู่ระบบ');
        $this->redirect('/admin/login');
    }
}
