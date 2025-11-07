<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

// Program tablosunu oluştur (eğer yoksa)
$db->query("CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL,
    class_time VARCHAR(50) NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Program ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $day_name = sanitizeInput($_POST['day_name'] ?? '');
        $class_time = sanitizeInput($_POST['class_time'] ?? '');
        $class_name = sanitizeInput($_POST['class_name'] ?? '');
        
        if (!empty($day_name) && !empty($class_time) && !empty($class_name)) {
            $stmt = $db->prepare("INSERT INTO schedule (day_name, class_time, class_name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $day_name, $class_time, $class_name);
            $stmt->execute();
            $stmt->close();
            $success = 'Program başarıyla eklendi!';
        } else {
            $error = 'Lütfen tüm alanları doldurun!';
        }
    }
}

// Program silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: schedule.php');
    exit;
}

// Program güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $id = (int)$_POST['class_id'];
        $day_name = sanitizeInput($_POST['day_name'] ?? '');
        $class_time = sanitizeInput($_POST['class_time'] ?? '');
        $class_name = sanitizeInput($_POST['class_name'] ?? '');
        
        if (!empty($day_name) && !empty($class_time) && !empty($class_name)) {
            $stmt = $db->prepare("UPDATE schedule SET day_name = ?, class_time = ?, class_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $day_name, $class_time, $class_name, $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Program başarıyla güncellendi!';
        }
    }
}

// Tüm programları getir
$days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
$schedule_data = [];
foreach ($days as $day) {
    $stmt = $db->prepare("SELECT * FROM schedule WHERE day_name = ? ORDER BY class_time ASC");
    $stmt->bind_param("s", $day);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule_data[$day] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Yönetimi - Admin Panel</title>
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
                <a href="schedule.php" class="nav-item active">
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
                <h1>Program Yönetimi</h1>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Yeni Program Ekleme Formu -->
            <div class="content-section">
                <h2 class="section-title">Yeni Program Ekle</h2>
                <form method="POST" class="schedule-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Gün</label>
                            <select name="day_name" class="form-control" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Saat</label>
                            <input type="text" name="class_time" placeholder="09:00 - 10:30" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Ders Adı</label>
                            <input type="text" name="class_name" placeholder="Başlangıç Boks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" name="add_class" class="btn btn-primary">Ekle</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mevcut Programlar -->
            <div class="content-section">
                <h2 class="section-title">Haftalık Program</h2>
                
                <?php foreach ($days as $day): ?>
                    <div class="schedule-day-section">
                        <h3 class="day-title"><?php echo $day; ?></h3>
                        <div class="schedule-classes">
                            <?php if (empty($schedule_data[$day])): ?>
                                <p class="text-muted">Bu gün için program yok</p>
                            <?php else: ?>
                                <?php foreach ($schedule_data[$day] as $class): ?>
                                    <div class="schedule-class-item">
                                        <div class="class-info">
                                            <span class="class-time"><?php echo htmlspecialchars($class['class_time']); ?></span>
                                            <span class="class-name"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                        </div>
                                        <div class="class-actions">
                                            <button class="btn btn-sm btn-primary" onclick="editClass(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['day_name']); ?>', '<?php echo htmlspecialchars($class['class_time']); ?>', '<?php echo htmlspecialchars($class['class_name']); ?>')">Düzenle</button>
                                            <a href="?delete=<?php echo $class['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu programı silmek istediğinize emin misiniz?')">Sil</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Düzenleme Modal -->
            <div id="editModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Programı Düzenle</h2>
                    <form method="POST" class="schedule-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="class_id" id="edit_class_id">
                        <div class="form-group">
                            <label>Gün</label>
                            <select name="day_name" id="edit_day_name" class="form-control" required>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Saat</label>
                            <input type="text" name="class_time" id="edit_class_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Ders Adı</label>
                            <input type="text" name="class_name" id="edit_class_name" class="form-control" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="update_class" class="btn btn-primary">Güncelle</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">İptal</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function editClass(id, day, time, name) {
            document.getElementById('edit_class_id').value = id;
            document.getElementById('edit_day_name').value = day;
            document.getElementById('edit_class_time').value = time;
            document.getElementById('edit_class_name').value = name;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <style>
        .schedule-day-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .day-title {
            color: #ff0000;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .schedule-class-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 6px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .class-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .class-time {
            font-weight: 600;
            color: #333;
            min-width: 150px;
        }
        .class-name {
            color: #666;
        }
        .class-actions {
            display: flex;
            gap: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .text-muted {
            color: #999;
            font-style: italic;
        }
    </style>
</body>
</html>

