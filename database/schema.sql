SET NAMES utf8mb4;
-- ============================================================
--  Phase 1 Schema : เว็บไซต์โรงเรียน (ฉบับ PR)
--  ตารางในเฟสนี้: users, categories, news, settings
--  charset utf8mb4 รองรับภาษาไทยและ emoji
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_pr
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_pr;

-- ---------- ผู้ใช้หลังบ้าน ----------
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(190) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    failed_attempts INT NOT NULL DEFAULT 0,       -- นับครั้งที่ล็อกอินผิด
    locked_until    DATETIME NULL,                 -- ล็อกบัญชีจนถึงเวลานี้
    reset_token     VARCHAR(64) NULL,
    reset_expires   DATETIME NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- หมวดหมู่ (Phase 1 ใช้กับข่าว) ----------
CREATE TABLE categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    slug       VARCHAR(180) NOT NULL UNIQUE,
    type       ENUM('news','download') NOT NULL DEFAULT 'news',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ข่าวประชาสัมพันธ์ ----------
CREATE TABLE news (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NULL,
    title        VARCHAR(255) NOT NULL,
    slug         VARCHAR(280) NOT NULL UNIQUE,
    excerpt      VARCHAR(500) NULL,
    content      LONGTEXT NOT NULL,
    cover_image  VARCHAR(255) NULL,
    meta_title   VARCHAR(255) NULL,
    meta_desc    VARCHAR(300) NULL,
    og_image     VARCHAR(255) NULL,
    views        INT NOT NULL DEFAULT 0,
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    author_id    INT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE SET NULL,
    INDEX idx_status_pub (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ตั้งค่าเว็บไซต์ ----------
CREATE TABLE settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  ข้อมูลตัวอย่างสำหรับทดสอบ
-- ============================================================

-- บัญชีผู้ดูแลเริ่มต้น
-- อีเมล: admin@school.ac.th   รหัสผ่าน: Admin@1234
-- (hash ด้านล่างสร้างจาก password_hash('Admin@1234', PASSWORD_DEFAULT))
INSERT INTO users (name, email, password_hash, role) VALUES
('ผู้ดูแลระบบ', 'admin@school.ac.th',
 '$2y$10$LD2mUrumDaCdSDPCEEPlceWOzloChGDPxiLCh0RexqEUXbrF8LU6S', 'super_admin');

INSERT INTO categories (name, slug, type) VALUES
('ข่าวทั่วไป',      'general',     'news'),
('กิจกรรมโรงเรียน', 'activities',  'news'),
('ประกาศ',         'announcement','news'),
('รับสมัคร',        'admission',   'news');

INSERT INTO news (category_id, title, slug, excerpt, content, status, published_at, author_id) VALUES
(2, 'กิจกรรมวันวิทยาศาสตร์ ประจำปีการศึกษา 2568',
    'science-day-2568',
    'โรงเรียนจัดกิจกรรมวันวิทยาศาสตร์ พร้อมนิทรรศการและการแข่งขันมากมาย',
    '<p>โรงเรียนขอเชิญนักเรียนและผู้ปกครองร่วมกิจกรรมวันวิทยาศาสตร์ ในวันที่ 18 สิงหาคม 2568 ณ หอประชุมโรงเรียน</p>',
    'published', NOW(), 1),
(3, 'ประกาศหยุดเรียนกรณีพิเศษ',
    'special-holiday-notice',
    'แจ้งกำหนดการหยุดเรียนกรณีพิเศษ พร้อมรายละเอียดการชดเชย',
    '<p>ด้วยโรงเรียนมีความจำเป็น จึงประกาศหยุดเรียนในวันศุกร์ที่ 25 กรกฎาคม 2568</p>',
    'published', NOW(), 1),
(4, 'เปิดรับสมัครนักเรียนใหม่ ปีการศึกษา 2569',
    'admission-2569',
    'เปิดรับสมัครนักเรียนชั้น ม.1 และ ม.4 ประจำปีการศึกษา 2569',
    '<p>โรงเรียนเปิดรับสมัครนักเรียนใหม่ ตั้งแต่บัดนี้เป็นต้นไป สอบถามเพิ่มเติมที่ฝ่ายวิชาการ</p>',
    'published', NOW(), 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name',        'โรงเรียนตัวอย่างวิทยา'),
('site_tagline',     'มุ่งมั่นพัฒนา การศึกษาก้าวไกล'),
('contact_phone',    '043-000-000'),
('contact_email',    'contact@school.ac.th'),
('contact_address',  'อำเภอเมือง จังหวัดร้อยเอ็ด 45000'),
('facebook_url',     'https://facebook.com/'),
('meta_description', 'เว็บไซต์ประชาสัมพันธ์โรงเรียนตัวอย่างวิทยา ข่าวสาร กิจกรรม และประกาศต่าง ๆ');
