<?php
require_once 'config.php';

// Veritabanından içerikleri çek
$db = getDB();
$contents = $db->query("SELECT content_key, content_value FROM site_content")->fetch_all(MYSQLI_ASSOC);
$contentMap = [];
foreach ($contents as $content) {
    $contentMap[$content['content_key']] = $content['content_value'];
}

// Program verilerini çek
$days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
$schedule_data = [];
try {
    foreach ($days as $day) {
        $stmt = $db->prepare("SELECT * FROM schedule WHERE day_name = ? ORDER BY class_time ASC");
        $stmt->bind_param("s", $day);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule_data[$day] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} catch (Exception $e) {
    // Schedule tablosu yoksa boş bırak
    $schedule_data = [];
}

// Varsayılan değerler (veritabanında yoksa)
function getContent($key, $default = '') {
    global $contentMap;
    return htmlspecialchars($contentMap[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YAY Boxing Club - Profesyonel Boks Eğitimi</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <img src="yay-logo.png" alt="YAY Boxing Club" class="logo-img">
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Ana Sayfa</a></li>
                <li><a href="#about" class="nav-link">Hakkımızda</a></li>
                <li><a href="#services" class="nav-link">Hizmetler</a></li>
                <li><a href="#schedule" class="nav-link">Program</a></li>
                <li><a href="#contact" class="nav-link">İletişim</a></li>
            </ul>
            <div class="nav-actions">
                <button class="theme-toggle" id="themeToggle" aria-label="Tema Değiştir">
                    <span class="theme-icon">🌙</span>
                </button>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="title-line"><?php echo getContent('hero_title_1', 'GÜCÜNÜZÜ'); ?></span>
                <span class="title-line"><?php echo getContent('hero_title_2', 'KEŞFEDİN'); ?></span>
            </h1>
            <p class="hero-subtitle"><?php echo getContent('hero_subtitle', 'Profesyonel boks eğitimi ile limitlerinizi aşın'); ?></p>
            <div class="hero-buttons">
                <a href="#contact" class="btn btn-primary">Hemen Başla</a>
                <a href="#about" class="btn btn-secondary">Daha Fazla</a>
            </div>
        </div>
        <div class="floating-elements">
            <div class="float-box float-1"></div>
            <div class="float-box float-2"></div>
            <div class="float-box float-3"></div>
        </div>
        <div class="scroll-indicator">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">HAKKIMIZDA</h2>
                <div class="title-underline"></div>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3><?php echo getContent('about_title', 'YAY Boxing Club'); ?></h3>
                    <p><?php echo nl2br(getContent('about_text', '20 yıllık deneyimimizle, boks sporunu seven herkes için profesyonel eğitim sunuyoruz. Modern tesislerimiz ve uzman antrenörlerimizle, ister amatör ister profesyonel seviyede olsun, her seviyeden sporcuya hizmet veriyoruz.')); ?></p>
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-number" data-target="<?php echo getContent('stat_members', '500'); ?>">0</div>
                            <div class="stat-label">Aktif Üye</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-target="<?php echo getContent('stat_experience', '20'); ?>">0</div>
                            <div class="stat-label">Yıllık Deneyim</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-target="<?php echo getContent('stat_trainers', '15'); ?>">0</div>
                            <div class="stat-label">Uzman Antrenör</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-target="<?php echo getContent('stat_championships', '50'); ?>">0</div>
                            <div class="stat-label">Kazanılan Şampiyonluk</div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <div class="image-wrapper">
                        <div class="boxing-elements">
                            <!-- Boks Eldiveni -->
                            <div class="boxing-glove">
                                <div class="glove-icon">🥊</div>
                            </div>
                            <!-- Başarı Rozetleri -->
                            <div class="achievement-badges">
                                <div class="badge-item badge-1">
                                    <div class="badge-icon">🏆</div>
                                    <span>Şampiyonluk</span>
                                </div>
                                <div class="badge-item badge-2">
                                    <div class="badge-icon">⭐</div>
                                    <span>Mükemmellik</span>
                                </div>
                                <div class="badge-item badge-3">
                                    <div class="badge-icon">💪</div>
                                    <span>Güç</span>
                                </div>
                            </div>
                            <!-- Boksör Silüeti -->
                            <div class="boxer-silhouette">
                                <div class="boxer-figure"></div>
                            </div>
                            <!-- Motivasyonel Metin -->
                            <div class="motivational-quote">
                                <p>"Güç, zihinde başlar"</p>
                            </div>
                        </div>
                        <div class="image-glow"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">HİZMETLERİMİZ</h2>
                <div class="title-underline"></div>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🥊</div>
                    <h3>Boks Eğitimi</h3>
                    <p>Başlangıç seviyesinden ileri seviyeye kadar profesyonel boks teknikleri öğrenin.</p>
                    <div class="service-hover"></div>
                </div>
                <div class="service-card">
                    <div class="service-icon">💪</div>
                    <h3>Fitness & Kondisyon</h3>
                    <p>Güç, dayanıklılık ve esneklik için özel antrenman programları.</p>
                    <div class="service-hover"></div>
                </div>
                <div class="service-card">
                    <div class="service-icon">👥</div>
                    <h3>Grup Dersleri</h3>
                    <p>Eğlenceli ve motivasyon dolu grup antrenmanları ile birlikte çalışın.</p>
                    <div class="service-hover"></div>
                </div>
                <div class="service-card">
                    <div class="service-icon">⭐</div>
                    <h3>Özel Dersler</h3>
                    <p>Kişiselleştirilmiş antrenman programları ile hedeflerinize ulaşın.</p>
                    <div class="service-hover"></div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏆</div>
                    <h3>Yarışma Hazırlığı</h3>
                    <p>Profesyonel müsabakalar için özel hazırlık programları.</p>
                    <div class="service-hover"></div>
                </div>
                <div class="service-card">
                    <div class="service-icon">🧘</div>
                    <h3>Mental Antrenman</h3>
                    <p>Zihinsel güç ve konsantrasyon teknikleri ile performansınızı artırın.</p>
                    <div class="service-hover"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section id="schedule" class="schedule">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">HAFTA PROGRAMI</h2>
                <div class="title-underline"></div>
            </div>
            <div class="schedule-container">
                <?php foreach ($days as $day): ?>
                    <div class="schedule-day">
                        <div class="day-header">
                            <h3><?php echo $day; ?></h3>
                        </div>
                        <div class="day-classes">
                            <?php if (!empty($schedule_data[$day])): ?>
                                <?php foreach ($schedule_data[$day] as $class): ?>
                                    <div class="class-item">
                                        <span class="class-time"><?php echo htmlspecialchars($class['class_time']); ?></span>
                                        <span class="class-name"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="class-item">
                                    <span class="class-name" style="color: #999; font-style: italic;">Program yok</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">İLETİŞİM</h2>
                <div class="title-underline"></div>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-item">
                        <div class="info-icon">📍</div>
                        <div class="info-text">
                            <h4>Adres</h4>
                            <p><?php echo nl2br(getContent('contact_address', 'YAY Boxing Club\nİstanbul, Türkiye')); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">📞</div>
                        <div class="info-text">
                            <h4>Telefon</h4>
                            <p><?php echo getContent('contact_phone', '+90 (212) 555 0123'); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">✉️</div>
                        <div class="info-text">
                            <h4>E-posta</h4>
                            <p><?php echo getContent('contact_email', 'info@yayboxing.com'); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">🕒</div>
                        <div class="info-text">
                            <h4>Çalışma Saatleri</h4>
                            <p><?php echo nl2br(getContent('contact_hours', 'Pazartesi - Cuma: 08:00 - 22:00\nCumartesi - Pazar: 09:00 - 18:00')); ?></p>
                        </div>
                    </div>
                </div>
                <form class="contact-form" id="contactForm">
                    <div id="formMessage" class="form-message" style="display: none;"></div>
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Adınız" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="E-posta" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Telefon">
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Mesajınız" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Gönder</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="yay-logo.png" alt="YAY Boxing Club" class="logo-img">
                </div>
                <div class="footer-links">
                    <a href="#home">Ana Sayfa</a>
                    <a href="#about">Hakkımızda</a>
                    <a href="#services">Hizmetler</a>
                    <a href="#schedule">Program</a>
                    <a href="#contact">İletişim</a>
                </div>
                <div class="social-links">
                    <a href="#" class="social-icon">📘</a>
                    <a href="#" class="social-icon">📷</a>
                    <a href="#" class="social-icon">🐦</a>
                    <a href="#" class="social-icon">📺</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 YAY Boxing Club. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>

