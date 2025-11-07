<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Geçersiz istek metodu');
}

// Form verilerini al ve temizle
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// Validasyon
$errors = [];

if (empty($name)) {
    $errors[] = 'Ad alanı zorunludur.';
}

if (empty($email)) {
    $errors[] = 'E-posta alanı zorunludur.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
}

if (empty($message)) {
    $errors[] = 'Mesaj alanı zorunludur.';
}

if (!empty($errors)) {
    jsonResponse(false, implode(' ', $errors));
}

// Veritabanına kaydet
try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $message);
    
    if ($stmt->execute()) {
        // E-posta gönder (info@yayboxing.com.tr)
        $to = 'info@yayboxing.com.tr';
        $subject = 'YAY Boxing Club - Yeni İletişim Formu Mesajı';
        $message_body = "Yeni bir iletişim formu mesajı alındı:\n\n";
        $message_body .= "Ad: " . $name . "\n";
        $message_body .= "E-posta: " . $email . "\n";
        $message_body .= "Telefon: " . ($phone ?: 'Belirtilmemiş') . "\n";
        $message_body .= "Mesaj:\n" . $message . "\n";
        
        $headers = "From: noreply@yayboxing.com.tr\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // E-posta göndermeyi dene (hata olsa bile başarı mesajı göster)
        @mail($to, $subject, $message_body, $headers);
        
        jsonResponse(true, 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.');
    } else {
        jsonResponse(false, 'Bir hata oluştu. Lütfen tekrar deneyin.');
    }
    
    $stmt->close();
} catch (Exception $e) {
    jsonResponse(false, 'Bir hata oluştu: ' . $e->getMessage());
}
?>

