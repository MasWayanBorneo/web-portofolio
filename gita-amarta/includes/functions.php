<?php
/**
 * Kumpulan fungsi bantu (helper) dan SEMUA query database.
 *
 * Halaman (index.php, layanan.php, dst.) hanya memanggil fungsi di file ini,
 * sehingga tidak ada SQL di tengah HTML. Fungsi-fungsi ini nantinya juga
 * dapat dipakai ulang oleh halaman admin.
 */

require_once __DIR__ . '/../config/database.php';

/* =====================================================================
 * 1. HELPER UMUM
 * ===================================================================== */

/**
 * Escape teks sebelum dicetak ke HTML (mencegah XSS).
 */
function e(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format angka menjadi rupiah, contoh: 300000 -> "Rp300.000".
 */
function formatRupiah(int $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

/**
 * Format kisaran harga, contoh: "Rp300.000 – Rp750.000".
 */
function formatKisaranHarga(int $min, int $max): string
{
    if ($max <= $min) {
        return formatRupiah($min);
    }

    return formatRupiah($min) . ' – ' . formatRupiah($max);
}

/**
 * Format tanggal Y-m-d menjadi tanggal Indonesia, contoh: "20 Oktober 2026".
 */
function formatTanggal(string $tanggal): string
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    $waktu = strtotime($tanggal);
    if ($waktu === false) {
        return $tanggal;
    }

    return date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

/**
 * Mengembalikan URL gambar di folder assets/img.
 * Jika nama file kosong atau file tidak ada, dipakai gambar placeholder.
 */
function urlGambar(?string $namaFile): string
{
    $namaFile = basename((string) $namaFile); // cegah path traversal seperti "../"

    if ($namaFile === '' || !is_file(__DIR__ . '/../assets/img/' . $namaFile)) {
        return 'assets/img/placeholder.svg';
    }

    return 'assets/img/' . rawurlencode($namaFile);
}

/**
 * Membuat link wa.me. Pesan (jika ada) di-encode dengan rawurlencode().
 */
function linkWhatsApp(string $nomor, string $pesan = ''): string
{
    $nomor = preg_replace('/\D/', '', $nomor);
    $url   = 'https://wa.me/' . $nomor;

    if ($pesan !== '') {
        $url .= '?text=' . rawurlencode($pesan);
    }

    return $url;
}

/**
 * Menampilkan nomor 62xxx dalam bentuk yang mudah dibaca: "+62 812-3456-7890".
 */
function formatNomorTampil(string $nomor): string
{
    $nomor = preg_replace('/\D/', '', $nomor);

    if (!str_starts_with($nomor, '62')) {
        return $nomor;
    }

    $sisa = substr($nomor, 2);

    return '+62 ' . substr($sisa, 0, 3) . '-' . substr($sisa, 3, 4) . '-' . substr($sisa, 7);
}

/**
 * Mengambil parameter GET berupa bilangan bulat positif (contoh: ?id=3).
 * Mengembalikan null jika tidak ada atau tidak valid.
 */
function ambilIdDariUrl(string $nama): ?int
{
    $nilai = filter_input(INPUT_GET, $nama, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return is_int($nilai) ? $nilai : null;
}

/**
 * Memulai session jika belum berjalan (dipakai untuk menyimpan error formulir sementara).
 */
function mulaiSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Ikon SVG sederhana (inline) agar tidak perlu library ikon eksternal.
 * Nilai yang dikembalikan adalah markup tetap, bukan data dari pengguna.
 */
function ikon(string $nama): string
{
    $path = [
        'whatsapp' => '<path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z"/><path d="M9 8.6c.2-.5.5-.6.9-.6h.6c.2 0 .4.1.5.4l.8 1.8c.1.2 0 .5-.1.7l-.6.7c.6 1.2 1.6 2.2 2.8 2.8l.7-.6c.2-.2.5-.2.7-.1l1.8.8c.3.1.4.3.4.5v.6c0 .4-.1.7-.6.9-.6.3-1.6.4-2.9-.2a9 9 0 0 1-4.2-4.2c-.6-1.3-.5-2.3-.2-2.9z" fill="currentColor" stroke="none"/>',
        'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/>',
        'email'     => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/>',
        'lokasi'    => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'jam'       => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'tag'       => '<path d="M3.5 12.6V4.5a1 1 0 0 1 1-1h8.1l7.9 7.9a1.5 1.5 0 0 1 0 2.1l-6 6a1.5 1.5 0 0 1-2.1 0z"/><circle cx="8.5" cy="8.5" r="1.5"/>',
        'panah'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'klip'      => '<path d="m20 11.5-8.2 8.2a5 5 0 0 1-7-7l8.5-8.5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4l7.8-7.8"/>',
        'peringatan'=> '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><circle cx="12" cy="16.3" r="1" fill="currentColor" stroke="none"/>',
        'perbesar'  => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4M11 8.5v5M8.5 11h5"/>',
    ][$nama] ?? '';

    return '<svg class="icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $path . '</svg>';
}

/* =====================================================================
 * 2. QUERY: PROFIL USAHA
 * ===================================================================== */

/**
 * Mengambil profil usaha (1 baris). Hasil disimpan agar tidak query berulang.
 */
function getProfilUsaha(): array
{
    static $profil = null;

    if ($profil === null) {
        $stmt = getKoneksi()->prepare(
            'SELECT id_profil, nama_usaha, deskripsi, no_whatsapp, email, instagram, alamat
             FROM profil_usaha
             ORDER BY id_profil ASC
             LIMIT 1'
        );
        $stmt->execute();

        // Nilai cadangan jika tabel profil_usaha masih kosong.
        $profil = $stmt->fetch() ?: [
            'id_profil'   => 0,
            'nama_usaha'  => 'Gita Amarta',
            'deskripsi'   => 'Jasa desain grafis.',
            'no_whatsapp' => '',
            'email'       => '',
            'instagram'   => '',
            'alamat'      => '',
        ];
    }

    return $profil;
}

/**
 * Data desainer untuk section "Tentang Desainer" di beranda (1 baris).
 * Mengembalikan null jika tabel kosong. Status dicek oleh halaman pemanggil.
 */
function getDesainer(): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_desainer, nama, peran, bio, kutipan, foto, status
         FROM desainer
         ORDER BY id_desainer ASC
         LIMIT 1'
    );
    $stmt->execute();

    return $stmt->fetch() ?: null;
}

/* =====================================================================
 * 3. QUERY: KATEGORI
 * ===================================================================== */

function getSemuaKategori(): array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_kategori, nama_kategori, deskripsi
         FROM kategori
         ORDER BY id_kategori ASC'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

function getKategoriById(int $idKategori): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_kategori, nama_kategori, deskripsi
         FROM kategori
         WHERE id_kategori = :id'
    );
    $stmt->execute([':id' => $idKategori]);

    return $stmt->fetch() ?: null;
}

/* =====================================================================
 * 4. QUERY: LAYANAN (publik hanya melihat status 'aktif')
 * ===================================================================== */

/**
 * Semua layanan aktif. Jika $idKategori diisi, hanya layanan kategori tersebut.
 */
function getLayananAktif(?int $idKategori = null): array
{
    $sql = "SELECT l.id_layanan, l.id_kategori, l.nama_layanan, l.deskripsi,
                   l.harga_min, l.harga_max, l.estimasi_hari, l.gambar,
                   k.nama_kategori
            FROM layanan l
            JOIN kategori k ON k.id_kategori = l.id_kategori
            WHERE l.status = 'aktif'";
    $parameter = [];

    if ($idKategori !== null) {
        $sql .= ' AND l.id_kategori = :id_kategori';
        $parameter[':id_kategori'] = $idKategori;
    }

    $sql .= ' ORDER BY l.id_kategori ASC, l.id_layanan ASC';

    $stmt = getKoneksi()->prepare($sql);
    $stmt->execute($parameter);

    return $stmt->fetchAll();
}

/**
 * Layanan unggulan untuk beranda: layanan aktif dengan id_layanan terbesar.
 */
function getLayananUnggulan(int $jumlah = 4): array
{
    $stmt = getKoneksi()->prepare(
        "SELECT l.id_layanan, l.id_kategori, l.nama_layanan, l.harga_min, l.harga_max,
                l.estimasi_hari, l.gambar, k.nama_kategori
         FROM layanan l
         JOIN kategori k ON k.id_kategori = l.id_kategori
         WHERE l.status = 'aktif'
         ORDER BY l.id_layanan DESC
         LIMIT :jumlah"
    );
    $stmt->bindValue(':jumlah', $jumlah, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Satu layanan aktif berdasarkan id. Mengembalikan null jika tidak ada / nonaktif.
 */
function getLayananAktifById(int $idLayanan): ?array
{
    $stmt = getKoneksi()->prepare(
        "SELECT l.id_layanan, l.id_kategori, l.nama_layanan, l.deskripsi,
                l.harga_min, l.harga_max, l.estimasi_hari, l.gambar,
                k.nama_kategori
         FROM layanan l
         JOIN kategori k ON k.id_kategori = l.id_kategori
         WHERE l.id_layanan = :id AND l.status = 'aktif'"
    );
    $stmt->execute([':id' => $idLayanan]);

    return $stmt->fetch() ?: null;
}

/* =====================================================================
 * 5. QUERY: PORTOFOLIO
 * ===================================================================== */

/**
 * Semua portofolio (terbaru dulu). Jika $idKategori diisi, hanya kategori tersebut.
 */
function getSemuaPortofolio(?int $idKategori = null): array
{
    $sql = 'SELECT p.id_portofolio, p.id_kategori, p.judul, p.deskripsi, p.gambar, p.tanggal,
                   k.nama_kategori
            FROM portofolio p
            JOIN kategori k ON k.id_kategori = p.id_kategori';
    $parameter = [];

    if ($idKategori !== null) {
        $sql .= ' WHERE p.id_kategori = :id_kategori';
        $parameter[':id_kategori'] = $idKategori;
    }

    $sql .= ' ORDER BY p.tanggal DESC, p.id_portofolio DESC';

    $stmt = getKoneksi()->prepare($sql);
    $stmt->execute($parameter);

    return $stmt->fetchAll();
}

/**
 * Portofolio terbaru (untuk gambar hero di beranda).
 */
function getPortofolioTerbaru(): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT p.id_portofolio, p.judul, p.gambar, p.tanggal, k.nama_kategori
         FROM portofolio p
         JOIN kategori k ON k.id_kategori = p.id_kategori
         ORDER BY p.tanggal DESC, p.id_portofolio DESC
         LIMIT 1'
    );
    $stmt->execute();

    return $stmt->fetch() ?: null;
}

/**
 * Beberapa portofolio dari kategori tertentu (untuk "Contoh Karya" di detail layanan).
 */
function getPortofolioByKategori(int $idKategori, int $jumlah = 4): array
{
    $stmt = getKoneksi()->prepare(
        'SELECT p.id_portofolio, p.id_kategori, p.judul, p.deskripsi, p.gambar, p.tanggal,
                k.nama_kategori
         FROM portofolio p
         JOIN kategori k ON k.id_kategori = p.id_kategori
         WHERE p.id_kategori = :id_kategori
         ORDER BY p.tanggal DESC, p.id_portofolio DESC
         LIMIT :jumlah'
    );
    $stmt->bindValue(':id_kategori', $idKategori, PDO::PARAM_INT);
    $stmt->bindValue(':jumlah', $jumlah, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function hitungPortofolio(): int
{
    $stmt = getKoneksi()->prepare('SELECT COUNT(*) FROM portofolio');
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

/* =====================================================================
 * 6. FORMULIR PEMESANAN (tidak disimpan ke database)
 * ===================================================================== */

/**
 * Mengambil dan merapikan (trim) isian formulir dari $_POST.
 */
function ambilInputPesanan(array $post): array
{
    $teks = static function (string $kunci) use ($post): string {
        $nilai = $post[$kunci] ?? '';
        return is_string($nilai) ? trim($nilai) : '';
    };

    $idLayanan = filter_var($post['id_layanan'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return [
        'id_layanan' => is_int($idLayanan) ? $idLayanan : null,
        'nama'       => $teks('nama'),
        'whatsapp'   => $teks('whatsapp'),
        'kebutuhan'  => $teks('kebutuhan'),
        'tenggat'    => $teks('tenggat'),
    ];
}

/**
 * Validasi isian formulir. Aturannya SAMA dengan validasi di assets/js/main.js.
 * Mengembalikan array pesan error dengan kunci nama kolom (kosong = valid).
 */
function validasiPesanan(array $data): array
{
    $error = [];

    // Nama lengkap: wajib.
    if ($data['nama'] === '') {
        $error['nama'] = 'Nama lengkap wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 100) {
        $error['nama'] = 'Nama lengkap maksimal 100 karakter.';
    }

    // Nomor WhatsApp: wajib, hanya angka, 10–15 digit.
    if ($data['whatsapp'] === '') {
        $error['whatsapp'] = 'Nomor WhatsApp wajib diisi.';
    } elseif (!preg_match('/^[0-9]+$/', $data['whatsapp'])) {
        $error['whatsapp'] = 'Nomor WhatsApp hanya boleh berisi angka (tanpa huruf, spasi, atau tanda +).';
    } elseif (strlen($data['whatsapp']) < 10 || strlen($data['whatsapp']) > 15) {
        $error['whatsapp'] = 'Nomor WhatsApp harus terdiri dari 10–15 digit.';
    }

    // Deskripsi kebutuhan: wajib.
    if ($data['kebutuhan'] === '') {
        $error['kebutuhan'] = 'Deskripsi kebutuhan wajib diisi.';
    } elseif (mb_strlen($data['kebutuhan']) > 1000) {
        $error['kebutuhan'] = 'Deskripsi kebutuhan maksimal 1000 karakter.';
    }

    // Tenggat waktu: opsional, tetapi tidak boleh tanggal yang sudah lewat.
    if ($data['tenggat'] !== '') {
        $tanggal = DateTime::createFromFormat('!Y-m-d', $data['tenggat']);

        if ($tanggal === false || $tanggal->format('Y-m-d') !== $data['tenggat']) {
            $error['tenggat'] = 'Format tanggal tidak valid.';
        } elseif ($data['tenggat'] < date('Y-m-d')) {
            $error['tenggat'] = 'Tenggat waktu tidak boleh tanggal yang sudah lewat.';
        }
    }

    return $error;
}

/**
 * Menyusun teks pesan WhatsApp yang rapi dari data formulir.
 */
function susunPesanWhatsApp(array $layanan, array $data): string
{
    $tenggat = $data['tenggat'] !== '' ? formatTanggal($data['tenggat']) : 'Fleksibel / belum ditentukan';

    $baris = [
        'Halo ' . getProfilUsaha()['nama_usaha'] . ', saya ingin memesan jasa desain.',
        '',
        '*Layanan:* ' . $layanan['nama_layanan'],
        '*Nama:* ' . $data['nama'],
        '*No. WhatsApp:* ' . $data['whatsapp'],
        '*Kebutuhan:*',
        $data['kebutuhan'],
        '*Tenggat:* ' . $tenggat,
        '',
        'Terima kasih.',
    ];

    return implode("\n", $baris);
}

/**
 * Menyimpan error + isian lama ke session sebelum redirect kembali ke formulir.
 */
function simpanFlashPesanan(int $idLayanan, array $error, array $isianLama): void
{
    mulaiSession();
    $_SESSION['flash_pesanan'] = [
        'id_layanan' => $idLayanan,
        'error'      => $error,
        'isian'      => $isianLama,
    ];
}

/**
 * Mengambil (lalu menghapus) error + isian lama untuk layanan tertentu.
 */
function ambilFlashPesanan(int $idLayanan): array
{
    mulaiSession();
    $kosong = ['error' => [], 'isian' => []];
    $flash  = $_SESSION['flash_pesanan'] ?? null;
    unset($_SESSION['flash_pesanan']);

    if (!is_array($flash) || ($flash['id_layanan'] ?? null) !== $idLayanan) {
        return $kosong;
    }

    return ['error' => $flash['error'], 'isian' => $flash['isian']];
}
