# Dagang.in - Aplikasi Web untuk UMKM

Aplikasi web full-stack sederhana untuk membantu UMKM (Usaha Mikro, Kecil, dan Menengah) go digital dengan mudah mengelola produk, stok, pesanan, dan etalase online.

## 🚀 Fitur Utama

### Manajemen Produk & Stok

- ✅ Tambah, edit, hapus produk
- ✅ Upload gambar produk
- ✅ Stok otomatis berkurang saat pesanan masuk
- ✅ CRUD lengkap untuk produk

### Pencatatan Pesanan & Pelanggan

- ✅ Form input pesanan baru
- ✅ Tracking status pesanan (pending, diproses, selesai)
- ✅ Database pelanggan terintegrasi
- ✅ Detail pesanan lengkap

### Dashboard Penjualan

- ✅ Ringkasan penjualan harian/bulanan
- ✅ Grafik sederhana progress penjualan
- ✅ Statistik produk terlaris
- ✅ Overview bisnis real-time

### Profil & Etalase Online

- ✅ Halaman profil toko UMKM
- ✅ Display produk dengan grid layout responsif
- ✅ Informasi kontak dan alamat lengkap

### Integrasi WhatsApp

- ✅ Tombol chat langsung ke nomor penjual
- ✅ Pre-filled message dengan produk yang dipilih
- ✅ Mudah menghubungi pelanggan

## 🛠️ Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5
- **Backend:** PHP Native (tanpa framework)
- **Database:** MySQL
- **Server:** XAMPP Environment
- **Tools:** VS Code, phpMyAdmin

## 📋 Struktur Database

```sql
- users: Informasi akun UMKM
- products: Data produk dan stok
- customers: Data pelanggan
- orders: Data pesanan
- order_items: Detail item dalam pesanan
```

## 🏗️ Arsitektur Aplikasi

```
dagang.in/
├── css/
│   └── style.css          # Custom styles
├── js/
│   └── (future scripts)
├── includes/
│   ├── config.php         # Database configuration
│   └── auth.php           # Authentication functions
├── php/
│   └── (future PHP files)
├── uploads/               # Product images
├── images/                # Static images
├── public/index.php       # Public storefront
├── login.php              # Admin login
├── register.php           # Admin registration
├── dashboard.php          # Admin dashboard
├── products.php           # Product management
├── add_product.php        # Add new product
├── orders.php             # Order management
├── add_order.php          # Add new order
├── database_schema.sql    # Database schema
└── README.md              # This file
```

## ⚙️ Instalasi & Setup

### Persyaratan Sistem

- XAMPP (Apache, MySQL, PHP)
- Browser web modern
- VS Code (opsional)

### Langkah Instalasi

1. **Clone atau Download Project**

   ```bash
   # Jika menggunakan Git
   git clone https://github.com/username/dagang.in.git
   cd dagang.in
   ```

2. **Setup XAMPP**

   - Pastikan XAMPP terinstall
   - Start Apache dan MySQL di XAMPP Control Panel

3. **Import Database**

   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru: `dagang_in`
   - Import file `database_schema.sql`

4. **Konfigurasi Database**

   - Edit `includes/config.php` jika perlu
   - Default: host=localhost, user=root, password=(kosong), db=dagang_in

5. **Akses Aplikasi**
   - Frontend Toko: http://localhost/dagang.in/
   - Admin Login: http://localhost/dagang.in/login.php

### Akun Default

- Username: admin
- Password: admin123 (hashed di database)

## 📖 Cara Penggunaan

### Untuk UMKM (Admin)

1. **Registrasi/Login** ke dashboard admin
2. **Kelola Produk**: Tambah produk baru dengan gambar
3. **Kelola Pesanan**: Input pesanan manual atau otomatis
4. **Pantau Dashboard**: Lihat statistik penjualan
5. **Hubungi Pelanggan**: Via WhatsApp integration

### Untuk Pembeli

1. **Kunjungi Etalase**: Lihat produk di halaman utama
2. **Pilih Produk**: Klik untuk detail
3. **Hubungi Penjual**: Tombol WhatsApp langsung

## 🔒 Keamanan

- Password hashing menggunakan `password_hash()`
- Input sanitization untuk mencegah SQL injection
- Session-based authentication
- File upload validation

## 🎨 Tema & UI

- **Tema**: Local dan cocok untuk UMKM Indonesia
- **Responsif**: Mobile-friendly dengan Bootstrap
- **User-friendly**: Mudah digunakan tanpa training
- **Clean Design**: Modern namun sederhana

## 📈 Roadmap Pengembangan

- [ ] Dashboard dengan grafik chart.js
- [ ] Export laporan PDF/Excel
- [ ] Multi-user untuk staff
- [ ] API untuk integrasi marketplace
- [ ] PWA (Progressive Web App)
- [ ] Multi-language support

## 🤝 Kontribusi

1. Fork repository
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Dukungan

Jika ada pertanyaan atau masalah:

- Email: support@dagang.in
- WhatsApp: +62 812-3456-7890
- GitHub Issues: [Buat Issue Baru](https://github.com/username/dagang.in/issues)

---

**Dagang.in** - Membantu UMKM Go Digital dengan Mudah! 🚀
