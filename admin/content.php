<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

// İçerik güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        foreach ($_POST as $key => $value) {
            if ($key !== 'update_content' && $key !== 'csrf_token') {
                $key = sanitizeInput($key);
                $value = sanitizeInput($value);
                
                $stmt = $db->prepare("INSERT INTO site_content (content_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
                $stmt->close();
            }
        }
        $success = 'İçerikler başarıyla güncellendi!';
    }
}

// Tüm içerikleri getir
$contents = $db->query("SELECT * FROM site_content ORDER BY content_key")->fetch_all(MYSQLI_ASSOC);
$contentMap = [];
foreach ($contents as $content) {
    $contentMap[$content['content_key']] = $content['content_value'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İçerik Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Menüyü Aç/Kapat">☰</button>
        
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../yay-logo.png" alt="YAY Boxing Club" class="admin-logo">
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="messages.php" class="nav-item">
                    <span class="icon">✉️</span>
                    <span>Mesajlar</span>
                </a>
                <a href="content.php" class="nav-item active">
                    <span class="icon">📝</span>
                    <span>İçerik Yönetimi</span>
                </a>
                <a href="schedule.php" class="nav-item">
                    <span class="icon">📅</span>
                    <span>Program Yönetimi</span>
                </a>
                <a href="logs.php" class="nav-item">
                    <span class="icon">🔒</span>
                    <span>Erişim Logları</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <span class="icon">⚙️</span>
                    <span>Ayarlar</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">Siteyi Görüntüle</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Çıkış Yap</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="content-header">
                <h1>İçerik Yönetimi</h1>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="content-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <!-- Hero Bölümü -->
                <div class="content-section">
                    <h2 class="section-title">Hero Bölümü</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Hero Başlık 1</label>
                            <input type="text" name="hero_title_1" value="<?php echo htmlspecialchars($contentMap['hero_title_1'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Hero Başlık 2</label>
                            <input type="text" name="hero_title_2" value="<?php echo htmlspecialchars($contentMap['hero_title_2'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label>Hero Alt Başlık</label>
                            <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($contentMap['hero_subtitle'] ?? ''); ?>" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Hakkımızda Bölümü -->
                <div class="content-section">
                    <h2 class="section-title">Hakkımızda</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Başlık</label>
                            <input type="text" name="about_title" value="<?php echo htmlspecialchars($contentMap['about_title'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label>Metin</label>
                            <textarea name="about_text" rows="5" class="form-control"><?php echo htmlspecialchars($contentMap['about_text'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- İstatistikler -->
                <div class="content-section">
                    <h2 class="section-title">İstatistikler</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Aktif Üye</label>
                            <input type="number" name="stat_members" value="<?php echo htmlspecialchars($contentMap['stat_members'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Yıllık Deneyim</label>
                            <input type="number" name="stat_experience" value="<?php echo htmlspecialchars($contentMap['stat_experience'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Uzman Antrenör</label>
                            <input type="number" name="stat_trainers" value="<?php echo htmlspecialchars($contentMap['stat_trainers'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Kazanılan Şampiyonluk</label>
                            <input type="number" name="stat_championships" value="<?php echo htmlspecialchars($contentMap['stat_championships'] ?? ''); ?>" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="content-section">
                    <h2 class="section-title">İletişim Bilgileri</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Adres</label>
                            <textarea name="contact_address" rows="3" class="form-control"><?php echo htmlspecialchars($contentMap['contact_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($contentMap['contact_phone'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>E-posta</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($contentMap['contact_email'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label>Çalışma Saatleri</label>
                            <textarea name="contact_hours" rows="2" class="form-control"><?php echo htmlspecialchars($contentMap['contact_hours'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_content" class="btn btn-primary btn-lg">Kaydet</button>
                    <a href="../index.php" target="_blank" class="btn btn-secondary btn-lg">Önizleme</a>
                </div>
            </form>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>

