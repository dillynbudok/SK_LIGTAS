CREATE DATABASE IF NOT EXISTS sk_ligtas
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sk_ligtas;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL DEFAULT 'Barangay',
    name VARCHAR(150) NOT NULL,
    position VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    distance VARCHAR(50) DEFAULT '',
    status VARCHAR(50) DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(30) DEFAULT 'info',
    alert_date VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS evacuation_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS first_aid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    name VARCHAR(150) DEFAULT '',
    contact VARCHAR(50) DEFAULT '',
    location VARCHAR(255) DEFAULT '',
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    description TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    site_name VARCHAR(200) DEFAULT 'SK LIGTAS',
    municipality VARCHAR(200) DEFAULT 'Poblacion Este, Sta. Cruz, Ilocos Sur',
    emergency_number VARCHAR(50) DEFAULT '911',
    tagline VARCHAR(255) DEFAULT 'Isang Tap. Isang Tawag. Agarang Tulong.'
);

INSERT INTO settings
(id, site_name, municipality, emergency_number, tagline)
VALUES
(1, 'SK LIGTAS', 'Poblacion Este, Sta. Cruz, Ilocos Sur', '911', 'Isang Tap. Isang Tawag. Agarang Tulong.')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO contacts
(category,name,position,phone,location)
VALUES
('Barangay','Punong Barangay','Barangay Captain','0917-123-4567','Barangay Poblacion Este'),
('Barangay','Barangay Secretary','Secretary','0918-123-4567','Barangay Poblacion Este'),
('Barangay','Barangay Tanod','Barangay Tanod','0999-123-4567','Barangay Poblacion Este'),
('Barangay','Barangay Health Worker','Health Worker','0906-123-4567','Barangay Poblacion Este'),
('Barangay','SK Chairman','SK Chairman','0917-765-4321','Barangay Poblacion Este');

INSERT INTO contacts
(category,name,position,phone,location)
VALUES
('Municipal','Municipal Disaster Risk Reduction Office','MDRRMO','(077) 123-4567','Sta. Cruz, Ilocos Sur'),
('Municipal','Sta. Cruz Police Station','Police','(077) 123-4568','Sta. Cruz, Ilocos Sur'),
('Municipal','Bureau of Fire Protection','Fire Department','(077) 123-4569','Sta. Cruz, Ilocos Sur');

INSERT INTO contacts
(category,name,position,phone,location)
VALUES
('National','Philippine National Police','PNP','911','Philippines'),
('National','Bureau of Fire Protection','BFP','911','Philippines'),
('National','Philippine Red Cross','Emergency','143','Philippines');

INSERT INTO hospitals
(name,location,phone,distance,status)
VALUES
('Sta. Cruz Municipal Hospital','Sta. Cruz, Ilocos Sur','(077) 123-4567','0.5 km','Open'),
('Ilocos Sur Medical Center','Candon City, Ilocos Sur','(077) 632-1234','12.4 km','Open'),
('Rural Health Unit (RHU)','Sta. Cruz, Ilocos Sur','(077) 123-5678','1.2 km','Open');

INSERT INTO alerts
(title,message,type,alert_date)
VALUES
('Heavy Rainfall Advisory','Possible flooding in low-lying areas. Stay alert and take necessary precautions.','danger','Aug 29, 2026 - 10:00 AM'),
('Typhoon Update','Typhoon outside PAR may affect Northern Luzon. Keep monitoring.','warning','Aug 28, 2026 - 6:00 PM'),
('Class Suspension','No class today. Please monitor official announcements.','info','Aug 28, 2026 - 5:00 PM');

INSERT INTO evacuation_centers
(name,location,capacity,status)
VALUES
('Sta. Cruz Covered Court','Poblacion Este, Sta. Cruz',200,'Open'),
('Sta. Cruz Elementary School','Poblacion Este, Sta. Cruz',300,'Open'),
('Sta. Cruz National High School','Poblacion Este, Sta. Cruz',500,'Open');

INSERT INTO first_aid
(title,description)
VALUES
('CPR','Cardiopulmonary Resuscitation'),
('Choking','What to do when someone is choking'),
('Severe Bleeding','Basic steps to help stop severe bleeding'),
('Burns','Immediate first-aid care for burns'),
('Fractures','Basic treatment while waiting for medical help'),
('Heat Stroke','Prevention and immediate care');

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
    INDEX idx_report_messages_admin (sent_by),
    CONSTRAINT fk_report_messages_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_messages_admin FOREIGN KEY (sent_by) REFERENCES admins(id) ON DELETE RESTRICT
);

INSERT INTO admins (username, password)
VALUES ('admin', '$2y$12$9FCpI5AlU.CHMM7XkYirOe21IDnSLAMlhgM8yro3kJ5YaCFEK9nsm')
ON DUPLICATE KEY UPDATE username=username;
