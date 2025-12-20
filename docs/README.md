# 🛒 Dagang.in - Solusi Digital UMKM Lokal

![Banner Dagang.in](../assets/images/log.png)

> **Platform E-Commerce Sederhana & Handal untuk Membantu UMKM Go Digital.**

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Status](https://img.shields.io/badge/Status-Active-success?style=for-the-badge)

---

## 📖 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Unggulan](#-fitur-unggulan)
- [Tech Stack](#-tech-stack)
- [Struktur Proyek](#-struktur-proyek)
- [Instalasi & Setup](#-instalasi--setup)
- [Screenshot](#-screenshot-aplikasi)
- [Tim Pengembang](#-tim-pengembang)

---

## 💡 Tentang Proyek

**Dagang.in** dibangun untuk menjawab masalah klasik UMKM: pembukuan manual yang berantakan dan jangkauan pasar yang sempit. Aplikasi ini dirancang dengan pendekatan **Mobile-First** dan **User-Friendly** agar mudah digunakan oleh pemilik usaha yang awam teknologi sekalipun.

**Mengapa Dagang.in?**

- ✅ **Ringan:** Dibangun dengan PHP Native yang efisien.
- ✅ **Aman:** Proteksi CSRF, XSS Filtering, dan Password Hashing.
- ✅ **Lengkap:** Dari manajemen stok hingga laporan keuangan sederhana.

---

## 🚀 Fitur Unggulan

### 🏢 Untuk Penjual (Admin UMKM)

- **Dashboard Statistik:** Pantau omzet, stok menipis, dan total order secara real-time.
- **Manajemen Produk:** Tambah, edit, arsip (Soft Delete) produk dengan mudah.
- **Laporan Fleksibel:** Export data pesanan ke **CSV** (untuk olah data) atau **Cetak PDF** (untuk laporan fisik).
- **Manajemen Kategori:** Kelola kategori produk dinamis.

### 🛍️ Untuk Pembeli (Pelanggan)

- **Etalase Responsif:** Tampilan nyaman di HP maupun Laptop.
- **Smart Search:** Filter produk berdasarkan harga, nama, dan ketersediaan stok.
- **Click-to-Chat:** Tombol WhatsApp otomatis yang langsung menghubungkan pembeli ke penjual dengan pesan pre-filled.

---

## 🛠 Tech Stack

| Komponen         | Teknologi         | Alasan Pemilihan                                                   |
| :--------------- | :---------------- | :----------------------------------------------------------------- |
| **Backend**      | PHP Native (8.0+) | Performa tinggi, _low overhead_, mudah di-deploy di hosting murah. |
| **Frontend**     | HTML5, CSS3, JS   | Vanilla JS untuk interaktivitas ringan tanpa bloatware.            |
| **Framework UI** | Bootstrap 5       | Responsif dan modern _out-of-the-box_.                             |
| **Database**     | MySQL / MariaDB   | Relasional database yang stabil untuk transaksi.                   |
| **Server**       | Apache (XAMPP)    | Environment standar pengembangan web.                              |

---

## 📂 Struktur Proyek

```text
dagang.in/
├── admin/              # Panel Admin (Backend Logic)
│   ├── dashboard.php   # Halaman utama admin
│   ├── products.php    # CRUD Produk
│   └── ...
├── assets/             # Static Assets
│   ├── css/            # Styling (Custom & Libs)
│   ├── js/             # JavaScript Logic
│   └── uploads/        # Folder Gambar Produk
├── config/             # Konfigurasi & Helper
│   ├── includes/       # DB Connection, Auth
│   └── utils/          # Class Helper (Validator, Pagination)
├── database/           # Skema Database & Migrasi
├── docs/               # Dokumentasi Proyek
└── public/             # Halaman Depan (Storefront)
    ├── index.php       # Landing Page
    └── ...


```

## 📸 Screenshot Aplikasi

|            Halaman Depan (Landing Page)            |                     Dashboard Admin                     |
| :------------------------------------------------: | :-----------------------------------------------------: |
| <img src="..\assets\images\home.png" width="100%"> | <img src="..\assets\images\dashboard.png" width="100%"> |

|                   Manajemen Produk                   |                    Detail Produk                     |
| :--------------------------------------------------: | :--------------------------------------------------: |
| <img src="..\assets\images\produk.png" width="100%"> | <img src="..\assets\images\detail.png" width="100%"> |

---

## 👥 Tim Pengembang

Proyek ini dikembangkan oleh **Tim Dagang.in** untuk mata kuliah Manajemen Proyek TI (MPTI).

- **Gujestok J. Welay** - _Project Manager & Lead Developer_
- **Tina** - _UI/UX Designer_
- **Esly & Joan** - _Frontend Developer_
- **Mirna & Gloria** - _Backend Developer_
- **Ralf J. Patikawa** - _Quality Assurance_
- **Almendo** - _Documentation_

---

---

## 🗺️ Roadmap Pengembangan (Rencana Masa Depan)

Meskipun versi 1.0 sudah rilis, kami memiliki rencana besar untuk Dagang.in versi selanjutnya:

- [ ] **Integrasi Payment Gateway:** Pembayaran otomatis via QRIS/Virtual Account (Midtrans/Xendit).
- [ ] **Cek Ongkir Otomatis:** Integrasi API RajaOngkir untuk hitung biaya pengiriman.
- [ ] **Notifikasi Real-time:** Notifikasi WA otomatis ke penjual saat ada pesanan baru.
- [ ] **Migrasi Framework:** Upgrade backend ke Laravel untuk skalabilitas tinggi.

---

## 📄 Lisensi

Didistribusikan di bawah Lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.

---

<center\>Dibuat dengan ❤️ untuk UMKM Indonesia\</center\>
