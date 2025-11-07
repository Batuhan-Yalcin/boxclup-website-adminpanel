<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

// İstatistikler
$stats = [
    'total_messages' => $db->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'],
    'unread_messages' => $db->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0")->fetch_assoc()['count'],
    'today_messages' => $db->query("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'],
    'this_month_messages' => $db->query("SELECT COUNT(*) as count FROM messages WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch_assoc()['count']
];

// Son mesajlar
$recent_messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - YAY Boxing Club</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Menüyü Aç/Kapat">☰</button>
        
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../yay-logo.png" alt="YAY Boxing Club" class="admin-logo">
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="messages.php" class="nav-item">
                    <span class="icon">✉️</span>
                    <span>Mesajlar</span>
                    <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="badge"><?php echo $stats['unread_messages']; ?></span>
                    <?php endif; ?>
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
                </div>
            </header>

            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">✉️</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_messages']; ?></h3>
                        <p>Toplam Mesaj</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['unread_messages']; ?></h3>
                        <p>Okunmamış</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['today_messages']; ?></h3>
                        <p>Bugün</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['this_month_messages']; ?></h3>
                        <p>Bu Ay</p>
                    </div>
                </div>
            </div>

            <!-- Son Mesajlar -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Son Mesajlar</h2>
                    <a href="messages.php" class="btn btn-primary btn-sm">Tümünü Gör</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ad</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Mesaj</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_messages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Henüz mesaj yok</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_messages as $msg): ?>
                                    <tr class="<?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['phone'] ?: '-'); ?></td>
                                        <td class="message-preview"><?php echo htmlspecialchars(mb_substr($msg['message'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></td>
                                        <td>
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="badge badge-warning">Yeni</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Okundu</span>
                                            <?php endif; ?>
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

