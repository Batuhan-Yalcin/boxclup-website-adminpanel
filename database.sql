-- YAY Boxing Club Veritabanı Yapısı
-- cPanel'de veritabanı zaten oluşturulmuş olmalı, sadece tabloları oluşturuyoruz

-- Admin kullanıcıları tablosu
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İletişim mesajları tablosu
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site içerikleri tablosu
CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(100) UNIQUE NOT NULL,
    content_value TEXT,
    content_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin kullanıcı (şifre: b190758x)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$12$EzIsUZKSnU5xRoQ2Pmpca.I6kcxidsuX1TpoJobTP5ptAWrk6kFfS', 'admin@yayboxing.com.tr');

-- Varsayılan site içerikleri
INSERT INTO site_content (content_key, content_value, content_type) VALUES
('hero_title_1', 'GÜCÜNÜZÜ', 'text'),
('hero_title_2', 'KEŞFEDİN', 'text'),
('hero_subtitle', 'Profesyonel boks eğitimi ile limitlerinizi aşın', 'text'),
('about_title', 'YAY Boxing Club', 'text'),
('about_text', '20 yıllık deneyimimizle, boks sporunu seven herkes için profesyonel eğitim sunuyoruz. Modern tesislerimiz ve uzman antrenörlerimizle, ister amatör ister profesyonel seviyede olsun, her seviyeden sporcuya hizmet veriyoruz.', 'text'),
('stat_members', '500', 'number'),
('stat_experience', '20', 'number'),
('stat_trainers', '15', 'number'),
('stat_championships', '50', 'number'),
('contact_address', 'YAY Boxing Club\nİstanbul, Türkiye', 'text'),
('contact_phone', '+90 (212) 555 0123', 'text'),
('contact_email', 'info@yayboxing.com.tr', 'text'),
('contact_hours', 'Pazartesi - Cuma: 08:00 - 22:00\nCumartesi - Pazar: 09:00 - 18:00', 'text');

