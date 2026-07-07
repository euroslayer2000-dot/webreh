SET NAMES utf8mb4;
-- ============================================================
--  Phase 2 Migration : เพิ่มระบบเนื้อหา
--  ตาราง: teachers, galleries, gallery_images, downloads, banners, contacts
--  รันต่อจาก schema.sql (Phase 1) — ใช้ฐานข้อมูล school_pr เดิม
-- ============================================================

USE school_pr;

-- ---------- บุคลากร ----------
CREATE TABLE teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    position    VARCHAR(150) NULL,           -- ตำแหน่ง เช่น ครูชำนาญการ
    department  VARCHAR(150) NULL,           -- กลุ่มสาระ
    photo       VARCHAR(255) NULL,
    email       VARCHAR(190) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dept (department),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- อัลบั้มแกลเลอรี ----------
CREATE TABLE galleries (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    slug        VARCHAR(280) NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    cover_image VARCHAR(255) NULL,
    event_date  DATE NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- รูปในอัลบั้ม ----------
CREATE TABLE gallery_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    gallery_id  INT NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    caption     VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- เอกสารดาวน์โหลด ----------
CREATE TABLE downloads (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    category_id    INT NULL,
    title          VARCHAR(255) NOT NULL,
    file_path      VARCHAR(255) NOT NULL,
    file_ext       VARCHAR(10) NULL,
    file_size      INT NULL,                 -- byte
    download_count INT NOT NULL DEFAULT 0,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- แบนเนอร์หน้าแรก ----------
CREATE TABLE banners (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NULL,
    image_path  VARCHAR(255) NOT NULL,
    link_url    VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- ข้อความจากฟอร์มติดต่อ ----------
CREATE TABLE contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(190) NULL,
    phone      VARCHAR(30) NULL,
    subject    VARCHAR(200) NULL,
    message    TEXT NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    -- PDPA: บันทึกการยินยอมให้เก็บข้อมูล
    consent_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- เพิ่มหมวดหมู่เอกสารตัวอย่าง (type = download)
INSERT INTO categories (name, slug, type) VALUES
('แบบฟอร์มราชการ', 'gov-forms',  'download'),
('เอกสารวิชาการ',   'academic-docs', 'download');

-- ข้อมูลตัวอย่างบุคลากร
INSERT INTO teachers (name, position, department, sort_order) VALUES
('นายสมชาย ใจดี',   'ผู้อำนวยการโรงเรียน', 'ฝ่ายบริหาร',        1),
('นางสาวสมหญิง รักเรียน', 'รองผู้อำนวยการ',   'ฝ่ายวิชาการ',       2),
('นายวิชัย สอนเก่ง',  'ครูชำนาญการพิเศษ',  'กลุ่มสาระวิทยาศาสตร์', 3),
('นางมาลี ภาษางาม',  'ครูชำนาญการ',      'กลุ่มสาระภาษาไทย',    4);

-- อัลบั้มตัวอย่าง
INSERT INTO galleries (title, slug, description, event_date) VALUES
('กิจกรรมวันวิทยาศาสตร์ 2568', 'science-day-album-2568', 'ภาพบรรยากาศงานวันวิทยาศาสตร์', '2025-08-18'),
('พิธีไหว้ครู ประจำปี 2568', 'wai-kru-2568', 'พิธีไหว้ครูและมอบทุนการศึกษา', '2025-06-12');
