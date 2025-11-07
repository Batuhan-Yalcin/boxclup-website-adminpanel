# cPanel Yayına Alma Rehberi

## 📋 Yapılması Gerekenler

### 1. Veritabanı Oluşturma (cPanel MySQL)

1. cPanel'e giriş yapın
2. **MySQL Databases** bölümüne gidin
3. Yeni veritabanı oluşturun (örn: `kullanici_yayboxing`)
4. Yeni kullanıcı oluşturun ve veritabanına yetki verin
5. **Veritabanı adı, kullanıcı adı ve şifreyi not edin!**

### 2. Veritabanı İçe Aktarma

1. cPanel'de **phpMyAdmin**'e gidin
2. Oluşturduğunuz veritabanını seçin
3. **Import** sekmesine gidin
4. `database.sql` dosyasını yükleyin ve çalıştırın
5. Veya SQL sekmesinden `database.sql` içeriğini kopyalayıp çalıştırın

### 3. Dosyaları Yükleme

#### FTP ile:
1. FileZilla veya başka bir FTP programı kullanın
2. cPanel FTP bilgilerinizle bağlanın
3. `public_html` klasörüne tüm dosyaları yükleyin
4. Dosya yapısı:
   ```
   public_html/
   ├── admin/
   ├── config.php
   ├── index.php
   ├── style.css
   ├── script.js
   └── ... (diğer dosyalar)
   ```

#### cPanel File Manager ile:
1. cPanel'de **File Manager**'a gidin
2. `public_html` klasörüne gidin
3. Tüm dosyaları ZIP olarak sıkıştırın
4. cPanel'de yükleyin ve çıkartın

### 4. Config.php Güncelleme

`config.php` dosyasını düzenleyin:

```php
// Veritabanı Yapılandırması
define('DB_HOST', 'localhost'); // Genellikle localhost kalır
define('DB_USER', 'cpanel_kullanici_adi'); // cPanel'de oluşturduğunuz kullanıcı
define('DB_PASS', 'veritabani_sifresi'); // Veritabanı şifresi
define('DB_NAME', 'cpanel_veritabani_adi'); // Veritabanı adı

// Site Yapılandırması
define('SITE_URL', 'https://yayboxing.com.tr'); // Domain adresiniz
define('ADMIN_URL', SITE_URL . '/admin');
```

**ÖNEMLİ:**
- HTTPS kullanıyorsanız `session.cookie_secure` değerini `1` yapın
- Error reporting'i kapatın (üretim için)

### 5. Güvenlik Ayarları

`config.php` dosyasında şu satırları değiştirin:

```php
// Hata Raporlama (ÜRETİM İÇİN KAPALI)
error_reporting(0);
ini_set('display_errors', 0);

// HTTPS kullanıyorsanız:
ini_set('session.cookie_secure', 1);
```

### 6. Dosya İzinleri (Permissions)

cPanel File Manager'da şu izinleri ayarlayın:
- Klasörler: **755**
- Dosyalar: **644**
- `config.php`: **644** (güvenlik için)

### 7. .htaccess Kontrolü

`.htaccess` dosyasının mevcut olduğundan emin olun. Gerekirse oluşturun.

### 8. Test Etme

1. Ana sayfayı ziyaret edin: `https://yayboxing.com.tr`
2. Admin paneline giriş yapın: `https://yayboxing.com.tr/admin`
3. Varsayılan giriş bilgileri:
   - Kullanıcı: `admin`
   - Şifre: `b190758x`
4. **Güvenlik için şifrenizi düzenli olarak değiştirmeniz önerilir.**

### 9. SSL Sertifikası (Önerilir)

1. cPanel'de **SSL/TLS** bölümüne gidin
2. Let's Encrypt veya başka bir SSL sertifikası kurun
3. HTTPS yönlendirmesi için `.htaccess` güncelleyin

### 10. Yedekleme

Düzenli yedek alın:
- Veritabanı yedeği (cPanel > phpMyAdmin > Export)
- Dosya yedeği (cPanel > Backup)

## ⚠️ ÖNEMLİ GÜVENLİK NOTLARI

1. ✅ Varsayılan admin şifresini değiştirin
2. ✅ Güçlü şifre kullanın
3. ✅ Error reporting'i kapatın
4. ✅ HTTPS kullanın
5. ✅ Düzenli yedek alın
6. ✅ Admin panelini sadece güvenli ağlardan kullanın

## 🔧 Sorun Giderme

### Veritabanı bağlantı hatası:
- Veritabanı bilgilerini kontrol edin
- Kullanıcının veritabanına yetkisi olduğundan emin olun

### 404 hatası:
- `.htaccess` dosyasını kontrol edin
- Dosya yollarını kontrol edin

### CSS/JS yüklenmiyor:
- Dosya yollarını kontrol edin
- Browser cache'i temizleyin

### Admin paneline giriş yapamıyorum:
- Veritabanının doğru import edildiğinden emin olun
- Varsayılan şifreyi deneyin: `b190758x`
- Kullanıcı adı: `admin`

## 📞 Destek

Sorun yaşarsanız:
1. cPanel error loglarını kontrol edin
2. PHP error loglarını kontrol edin
3. Browser console'u kontrol edin

