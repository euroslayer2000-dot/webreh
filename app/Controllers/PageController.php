<?php
/**
 * app/Controllers/PageController.php
 * ----------------------------------
 * หน้าคงที่ เช่น นโยบายความเป็นส่วนตัว (PDPA)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PageController extends Controller
{
    public function privacy(): void
    {
        $this->view('page/privacy', [
            'pageTitle' => 'นโยบายความเป็นส่วนตัว',
            'metaDesc'  => 'นโยบายความเป็นส่วนตัวและการคุ้มครองข้อมูลส่วนบุคคล (PDPA) ของโรงเรียน',
        ]);
    }
}
