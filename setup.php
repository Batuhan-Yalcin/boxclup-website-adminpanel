<?php
/**
 * Kurulum Scripti
 * Bu dosyayı sadece ilk kurulumda kullanın, sonra silin!
 */

// Güvenlik kontrolü - sadece localhost'tan çalışsın
if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    die('Bu script sadece localhost üzerinde çalıştırılabilir!');
}

require_once 'config.php';

$step = $_GET['step'] ?? '1';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '2') {
    // Veritabanı bağlantısını test et
    try {
        $db = getDB();
        $message = '✅ Veritabanı bağlantısı başarılı!';
    } catch (Exception $e) {
        $error = '❌ Veritabanı bağlantı hatası: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '3') {
    // Veritabanını oluştur
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            throw new Exception("Bağlantı hatası: " . $conn->connect_error);
        }
        
        // SQL dosyasını oku ve çalıştır
        $sql = file_get_contents('database.sql');
        $queries = explode(';', $sql);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $conn->query($query);
            }
        }
        
        $conn->close();
        $message = '✅ Veritabanı başarıyla oluşturuldu!';
    } catch (Exception $e) {
        $error = '❌ Hata: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum - YAY Boxing Club</title>
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
        .setup-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #ff0000;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .step {
            margin: 30px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 10px;
        }
        .step h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .step p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #ff0000;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #cc0000;
            transform: translateY(-2px);
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
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🥊 YAY Boxing Club - Kurulum</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($step === '1'): ?>
            <div class="step">
                <h2>Adım 1: Veritabanı Yapılandırması</h2>
                <p><code>config.php</code> dosyasını düzenleyin ve veritabanı bilgilerinizi girin:</p>
                <div class="info-box">
                    <strong>Örnek:</strong><br>
                    DB_HOST: localhost<br>
                    DB_USER: root<br>
                    DB_PASS: (şifreniz)<br>
                    DB_NAME: yay_boxing_club
                </div>
                <form method="POST" action="?step=2">
                    <button type="submit" class="btn">Bağlantıyı Test Et</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($step === '2'): ?>
            <div class="step">
                <h2>Adım 2: Veritabanını Oluştur</h2>
                <p>Veritabanı tablolarını ve varsayılan verileri oluşturmak için aşağıdaki butona tıklayın.</p>
                <form method="POST" action="?step=3">
                    <button type="submit" class="btn">Veritabanını Oluştur</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($step === '3' && $message): ?>
            <div class="step">
                <h2>✅ Kurulum Tamamlandı!</h2>
                <p>Artık web sitenizi kullanabilirsiniz.</p>
                <div class="info-box">
                    <strong>Varsayılan Admin Girişi:</strong><br>
                    Kullanıcı Adı: <code>admin</code><br>
                    Şifre: <code>admin123</code><br><br>
                    <strong>⚠️ ÖNEMLİ:</strong> Üretim ortamında mutlaka şifreyi değiştirin!
                </div>
                <div style="margin-top: 20px;">
                    <a href="index.php" class="btn">Ana Sayfaya Git</a>
                    <a href="admin/login.php" class="btn" style="background: #6c757d; margin-left: 10px;">Admin Panele Git</a>
                </div>
                <div class="alert alert-error" style="margin-top: 20px;">
                    <strong>Güvenlik:</strong> Bu setup.php dosyasını silin veya erişimi engelleyin!
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

