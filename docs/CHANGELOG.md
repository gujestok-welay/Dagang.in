# Changelog - Dagang.in

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added - 2025-11-22

- **Categories Management**

  - CSRF token protection untuk create, update, dan delete operations
  - Flash message system dengan POST-Redirect-GET pattern
  - Unique slug generator (global, bukan per-user)
  - Validasi format warna hex (#RRGGBB)
  - Proteksi hapus kategori yang masih digunakan produk
  - Debug mode dengan parameter `?debug=1`
  - Table existence check dengan pesan friendly
  - Link to migration page saat tabel belum ada

- **Product Images**

  - Thumbnail generator 400x400 dengan center crop
  - Placeholder SVG default untuk produk tanpa gambar
  - Fallback chain: thumbnail → original → placeholder
  - Auto cleanup gambar & thumbnail lama saat edit
  - GD extension availability check dengan graceful failure
  - ImageProcessor utility class dengan MIME type detection

- **Public Page - Filter & Display**

  - Accessibility: `for`/`id` attributes untuk semua form inputs
  - Accessibility: `aria-label` attributes untuk screen readers
  - Form `action` attribute eksplisit ke `index.php`
  - Input `step="1000"` untuk harga minimum/maksimum
  - Responsive media queries untuk filter-card (768px & 576px)
  - WhatsApp integration dengan auto phone format (08xxx → 628xxx)
  - Markdown formatting untuk pesan WhatsApp
  - Security: `rel="noopener noreferrer"` pada external links

- **Dynamic Product Filter (AJAX/No Reload)**
  - AJAX endpoint untuk filter produk tanpa reload halaman (`ajax_filter_products.php`)
  - JavaScript Fetch API untuk real-time filtering
  - Loading spinner dengan smooth animation
  - Empty state UI untuk hasil pencarian kosong
  - Smooth scroll ke products section saat filter
  - Browser history management dengan pushState API
  - Back/forward button support (popstate handling)
  - Auto update: product count badge, pagination, reset button
  - Pagination links menggunakan AJAX (no page reload)
  - Performance optimization: hanya update product section

### Fixed - 2025-11-22

- **Categories**: `bind_param` type mismatch di UPDATE query (was: `sssssii`, now: `ssssiii`)
- **Categories**: Modal form reset tidak berfungsi (selector ambiguity)
- **Categories**: Slug tidak unique secara global
- **Products (Admin)**: Kolom `image` tersimpan sebagai `0` karena bind type salah (was: `i`, now: `s`)
- **Products (Admin)**: Gambar tidak tampil karena filename tidak tersimpan di database
- **Public Page**: Filter form tidak punya accessibility attributes (labels, aria-labels)
- **Public Page**: WhatsApp link error karena undefined variable `$user['phone']` (fixed: menggunakan `$product['seller_phone']` dari JOIN query)
- **Public Page**: Product card masih pakai gradient placeholder (diganti SVG placeholder)
- **Public Page**: Filter inputs tidak responsive di mobile devices

### Changed - 2025-11-22

- **Categories**: File reconstructed untuk memperbaiki struktur korup
- **Products (Admin)**: Tampilan gambar menggunakan prioritas thumbnail → original → placeholder
- **Public Page**: Image display menggunakan fallback chain yang konsisten dengan admin
- **Public Page**: Query produk sekarang fetch `seller_phone` untuk WhatsApp link

---

## Development Guidelines

**Update Workflow**:
Setiap selesai troubleshooting atau implementasi fitur baru:

1. Update `docs/TODO.md` (move to completed, add new items)
2. Update `docs/CHANGELOG.md` dengan detail perubahan
3. Commit dengan pesan deskriptif

**Format Commit**:

- `fix:` untuk bug fixes
- `feat:` untuk fitur baru
- `docs:` untuk perubahan dokumentasi
- `refactor:` untuk restructuring kode

---

**Last Updated**: 2025-11-22
