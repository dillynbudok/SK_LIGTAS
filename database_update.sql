USE sk_ligtas;

CREATE TABLE IF NOT EXISTS report_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    sent_by INT NOT NULL,
    sms_status VARCHAR(50) NOT NULL DEFAULT 'Not Sent',
    provider_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(report_id),
    INDEX(sent_by)
);

SET @has_logo := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contacts' AND COLUMN_NAME='logo'
);
SET @sql := IF(@has_logo=0,
    'ALTER TABLE contacts ADD COLUMN logo VARCHAR(255) DEFAULT NULL AFTER phone',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- Compatibility migration for older SK LIGTAS schemas.
USE sk_ligtas;

SET @db := DATABASE();

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='reports' AND COLUMN_NAME='contact')=0,
 'ALTER TABLE reports ADD COLUMN contact VARCHAR(50) DEFAULT '''' AFTER name',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='reports' AND COLUMN_NAME='phone')=1,
 'UPDATE reports SET contact=phone WHERE contact IS NULL OR contact=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='contacts' AND COLUMN_NAME='position')=0,
 'ALTER TABLE contacts ADD COLUMN position VARCHAR(150) NOT NULL DEFAULT '''' AFTER name',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='contacts' AND COLUMN_NAME='role')=1,
 'UPDATE contacts SET position=role WHERE position IS NULL OR position=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='contacts' AND COLUMN_NAME='location')=0,
 'ALTER TABLE contacts ADD COLUMN location VARCHAR(255) DEFAULT ''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='contacts' AND COLUMN_NAME='address')=1,
 'UPDATE contacts SET location=address WHERE location IS NULL OR location=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='hospitals' AND COLUMN_NAME='location')=0,
 'ALTER TABLE hospitals ADD COLUMN location VARCHAR(255) NOT NULL DEFAULT '''' AFTER name',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='hospitals' AND COLUMN_NAME='address')=1,
 'UPDATE hospitals SET location=address WHERE location IS NULL OR location=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='evacuation_centers' AND COLUMN_NAME='location')=0,
 'ALTER TABLE evacuation_centers ADD COLUMN location VARCHAR(255) NOT NULL DEFAULT '''' AFTER name',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='evacuation_centers' AND COLUMN_NAME='address')=1,
 'UPDATE evacuation_centers SET location=address WHERE location IS NULL OR location=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='admins' AND COLUMN_NAME='password')=0,
 'ALTER TABLE admins ADD COLUMN password VARCHAR(255) NULL',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='admins' AND COLUMN_NAME='password_hash')=1,
 'UPDATE admins SET password=password_hash WHERE password IS NULL OR password=''''',
 'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- GPS coordinates for emergency reports
SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='reports' AND COLUMN_NAME='latitude')=0,
 'ALTER TABLE reports ADD COLUMN latitude DECIMAL(10,7) DEFAULT NULL AFTER location',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
 (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='reports' AND COLUMN_NAME='longitude')=0,
 'ALTER TABLE reports ADD COLUMN longitude DECIMAL(10,7) DEFAULT NULL AFTER latitude',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS report_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    sent_by INT NOT NULL,
    sms_status VARCHAR(50) NOT NULL DEFAULT 'Not Sent',
    provider_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_messages_report (report_id),
    INDEX idx_report_messages_admin (sent_by)
);
