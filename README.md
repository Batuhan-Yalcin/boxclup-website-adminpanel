# YAY Boxing Club - Web Sitesi ve Admin Paneli

Modern, animasyonlu boks kulübü web sitesi ve tam özellikli admin paneli.

## Özellikler

### Web Sitesi
- ✨ Modern ve animasyonlu tasarım
- 📱 Tam responsive (mobil, tablet, masaüstü)
- 🎨 Güzel animasyonlar ve efektler
- 📝 Dinamik içerik yönetimi

### Admin Paneli
- 🔐 Güvenli giriş sistemi
- ✉️ İletişim mesajlarını görüntüleme ve yönetme
- 📝 Site içeriklerini düzenleme
- 📊 Dashboard ile istatistikler
- 🗑️ Mesaj silme ve okundu işaretleme

## Kurulum

### Gereksinimler
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu

### Adımlar

1. **Veritabanını Oluştur**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Veritabanı Ayarlarını Yapılandır**
   `config.php` dosyasını düzenleyin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'yay_boxing_club');
   ```

3. **Web Sunucusunu Yapılandır**
   - Apache: DocumentRoot'u proje klasörüne ayarlayın
   - Nginx: root direktifini proje klasörüne ayarlayın

4. **Dosya İzinlerini Ayarlayın**
   ```bash
   chmod 755 admin/
   chmod 644 *.php
   ```

## Varsayılan Admin Girişi

- **Kullanıcı Adı:** admin
- **Şifre:** admin123

⚠️ **ÖNEMLİ:** Üretim ortamında mutlaka şifreyi değiştirin!

## Kullanım

### Web Sitesi
- Ana sayfa: `http://localhost/index.php`
- Tüm içerikler admin panelinden düzenlenebilir

### Admin Paneli
- Giriş: `http://localhost/admin/login.php`
- Dashboard: Mesaj istatistikleri ve son mesajlar
- Mesajlar: Tüm iletişim mesajlarını görüntüleme ve yönetme
- İçerik Yönetimi: Site içeriklerini düzenleme
  - Hero bölümü başlıkları
  - Hakkımızda metni
  - İstatistikler
  - İletişim bilgileri

## Dosya Yapısı

```
/
├── index.php              # Ana sayfa (PHP)
├── index.html             # Eski HTML versiyonu (yedek)
├── style.css              # Ana site stilleri
├── script.js              # Ana site JavaScript
├── config.php              # Veritabanı yapılandırması
├── submit_contact.php      # İletişim formu işleme
├── database.sql            # Veritabanı yapısı
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin giriş
│   ├── logout.php         # Admin çıkış
│   ├── messages.php       # Mesaj yönetimi
│   ├── content.php        # İçerik yönetimi
│   └── assets/
│       ├── css/
│       │   └── admin.css  # Admin panel stilleri
│       └── js/
│           └── admin.js   # Admin panel JavaScript
└── README.md              # Bu dosya
```

## Güvenlik

- ✅ SQL Injection koruması (Prepared Statements)
- ✅ XSS koruması (htmlspecialchars)
- ✅ Session yönetimi
- ✅ Şifre hashleme (password_hash)

## Geliştirme Notları

- PHP hata raporlama geliştirme için açık (üretimde kapatın)
- Veritabanı bağlantısı singleton pattern kullanıyor
- Tüm kullanıcı girdileri sanitize ediliyor

## Destek

Sorularınız için: info@yayboxing.com

## Lisans

© 2025 YAY Boxing Club. Tüm hakları saklıdır.

