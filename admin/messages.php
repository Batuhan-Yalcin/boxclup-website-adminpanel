<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

// Mesaj okundu işaretleme
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: messages.php');
    exit;
}

// Mesaj silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: messages.php');
    exit;
}

// Filtreleme
$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'unread') {
    $where = 'WHERE is_read = 0';
} elseif ($filter === 'read') {
    $where = 'WHERE is_read = 1';
}

// Mesajları getir
$messages = $db->query("SELECT * FROM messages $where ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar - Admin Panel</title>
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
                <a href="messages.php" class="nav-item active">
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
                <h1>Mesajlar</h1>
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">Tümü</a>
                    <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">Okunmamış</a>
                    <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">Okunmuş</a>
                </div>
            </header>

            <div class="content-section">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ad</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Mesaj</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Mesaj bulunamadı</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                                        <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['phone'] ?: '-'); ?></td>
                                        <td class="message-preview">
                                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (!$msg['is_read']): ?>
                                                    <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-success" title="Okundu İşaretle">✓</a>
                                                <?php endif; ?>
                                                <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')" title="Sil">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>

