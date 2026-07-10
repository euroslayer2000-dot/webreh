SET NAMES utf8mb4;
-- ============================================================
--  Phase 4 : ระบบจัดการผู้ใช้งาน (Admin-created accounts) + บทบาท teacher
--  - เพิ่มบทบาท 'teacher' ใน ENUM ของ role (เดิมมีแค่ super_admin, editor)
--  - สิทธิ์ของ teacher เท่ากับ editor ในตอนนี้ (จัดการเนื้อหาได้ทุกอย่าง
--    แต่เข้าถึงตั้งค่าเว็บไซต์ / จัดการผู้ใช้งานไม่ได้) แยกป้ายไว้เพื่อความ
--    ชัดเจนของบทบาทหน้าที่ ไม่ใช่สิทธิ์ที่ต่างกัน
-- ============================================================
USE school_pr;

ALTER TABLE users
    MODIFY COLUMN role ENUM('super_admin','editor','teacher') NOT NULL DEFAULT 'editor';
