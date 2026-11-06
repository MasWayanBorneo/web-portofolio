Buatkan website publik (tanpa halaman admin) untuk "Gita Amarta – Jasa Desain Grafis", sebuah sistem katalog layanan dan pemesanan jasa via WhatsApp. Ini proyek akhir kuliah, jadi kode harus rapi, mudah dijelaskan, dan sesuai spesifikasi di bawah.

## Teknologi (wajib, jangan diganti)

- PHP 8 native (tanpa framework), MySQL/MariaDB, dijalankan di XAMPP.
- Koneksi database pakai PDO, SEMUA query pakai prepared statement.
- HTML, CSS, dan JavaScript vanilla. Tanpa Bootstrap, Tailwind, jQuery, atau library animasi. Font boleh dari Google Fonts.
- Semua output dari database di-escape dengan htmlspecialchars().

## Struktur folder

gita-amarta/
├── config/database.php (koneksi PDO, kredensial default XAMPP: root tanpa password, db: gita_amarta)
├── includes/header.php, footer.php, functions.php (format rupiah, ambil profil usaha, helper escape)
├── assets/css/style.css
├── assets/js/main.js
├── assets/img/ (gambar placeholder SVG buatan sendiri, bukan link eksternal)
├── index.php (beranda)
├── layanan.php (katalog, filter ?kategori=id)
├── detail.php (detail layanan, ?id=)
├── portofolio.php (galeri + lightbox)
├── pesan.php (memproses formulir → redirect ke wa.me)
└── database/gita_amarta.sql (struktur + data contoh)

## Database (ikuti persis, karena sudah ada di proposal)

- kategori: id_kategori PK AI, nama_kategori VARCHAR(50), deskripsi TEXT
- layanan: id_layanan PK AI, id_kategori FK, nama_layanan VARCHAR(100), deskripsi TEXT, harga_min INT, harga_max INT, estimasi_hari INT, gambar VARCHAR(255), status ENUM('aktif','nonaktif')
- portofolio: id_portofolio PK AI, id_kategori FK, judul VARCHAR(100), deskripsi TEXT, gambar VARCHAR(255), tanggal DATE
- admin: id_admin PK AI, nama, username UNIQUE, password VARCHAR(255), email, created_at (buat tabelnya saja, belum dipakai)
- profil_usaha: id_profil PK AI, nama_usaha, deskripsi, no_whatsapp VARCHAR(20) format 62xxx, email, instagram, alamat

Data contoh:

- Kategori: Logo, Poster, Kemasan, UI/UX.
- Minimal 7 layanan, termasuk: Desain Logo (Rp300.000–Rp750.000, 5 hari), Logo Premium (Rp1.000.000–Rp2.000.000, 7 hari), Desain Poster (Rp150.000–Rp400.000, 3 hari). Satu layanan berstatus nonaktif untuk menguji bahwa layanan itu tidak tampil.
- Minimal 12 portofolio tersebar di semua kategori.
- 1 baris profil_usaha.

## Halaman dan kebutuhan fungsional

1. Beranda (KF-01)
   - Hero: nama usaha + deskripsi dari profil_usaha, tombol "Lihat Layanan" dan "Lihat Portofolio", gambar portofolio terbaru.
   - "Layanan Unggulan": 4 layanan berstatus aktif dengan id_layanan terbesar (kartu: gambar, nama, kisaran harga, tombol Detail).
   - Section "Cara Pesan" (id="cara-pesan"): 3 langkah, yaitu Pilih layanan → Isi formulir → Lanjut diskusi di WhatsApp.
   - Footer "Kontak" (id="kontak"): WhatsApp, Instagram, email, alamat dari profil_usaha.
   - Menu navbar: Beranda, Layanan, Portofolio, Cara Pesan (anchor ke beranda#cara-pesan), Kontak (anchor ke #kontak).
2. Katalog layanan (KF-02): grid kartu, hanya layanan aktif, dengan chip filter "Semua" + tiap kategori. Filter berjalan di sisi klien dengan animasi, tetapi tetap berfungsi tanpa JavaScript lewat link ?kategori=id.
3. Detail layanan (KF-03): breadcrumb, gambar sampul, kisaran harga, estimasi pengerjaan (hari kerja), deskripsi, dan "Contoh Karya" berupa 4 portofolio dengan kategori yang sama, plus link ke portofolio. Jika id tidak valid atau layanan nonaktif, tampilkan halaman "Layanan tidak ditemukan".
4. Formulir pemesanan (KF-05, KF-06), di sebelah detail (desktop) atau di bawahnya (mobile):
   - Field: layanan (readonly, terisi otomatis), nama lengkap*, nomor WhatsApp*, deskripsi kebutuhan\*, tenggat waktu (date, opsional).
   - Validasi di JavaScript DAN diulang di pesan.php: nama wajib; nomor WA hanya angka, 10–15 digit; deskripsi wajib; tenggat tidak boleh tanggal yang sudah lewat.
   - Jika gagal: kotak peringatan di atas form + pesan error di bawah tiap kolom yang salah, isian yang benar tetap terisi, WhatsApp tidak dibuka.
   - Jika valid: susun pesan rapi (nama layanan, nama, no. WA, kebutuhan, tenggat), encode dengan rawurlencode, lalu redirect ke https://wa.me/{no_whatsapp dari profil_usaha}?text=...
   - Catatan di bawah form: "File referensi desain dapat dikirim langsung melalui WhatsApp."
   - Data pemesanan TIDAK disimpan ke database.
5. Portofolio (KF-04): grid galeri dengan filter kategori seperti di katalog. Klik karya membuka lightbox: gambar besar, judul, kategori, tanggal, deskripsi, tombol sebelumnya/berikutnya, tutup dengan tombol ✕, klik di luar, atau tombol Esc.
6. Tombol WhatsApp melayang di semua halaman publik.

## Kebutuhan non-fungsional

- Responsive di HP (360px), tablet, dan desktop, tanpa scroll horizontal. Di mobile navbar menjadi hamburger menu.
- Gambar memakai loading="lazy" dan ukuran yang wajar.
- Berjalan di Chrome, Firefox, dan Edge versi terbaru.
- Mudah dipahami tanpa panduan: label jelas, tombol utama menonjol, kontras teks cukup (WCAG AA).

## Desain visual

- Kesan: studio desain yang kreatif, segar, dan percaya diri, bukan template korporat.
- Palet (pakai CSS custom properties):
  --bg #FFF8F0 (krem), --ink #1A1B3A (navy gelap untuk teks), --primary #6C4CF1 (ungu), --accent #FF7A59 (koral), --sun #FFC94A (kuning, untuk aksen kecil saja), --muted #6B6B85, --card #FFFFFF.
- Font: "Plus Jakarta Sans" (heading tebal 700–800, body 400–500).
- Sudut membulat (radius 16–24px), bayangan lembut, bentuk blob atau gradien halus ungu→koral di belakang hero, harga ditampilkan dengan font tebal.
- Satu tema terang saja.

## Motion (modern tapi ringan)

- Hero: judul muncul per kata dengan stagger (fade + naik), blob latar bergerak pelan.
- Scroll reveal pakai IntersectionObserver: elemen fade-up dengan stagger antar kartu, cukup sekali saja (tidak berulang).
- Kartu: hover terangkat dan bayangan membesar, gambar zoom halus di dalam kartu.
- Filter: kartu yang hilang mengecil dan memudar, yang muncul masuk dengan stagger, chip aktif punya indikator yang meluncur.
- Lightbox: overlay blur + gambar scale-in, transisi saat ganti gambar.
- Navbar: transparan di atas, menjadi solid dengan blur saat discroll.
- Tombol WhatsApp: pulse halus setiap beberapa detik.
- Error form: kolom bergetar singkat (shake) sekali.
- Hanya animasikan transform dan opacity, durasi 200–600ms, easing cubic-bezier(.2,.8,.2,1).
- Wajib: @media (prefers-reduced-motion: reduce) mematikan semua animasi non-esensial.

## Output yang saya minta

1. Seluruh file lengkap, tanpa potongan "// ... sisanya sama".
2. File SQL siap di-import lewat phpMyAdmin.
3. Langkah menjalankan di XAMPP.
4. Daftar uji singkat sesuai skenario ini, dan pastikan semuanya lolos:
   - Filter kategori hanya menampilkan item kategori itu (katalog dan portofolio).
   - Detail menampilkan deskripsi, harga, estimasi, dan contoh karya.
   - Form valid → WhatsApp terbuka dengan pesan berisi nama layanan dan isi form.
   - Kolom wajib kosong → muncul pesan error, WhatsApp tidak terbuka.
   - Nomor WA berisi huruf → ditolak.
   - Layanan nonaktif tidak tampil di katalog maupun beranda.
   - Dibuka di lebar 360px → tidak ada scroll horizontal.

Kode akan dikembangkan lagi untuk halaman admin, jadi pisahkan query ke functions.php, jangan ditulis langsung di tengah HTML.
