<?php
require_once '../config.php';

// Zaten giriş yapmışsa yönlendir
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Login attempts tablosunu başlat
initLoginAttemptsTable();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } elseif (!empty($username_or_email) && !empty($password)) {
        // Brute force koruması
        if (!checkLoginAttempts($username_or_email)) {
            $error = 'Çok fazla başarısız giriş denemesi! Lütfen 15 dakika sonra tekrar deneyin.';
        } else {
            $db = getDB();
            // Hem kullanıcı adı hem de e-posta ile giriş yapılabilir
            $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username_or_email, $username_or_email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    // Başarılı giriş
                    recordLoginAttempt($username_or_email, true);
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    session_regenerate_id(true); // Session fixation koruması
                    // Admin giriş logunu kaydet
                    logAdminLogin($admin['id'], $admin['username']);
                    header('Location: index.php');
                    exit;
                } else {
                    // Başarısız giriş
                    recordLoginAttempt($username_or_email, false);
                    $error = 'Kullanıcı adı/e-posta veya şifre hatalı!';
                }
            } else {
                // Kullanıcı bulunamadı
                recordLoginAttempt($username_or_email, false);
                $error = 'Kullanıcı adı/e-posta veya şifre hatalı!';
            }
            $stmt->close();
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - YAY Boxing Club</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../yay-logo.png" alt="YAY Boxing Club" class="login-logo">
                <p>Admin Paneli</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı veya E-posta</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </form>
            <div class="login-footer">
                <p>Varsayılan: admin / admin123</p>
            </div>
        </div>
    </div>
</body>
</html>

