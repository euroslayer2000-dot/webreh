<?php
/**
 * app/Controllers/Admin/ContactController.php
 * -------------------------------------------
 * กล่องข้อความติดต่อ: ดูรายการ อ่าน ลบ
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Contact;

final class ContactController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('admin/contacts/index', [
            'pageTitle' => 'กล่องข้อความ',
            'contacts'  => Contact::all(),
        ], 'layouts/admin');
    }

    public function show(string $id): void
    {
        Auth::require();
        $contact = Contact::find((int) $id);
        if ($contact === null) {
            $this->flash('error', 'ไม่พบข้อความ');
            $this->redirect('/admin/contacts');
        }
        // อ่านแล้ว = ทำเครื่องหมายอัตโนมัติ
        if ((int) $contact['is_read'] === 0) {
            Contact::markRead((int) $id);
        }
        $this->view('admin/contacts/show', [
            'pageTitle' => 'ข้อความจาก ' . $contact['name'],
            'contact'   => $contact,
        ], 'layouts/admin');
    }

    public function delete(string $id): void
    {
        Auth::require();
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'เซสชันหมดอายุ กรุณาลองใหม่');
            $this->redirect('/admin/contacts');
        }
        Contact::delete((int) $id);
        $this->flash('success', 'ลบข้อความเรียบร้อยแล้ว');
        $this->redirect('/admin/contacts');
    }
}
