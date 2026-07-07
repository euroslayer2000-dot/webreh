<?php

/**
 * public/index.php
 * ----------------
 * Front Controller — จุดเข้าเดียวของทั้งเว็บไซต์
 * ทุก request จะถูก .htaccess ส่งมาที่นี่
 */

declare(strict_types=1);

/* ---------- 1) โหลด config ---------- */
require dirname(__DIR__) . '/config/config.php';

/* ---------- 2) โหมด error ตาม environment ---------- */
if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

/* ---------- 3) Autoload คลาสในเนมสเปซ App\ ---------- */
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = $baseDir . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/* ---------- 4) เริ่ม session อย่างปลอดภัย ---------- */
session_set_cookie_params([
    'httponly' => true,                       // JS อ่าน cookie ไม่ได้ (กัน XSS ขโมย session)
    'samesite' => 'Lax',                      // กัน CSRF ข้ามเว็บบางส่วน
    'secure'   => !empty($_SERVER['HTTPS']),  // ส่งผ่าน HTTPS เท่านั้นเมื่อมี
]);
session_start();

use App\Core\Router;

$router = new Router();

/* ============================================================
 *  เส้นทาง (Routes)
 * ============================================================ */

// ---------- สาธารณะ ----------
$router->get('/',              'HomeController@index');
$router->get('/news',          'NewsController@index');
$router->get('/news/{slug}',   'NewsController@show');

// บุคลากร / แกลเลอรี / ดาวน์โหลด / ติดต่อ
$router->get('/personnel',       'PersonnelController@index');
$router->get('/gallery',         'GalleryController@index');
$router->get('/gallery/{slug}',  'GalleryController@show');
$router->get('/downloads',       'DownloadController@index');
$router->get('/download/{id}',   'DownloadController@download');
$router->get('/contact',         'ContactController@index');
$router->post('/contact',        'ContactController@store');
$router->get('/privacy',         'PageController@privacy');
$router->get('/sitemap.xml',     'SitemapController@index');

// ---------- หลังบ้าน: ล็อกอิน ----------
$router->get('/admin/login',   'Admin\AuthController@showLogin');
$router->post('/admin/login',  'Admin\AuthController@login');
$router->post('/admin/logout', 'Admin\AuthController@logout');
$router->get('/admin/forgot',       'Admin\AuthController@showForgot');
$router->post('/admin/forgot',      'Admin\AuthController@sendReset');
$router->get('/admin/reset/{token}',  'Admin\AuthController@showReset');
$router->post('/admin/reset/{token}', 'Admin\AuthController@resetPassword');

// ---------- หลังบ้าน: แดชบอร์ด ----------
$router->get('/admin',           'Admin\DashboardController@index');
$router->get('/admin/dashboard', 'Admin\DashboardController@index');
$router->get('/admin/settings',        'Admin\SettingController@index');
$router->post('/admin/settings/update', 'Admin\SettingController@update');

// ---------- หลังบ้าน: จัดการข่าว (CRUD) ----------
$router->get('/admin/news',              'Admin\NewsController@index');
$router->get('/admin/news/create',       'Admin\NewsController@create');
$router->post('/admin/news/store',       'Admin\NewsController@store');
$router->get('/admin/news/edit/{id}',    'Admin\NewsController@edit');
$router->post('/admin/news/update/{id}', 'Admin\NewsController@update');
$router->post('/admin/news/delete/{id}', 'Admin\NewsController@delete');

// ---------- หลังบ้าน: บุคลากร ----------
$router->get('/admin/teachers',              'Admin\TeacherController@index');
$router->get('/admin/teachers/create',       'Admin\TeacherController@create');
$router->post('/admin/teachers/store',       'Admin\TeacherController@store');
$router->get('/admin/teachers/edit/{id}',    'Admin\TeacherController@edit');
$router->post('/admin/teachers/update/{id}', 'Admin\TeacherController@update');
$router->post('/admin/teachers/delete/{id}', 'Admin\TeacherController@delete');

// ---------- หลังบ้าน: แกลเลอรี ----------
$router->get('/admin/gallery',                 'Admin\GalleryController@index');
$router->get('/admin/gallery/create',          'Admin\GalleryController@create');
$router->post('/admin/gallery/store',          'Admin\GalleryController@store');
$router->get('/admin/gallery/edit/{id}',       'Admin\GalleryController@edit');
$router->post('/admin/gallery/update/{id}',    'Admin\GalleryController@update');
$router->post('/admin/gallery/images/{id}',    'Admin\GalleryController@addImages');
$router->post('/admin/gallery/image-delete/{id}', 'Admin\GalleryController@deleteImage');
$router->post('/admin/gallery/delete/{id}',    'Admin\GalleryController@delete');

// ---------- หลังบ้าน: เอกสารดาวน์โหลด ----------
$router->get('/admin/downloads',              'Admin\DownloadController@index');
$router->get('/admin/downloads/create',       'Admin\DownloadController@create');
$router->post('/admin/downloads/store',       'Admin\DownloadController@store');
$router->post('/admin/downloads/delete/{id}', 'Admin\DownloadController@delete');

// ---------- หลังบ้าน: แบนเนอร์ ----------
$router->get('/admin/banners',              'Admin\BannerController@index');
$router->get('/admin/banners/create',       'Admin\BannerController@create');
$router->post('/admin/banners/store',       'Admin\BannerController@store');
$router->get('/admin/banners/edit/{id}',    'Admin\BannerController@edit');
$router->post('/admin/banners/update/{id}', 'Admin\BannerController@update');
$router->post('/admin/banners/delete/{id}', 'Admin\BannerController@delete');

// ---------- หลังบ้าน: กล่องข้อความ ----------
$router->get('/admin/contacts',              'Admin\ContactController@index');
$router->get('/admin/contacts/{id}',         'Admin\ContactController@show');
$router->post('/admin/contacts/delete/{id}', 'Admin\ContactController@delete');

/* ---------- ปล่อยงานให้ router ---------- */
// ตัด base path ของซับโฟลเดอร์ออกก่อนส่งให้ router (รองรับทั้งรันที่ root
// เช่น http://example.com/ และรันในซับโฟลเดอร์ เช่น http://localhost/school-website/public/)
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath));
}
if ($requestPath === '' || $requestPath === false) {
    $requestPath = '/';
}
$router->dispatch($_SERVER['REQUEST_METHOD'], $requestPath);
