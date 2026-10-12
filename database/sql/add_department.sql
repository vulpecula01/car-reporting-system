-- ใช้แทน php artisan migrate ได้ (รันใน MySQL ฐานข้อมูล xray)
-- เพิ่มคอลัมน์ department ให้ users, cars, events  (ค่าเดิมทั้งหมด = 'xray')

ALTER TABLE users  ADD COLUMN department VARCHAR(20) NOT NULL DEFAULT 'xray', ADD INDEX users_department_index (department);
ALTER TABLE cars   ADD COLUMN department VARCHAR(20) NOT NULL DEFAULT 'xray', ADD INDEX cars_department_index (department);
ALTER TABLE events ADD COLUMN department VARCHAR(20) NOT NULL DEFAULT 'xray', ADD INDEX events_department_index (department);

UPDATE events e JOIN cars c ON c.id = e.car_id SET e.department = c.department;

-- ===== ตัวอย่าง: สร้าง user สำหรับแผนก Checkup =====
-- (รหัสผ่านต้องเป็น bcrypt hash — สร้างได้ด้วย: php artisan tinker --execute="echo bcrypt('รหัสผ่าน');")
-- INSERT INTO users (name, email, password, department, created_at, updated_at)
-- VALUES ('checkup', 'checkup@local', '$2y$12$....', 'checkup', NOW(), NOW());

-- หรือเปลี่ยน user ที่มีอยู่ให้เป็นแผนก Checkup
-- UPDATE users SET department = 'checkup' WHERE name = 'checkup';

-- ย้ายรถคันที่เป็นของ Checkup
-- UPDATE cars SET department = 'checkup' WHERE id IN (...);
