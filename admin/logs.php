<?php
require_once '../config.php';
requireAdmin();

$db = getDB();

// Log tablosunu başlat
initAdminLogsTable();

// Filtreleme
$filter_ip = $_GET['filter_ip'] ?? '';
$filter_admin = $_GET['filter_admin'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// WHERE koşulları
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($filter_ip)) {
    $where_conditions[] = "ip_address LIKE ?";
    $params[] = '%' . $filter_ip . '%';
    $param_types .= 's';
}

if (!empty($filter_admin)) {
    $where_conditions[] = "admin_username LIKE ?";
    $params[] = '%' . $filter_admin . '%';
    $param_types .= 's';
}

if (!empty($filter_date)) {
    $where_conditions[] = "DATE(login_time) = ?";
    $params[] = $filter_date;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Toplam kayıt sayısı
$count_query = "SELECT COUNT(*) as total FROM admin_logs $where_clause";
if (!empty($params)) {
    $stmt = $db->prepare($count_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $stmt->close();
} else {
    $result = $db->query($count_query);
    $total_records = $result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $per_page);

// Logları getir
$query = "SELECT * FROM admin_logs $where_clause ORDER BY login_time DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $db->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// İstatistikler
$stats = [
    'total_logins' => $db->query("SELECT COUNT(*) as count FROM admin_logs")->fetch_assoc()['count'],
    'today_logins' => $db->query("SELECT COUNT(*) as count FROM admin_logs WHERE DATE(login_time) = CURDATE()")->fetch_assoc()['count'],
    'unique_ips' => $db->query("SELECT COUNT(DISTINCT ip_address) as count FROM admin_logs")->fetch_assoc()['count'],
    'active_sessions' => $db->query("SELECT COUNT(*) as count FROM admin_logs WHERE logout_time IS NULL")->fetch_assoc()['count']
];

// En çok giriş yapan IP'ler
$top_ips = $db->query("SELECT ip_address, COUNT(*) as count FROM admin_logs GROUP BY ip_address ORDER BY count DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erişim Logları - Admin Panel</title>
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
                <a href="logs.php" class="nav-item active">
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
                <h1>Erişim Logları</h1>
            </header>

            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🔐</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_logins']; ?></h3>
                        <p>Toplam Giriş</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['today_logins']; ?></h3>
                        <p>Bugün</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🌐</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['unique_ips']; ?></h3>
                        <p>Farklı IP</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🟢</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['active_sessions']; ?></h3>
                        <p>Aktif Oturum</p>
                    </div>
                </div>
            </div>

            <!-- Filtreleme -->
            <div class="content-section">
                <h2 class="section-title">Filtrele</h2>
                <form method="GET" class="filter-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="filter_ip">IP Adresi</label>
                            <input type="text" id="filter_ip" name="filter_ip" class="form-control" value="<?php echo htmlspecialchars($filter_ip); ?>" placeholder="192.168.1.1">
                        </div>
                        <div class="form-group">
                            <label for="filter_admin">Kullanıcı Adı</label>
                            <input type="text" id="filter_admin" name="filter_admin" class="form-control" value="<?php echo htmlspecialchars($filter_admin); ?>" placeholder="admin">
                        </div>
                        <div class="form-group">
                            <label for="filter_date">Tarih</label>
                            <input type="date" id="filter_date" name="filter_date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Filtrele</button>
                                <a href="logs.php" class="btn btn-secondary">Temizle</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- En Çok Giriş Yapan IP'ler -->
            <?php if (!empty($top_ips)): ?>
            <div class="content-section">
                <h2 class="section-title">En Çok Giriş Yapan IP Adresleri</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>IP Adresi</th>
                                <th>Giriş Sayısı</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_ips as $ip_data): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ip_data['ip_address']); ?></strong></td>
                                    <td><?php echo $ip_data['count']; ?></td>
                                    <td>
                                        <a href="?filter_ip=<?php echo urlencode($ip_data['ip_address']); ?>" class="btn btn-sm btn-primary">Görüntüle</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Log Listesi -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Giriş Logları</h2>
                    <span class="text-muted">Toplam: <?php echo $total_records; ?> kayıt</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kullanıcı</th>
                                <th>IP Adresi</th>
                                <th>Tarayıcı</th>
                                <th>Giriş Zamanı</th>
                                <th>Çıkış Zamanı</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Log bulunamadı</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="<?php echo $log['logout_time'] ? '' : 'active-session'; ?>">
                                        <td><?php echo $log['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($log['admin_username']); ?></strong></td>
                                        <td>
                                            <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                            <a href="?filter_ip=<?php echo urlencode($log['ip_address']); ?>" class="btn-link" title="Bu IP'yi filtrele">🔍</a>
                                        </td>
                                        <td class="user-agent-cell">
                                            <span title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                                <?php 
                                                $ua = $log['user_agent'];
                                                if (stripos($ua, 'Chrome') !== false) echo '🌐 Chrome';
                                                elseif (stripos($ua, 'Firefox') !== false) echo '🦊 Firefox';
                                                elseif (stripos($ua, 'Safari') !== false) echo '🧭 Safari';
                                                elseif (stripos($ua, 'Edge') !== false) echo '🔷 Edge';
                                                elseif (stripos($ua, 'Opera') !== false) echo '🎭 Opera';
                                                else echo '🌐 ' . htmlspecialchars(mb_substr($ua, 0, 30)) . '...';
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i:s', strtotime($log['login_time'])); ?></td>
                                        <td>
                                            <?php if ($log['logout_time']): ?>
                                                <?php echo date('d.m.Y H:i:s', strtotime($log['logout_time'])); ?>
                                                <br>
                                                <small class="text-muted">
                                                    (<?php 
                                                    $duration = strtotime($log['logout_time']) - strtotime($log['login_time']);
                                                    $hours = floor($duration / 3600);
                                                    $minutes = floor(($duration % 3600) / 60);
                                                    echo $hours . 's ' . $minutes . 'dk';
                                                    ?>)
                                                </small>
                                            <?php else: ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['logout_time']): ?>
                                                <span class="badge badge-secondary">Kapalı</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sayfalama -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    if ($page > 1): 
                        $query_params['page'] = $page - 1;
                    ?>
                        <a href="?<?php echo http_build_query($query_params); ?>" class="btn btn-secondary">« Önceki</a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?>
                    </span>
                    
                    <?php
                    if ($page < $total_pages):
                        $query_params['page'] = $page + 1;
                    ?>
                        <a href="?<?php echo http_build_query($query_params); ?>" class="btn btn-secondary">Sonraki »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <style>
        .filter-form {
            margin-top: 15px;
        }
        .user-agent-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .active-session {
            background-color: #e8f5e9;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
            padding: 15px;
        }
        .page-info {
            font-weight: 600;
            color: #666;
        }
        .btn-link {
            color: #007bff;
            text-decoration: none;
            margin-left: 5px;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
    </style>
</body>
</html>

