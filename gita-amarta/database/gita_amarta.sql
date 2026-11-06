-- =====================================================================
-- Database  : gita_amarta
-- Proyek    : Gita Amarta – Jasa Desain Grafis
-- Keterangan: Struktur tabel + data contoh. Siap di-import lewat phpMyAdmin.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS portofolio;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS profil_usaha;
DROP TABLE IF EXISTS desainer;
DROP TABLE IF EXISTS tampilan;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Tabel kategori
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
  id_kategori   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(50)  NOT NULL,
  deskripsi     TEXT         NULL,
  PRIMARY KEY (id_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel layanan
-- ---------------------------------------------------------------------
CREATE TABLE layanan (
  id_layanan    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_kategori   INT UNSIGNED NOT NULL,
  nama_layanan  VARCHAR(100) NOT NULL,
  deskripsi     TEXT         NULL,
  harga_min     INT          NOT NULL DEFAULT 0,
  harga_max     INT          NOT NULL DEFAULT 0,
  estimasi_hari INT          NOT NULL DEFAULT 1,
  gambar        VARCHAR(255) NULL,
  status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (id_layanan),
  KEY idx_layanan_kategori (id_kategori),
  KEY idx_layanan_status (status),
  CONSTRAINT fk_layanan_kategori
    FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel portofolio
-- ---------------------------------------------------------------------
CREATE TABLE portofolio (
  id_portofolio INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_kategori   INT UNSIGNED NOT NULL,
  judul         VARCHAR(100) NOT NULL,
  deskripsi     TEXT         NULL,
  gambar        VARCHAR(255) NULL,
  tanggal       DATE         NOT NULL,
  PRIMARY KEY (id_portofolio),
  KEY idx_portofolio_kategori (id_kategori),
  CONSTRAINT fk_portofolio_kategori
    FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel admin (akun untuk masuk ke panel admin: /admin/login.php)
-- ---------------------------------------------------------------------
CREATE TABLE admin (
  id_admin   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama       VARCHAR(100) NOT NULL,
  username   VARCHAR(50)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  email      VARCHAR(100) NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_admin),
  UNIQUE KEY uk_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel profil_usaha
-- ---------------------------------------------------------------------
CREATE TABLE profil_usaha (
  id_profil   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_usaha  VARCHAR(100) NOT NULL,
  deskripsi   TEXT         NULL,
  no_whatsapp VARCHAR(20)  NOT NULL COMMENT 'Format internasional tanpa +, contoh 6281234567890',
  email       VARCHAR(100) NULL,
  instagram   VARCHAR(100) NULL COMMENT 'Username tanpa @',
  alamat      TEXT         NULL,
  PRIMARY KEY (id_profil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel desainer (TAMBAHAN di luar proposal, untuk section "Tentang Desainer"
-- di beranda). Dibuat sebagai tabel terpisah agar tabel proposal tidak berubah.
-- Berisi 1 baris, sama seperti profil_usaha.
-- ---------------------------------------------------------------------
CREATE TABLE desainer (
  id_desainer INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama        VARCHAR(100) NOT NULL,
  peran       VARCHAR(100) NOT NULL,
  bio         TEXT         NOT NULL,
  kutipan     VARCHAR(255) NULL,
  foto        VARCHAR(255) NULL,
  status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif' COMMENT 'nonaktif = section disembunyikan',
  PRIMARY KEY (id_desainer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel tampilan (TAMBAHAN di luar proposal): tema & warna website
-- yang diatur admin. Berisi 1 baris. Warna disimpan dalam format #RRGGBB.
-- ---------------------------------------------------------------------
CREATE TABLE tampilan (
  id_tampilan INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tema        VARCHAR(30)  NOT NULL DEFAULT 'ungu-koral' COMMENT 'kunci tema bawaan, atau "kustom"',
  warna_utama CHAR(7)      NOT NULL COMMENT 'tombol, link, judul berwarna',
  warna_aksen CHAR(7)      NOT NULL COMMENT 'aksen kedua (koral)',
  warna_sorot CHAR(7)      NOT NULL COMMENT 'aksen kecil (kuning)',
  warna_latar CHAR(7)      NOT NULL COMMENT 'latar halaman, harus terang',
  warna_teks  CHAR(7)      NOT NULL COMMENT 'teks utama & footer',
  PRIMARY KEY (id_tampilan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATA CONTOH
-- =====================================================================

INSERT INTO kategori (id_kategori, nama_kategori, deskripsi) VALUES
(1, 'Logo',    'Perancangan logo dan identitas visual untuk usaha, komunitas, maupun personal brand.'),
(2, 'Poster',  'Desain poster promosi, acara, dan media cetak maupun digital.'),
(3, 'Kemasan', 'Desain label, stiker, pouch, dan box kemasan produk.'),
(4, 'UI/UX',   'Desain antarmuka website dan aplikasi yang mudah dipakai.');

INSERT INTO layanan (id_layanan, id_kategori, nama_layanan, deskripsi, harga_min, harga_max, estimasi_hari, gambar, status) VALUES
(1, 1, 'Desain Logo',
 'Logo yang sederhana, mudah diingat, dan mewakili karakter usahamu.\n\nKamu akan mendapatkan 2 alternatif konsep logo, revisi hingga 3 kali, serta file akhir dalam format PNG transparan, JPG, dan PDF siap cetak.\n\nCocok untuk UMKM, toko daring, komunitas, atau personal brand yang baru mulai.',
 300000, 750000, 5, 'layanan-desain-logo.svg', 'aktif'),
(2, 1, 'Logo Premium',
 'Paket identitas visual lengkap untuk brand yang ingin tampil lebih profesional.\n\nTermasuk riset singkat target pasar, 3 alternatif konsep logo, palet warna, pilihan font, serta panduan brand (brand guideline) sederhana dalam bentuk PDF.\n\nFile akhir diberikan dalam format vektor (AI/SVG/PDF) dan PNG.',
 1000000, 2000000, 7, 'layanan-logo-premium.svg', 'aktif'),
(3, 2, 'Desain Poster',
 'Poster promosi yang menarik perhatian dan mudah dibaca, untuk dicetak maupun dibagikan di media sosial.\n\nUkuran dapat disesuaikan (A4, A3, atau format feed/story Instagram). Revisi hingga 2 kali dan file akhir resolusi tinggi siap cetak.',
 150000, 400000, 3, 'layanan-desain-poster.svg', 'aktif'),
(4, 2, 'Poster Event & Banner',
 'Satu paket materi publikasi untuk acara: poster utama, banner (X-banner atau spanduk), dan versi media sosial.\n\nCocok untuk seminar, konser kampus, bazar, maupun peluncuran produk. Desain dibuat konsisten di semua ukuran.',
 200000, 500000, 4, 'layanan-poster-event.svg', 'aktif'),
(5, 3, 'Desain Label Kemasan',
 'Label atau stiker produk yang informatif dan menggugah selera pembeli.\n\nTermasuk penataan informasi wajib (komposisi, berat bersih, PIRT/halal bila ada) dan file siap cetak sesuai ukuran kemasanmu.',
 250000, 600000, 5, 'layanan-label-kemasan.svg', 'aktif'),
(6, 3, 'Desain Box Kemasan',
 'Desain box atau dus kemasan lengkap dengan pola (dieline) yang siap dikirim ke percetakan.\n\nTermasuk mockup 3D agar kamu bisa melihat tampilan akhir produk sebelum dicetak. Revisi hingga 3 kali.',
 500000, 1200000, 7, 'layanan-box-kemasan.svg', 'aktif'),
(7, 4, 'Desain UI Landing Page',
 'Desain tampilan landing page yang rapi, modern, dan fokus pada tujuan: menjual, mendaftar, atau menghubungi.\n\nTermasuk versi desktop dan mobile, komponen yang konsisten, serta file Figma yang siap diserahkan ke programmer.',
 1500000, 3500000, 10, 'layanan-ui-landing-page.svg', 'aktif'),
(8, 4, 'Desain UI Aplikasi Mobile',
 'Desain antarmuka aplikasi mobile mulai dari alur pengguna, wireframe, hingga tampilan akhir siap dikembangkan.\n\n(Layanan ini sedang tidak dibuka sementara.)',
 2500000, 5000000, 14, 'layanan-ui-aplikasi-mobile.svg', 'nonaktif');

INSERT INTO portofolio (id_portofolio, id_kategori, judul, deskripsi, gambar, tanggal) VALUES
(1,  1, 'Logo Kopi Senja',
 'Logo kedai kopi lokal dengan nuansa hangat sore hari. Ikon cangkir dan uap dibuat sederhana agar tetap jelas saat dicetak kecil di gelas dan stiker.',
 'porto-logo-kopi-senja.svg', '2025-09-14'),
(2,  2, 'Poster Seminar Teknologi',
 'Poster seminar "AI untuk UMKM" dengan tata letak bersih dan hierarki informasi yang jelas: judul, tanggal, lalu lokasi.',
 'porto-poster-seminar-teknologi.svg', '2025-11-02'),
(3,  3, 'Kemasan Keripik Tempe Mbok Sari',
 'Desain pouch keripik tempe rumahan. Warna kuning cerah dipilih agar menonjol di rak toko oleh-oleh.',
 'porto-kemasan-keripik-tempe.svg', '2025-12-10'),
(4,  1, 'Logo Batik Laras',
 'Identitas visual untuk butik batik. Motif kawung disederhanakan menjadi bentuk geometris yang modern.',
 'porto-logo-batik-laras.svg', '2026-01-18'),
(5,  4, 'UI Aplikasi Kasir Warung',
 'Rancangan aplikasi kasir untuk warung kelontong dengan tombol besar dan alur transaksi yang singkat agar mudah dipakai siapa saja.',
 'porto-ui-aplikasi-kasir.svg', '2026-02-05'),
(6,  2, 'Poster Bazar UMKM',
 'Poster bazar produk lokal tiga hari. Dibuat dalam versi cetak A3 dan versi story Instagram.',
 'porto-poster-bazar-umkm.svg', '2026-03-21'),
(7,  3, 'Label Madu Hutan Lestari',
 'Label botol madu hutan dengan informasi produk yang rapi dan kesan alami lewat warna hijau toska.',
 'porto-kemasan-madu-lestari.svg', '2026-04-11'),
(8,  1, 'Logo Prana Yoga Studio',
 'Logo studio yoga dengan simbol bunga teratai yang tenang dan seimbang. Palet hijau mint dipadukan aksen koral.',
 'porto-logo-prana-yoga.svg', '2026-05-27'),
(9,  4, 'Dashboard Toko Daring',
 'Dashboard penjualan untuk pemilik toko daring: ringkasan omzet, grafik mingguan, dan target bulanan dalam satu layar.',
 'porto-ui-dashboard-toko.svg', '2026-06-30'),
(10, 3, 'Box Teh Melati Nusantara',
 'Box teh celup premium untuk oleh-oleh. Dilengkapi pola dieline dan mockup 3D untuk kebutuhan cetak.',
 'porto-kemasan-teh-melati.svg', '2026-08-08'),
(11, 2, 'Poster Festival Musik Kampus',
 'Poster festival musik tahunan kampus dengan warna kontras dan tipografi besar agar terbaca dari jauh.',
 'porto-poster-festival-musik.svg', '2026-09-15'),
(12, 4, 'Landing Page Kursus Online',
 'Landing page untuk kursus desain online, lengkap dengan bagian manfaat, testimoni, dan tombol daftar yang menonjol.',
 'porto-ui-kursus-online.svg', '2026-10-20'),
(13, 1, 'Logo Bengkel Maju Jaya',
 'Logo monogram untuk bengkel motor. Bentuk huruf M dibuat tegas dan tebal agar tetap terbaca di papan nama, seragam, dan nota.',
 'porto-logo-bengkel-maju.svg', '2025-10-06'),
(14, 2, 'Poster Lomba Mewarnai Anak',
 'Poster lomba mewarnai dengan warna ceria dan ilustrasi sederhana. Informasi hadiah dan cara daftar ditata agar mudah dibaca orang tua.',
 'porto-poster-lomba-mewarnai.svg', '2026-07-12'),
(15, 3, 'Label Sambal Bu Rini',
 'Label botol sambal bawang rumahan. Warna merah dipilih untuk menegaskan rasa pedas, lengkap dengan area informasi produk.',
 'porto-kemasan-sambal-bu-rini.svg', '2025-08-19'),
(16, 4, 'UI Aplikasi Reservasi Klinik',
 'Rancangan aplikasi pemesanan jadwal dokter: pilih poli, pilih jam, lalu konfirmasi, cukup dalam tiga langkah.',
 'porto-ui-reservasi-klinik.svg', '2026-04-28');

INSERT INTO profil_usaha (id_profil, nama_usaha, deskripsi, no_whatsapp, email, instagram, alamat) VALUES
(1, 'Gita Amarta',
 'Studio desain grafis yang membantu UMKM dan brand lokal tampil percaya diri lewat logo, poster, kemasan, dan desain UI/UX yang rapi serta mudah diingat.',
 '6281234567890', 'halo@gitaamarta.id', 'gitaamarta.design',
 'Jl. Kaliurang Km 5, Sleman, Daerah Istimewa Yogyakarta');

-- Akun admin contoh. Username: admin | Password: admin123
-- Password disimpan sebagai hash password_hash() (bcrypt), bukan teks asli.
-- SEGERA ganti password lewat menu "Akun Saya" setelah login pertama.
INSERT INTO admin (id_admin, nama, username, password, email) VALUES
(1, 'Admin Gita Amarta', 'admin', '$2y$10$/frI1ccz/ivA/EpyX7fZFuq42vGwKoc89ubIE0nGQRnrl38kBmpV6', 'halo@gitaamarta.id');

-- Data desainer contoh (ubah lewat panel admin: menu "Desainer").
INSERT INTO desainer (id_desainer, nama, peran, bio, kutipan, foto, status) VALUES
(1, 'Gita Amarta', 'Pendiri & Desainer Grafis',
 'Halo! Saya desainer di balik studio ini. Saya senang membantu UMKM dan brand lokal menemukan identitas visual yang jujur, rapi, dan mudah diingat.',
 'Desain yang baik dimulai dari mendengarkan cerita pemilik usahanya.',
 'desainer.svg', 'aktif');

-- Tema bawaan (ubah lewat panel admin: menu "Tampilan").
INSERT INTO tampilan (id_tampilan, tema, warna_utama, warna_aksen, warna_sorot, warna_latar, warna_teks) VALUES
(1, 'ungu-koral', '#6C4CF1', '#FF7A59', '#FFC94A', '#FFF8F0', '#1A1B3A');
