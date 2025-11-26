# Konteks Proyek: DAGANG.IN

Kita sedang membangun platform E-commerce khusus untuk membantu UMKM Lokal agar bisa go-digital.
Tujuan utama: Membuat sistem yang sederhana, ringan, dan mudah digunakan oleh orang awam.

# Tech Stack (ATURAN KETAT)

- **Backend:** PHP Native Murni. DILARANG menggunakan framework (seperti Laravel, CI, Symfony). Gunakan `PDO` untuk koneksi database agar aman dari SQL Injection.
- **Frontend:** HTML5, CSS3 (Framework: Bootstrap 5.3), JavaScript (Vanilla JS).
- **Database:** MySQL / MariaDB.
- **Styling:** UTAMAKAN penggunaan _Utility Classes_ Bootstrap 5 (contoh: `p-3`, `d-flex`, `text-center`, `btn-primary`) sebelum menulis CSS custom manual.

# Aturan Penulisan Kode (Coding Standards)

## 1. Struktur PHP Native

- Gunakan `include` atau `require_once` untuk memecah komponen (contoh: header, footer, sidebar).
- Pisahkan file koneksi database (biasanya di `config/koneksi.php`).
- Jangan mencampur logika pemrosesan data (PHP) di tengah-tengah elemen HTML jika tidak perlu. Taruh logika PHP di bagian paling atas file.

## 2. Panduan UI/UX (Fokus UMKM)

- **Mobile First:** Sebagian besar UMKM mengakses lewat HP. Pastikan layout responsif menggunakan Grid System Bootstrap (`col-12` untuk HP, `col-md-6` untuk laptop).
- **Simpel & Jelas:** Hindari desain yang terlalu rumit. Tombol harus besar dan jelas fungsinya (misal: "Simpan Produk", bukan icon disket saja).
- **Feedback:** Berikan notifikasi (Alert Bootstrap) setiap kali user berhasil atau gagal melakukan aksi.

## 3. Instruksi Berpikir Kritis (Critical Thinking)

- **Baca Konteks:** Sebelum memberikan kode, jalankan perintah `@workspace` secara internal untuk memahami struktur folder saya saat ini.
- **Analisis Dampak:** Jika saya menyuruh mengubah layout, peringatkan saya jika perubahan itu akan merusak tampilan di halaman lain yang menggunakan file `include` yang sama.
- **Keamanan:** Selalu tambahkan validasi input (Sanitization) di sisi server (PHP) untuk mencegah XSS dan SQL Injection.

## 4. Gaya Bahasa

- Jelaskan penjelasannya dalam Bahasa Indonesia yang santai tapi teknis.
- Jika memberikan komentar dalam kode (`// comment`), gunakan Bahasa Indonesia agar mudah dibaca tim.
