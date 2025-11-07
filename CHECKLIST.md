# ✅ cPanel Yayına Alma Kontrol Listesi

## 📦 Ön Hazırlık

- [ ] Tüm dosyaların yedeğini aldım
- [ ] Veritabanı yedeğini aldım
- [ ] Domain adresimi not ettim
- [ ] cPanel giriş bilgilerim hazır

## 🗄️ Veritabanı İşlemleri

- [ ] cPanel'de MySQL Databases bölümüne gittim
- [ ] Yeni veritabanı oluşturdum (örn: `kullanici_yayboxing`)
- [ ] Yeni kullanıcı oluşturdum ve veritabanına yetki verdim
- [ ] Veritabanı adı, kullanıcı adı ve şifreyi not ettim
- [ ] phpMyAdmin'e gittim
- [ ] `database.sql` dosyasını import ettim
- [ ] Veritabanı tablolarının oluştuğunu kontrol ettim

## 📁 Dosya Yükleme

- [ ] Tüm dosyaları `public_html` klasörüne yükledim
- [ ] Dosya yapısının doğru olduğunu kontrol ettim
- [ ] Dosya izinlerini ayarladım (klasörler: 755, dosyalar: 644)

## ⚙️ Yapılandırma

- [ ] `config.php` dosyasını düzenledim
  - [ ] Veritabanı bilgilerini güncelledim
  - [ ] Domain adresini güncelledim (SITE_URL)
  - [ ] HTTPS kullanıyorsam `session.cookie_secure = 1` yaptım
  - [ ] Error reporting'i kapattım (`error_reporting(0)`)
- [ ] `.htaccess` dosyasını kontrol ettim
- [ ] HTTPS kullanıyorsam `.htaccess`'te HTTPS yönlendirmesini aktif ettim

## 🔒 Güvenlik

- [ ] Varsayılan admin şifresini değiştirdim
- [ ] Güçlü bir şifre kullandım (min. 8 karakter, büyük/küçük harf, rakam, özel karakter)
- [ ] `config.php` dosyasının izinlerini kontrol ettim (644)
- [ ] Hassas dosyaların erişilemez olduğunu kontrol ettim

## 🧪 Test

- [ ] Ana sayfayı ziyaret ettim: `https://yayboxing.com.tr`
- [ ] Site düzgün görünüyor
- [ ] CSS ve JS dosyaları yükleniyor
- [ ] İletişim formu çalışıyor
- [ ] Admin paneline giriş yaptım: `https://yayboxing.com.tr/admin`
- [ ] Admin paneli düzgün çalışıyor
- [ ] Mesaj gönderme testi yaptım
- [ ] İçerik yönetimi çalışıyor

## 🔐 SSL/HTTPS (Önerilir)

- [ ] cPanel'de SSL/TLS bölümüne gittim
- [ ] Let's Encrypt veya başka bir SSL sertifikası kurdum
- [ ] `.htaccess`'te HTTPS yönlendirmesini aktif ettim
- [ ] `config.php`'de `session.cookie_secure = 1` yaptım
- [ ] HTTPS çalışıyor

## 📊 Yedekleme

- [ ] İlk yedeği aldım
- [ ] Yedekleme planı oluşturdum (haftalık/aylık)

## 📝 Son Kontroller

- [ ] Tüm linkler çalışıyor
- [ ] Resimler yükleniyor
- [ ] Mobil görünüm düzgün
- [ ] Admin paneli tüm özellikleriyle çalışıyor
- [ ] Error loglarını kontrol ettim (hata yok)

## 🎉 Tamamlandı!

- [ ] Site canlı ve çalışıyor
- [ ] Tüm özellikler test edildi
- [ ] Güvenlik ayarları yapıldı

---

## ⚠️ Önemli Notlar

1. **İlk girişte mutlaka admin şifresini değiştirin!**
2. **Düzenli yedek almayı unutmayın!**
3. **Güvenlik güncellemelerini takip edin!**
4. **Error loglarını düzenli kontrol edin!**

## 🆘 Sorun Giderme

### Veritabanı bağlantı hatası:
- Veritabanı bilgilerini tekrar kontrol edin
- Kullanıcının veritabanına yetkisi olduğundan emin olun
- cPanel'de veritabanı durumunu kontrol edin

### 404 hatası:
- `.htaccess` dosyasının mevcut olduğundan emin olun
- Dosya yollarını kontrol edin
- cPanel'de mod_rewrite aktif mi kontrol edin

### CSS/JS yüklenmiyor:
- Dosya yollarını kontrol edin
- Browser cache'i temizleyin
- Dosya izinlerini kontrol edin

### Admin paneline giriş yapamıyorum:
- Veritabanının doğru import edildiğinden emin olun
- Varsayılan şifreyi deneyin: `b190758x`
- Kullanıcı adı: `admin`
- Veritabanı bağlantısını kontrol edin

