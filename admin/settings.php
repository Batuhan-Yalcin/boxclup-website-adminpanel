<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

$success = '';
$error = '';

// E-posta değiştirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $new_email = sanitizeInput($_POST['new_email'] ?? '');
        
        if (empty($new_email)) {
            $error = 'Lütfen e-posta adresini girin!';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi girin!';
        } else {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $db->prepare("UPDATE admins SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $new_email, $admin_id);
        $stmt->execute();
        $stmt->close();
        $success = 'E-posta adresi başarıyla güncellendi!';
        // Bilgileri yeniden yükle
        $stmt = $db->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_info = $result->fetch_assoc();
        $stmt->close();
        }
    }
}

// Şifre değiştirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Lütfen tüm alanları doldurun!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Yeni şifreler eşleşmiyor!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır!';
        } else {
        // Mevcut şifreyi kontrol et
        $admin_id = $_SESSION['admin_id'];
        $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        if ($admin && password_verify($current_password, $admin['password'])) {
            // Yeni şifreyi hashle ve güncelle
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $admin_id);
            $stmt->execute();
            $stmt->close();
            $success = 'Şifre başarıyla değiştirildi!';
        } else {
            $error = 'Mevcut şifre hatalı!';
        }
        }
    }
}

// Yeni admin ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $new_username = sanitizeInput($_POST['new_username'] ?? '');
        $new_email = sanitizeInput($_POST['new_email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_username) || empty($new_email) || empty($new_password) || empty($confirm_password)) {
            $error = 'Lütfen tüm alanları doldurun!';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi girin!';
        } elseif (strlen($new_username) < 3) {
            $error = 'Kullanıcı adı en az 3 karakter olmalıdır!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Şifreler eşleşmiyor!';
        } else {
            // Kullanıcı adı ve e-posta kontrolü
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $new_username, $new_email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor!';
                $stmt->close();
            } else {
                $stmt->close();
                // Yeni admin ekle
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $new_username, $new_email, $hashed_password);
                
                if ($stmt->execute()) {
                    $success = 'Yeni admin başarıyla eklendi!';
                    // Formu temizlemek için sayfayı yenile
                    header('Location: settings.php?admin_added=1');
                    exit;
                } else {
                    $error = 'Admin eklenirken bir hata oluştu!';
                }
                $stmt->close();
            }
        }
    }
}

// Admin silme
if (isset($_GET['delete_admin']) && is_numeric($_GET['delete_admin'])) {
    $delete_id = (int)$_GET['delete_admin'];
    $current_admin_id = $_SESSION['admin_id'];
    
    // Kendi hesabını silmeyi engelle
    if ($delete_id == $current_admin_id) {
        $error = 'Kendi hesabınızı silemezsiniz!';
    } else {
        // Toplam admin sayısını kontrol et (en az 1 admin kalmalı)
        $total_admins = $db->query("SELECT COUNT(*) as count FROM admins")->fetch_assoc()['count'];
        if ($total_admins <= 1) {
            $error = 'En az bir admin hesabı bulunmalıdır!';
        } else {
            $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $success = 'Admin başarıyla silindi!';
                header('Location: settings.php?admin_deleted=1');
                exit;
            } else {
                $error = 'Admin silinirken bir hata oluştu!';
            }
            $stmt->close();
        }
    }
}

// Başarı mesajları (URL parametrelerinden)
if (isset($_GET['admin_added'])) {
    $success = 'Yeni admin başarıyla eklendi!';
}
if (isset($_GET['admin_deleted'])) {
    $success = 'Admin başarıyla silindi!';
}

// Admin bilgilerini getir
$admin_id = $_SESSION['admin_id'];
$stmt = $db->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_info = $result->fetch_assoc();
$stmt->close();

// Tüm adminleri getir
$all_admins = $db->query("SELECT id, username, email, created_at FROM admins ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Admin Panel</title>
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
                <a href="content.php" class="nav-item">
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
                <a href="settings.php" class="nav-item active">
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
                <h1>Ayarlar</h1>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Hesap Bilgileri -->
            <div class="content-section">
                <h2 class="section-title">Hesap Bilgileri</h2>
                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">Kullanıcı Adı:</span>
                        <span class="info-value"><?php echo htmlspecialchars($admin_info['username']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">E-posta:</span>
                        <span class="info-value"><?php echo htmlspecialchars($admin_info['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kayıt Tarihi:</span>
                        <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($admin_info['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- E-posta Değiştirme -->
            <div class="content-section">
                <h2 class="section-title">E-posta Değiştir</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="new_email">Yeni E-posta Adresi</label>
                        <input type="email" id="new_email" name="new_email" class="form-control" value="<?php echo htmlspecialchars($admin_info['email']); ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="change_email" class="btn btn-primary">E-postayı Güncelle</button>
                    </div>
                </form>
            </div>

            <!-- Şifre Değiştirme -->
            <div class="content-section">
                <h2 class="section-title">Şifre Değiştir</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="current_password">Mevcut Şifre</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Yeni Şifre</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                        <small class="form-text">Şifre en az 6 karakter olmalıdır.</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">Şifreyi Değiştir</button>
                    </div>
                </form>
            </div>

            <!-- Sistem Bilgileri -->
            <div class="content-section">
                <h2 class="section-title">Sistem Bilgileri</h2>
                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">PHP Versiyonu:</span>
                        <span class="info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Veritabanı:</span>
                        <span class="info-value">MySQL</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Sunucu:</span>
                        <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Yeni Admin Ekleme -->
            <div class="content-section">
                <h2 class="section-title">Yeni Admin Ekle</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="new_username">Kullanıcı Adı</label>
                        <input type="text" id="new_username" name="new_username" class="form-control" required minlength="3" placeholder="admin2">
                        <small class="form-text">En az 3 karakter olmalıdır.</small>
                    </div>
                    <div class="form-group">
                        <label for="new_email">E-posta Adresi</label>
                        <input type="email" id="new_email" name="new_email" class="form-control" required placeholder="admin2@example.com">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Şifre</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" placeholder="••••••">
                        <small class="form-text">Şifre en az 6 karakter olmalıdır.</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Şifre (Tekrar)</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6" placeholder="••••••">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_admin" class="btn btn-primary">Admin Ekle</button>
                    </div>
                </form>
            </div>

            <!-- Mevcut Adminler -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Mevcut Adminler</h2>
                    <span class="text-muted">Toplam: <?php echo count($all_admins); ?> admin</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_admins as $admin): ?>
                                <tr class="<?php echo $admin['id'] == $admin_id ? 'current-user' : ''; ?>">
                                    <td><?php echo $admin['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                                        <?php if ($admin['id'] == $admin_id): ?>
                                            <span class="badge badge-info">Siz</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($admin['created_at'])); ?></td>
                                    <td>
                                        <?php if ($admin['id'] != $admin_id): ?>
                                            <a href="?delete_admin=<?php echo $admin['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bu admini silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')" 
                                               title="Sil">🗑️ Sil</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Güvenlik Uyarısı -->
            <div class="content-section">
                <div class="alert alert-warning">
                    <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                    <ul style="margin: 10px 0 0 20px; padding: 0;">
                        <li>Güçlü bir şifre kullanın (en az 8 karakter, büyük/küçük harf, rakam ve özel karakter içermeli)</li>
                        <li>Şifrenizi düzenli olarak değiştirin</li>
                        <li>Şifrenizi kimseyle paylaşmayın</li>
                        <li>Oturumunuzu kullanmadığınızda mutlaka çıkış yapın</li>
                        <li>Sadece güvendiğiniz kişilere admin yetkisi verin</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <style>
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #333;
        }
        .info-value {
            color: #666;
            word-break: break-word;
        }
        .settings-form {
            max-width: 600px;
            margin-top: 15px;
            width: 100%;
        }
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 0.875rem;
            color: #666;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
        }
        .alert-warning ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        .alert-warning li {
            margin: 5px 0;
        }
        .current-user {
            background-color: #e3f2fd;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .text-muted {
            color: #666;
            font-size: 0.9em;
        }
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .data-table {
            min-width: 600px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .info-card {
                padding: 15px;
            }
            .info-row {
                flex-direction: column;
                gap: 5px;
                padding: 15px 0;
            }
            .info-label {
                font-size: 0.9em;
                margin-bottom: 5px;
            }
            .info-value {
                font-size: 0.95em;
                word-break: break-all;
            }
            .settings-form {
                max-width: 100%;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-control {
                width: 100%;
                font-size: 16px; /* iOS zoom önleme */
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .section-header .text-muted {
                margin-top: 5px;
            }
            .table-container {
                margin: 0 -15px;
                padding: 0 15px;
            }
            .data-table {
                font-size: 0.85em;
            }
            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
            .data-table th:nth-child(1),
            .data-table td:nth-child(1) {
                display: none; /* ID kolonunu gizle */
            }
            .data-table th:nth-child(4),
            .data-table td:nth-child(4) {
                font-size: 0.8em;
            }
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8em;
            }
            .alert-warning {
                padding: 12px;
                font-size: 0.9em;
            }
            .alert-warning ul {
                margin-left: 15px;
            }
            .content-section {
                margin-bottom: 20px;
            }
            .form-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .form-actions .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .info-card {
                padding: 12px;
            }
            .info-row {
                padding: 12px 0;
            }
            .data-table {
                font-size: 0.75em;
                min-width: 500px;
            }
            .data-table th,
            .data-table td {
                padding: 6px 4px;
            }
            .data-table th:nth-child(3),
            .data-table td:nth-child(3) {
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .section-title {
                font-size: 1.2em;
            }
            .badge {
                font-size: 0.7em;
                padding: 2px 6px;
            }
        }

        /* Tablet için */
        @media (min-width: 769px) and (max-width: 1024px) {
            .settings-form {
                max-width: 100%;
            }
            .data-table {
                font-size: 0.9em;
            }
        }
    </style>
</body>
</html>

