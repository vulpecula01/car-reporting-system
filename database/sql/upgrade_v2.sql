-- ===== Upgrade V2: user roles + activity log =====
-- รันใน MySQL ฐานข้อมูล xray (หลังจากรัน add_department.sql แล้ว)

-- 1) สิทธิ์ผู้ใช้: admin = จัดการ user ได้ / ดู log ทุกแผนก
ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER department;
UPDATE users SET role = 'admin' WHERE id = 1;   -- ตั้ง user id 1 (Anurak) เป็น admin  (เปลี่ยนได้)

-- 2) ตารางประวัติการใช้งาน (ใคร ทำอะไร เมื่อไหร่)
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(255) NULL,
    department VARCHAR(20) NULL,
    action VARCHAR(30) NOT NULL,          -- create / update / delete / copy / login / logout ...
    subject_type VARCHAR(50) NULL,        -- event / car / company / machine / user
    subject_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    changes JSON NULL,                    -- {"field": ["ค่าเดิม", "ค่าใหม่"]}
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX activity_logs_department_index (department),
    INDEX activity_logs_created_at_index (created_at),
    INDEX activity_logs_subject_index (subject_type, subject_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
