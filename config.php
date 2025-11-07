<?php
// Yerel geliştirme kontrolü
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($httpHost, ['localhost', '127.0.0.1']) || 
           strpos($httpHost, 'localhost') !== false ||
           strpos($httpHost, '127.0.0.1') !== false;

// Veritabanı Yapılandırması
if ($isLocal) {
    // Yerel geliştirme ayarları
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'yay_boxing_club');
    define('SITE_URL', 'http://localhost/yayboxclup');
} else {
    // Production ayarları
    define('DB_HOST', 'localhost');
    define('DB_USER', 'batununy_4');
    define('DB_PASS', 'B190758xd');
    define('DB_NAME', 'batununy_yay');
    define('SITE_URL', 'https://yayboxing.com.tr');
}

define('ADMIN_URL', SITE_URL . '/admin');

// Oturum Güvenlik Ayarları
if (session_status() === PHP_SESSION_NONE) {
    // Güvenli session ayarları
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $isLocal ? 0 : 1); // Yerel için HTTP, production için HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
    
    // Session fixation koruması
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Zaman Dilimi
date_default_timezone_set('Europe/Istanbul');

// Hata Raporlama
if ($isLocal) {
    // Yerel geliştirme - hataları göster
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error_log.txt');
} else {
    // Production - hataları gizle
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error_log.txt');
}

// Veritabanı Bağlantısı
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $conn->set_charset("utf8mb4");
            
            if ($conn->connect_error) {
                // Üretimde detaylı hata gösterme
                error_log("Veritabanı bağlantı hatası: " . $conn->connect_error);
                die("Sistem bakımda. Lütfen daha sonra tekrar deneyin.");
            }
        } catch (Exception $e) {
            error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
            die("Sistem bakımda. Lütfen daha sonra tekrar deneyin.");
        }
    }
    
    return $conn;
}

// Güvenlik Fonksiyonları
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return sanitize($input);
}

// Admin Kontrolü
function isAdmin() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

// JSON Response
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// CSRF Token Fonksiyonları
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// IP Adresi Alma Fonksiyonu (IPv6 localhost'u normalize eder)
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Proxy arkasındaysa gerçek IP'yi al
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    
    // IPv6 localhost'u IPv4'e çevir
    if ($ip === '::1' || $ip === '::ffff:127.0.0.1' || $ip === '0:0:0:0:0:0:0:1') {
        $ip = '127.0.0.1';
    }
    
    // IPv6 mapped IPv4 formatını temizle (::ffff:192.168.1.1 -> 192.168.1.1)
    if (strpos($ip, '::ffff:') === 0) {
        $ip = substr($ip, 7);
    }
    
    return $ip;
}

// Rate Limiting (Brute Force Koruması)
function checkLoginAttempts($identifier) {
    $db = getDB();
    $ip = getClientIP();
    $key = 'login_attempts_' . md5($identifier . $ip);
    
    // Son 15 dakikadaki denemeleri kontrol et
    $stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE identifier = ? AND ip_address = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("ss", $key, $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();
    
    if ($attempt && $attempt['attempts'] >= 5) {
        return false; // Çok fazla deneme
    }
    
    return true;
}

function recordLoginAttempt($identifier, $success = false) {
    $db = getDB();
    $ip = getClientIP();
    $key = 'login_attempts_' . md5($identifier . $ip);
    
    if ($success) {
        // Başarılı giriş - kayıtları temizle
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE identifier = ? AND ip_address = ?");
        $stmt->bind_param("ss", $key, $ip);
        $stmt->execute();
        $stmt->close();
    } else {
        // Başarısız deneme - kaydet
        $stmt = $db->prepare("INSERT INTO login_attempts (identifier, ip_address, attempts, last_attempt) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
        $stmt->bind_param("ss", $key, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

// Login attempts tablosunu oluştur (eğer yoksa)
function initLoginAttemptsTable() {
    $db = getDB();
    $db->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        identifier VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempts INT DEFAULT 1,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_attempt (identifier, ip_address)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Admin log tablosunu oluştur (eğer yoksa)
function initAdminLogsTable() {
    $db = getDB();
    $db->query("CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        admin_username VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        logout_time TIMESTAMP NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_login_time (login_time),
        INDEX idx_ip_address (ip_address)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Admin giriş logunu kaydet
function logAdminLogin($admin_id, $admin_username) {
    $db = getDB();
    initAdminLogsTable();
    
    $ip = getClientIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, admin_username, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $admin_id, $admin_username, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
    
    // Session'a log ID'sini kaydet (logout için)
    $_SESSION['admin_log_id'] = $db->insert_id;
}

// Admin çıkış logunu güncelle
function logAdminLogout() {
    if (isset($_SESSION['admin_log_id'])) {
        $db = getDB();
        $log_id = (int)$_SESSION['admin_log_id'];
        $stmt = $db->prepare("UPDATE admin_logs SET logout_time = NOW() WHERE id = ?");
        $stmt->bind_param("i", $log_id);
        $stmt->execute();
        $stmt->close();
    }
}
?>

