# 🎥 Laravel WebRTC Görüntülü Görüşme

Bu proje, **Laravel** kullanılarak geliştirilmiş basit bir **WebRTC tabanlı görüntülü görüşme** uygulamasıdır.  
Herhangi bir üçüncü taraf API kullanılmadan, tarayıcılar arasında **P2P (peer-to-peer)** bağlantı kurularak çalışır.  
Sinyalleşme işlemleri Laravel backend üzerinden yönetilmektedir.

---

## 🚀 Özellikler
- Oda oluşturma veya davet linki ile katılma
- WebRTC üzerinden gerçek zamanlı görüntü ve ses aktarımı
- Laravel tabanlı sinyalleşme mekanizması
- API kullanmadan direkt tarayıcılar arası bağlantı

---

## ⚙️ Kurulum

1. Projeyi klonlayın:
   ```bash
   git clone https://github.com/kullanici_adin/proje_adi.git
   cd proje_adi
Laravel bağımlılıklarını yükleyin:

composer install
npm install


Ortam dosyası oluşturun:

cp .env.example .env
php artisan key:generate


Sunucuyu başlatın:

php artisan serve
npm run dev

🔑 Notlar

.env dosyası GitHub’a yüklenmemeli, kendi ortam ayarlarınıza göre düzenlenmelidir.

Proje, API kullanmadan tamamen WebRTC + Laravel altyapısı ile çalışır.

Her kullanıcı kendi tarayıcısı üzerinden oda oluşturabilir veya davet kodu ile katılabilir.

📜 Lisans

Bu proje açık kaynaklıdır. Dilerseniz MIT License ile paylaşabilirsiniz.
