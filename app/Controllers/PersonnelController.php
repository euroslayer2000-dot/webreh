<?php
/**
 * app/Controllers/PersonnelController.php
 * ---------------------------------------
 * หน้าบุคลากรสาธารณะ จัดกลุ่มตามกลุ่มสาระ
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Teacher;

final class PersonnelController extends Controller
{
    public function index(): void
    {
        $this->view('personnel/index', [
            'pageTitle' => 'บุคลากร',
            'groups'    => Teacher::groupedByDepartment(),
        ]);
    }
}
