SET NAMES utf8mb4;
-- ============================================================
--  Phase 3 : เพิ่มค่าตั้งค่าเริ่มต้นสำหรับ SEO/โซเชียล
--  (ตาราง users มีคอลัมน์ reset_token/reset_expires อยู่แล้วตั้งแต่ Phase 1
--   จึงไม่ต้องเพิ่มตารางสำหรับรีเซ็ตรหัสผ่าน)
-- ============================================================
USE school_pr;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('meta_keywords', 'โรงเรียน, ข่าวประชาสัมพันธ์, รับสมัครนักเรียน, กิจกรรม'),
('og_image',      ''),
('line_url',      ''),
('youtube_url',   '');
