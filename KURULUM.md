# 🥊 YAY Boxing Club - Kurulum Rehberi

## Hızlı Kurulum Adımları

### 1️⃣ Veritabanı Ayarlarını Yap

`config.php` dosyasını aç ve veritabanı bilgilerini düzenle:

```php
define('DB_HOST', 'localhost');      // Genelde localhost
define('DB_USER', 'root');            // MySQL kullanıcı adın
define('DB_PASS', '');                // MySQL şifren (varsa)
define('DB_NAME', 'yay_boxing_club'); // Veritabanı adı
```

### 2️⃣ Veritabanını Oluştur

**Seçenek A: phpMyAdmin ile (Kolay)**
1. `http://localhost/phpmyadmin` adresine git
2. Sol menüden "Yeni" tıkla
3. Veritabanı adı: `yay_boxing_club`
4. Karakter seti: `utf8mb4_unicode_ci`
5. "Oluştur" butonuna tıkla
6. Üst menüden "İçe Aktar" sekmesine git
7. `database.sql` dosyasını seç ve "Git" butonuna tıkla

**Seçenek B: Terminal/Komut Satırı ile**
```bash
# Terminal'de proje klasörüne git
cd "/Users/test/Desktop/yay box clup"

# MySQL'e bağlan ve veritabanını oluştur
mysql -u root -p < database.sql
```

**Seçenek C: Otomatik Kurulum (En Kolay)**
1. Tarayıcıda `http://localhost/yay%20box%20clup/setup.php` adresine git
2. Adımları takip et

### 3️⃣ Web Sunucusunu Başlat

**XAMPP kullanıyorsan:**
- XAMPP Control Panel'i aç
- Apache'yi başlat
- MySQL'i başlat

**MAMP kullanıyorsan:**
- MAMP'i aç
- "Start Servers" butonuna tıkla

**WAMP kullanıyorsan:**
- WAMP'ı aç
- Yeşil ikon olana kadar bekle

### 4️⃣ Siteyi Aç

Tarayıcıda şu adrese git:
```
http://localhost/yay%20box%20clup/index.php
```

veya klasör adını değiştirdiysen:
```
http://localhost/elite-boxing-club/index.php
```

### 5️⃣ Admin Panele Giriş Yap

```
http://localhost/yay%20box%20clup/admin/login.php
```

**Varsayılan Giriş Bilgileri:**
- Kullanıcı Adı: `admin`
- Şifre: `admin123`

⚠️ **ÖNEMLİ:** Üretim ortamında mutlaka şifreyi değiştir!

### 6️⃣ İlk Yapılacaklar

1. ✅ Admin panele giriş yap
2. ✅ Şifreyi değiştir (Ayarlar bölümünden - yakında eklenecek)
3. ✅ Site içeriklerini kontrol et (İçerik Yönetimi)
4. ✅ Test mesajı gönder (Ana siteden iletişim formu)
5. ✅ Mesajları admin panelden kontrol et

## Sorun Giderme

### Veritabanı Bağlantı Hatası
- MySQL'in çalıştığından emin ol
- `config.php` dosyasındaki bilgileri kontrol et
- Şifre varsa doğru yazdığından emin ol

### Sayfa Açılmıyor
- Apache'nin çalıştığından emin ol
- Dosya yollarını kontrol et
- `.htaccess` dosyasının mevcut olduğundan emin ol

### Form Gönderilmiyor
- Tarayıcı konsolunu aç (F12) ve hataları kontrol et
- `submit_contact.php` dosyasının mevcut olduğundan emin ol
- PHP hata loglarını kontrol et

## Dosya Yapısı Kontrolü

Şu dosyaların mevcut olduğundan emin ol:
```
✅ index.php
✅ config.php
✅ submit_contact.php
✅ database.sql
✅ style.css
✅ script.js
✅ admin/login.php
✅ admin/index.php
✅ admin/messages.php
✅ admin/content.php
✅ admin/assets/css/admin.css
✅ admin/assets/js/admin.js
```

## İletişim

Sorun yaşarsan:
1. Hata mesajını not al
2. Tarayıcı konsolunu kontrol et (F12)
3. PHP hata loglarını kontrol et

Başarılar! 🥊

