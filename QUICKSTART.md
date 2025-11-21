# 🚀 Quick Start Guide - Priority 3 Features

## Langkah 1: Execute Database Migration

1. Login ke admin dashboard
2. Buka URL: `http://localhost/dagang.in/admin/run_migration.php`
3. Klik tombol **"Run Migration"**
4. Tunggu hingga muncul pesan "Migration Berhasil!"

✅ Database siap dengan categories table

---

## Langkah 2: Test Product Categories

1. Buka menu **"Kategori Produk"** di sidebar
2. Klik **"Tambah Kategori"**
3. Isi form:
   - Nama: "Elektronik"
   - Deskripsi: "Produk elektronik berkualitas"
   - Pilih warna: Hijau
4. Klik **"Simpan"**

✅ Kategori berhasil dibuat

---

## Langkah 3: Add/Edit Produk dengan Kategori

### Tambah Produk:

1. Buka **"Tambah Produk"**
2. Di form, akan ada dropdown **"Kategori"**
3. Pilih kategori yang sudah dibuat
4. Isi form produk lainnya
5. Klik **"Simpan"**

✅ Produk dengan kategori berhasil dibuat

### Edit Produk:

1. Buka halaman **"Manajemen Produk"**
2. Klik tombol **Edit** pada produk
3. Kategori sudah terpilih secara otomatis
4. Ubah kategori jika perlu
5. Klik **"Update"**

✅ Kategori produk berhasil diupdate

---

## Langkah 4: Test Enhanced Dashboard

1. Buka **"Dashboard"** dari sidebar
2. Lihat statistik cards:
   - Total Produk
   - Produk Tersedia
   - Produk Low Stock
   - Total Pesanan
   - Pesanan Menunggu
   - Total Pendapatan
   - Pendapatan Bulan Ini
   - Total Pelanggan
3. Lihat chart order status (doughnut chart)
4. Scroll bawah untuk lihat:
   - Produk Terlaris
   - Tombol Quick Access

✅ Dashboard lengkap dengan statistik dan chart

---

## Langkah 5: Test Bulk Actions

1. Buka **"Manajemen Produk"**
2. Beri centang pada beberapa produk (checkbox di kanan atas card)
3. Lihat **"Bulk Actions Bar"** muncul dengan:
   - Jumlah produk terpilih
   - Tombol "Hapus"
   - Tombol "Batal"
4. Klik **"Hapus"** untuk delete bulk
5. Konfirmasi penghapusan
6. Produk terhapus, checkbox di-clear otomatis

✅ Bulk actions berfungsi dengan baik

---

## Langkah 6: Test Export Features

### Export Produk:

1. Buka **"Manajemen Produk"**
2. Klik dropdown **"Export"** (tombol hijau)
3. Pilih:
   - **"Export ke CSV"** → File CSV download
   - **"Export ke Excel"** → File Excel download
4. Buka file di Excel/Sheets
5. Cek kolom: ID, Nama, Kategori, Deskripsi, Harga, Stok, Dibuat, Diupdate

✅ Semua produk berhasil export dengan format benar

### Export Order:

1. Buka **"Manajemen Pesanan"**
2. Klik dropdown **"Export"** (tombol hijau)
3. Pilih:
   - **"Export ke CSV"** → File CSV download
   - **"Export ke Excel"** → File Excel download
4. Buka file di Excel/Sheets
5. Cek kolom: ID, Nama Pelanggan, Email, Telepon, Total Item, Total Qty, Harga, Status, Dibuat, Diupdate

✅ Semua pesanan berhasil export dengan format benar

---

## Langkah 7: Test Search & Filter

### Manajemen Produk:

1. Di halaman products, test filter:
   - Search by nama produk
   - Filter by harga range
   - Filter by stok status
   - Filter by kategori
2. Klik **"Reset Filters"** untuk clear

✅ Semua filter berfungsi dan pagination terus aktif

### Manajemen Pesanan:

1. Di halaman orders, test filter:
   - Search by nama/phone customer
   - Filter by status
2. Pagination tetap bekerja dengan filter

✅ Search & filter berfungsi normal

---

## 🎯 Checklist Verifikasi

- [ ] Database migration sukses
- [ ] Categories page accessible dan berfungsi
- [ ] Kategori bisa ditambah/edit/delete
- [ ] Add/Edit produk menampilkan category dropdown
- [ ] Dashboard menampilkan semua statistik
- [ ] Chart order status muncul dan terisi data
- [ ] Bulk actions checkbox dan delete berfungsi
- [ ] Export CSV produk bisa didownload
- [ ] Export Excel produk bisa didownload
- [ ] Export CSV order bisa didownload
- [ ] Export Excel order bisa didownload
- [ ] Semua text dalam bahasa Indonesia
- [ ] Font awesome icons muncul
- [ ] Bootstrap styling terlihat normal
- [ ] Tidak ada error di browser console

---

## 📞 Common Issues & Solutions

### Issue: Categories table tidak ada

**Solution:**

- Pastikan sudah run migration di `/admin/run_migration.php`
- Cek database connection di `config/includes/config.php`

### Issue: Export tidak bisa download

**Solution:**

- Cek permission folder `admin/`
- Pastikan session login aktif
- Clear browser cache

### Issue: Category dropdown kosong

**Solution:**

- Pastikan minimal ada satu kategori di database
- Run migration sudah membuat default category
- Cek database connection

### Issue: Dashboard statistik tidak muncul

**Solution:**

- Cek browser console untuk SQL errors
- Pastikan ada data produk dan order di database
- Refresh halaman

---

## 🎉 Status

✅ **Semua Priority 3 features sudah siap ditest!**

**Last Updated:** November 21, 2025
**Version:** 1.0
