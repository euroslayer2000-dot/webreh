# เว็บไซต์โรงเรียน (PR) — Phase 1 + 2 + 3

โค้ดพร้อมใช้งานจริง รันบน XAMPP/MAMP ได้ทันที

**Phase 1:** โครงสร้าง MVC + ระบบล็อกอินหลังบ้าน (ล็อกบัญชีกัน brute force) + ระบบข่าว (CRUD) + หน้าแรก + ความปลอดภัยพื้นฐาน (CSRF, PDO Prepared, Validation) + Dark Mode

**Phase 2:** บุคลากร + แกลเลอรีภาพ (พร้อม lightbox) + ดาวน์โหลดเอกสาร + แบนเนอร์สไลด์หน้าแรก + ฟอร์มติดต่อพร้อม PDPA consent (+ honeypot กันบอท) + กล่องข้อความหลังบ้าน

**Phase 3:** SEO (`sitemap.xml` ไดนามิก + robots.txt + จัดการ Meta/OG จากหลังบ้าน) + ย่อ/บีบอัดรูปอัตโนมัติตอนอัปโหลด + ลืมรหัสผ่าน/รีเซ็ตทางอีเมล + Cookie consent + หน้านโยบายความเป็นส่วนตัว

> ผ่านการทดสอบ end-to-end จริง (เชื่อม MySQL, ล็อกอิน, ฟอร์มติดต่อ, ล็อกบัญชี, ทุกหน้า 200) แล้ว

---

## สิ่งที่ต้องมี

- PHP 8.1 ขึ้นไป (แนะนำ 8.2/8.3)
- MySQL 5.7+ หรือ MariaDB 10.4+
- Apache ที่เปิด `mod_rewrite` (XAMPP/MAMP มีให้อยู่แล้ว)
- PHP extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `gd` (ทั้งหมดมากับ XAMPP/MAMP โดยค่าเริ่มต้น — ถ้าติดตั้ง PHP เองต้องเปิดใช้ใน php.ini) — `gd` ใช้สำหรับย่อ/บีบอัดรูป (Phase 3)

---

## ติดตั้งบนเครื่อง (XAMPP / MAMP)

### 1. วางไฟล์
คัดลอกโฟลเดอร์ `school-website` ไปไว้ใน `htdocs` (XAMPP) หรือ `htdocs`/`www` (MAMP)

### 2. สร้างฐานข้อมูล
เปิด phpMyAdmin แล้ว **Import** ไฟล์ตามลำดับ:
1. `database/schema.sql` — สร้างฐานข้อมูล `school_pr` + ตาราง Phase 1 + ข้อมูลตัวอย่าง
2. `database/schema_phase2.sql` — เพิ่มตาราง Phase 2 (teachers, galleries, gallery_images, downloads, banners, contacts) + ข้อมูลตัวอย่าง
3. `database/schema_phase3.sql` — เพิ่มค่าตั้งค่าเริ่มต้นสำหรับ SEO/โซเชียล (meta_keywords, og_image, line_url, youtube_url)

> ทั้งสองไฟล์ขึ้นต้นด้วย `SET NAMES utf8mb4;` แล้ว จึง import ผ่าน command line ได้โดยไม่เพี้ยนภาษาไทย เช่น
> `mysql -u root < database/schema.sql && mysql -u root school_pr < database/schema_phase2.sql && mysql -u root school_pr < database/schema_phase3.sql`

### 3. ตั้งค่า `.env`
คัดลอก `.env.example` เป็น `.env` แล้วแก้ให้ตรงกับเครื่อง:

```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/school-website/public

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=school_pr
DB_USER=root
DB_PASS=          # MAMP ปกติรหัสคือ root
```

> **สำคัญ:** `APP_URL` ต้องชี้ไปที่โฟลเดอร์ `public` เสมอ

### 4. เปิดใช้งาน
เข้า `http://localhost/school-website/public/`

---

## เข้าสู่ระบบหลังบ้าน

- URL: `http://localhost/school-website/public/admin/login`
- อีเมล: `admin@school.ac.th`
- รหัสผ่าน: `Admin@1234`

> เปลี่ยนรหัสผ่านนี้ทันทีเมื่อขึ้นใช้งานจริง — ใช้เมนู **"ลืมรหัสผ่าน"** ที่หน้า login เพื่อรีเซ็ตทางอีเมลได้ (Phase 3)
> หรือแก้โดยตรงด้วย `php -r 'echo password_hash("รหัสใหม่", PASSWORD_DEFAULT);'` แล้วนำ hash ไปอัปเดตในตาราง `users`

---

## ตั้งค่าความปลอดภัยตอน Deploy ขึ้น Hosting จริง

1. **ตั้ง Document Root ไปที่ `public/` เท่านั้น** เพื่อไม่ให้ภายนอกเข้าถึง `app/`, `config/`, `.env`
2. ตั้ง `APP_DEBUG=false` และ `APP_ENV=production` ใน `.env`
3. ใช้ HTTPS (cookie จะตั้ง `secure` อัตโนมัติเมื่อเป็น HTTPS)
4. อย่า commit ไฟล์ `.env` ขึ้น git (มี `.gitignore` ให้แล้ว)
5. ตั้งสิทธิ์โฟลเดอร์ `public/assets/uploads` ให้เขียนได้ (เช่น 755)

หากโฮสต์ไม่ให้ย้าย document root ให้ปรับ `RewriteBase` ใน `public/.htaccess` ตามพาธจริง

---

## โครงสร้างโปรเจกต์

```
school-website/
├── public/              ← Document Root (จุดเข้าเดียว)
│   ├── index.php        ← Front Controller + routes
│   ├── .htaccess
│   └── assets/          ← css, js, รูปอัปโหลด
├── app/
│   ├── Core/            ← Router, Database, Controller, Auth, Csrf
│   ├── Controllers/     ← Home, News, Admin/{Auth,Dashboard,News}
│   ├── Models/          ← News, User, Category, Setting
│   └── Views/           ← layouts, home, news, admin, errors
├── config/config.php    ← อ่านค่าจาก .env
├── database/
│   ├── schema.sql         ← ตาราง Phase 1 + ข้อมูลตัวอย่าง
│   ├── schema_phase2.sql  ← ตาราง Phase 2 + ข้อมูลตัวอย่าง
│   └── schema_phase3.sql  ← ค่าตั้งค่า SEO/โซเชียลเริ่มต้น (Phase 3)
└── .env                 ← ค่าลับ (ไม่ commit)
```

---

## สิ่งที่มีใน Phase 3 (เพิ่มจาก Phase 2)

- ✅ **SEO — sitemap.xml** สร้างอัตโนมัติจากเนื้อหาจริง (หน้าเว็บ + ข่าวทุกข่าว + อัลบั้มทุกอัลบั้ม) เข้าถึงที่ `/sitemap.xml`
- ✅ **robots.txt** — อนุญาต bot เก็บ index หน้าเว็บ, กันหน้า `/admin/`, อ้างถึง sitemap
- ✅ **จัดการ Meta / Open Graph จากหลังบ้าน** — ตั้งชื่อเว็บ, meta description, meta keywords, รูป OG (ที่แชร์บนโซเชียล), ข้อมูลติดต่อ, ลิงก์โซเชียล ที่ `/admin/settings` แล้วสะท้อนบนทุกหน้าอัตโนมัติ
- ✅ **ย่อ/บีบอัดรูปอัตโนมัติตอนอัปโหลด** — รูปที่กว้างเกิน 1600px จะถูกย่อลงและบีบอัด (JPEG คุณภาพ 82) เพื่อให้หน้าเว็บโหลดเร็ว ประหยัดพื้นที่
- ✅ **ลืมรหัสผ่าน / รีเซ็ตทางอีเมล** — ขอลิงก์รีเซ็ตทางอีเมล, โทเค็นมีอายุจำกัดและใช้ได้ครั้งเดียว (`/admin/forgot`)
- ✅ **Cookie consent banner (PDPA)** — แถบแจ้งเตือนคุกกี้ จำการยอมรับไว้ ไม่รบกวนซ้ำ
- ✅ **หน้านโยบายความเป็นส่วนตัว** — `/privacy` อธิบายการเก็บ/ใช้ข้อมูลตาม PDPA

> **หมายเหตุเรื่องอีเมล:** โหมด dev (`APP_ENV=local`) จะ**เขียนอีเมลลงไฟล์** ที่ `storage/mail/` แทนการส่งจริง (เปิดไฟล์ .html เพื่อดูลิงก์รีเซ็ตได้เลย) เมื่อขึ้น production (`APP_ENV=production`) จะใช้ฟังก์ชัน `mail()` ของ PHP — แนะนำเปลี่ยนไปใช้ SMTP/PHPMailer สำหรับงานจริง

## สิ่งที่มีใน Phase 2 (เพิ่มจาก Phase 1)

- ✅ **บุคลากร** — หน้าเว็บจัดกลุ่มตามกลุ่มสาระ + หลังบ้าน CRUD พร้อมรูปและลำดับการแสดง
- ✅ **แกลเลอรีภาพ** — อัลบั้ม + อัปโหลดหลายรูปพร้อมกัน + lightbox เลื่อนดูรูปใหญ่ (คีย์บอร์ด/ปุ่มลูกศร)
- ✅ **ดาวน์โหลดเอกสาร** — อัปโหลด PDF/Word/Excel, จัดกลุ่มตามหมวด, นับยอดดาวน์โหลด, ส่งไฟล์แบบกัน path traversal
- ✅ **แบนเนอร์สไลด์หน้าแรก** — CRUD + เปิด/ปิด + จัดลำดับ + สไลด์อัตโนมัติ
- ✅ **ฟอร์มติดต่อ + PDPA** — บังคับติ๊กยินยอมก่อนส่ง, บันทึกเวลา+IP ที่ยินยอม, honeypot กันบอท, Google Map
- ✅ **กล่องข้อความหลังบ้าน** — อ่าน/ลบ, ทำเครื่องหมายอ่านแล้วอัตโนมัติ, badge นับข้อความใหม่บนเมนู
- ✅ ตัวช่วยอัปโหลดกลาง (`Uploader`) ตรวจ MIME จริงด้วย finfo + สุ่มชื่อไฟล์ ใช้ซ้ำทุกระบบ

## สิ่งที่มีใน Phase 1

- ✅ โครงสร้าง MVC น้ำหนักเบา (Router + PDO + View engine)
- ✅ หน้าแรก: Hero, สถิติ (animated counter), ข่าวล่าสุด, Quick Links, Footer
- ✅ หน้ารายการข่าว + ค้นหา + แบ่งหน้า + หน้าอ่านข่าว (นับยอดชม)
- ✅ ระบบล็อกอิน: bcrypt, CSRF, ล็อกบัญชี 15 นาที เมื่อผิด 5 ครั้ง
- ✅ แดชบอร์ด + จัดการข่าว CRUD ครบ (CKEditor, อัปโหลดรูปตรวจ MIME จริง)
- ✅ Dark Mode (จำค่าไว้ใน localStorage)
- ✅ ความปลอดภัย: PDO Prepared ทุก query, escape output กัน XSS, security headers

## ยังไม่รวม (อยู่ใน Phase 4 ตามสเปก)

- ฟีเจอร์ AI (เช่น ผู้ช่วยตอบคำถาม, สรุปข่าวอัตโนมัติ ฯลฯ) ตามสเปกเดิม

---

## หมายเหตุด้านความปลอดภัยของเนื้อหาข่าว

เนื้อหาข่าวรับ HTML จาก CKEditor และแสดงผลแบบไม่ escape (ตั้งใจ เพื่อให้จัดรูปแบบได้)
เพื่อความปลอดภัยสูงสุดบน production ควรเพิ่ม **HTMLPurifier** กรอง HTML ตอนบันทึก
(`composer require ezyang/htmlpurifier`) — จุดที่ต้องเสริมมีคอมเมนต์กำกับไว้ใน
`app/Controllers/Admin/NewsController.php`
