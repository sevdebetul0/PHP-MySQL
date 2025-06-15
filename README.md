# 🎬 Dizi ve Film Lokasyonları Uygulaması

Bu proje, kullanıcıların dizi ve film çekimlerinin yapıldığı lokasyonları ekleyip görüntüleyebildiği, tekrar düzenleyebildiği bir PHP & MySQL tabanlı web uygulamasıdır.
---
## 📌 Özellikler

- ✅ Yeni dizi/film lokasyonu ekleme
- ✅ Eklenen lokasyonları listeleme ve detaylarını görme
- ✅ Lokasyon bilgilerini düzenleme
- ✅ Lokasyon adresini haritada açabilme
- ✅ Kullanıcı kayıt ve giriş sistemi
- ✅ Aynı e-posta ile tekrar kayıt olmayı engelleme
- ✅ Oturum yönetimi

---
## 🛠️ Kullanılan Teknolojiler

- PHP (Sunucu tarafı programlama)
- MySQL (Veritabanı)
- BootStrap
---
## 📁 Proje Klasör Yapısı

```plaintext
movie-locations-project/
├── index.php                # Ana sayfa – lokasyonları listeler
├── Location.php             # Lokasyonların tutulduğu sınıf
├── register.php             # Kayıt olma işlemi
├── User.php                 # Kullanıcıların tutulduğu sınıf
├── login.php                # Giriş yapma işlemi
├── logout.php               # Çıkış yapma işlemi 
├── add_location.php         # Yeni lokasyon ekleme formu ve işlemi
├── edit_location.php        # Lokasyon düzenleme işlemi
├── view_location.php        # Lokasyon görüntüleme işlemi
├── Database.php             # Veritabanı bağlantı sınıfı 
├── SessionManager.php       # Oturum yönetimi 
├── README.md                # Bu dosya – proje açıklaması
└── db.sql                   # Veritabanı tablolarını oluşturacak SQL dosyası
```
## 📸 Ekran Görüntüleri

🧾Kayıt olma
![image](https://github.com/user-attachments/assets/78f42b34-1bf3-4468-8628-cc8dcaddff5f)

🔑Giriş yapma
![Ekran görüntüsü 2025-06-15 151444](https://github.com/user-attachments/assets/9f13dace-75d6-4b94-a28d-a369cd00def4)

🏠Anasayfa- Lokasyon Listeleri
![Ekran görüntüsü 2025-06-15 151457](https://github.com/user-attachments/assets/2fa20de3-eb34-4530-bf37-1d87ca530126)
![Ekran görüntüsü 2025-06-15 151545](https://github.com/user-attachments/assets/b2445be6-aa44-4e6f-bac0-8badf323cadb)

🎯Lokasyon Ekleme
![image](https://github.com/user-attachments/assets/5dcb2891-07a6-4925-b5be-3411c473998a)

🖼️Lokasyon Ayrıntıları Görüntüleme
![Ekran görüntüsü 2025-06-15 153334](https://github.com/user-attachments/assets/5de6fcf8-7bde-44ef-b122-db2a499bfa67)
![Ekran görüntüsü 2025-06-15 153354](https://github.com/user-attachments/assets/aa4a4112-fb9d-4ef6-88ad-aca825c80a79)

## 🔗 Uygulama
1. Kullanıcı kayıt olur ve giriş yapar.
2. Kullanıcı;
   
  - Film/dizi lokasyonu, adres bilgisi, fotoğraf ekler.
   
  - Hangi sahnenin çekildiğinin açıklamasını, ziyaret edip etmediğinin bilgisini girer.
4. Anasayfada lokasyonlar listelenir. Toplam lokasyon sayısı, yüzde olarak kaçının tamamlandığı bilgisi verilir.
5. Kullanıcı çıkış yapar.
   




