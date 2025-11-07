<?php
/**
 * Admin Şifre Sıfırlama Scripti
 * Bu dosyayı kullandıktan sonra silin!
 */

require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    
    if (empty($new_password)) {
        $error = 'Lütfen yeni şifreyi girin!';
    } else {
        try {
            $db = getDB();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
            $stmt->bind_param("s", $hashed_password);
            
            if ($stmt->execute()) {
                $message = '✅ Admin şifresi başarıyla güncellendi!';
            } else {
                $error = '❌ Şifre güncellenirken hata oluştu: ' . $db->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = '❌ Hata: ' . $e->getMessage();
        }
    }
}

// Mevcut admin kullanıcısını kontrol et
try {
    $db = getDB();
    $result = $db->query("SELECT id, username FROM admins WHERE username = 'admin'");
    $admin_exists = $result->num_rows > 0;
    
    if (!$admin_exists) {
        // Admin kullanıcısı yoksa oluştur
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, password, email) VALUES ('admin', ?, 'admin@yayboxing.com')");
        $stmt->bind_param("s", $default_password);
        $stmt->execute();
        $stmt->close();
        $message = '✅ Admin kullanıcısı oluşturuldu! Varsayılan şifre: admin123';
    }
} catch (Exception $e) {
    $error = '❌ Veritabanı hatası: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Şifre Sıfırlama - YAY Boxing Club</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #ff0000;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #ff0000;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #ff0000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Şifre Sıfırlama</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Yeni Şifre</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit" class="btn">Şifreyi Güncelle</button>
        </form>
        
        <div class="info-box">
            <strong>Bilgi:</strong><br>
            Bu script admin kullanıcısının şifresini günceller. Varsayılan şifre: <code>admin123</code>
        </div>
        
        <div class="warning">
            <strong>⚠️ Güvenlik Uyarısı:</strong><br>
            Bu dosyayı kullandıktan sonra mutlaka silin veya erişimi engelleyin!
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="admin/login.php" style="color: #ff0000; text-decoration: none;">← Admin Giriş Sayfasına Dön</a>
        </div>
    </div>
</body>
</html>

