<?php
/**
 * Query dan helper khusus halaman admin.
 *
 * Dipisah dari functions.php supaya halaman publik tidak ikut memuat kode admin.
 * Fungsi publik (getSemuaKategori, getSemuaPortofolio, urlGambar, e, dst.)
 * tetap dipakai ulang dari functions.php.
 */

require_once __DIR__ . '/functions.php';

/** Batas ukuran file gambar yang diunggah (dalam MB). Ubah angka ini saja jika batasnya ingin diganti. */
const UKURAN_MAKS_MB = 2;

/** Batas yang sama dalam byte: 2 MB = 2.097.152 byte. */
const UKURAN_MAKS_GAMBAR = UKURAN_MAKS_MB * 1024 * 1024;

/** Tipe gambar yang boleh diunggah (MIME => ekstensi). SVG sengaja tidak diizinkan karena bisa berisi script. */
const TIPE_GAMBAR_DIIZINKAN = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/** Folder penyimpanan gambar, sama dengan folder yang dibaca urlGambar(). */
const FOLDER_GAMBAR = __DIR__ . '/../assets/img/';

/** Awalan nama file hasil unggahan. Hanya file berawalan ini yang boleh dihapus otomatis. */
const AWALAN_UPLOAD = 'upload-';

/* =====================================================================
 * 1. HELPER INPUT
 * ===================================================================== */

/**
 * Mengambil teks dari array input ($_POST) lalu di-trim. Nilai non-string dianggap kosong.
 */
function ambilTeks(array $sumber, string $kunci): string
{
    $nilai = $sumber[$kunci] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}

/**
 * Mengambil bilangan bulat >= $min dari input. Mengembalikan null jika tidak valid.
 */
function ambilAngka(array $sumber, string $kunci, int $min = 0): ?int
{
    $nilai = filter_var($sumber[$kunci] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min]]);

    return is_int($nilai) ? $nilai : null;
}

/**
 * Mengecek format tanggal Y-m-d yang benar-benar ada di kalender (contoh: 2026-02-30 ditolak).
 */
function tanggalValid(string $tanggal): bool
{
    $hasil = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $hasil !== false && $hasil->format('Y-m-d') === $tanggal;
}

/* =====================================================================
 * 2. QUERY: AKUN ADMIN
 * ===================================================================== */

function getAdminByUsername(string $username): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_admin, nama, username, password, email, created_at
         FROM admin
         WHERE username = :username'
    );
    $stmt->execute([':username' => $username]);

    return $stmt->fetch() ?: null;
}

function getAdminById(int $idAdmin): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_admin, nama, username, password, email, created_at
         FROM admin
         WHERE id_admin = :id'
    );
    $stmt->execute([':id' => $idAdmin]);

    return $stmt->fetch() ?: null;
}

function ubahDataAdmin(int $idAdmin, string $nama, string $email): void
{
    $stmt = getKoneksi()->prepare(
        'UPDATE admin SET nama = :nama, email = :email WHERE id_admin = :id'
    );
    $stmt->execute([
        ':nama'  => $nama,
        ':email' => $email !== '' ? $email : null,
        ':id'    => $idAdmin,
    ]);
}

/**
 * Menyimpan password baru. Yang disimpan adalah hash, bukan password aslinya.
 */
function ubahPasswordAdmin(int $idAdmin, string $passwordBaru): void
{
    $stmt = getKoneksi()->prepare('UPDATE admin SET password = :password WHERE id_admin = :id');
    $stmt->execute([
        ':password' => password_hash($passwordBaru, PASSWORD_DEFAULT),
        ':id'       => $idAdmin,
    ]);
}

function validasiDataAdmin(string $nama, string $email): array
{
    $error = [];

    if ($nama === '') {
        $error['nama'] = 'Nama wajib diisi.';
    } elseif (mb_strlen($nama) > 100) {
        $error['nama'] = 'Nama maksimal 100 karakter.';
    }

    if ($email !== '' && (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 100)) {
        $error['email'] = 'Format email tidak valid.';
    }

    return $error;
}

function validasiGantiPassword(array $admin, string $lama, string $baru, string $konfirmasi): array
{
    $error = [];

    if ($lama === '' || !password_verify($lama, $admin['password'])) {
        $error['password_lama'] = 'Password lama salah.';
    }

    if (mb_strlen($baru) < 8) {
        $error['password_baru'] = 'Password baru minimal 8 karakter.';
    } elseif ($baru === $lama) {
        $error['password_baru'] = 'Password baru harus berbeda dari password lama.';
    }

    if ($konfirmasi !== $baru) {
        $error['konfirmasi'] = 'Konfirmasi password tidak sama.';
    }

    return $error;
}

/* =====================================================================
 * 3. QUERY: DASHBOARD
 * ===================================================================== */

/**
 * Jumlah layanan per status, contoh: ['aktif' => 7, 'nonaktif' => 1].
 */
function hitungLayananPerStatus(): array
{
    $stmt = getKoneksi()->prepare('SELECT status, COUNT(*) AS jumlah FROM layanan GROUP BY status');
    $stmt->execute();

    $hasil = ['aktif' => 0, 'nonaktif' => 0];
    foreach ($stmt->fetchAll() as $baris) {
        $hasil[$baris['status']] = (int) $baris['jumlah'];
    }

    return $hasil;
}

/**
 * Semua kategori beserta jumlah layanan dan portofolio di dalamnya.
 */
function getKategoriDenganJumlah(): array
{
    $stmt = getKoneksi()->prepare(
        'SELECT k.id_kategori, k.nama_kategori, k.deskripsi,
                (SELECT COUNT(*) FROM layanan l WHERE l.id_kategori = k.id_kategori)    AS jumlah_layanan,
                (SELECT COUNT(*) FROM portofolio p WHERE p.id_kategori = k.id_kategori) AS jumlah_portofolio
         FROM kategori k
         ORDER BY k.id_kategori ASC'
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

/* =====================================================================
 * 4. QUERY: KATEGORI
 * ===================================================================== */

function ambilInputKategori(array $post): array
{
    return [
        'nama_kategori' => ambilTeks($post, 'nama_kategori'),
        'deskripsi'     => ambilTeks($post, 'deskripsi'),
    ];
}

/**
 * $idSaatIni diisi saat mengedit, agar nama kategori itu sendiri tidak dianggap duplikat.
 */
function validasiKategori(array $data, ?int $idSaatIni = null): array
{
    $error = [];

    if ($data['nama_kategori'] === '') {
        $error['nama_kategori'] = 'Nama kategori wajib diisi.';
    } elseif (mb_strlen($data['nama_kategori']) > 50) {
        $error['nama_kategori'] = 'Nama kategori maksimal 50 karakter.';
    } elseif (namaKategoriSudahAda($data['nama_kategori'], $idSaatIni)) {
        $error['nama_kategori'] = 'Kategori dengan nama ini sudah ada.';
    }

    return $error;
}

function namaKategoriSudahAda(string $nama, ?int $kecualiId = null): bool
{
    $stmt = getKoneksi()->prepare(
        'SELECT COUNT(*) FROM kategori WHERE nama_kategori = :nama AND id_kategori <> :id'
    );
    $stmt->execute([':nama' => $nama, ':id' => $kecualiId ?? 0]);

    return (int) $stmt->fetchColumn() > 0;
}

function tambahKategori(array $data): int
{
    $stmt = getKoneksi()->prepare(
        'INSERT INTO kategori (nama_kategori, deskripsi) VALUES (:nama, :deskripsi)'
    );
    $stmt->execute([
        ':nama'      => $data['nama_kategori'],
        ':deskripsi' => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
    ]);

    return (int) getKoneksi()->lastInsertId();
}

function ubahKategori(int $idKategori, array $data): void
{
    $stmt = getKoneksi()->prepare(
        'UPDATE kategori SET nama_kategori = :nama, deskripsi = :deskripsi WHERE id_kategori = :id'
    );
    $stmt->execute([
        ':nama'      => $data['nama_kategori'],
        ':deskripsi' => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
        ':id'        => $idKategori,
    ]);
}

/**
 * Jumlah layanan + portofolio yang masih memakai kategori ini.
 * Kategori yang masih dipakai tidak boleh dihapus (foreign key ON DELETE RESTRICT).
 */
function hitungPemakaianKategori(int $idKategori): int
{
    $stmt = getKoneksi()->prepare(
        'SELECT (SELECT COUNT(*) FROM layanan WHERE id_kategori = :id1)
              + (SELECT COUNT(*) FROM portofolio WHERE id_kategori = :id2)'
    );
    $stmt->execute([':id1' => $idKategori, ':id2' => $idKategori]);

    return (int) $stmt->fetchColumn();
}

function hapusKategori(int $idKategori): void
{
    $stmt = getKoneksi()->prepare('DELETE FROM kategori WHERE id_kategori = :id');
    $stmt->execute([':id' => $idKategori]);
}

/**
 * Memindahkan semua layanan & karya ke kategori lain, lalu menghapus kategori lama.
 * Dijalankan dalam satu transaksi: jika satu langkah gagal, semuanya dibatalkan.
 */
function pindahkanIsiLaluHapusKategori(int $idKategori, int $idTujuan): void
{
    $pdo = getKoneksi();
    $pdo->beginTransaction();

    try {
        $parameter = [':tujuan' => $idTujuan, ':asal' => $idKategori];

        $pdo->prepare('UPDATE layanan SET id_kategori = :tujuan WHERE id_kategori = :asal')->execute($parameter);
        $pdo->prepare('UPDATE portofolio SET id_kategori = :tujuan WHERE id_kategori = :asal')->execute($parameter);
        hapusKategori($idKategori);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Menghapus kategori BESERTA semua layanan dan karya di dalamnya (tidak bisa dibatalkan).
 * File gambar hasil unggahan ikut dihapus setelah datanya terhapus.
 */
function hapusKategoriBesertaIsi(int $idKategori): void
{
    $pdo = getKoneksi();

    // Catat nama file gambar lebih dulu, karena barisnya akan hilang.
    $stmt = $pdo->prepare(
        'SELECT gambar FROM layanan WHERE id_kategori = :id1 AND gambar IS NOT NULL
         UNION
         SELECT gambar FROM portofolio WHERE id_kategori = :id2'
    );
    $stmt->execute([':id1' => $idKategori, ':id2' => $idKategori]);
    $daftarGambar = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();

    try {
        $parameter = [':id' => $idKategori];

        $pdo->prepare('DELETE FROM portofolio WHERE id_kategori = :id')->execute($parameter);
        $pdo->prepare('DELETE FROM layanan WHERE id_kategori = :id')->execute($parameter);
        hapusKategori($idKategori);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    // File dihapus SETELAH commit, agar jika database gagal, gambarnya masih ada.
    foreach ($daftarGambar as $namaFile) {
        hapusGambarJikaTidakDipakai($namaFile);
    }
}

/* =====================================================================
 * 5. QUERY: LAYANAN (admin melihat semua status)
 * ===================================================================== */

/**
 * Semua layanan (aktif & nonaktif). Jika $idKategori diisi, hanya layanan kategori tersebut.
 */
function getSemuaLayananAdmin(?int $idKategori = null): array
{
    $sql = 'SELECT l.id_layanan, l.id_kategori, l.nama_layanan, l.harga_min, l.harga_max,
                   l.estimasi_hari, l.gambar, l.status, k.nama_kategori
            FROM layanan l
            JOIN kategori k ON k.id_kategori = l.id_kategori';
    $parameter = [];

    if ($idKategori !== null) {
        $sql .= ' WHERE l.id_kategori = :id_kategori';
        $parameter[':id_kategori'] = $idKategori;
    }

    $sql .= ' ORDER BY l.id_layanan DESC';

    $stmt = getKoneksi()->prepare($sql);
    $stmt->execute($parameter);

    return $stmt->fetchAll();
}

function getLayananById(int $idLayanan): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_layanan, id_kategori, nama_layanan, deskripsi, harga_min, harga_max,
                estimasi_hari, gambar, status
         FROM layanan
         WHERE id_layanan = :id'
    );
    $stmt->execute([':id' => $idLayanan]);

    return $stmt->fetch() ?: null;
}

function ambilInputLayanan(array $post): array
{
    $status = ambilTeks($post, 'status');

    return [
        'id_kategori'   => ambilAngka($post, 'id_kategori', 1),
        'nama_layanan'  => ambilTeks($post, 'nama_layanan'),
        'deskripsi'     => ambilTeks($post, 'deskripsi'),
        'harga_min'     => ambilAngka($post, 'harga_min'),
        'harga_max'     => ambilAngka($post, 'harga_max'),
        'estimasi_hari' => ambilAngka($post, 'estimasi_hari', 1),
        'status'        => in_array($status, ['aktif', 'nonaktif'], true) ? $status : '',
    ];
}

function validasiLayanan(array $data): array
{
    $error = [];

    if ($data['nama_layanan'] === '') {
        $error['nama_layanan'] = 'Nama layanan wajib diisi.';
    } elseif (mb_strlen($data['nama_layanan']) > 100) {
        $error['nama_layanan'] = 'Nama layanan maksimal 100 karakter.';
    }

    if ($data['id_kategori'] === null || getKategoriById($data['id_kategori']) === null) {
        $error['id_kategori'] = 'Pilih kategori yang tersedia.';
    }

    if ($data['deskripsi'] === '') {
        $error['deskripsi'] = 'Deskripsi wajib diisi.';
    }

    if ($data['harga_min'] === null) {
        $error['harga_min'] = 'Harga minimal wajib diisi berupa angka (tanpa titik).';
    }

    if ($data['harga_max'] === null) {
        $error['harga_max'] = 'Harga maksimal wajib diisi berupa angka (tanpa titik).';
    } elseif ($data['harga_min'] !== null && $data['harga_max'] < $data['harga_min']) {
        $error['harga_max'] = 'Harga maksimal tidak boleh lebih kecil dari harga minimal.';
    }

    if ($data['estimasi_hari'] === null || $data['estimasi_hari'] > 365) {
        $error['estimasi_hari'] = 'Estimasi diisi angka 1–365 hari.';
    }

    if ($data['status'] === '') {
        $error['status'] = 'Pilih status layanan.';
    }

    return $error;
}

function tambahLayanan(array $data, ?string $gambar): int
{
    $stmt = getKoneksi()->prepare(
        'INSERT INTO layanan (id_kategori, nama_layanan, deskripsi, harga_min, harga_max, estimasi_hari, gambar, status)
         VALUES (:id_kategori, :nama, :deskripsi, :harga_min, :harga_max, :estimasi, :gambar, :status)'
    );
    $stmt->execute([
        ':id_kategori' => $data['id_kategori'],
        ':nama'        => $data['nama_layanan'],
        ':deskripsi'   => $data['deskripsi'],
        ':harga_min'   => $data['harga_min'],
        ':harga_max'   => $data['harga_max'],
        ':estimasi'    => $data['estimasi_hari'],
        ':gambar'      => $gambar,
        ':status'      => $data['status'],
    ]);

    return (int) getKoneksi()->lastInsertId();
}

function ubahLayanan(int $idLayanan, array $data, ?string $gambar): void
{
    $stmt = getKoneksi()->prepare(
        'UPDATE layanan
         SET id_kategori = :id_kategori, nama_layanan = :nama, deskripsi = :deskripsi,
             harga_min = :harga_min, harga_max = :harga_max, estimasi_hari = :estimasi,
             gambar = :gambar, status = :status
         WHERE id_layanan = :id'
    );
    $stmt->execute([
        ':id_kategori' => $data['id_kategori'],
        ':nama'        => $data['nama_layanan'],
        ':deskripsi'   => $data['deskripsi'],
        ':harga_min'   => $data['harga_min'],
        ':harga_max'   => $data['harga_max'],
        ':estimasi'    => $data['estimasi_hari'],
        ':gambar'      => $gambar,
        ':status'      => $data['status'],
        ':id'          => $idLayanan,
    ]);
}

function ubahStatusLayanan(int $idLayanan, string $status): void
{
    $stmt = getKoneksi()->prepare('UPDATE layanan SET status = :status WHERE id_layanan = :id');
    $stmt->execute([':status' => $status, ':id' => $idLayanan]);
}

function hapusLayanan(int $idLayanan): void
{
    $stmt = getKoneksi()->prepare('DELETE FROM layanan WHERE id_layanan = :id');
    $stmt->execute([':id' => $idLayanan]);
}

/* =====================================================================
 * 6. QUERY: PORTOFOLIO
 * ===================================================================== */

function getPortofolioById(int $idPortofolio): ?array
{
    $stmt = getKoneksi()->prepare(
        'SELECT id_portofolio, id_kategori, judul, deskripsi, gambar, tanggal
         FROM portofolio
         WHERE id_portofolio = :id'
    );
    $stmt->execute([':id' => $idPortofolio]);

    return $stmt->fetch() ?: null;
}

function ambilInputPortofolio(array $post): array
{
    return [
        'id_kategori' => ambilAngka($post, 'id_kategori', 1),
        'judul'       => ambilTeks($post, 'judul'),
        'deskripsi'   => ambilTeks($post, 'deskripsi'),
        'tanggal'     => ambilTeks($post, 'tanggal'),
    ];
}

function validasiPortofolio(array $data): array
{
    $error = [];

    if ($data['judul'] === '') {
        $error['judul'] = 'Judul karya wajib diisi.';
    } elseif (mb_strlen($data['judul']) > 100) {
        $error['judul'] = 'Judul maksimal 100 karakter.';
    }

    if ($data['id_kategori'] === null || getKategoriById($data['id_kategori']) === null) {
        $error['id_kategori'] = 'Pilih kategori yang tersedia.';
    }

    if ($data['tanggal'] === '' || !tanggalValid($data['tanggal'])) {
        $error['tanggal'] = 'Tanggal wajib diisi dengan format yang benar.';
    } elseif ($data['tanggal'] > date('Y-m-d')) {
        $error['tanggal'] = 'Tanggal karya tidak boleh di masa depan.';
    }

    return $error;
}

function tambahPortofolio(array $data, string $gambar): int
{
    $stmt = getKoneksi()->prepare(
        'INSERT INTO portofolio (id_kategori, judul, deskripsi, gambar, tanggal)
         VALUES (:id_kategori, :judul, :deskripsi, :gambar, :tanggal)'
    );
    $stmt->execute([
        ':id_kategori' => $data['id_kategori'],
        ':judul'       => $data['judul'],
        ':deskripsi'   => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
        ':gambar'      => $gambar,
        ':tanggal'     => $data['tanggal'],
    ]);

    return (int) getKoneksi()->lastInsertId();
}

function ubahPortofolio(int $idPortofolio, array $data, string $gambar): void
{
    $stmt = getKoneksi()->prepare(
        'UPDATE portofolio
         SET id_kategori = :id_kategori, judul = :judul, deskripsi = :deskripsi,
             gambar = :gambar, tanggal = :tanggal
         WHERE id_portofolio = :id'
    );
    $stmt->execute([
        ':id_kategori' => $data['id_kategori'],
        ':judul'       => $data['judul'],
        ':deskripsi'   => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
        ':gambar'      => $gambar,
        ':tanggal'     => $data['tanggal'],
        ':id'          => $idPortofolio,
    ]);
}

function hapusPortofolio(int $idPortofolio): void
{
    $stmt = getKoneksi()->prepare('DELETE FROM portofolio WHERE id_portofolio = :id');
    $stmt->execute([':id' => $idPortofolio]);
}

/* =====================================================================
 * 7. QUERY: PROFIL USAHA
 * ===================================================================== */

function ambilInputProfil(array $post): array
{
    return [
        'nama_usaha'  => ambilTeks($post, 'nama_usaha'),
        'deskripsi'   => ambilTeks($post, 'deskripsi'),
        // Spasi, tanda + dan - dibuang agar admin boleh menulis "+62 812-3456-7890".
        'no_whatsapp' => preg_replace('/[\s+\-]/', '', ambilTeks($post, 'no_whatsapp')),
        'email'       => ambilTeks($post, 'email'),
        'instagram'   => ltrim(ambilTeks($post, 'instagram'), '@'),
        'alamat'      => ambilTeks($post, 'alamat'),
    ];
}

function validasiProfil(array $data): array
{
    $error = [];

    if ($data['nama_usaha'] === '') {
        $error['nama_usaha'] = 'Nama usaha wajib diisi.';
    } elseif (mb_strlen($data['nama_usaha']) > 100) {
        $error['nama_usaha'] = 'Nama usaha maksimal 100 karakter.';
    }

    if ($data['deskripsi'] === '') {
        $error['deskripsi'] = 'Deskripsi wajib diisi (tampil di beranda dan footer).';
    }

    if (!preg_match('/^62\d{8,13}$/', $data['no_whatsapp'])) {
        $error['no_whatsapp'] = 'Nomor WhatsApp diawali 62, hanya angka, 10–15 digit. Contoh: 6281234567890.';
    }

    if ($data['email'] !== '' && (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false || mb_strlen($data['email']) > 100)) {
        $error['email'] = 'Format email tidak valid.';
    }

    if ($data['instagram'] !== '' && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $data['instagram'])) {
        $error['instagram'] = 'Username Instagram hanya huruf, angka, titik, dan garis bawah (maks. 30).';
    }

    return $error;
}

/**
 * Menyimpan profil usaha. Jika tabel masih kosong ($idProfil = 0), baris baru dibuat.
 */
function simpanProfilUsaha(int $idProfil, array $data): void
{
    $parameter = [
        ':nama'      => $data['nama_usaha'],
        ':deskripsi' => $data['deskripsi'],
        ':wa'        => $data['no_whatsapp'],
        ':email'     => $data['email'] !== '' ? $data['email'] : null,
        ':instagram' => $data['instagram'] !== '' ? $data['instagram'] : null,
        ':alamat'    => $data['alamat'] !== '' ? $data['alamat'] : null,
    ];

    if ($idProfil > 0) {
        $stmt = getKoneksi()->prepare(
            'UPDATE profil_usaha
             SET nama_usaha = :nama, deskripsi = :deskripsi, no_whatsapp = :wa,
                 email = :email, instagram = :instagram, alamat = :alamat
             WHERE id_profil = :id'
        );
        $parameter[':id'] = $idProfil;
    } else {
        $stmt = getKoneksi()->prepare(
            'INSERT INTO profil_usaha (nama_usaha, deskripsi, no_whatsapp, email, instagram, alamat)
             VALUES (:nama, :deskripsi, :wa, :email, :instagram, :alamat)'
        );
    }

    $stmt->execute($parameter);
}

/* =====================================================================
 * 8. QUERY: DESAINER (section "Tentang Desainer" di beranda)
 * ===================================================================== */

function ambilInputDesainer(array $post): array
{
    $status = ambilTeks($post, 'status');

    return [
        'nama'    => ambilTeks($post, 'nama'),
        'peran'   => ambilTeks($post, 'peran'),
        'bio'     => ambilTeks($post, 'bio'),
        'kutipan' => ambilTeks($post, 'kutipan'),
        'status'  => in_array($status, ['aktif', 'nonaktif'], true) ? $status : '',
    ];
}

function validasiDesainer(array $data): array
{
    $error = [];

    if ($data['nama'] === '') {
        $error['nama'] = 'Nama desainer wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 100) {
        $error['nama'] = 'Nama maksimal 100 karakter.';
    }

    if ($data['peran'] === '') {
        $error['peran'] = 'Peran/jabatan wajib diisi, contoh: Desainer Grafis.';
    } elseif (mb_strlen($data['peran']) > 100) {
        $error['peran'] = 'Peran maksimal 100 karakter.';
    }

    if ($data['bio'] === '') {
        $error['bio'] = 'Keterangan singkat wajib diisi.';
    } elseif (mb_strlen($data['bio']) > 600) {
        $error['bio'] = 'Keterangan maksimal 600 karakter agar tetap ringkas di beranda.';
    }

    if (mb_strlen($data['kutipan']) > 255) {
        $error['kutipan'] = 'Kutipan maksimal 255 karakter.';
    }

    if ($data['status'] === '') {
        $error['status'] = 'Pilih apakah section ditampilkan atau tidak.';
    }

    return $error;
}

/**
 * Menyimpan data desainer. Jika tabel masih kosong ($idDesainer = 0), baris baru dibuat.
 */
function simpanDesainer(int $idDesainer, array $data, ?string $foto): void
{
    $parameter = [
        ':nama'    => $data['nama'],
        ':peran'   => $data['peran'],
        ':bio'     => $data['bio'],
        ':kutipan' => $data['kutipan'] !== '' ? $data['kutipan'] : null,
        ':foto'    => $foto,
        ':status'  => $data['status'],
    ];

    if ($idDesainer > 0) {
        $stmt = getKoneksi()->prepare(
            'UPDATE desainer
             SET nama = :nama, peran = :peran, bio = :bio, kutipan = :kutipan, foto = :foto, status = :status
             WHERE id_desainer = :id'
        );
        $parameter[':id'] = $idDesainer;
    } else {
        $stmt = getKoneksi()->prepare(
            'INSERT INTO desainer (nama, peran, bio, kutipan, foto, status)
             VALUES (:nama, :peran, :bio, :kutipan, :foto, :status)'
        );
    }

    $stmt->execute($parameter);
}

/* =====================================================================
 * 9. QUERY: TAMPILAN (tema & warna website)
 * ===================================================================== */

function ambilInputTampilan(array $post): array
{
    $data = ['tema' => ambilTeks($post, 'tema')];

    foreach (KOLOM_WARNA as $kolom) {
        $data[$kolom] = strtoupper(ambilTeks($post, $kolom));
    }

    // Tema bawaan: warna diambil dari daftar resmi (bukan dari input), agar tidak bisa diubah-ubah.
    $daftar = daftarTema();
    if (isset($daftar[$data['tema']])) {
        foreach (KOLOM_WARNA as $kolom) {
            $data[$kolom] = $daftar[$data['tema']][$kolom];
        }
    }

    return $data;
}

/**
 * Pasangan warna yang wajib terbaca (WCAG AA). Dipakai di validasi dan ditampilkan di halaman admin.
 * Setiap baris: label, nama variabel depan & belakang (contoh '--ink'), rasio, minimal, lolos (bool).
 */
function cekKontrasTema(array $tema): array
{
    $v = variabelCssTema($tema) + ['putih' => '#FFFFFF'];

    // [label, variabel warna depan, variabel warna belakang, rasio minimal]
    $pasangan = [
        ['Teks utama di atas latar',                '--ink',          '--bg',      4.5],
        ['Teks abu-abu (keterangan) di atas latar', '--muted',        '--bg',      4.5],
        ['Tulisan putih di tombol warna utama',     'putih',          '--primary', 4.5],
        ['Link & judul kecil di atas latar',        '--primary-dark', '--bg',      4.5],
        ['Angka langkah di atas warna aksen',       '--ink',          '--accent',  3.0],
        ['Ikon & link warna sorot di footer',       '--sun',          '--ink',     3.0],
    ];

    $hasil = [];
    foreach ($pasangan as [$label, $depan, $belakang, $minimal]) {
        $rasio   = rasioKontras($v[$depan], $v[$belakang]);
        $hasil[] = [
            'label'    => $label,
            'depan'    => $depan,
            'belakang' => $belakang,
            'rasio'    => $rasio,
            'minimal'  => $minimal,
            'lolos'    => $rasio >= $minimal,
        ];
    }

    return $hasil;
}

function validasiTampilan(array $data): array
{
    $error = [];

    if ($data['tema'] !== 'kustom' && !isset(daftarTema()[$data['tema']])) {
        $error['tema'] = 'Pilih salah satu tema.';
        return $error;
    }

    foreach (KOLOM_WARNA as $kolom) {
        if (!warnaHexValid($data[$kolom])) {
            $error[$kolom] = 'Warna harus berformat #RRGGBB, contoh #6C4CF1.';
        }
    }

    if ($error) {
        return $error;
    }

    // Website hanya punya satu tema terang (kartu tetap putih), jadi latar harus terang.
    if (luminansi($data['warna_latar']) < 0.8) {
        $error['warna_latar'] = 'Warna latar terlalu gelap. Pilih warna yang terang (mendekati putih/krem).';
    }

    foreach (cekKontrasTema($data) as $cek) {
        if (!$cek['lolos']) {
            $error['kontras'][] = $cek['label'] . ': kontras ' . number_format($cek['rasio'], 2, ',', '')
                . ':1, minimal ' . number_format($cek['minimal'], 1, ',', '') . ':1.';
        }
    }

    return $error;
}

/**
 * Menyimpan tema. Jika tabel masih kosong ($idTampilan = 0), baris baru dibuat.
 */
function simpanTampilan(int $idTampilan, array $data): void
{
    $parameter = [
        ':tema'   => $data['tema'],
        ':utama'  => $data['warna_utama'],
        ':aksen'  => $data['warna_aksen'],
        ':sorot'  => $data['warna_sorot'],
        ':latar'  => $data['warna_latar'],
        ':teks'   => $data['warna_teks'],
    ];

    if ($idTampilan > 0) {
        $stmt = getKoneksi()->prepare(
            'UPDATE tampilan
             SET tema = :tema, warna_utama = :utama, warna_aksen = :aksen, warna_sorot = :sorot,
                 warna_latar = :latar, warna_teks = :teks
             WHERE id_tampilan = :id'
        );
        $parameter[':id'] = $idTampilan;
    } else {
        $stmt = getKoneksi()->prepare(
            'INSERT INTO tampilan (tema, warna_utama, warna_aksen, warna_sorot, warna_latar, warna_teks)
             VALUES (:tema, :utama, :aksen, :sorot, :latar, :teks)'
        );
    }

    $stmt->execute($parameter);
}

/**
 * Menyimpan logo website. $logo = null berarti kembali ke logo bawaan (logo.svg).
 * Jika tabel tampilan masih kosong, baris baru dibuat dengan warna tema bawaan.
 */
function simpanLogo(int $idTampilan, ?string $logo): void
{
    if ($idTampilan > 0) {
        $stmt = getKoneksi()->prepare('UPDATE tampilan SET logo = :logo WHERE id_tampilan = :id');
        $stmt->execute([':logo' => $logo, ':id' => $idTampilan]);
        return;
    }

    $bawaan = daftarTema()['ungu-koral'];
    $stmt = getKoneksi()->prepare(
        'INSERT INTO tampilan (tema, warna_utama, warna_aksen, warna_sorot, warna_latar, warna_teks, logo)
         VALUES (\'ungu-koral\', :utama, :aksen, :sorot, :latar, :teks, :logo)'
    );
    $stmt->execute([
        ':utama' => $bawaan['warna_utama'],
        ':aksen' => $bawaan['warna_aksen'],
        ':sorot' => $bawaan['warna_sorot'],
        ':latar' => $bawaan['warna_latar'],
        ':teks'  => $bawaan['warna_teks'],
        ':logo'  => $logo,
    ]);
}

/* =====================================================================
 * 10. UNGGAH & HAPUS GAMBAR
 * ===================================================================== */

/**
 * Memproses satu file dari $_FILES.
 *
 * Mengembalikan:
 *   ['nama' => null,  'error' => null]   jika tidak ada file yang dipilih
 *   ['nama' => '...', 'error' => null]   jika berhasil disimpan
 *   ['nama' => null,  'error' => '...']  jika gagal
 */
function unggahGambar(?array $file): array
{
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['nama' => null, 'error' => null];
    }

    if (is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $pesan = in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'Ukuran gambar maksimal ' . UKURAN_MAKS_MB . ' MB.'
            : 'Gambar gagal diunggah. Coba lagi.';

        return ['nama' => null, 'error' => $pesan];
    }

    if ($file['size'] > UKURAN_MAKS_GAMBAR) {
        return ['nama' => null, 'error' => 'Ukuran gambar maksimal ' . UKURAN_MAKS_MB . ' MB.'];
    }

    // Tipe file dicek dari ISI file (bukan dari nama/ekstensi yang bisa dipalsukan).
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(TIPE_GAMBAR_DIIZINKAN[$mime]) || getimagesize($file['tmp_name']) === false) {
        return ['nama' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
    }

    // Nama file dibuat acak agar tidak bentrok dan tidak bisa ditebak.
    $namaBaru = AWALAN_UPLOAD . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . TIPE_GAMBAR_DIIZINKAN[$mime];

    if (!move_uploaded_file($file['tmp_name'], FOLDER_GAMBAR . $namaBaru)) {
        return ['nama' => null, 'error' => 'Gambar gagal disimpan. Pastikan folder assets/img dapat ditulis.'];
    }

    return ['nama' => $namaBaru, 'error' => null];
}

/**
 * Berapa baris (layanan + portofolio + desainer + logo) yang masih memakai file gambar ini.
 */
function hitungPemakaianGambar(string $namaFile): int
{
    $stmt = getKoneksi()->prepare(
        'SELECT (SELECT COUNT(*) FROM layanan WHERE gambar = :g1)
              + (SELECT COUNT(*) FROM portofolio WHERE gambar = :g2)
              + (SELECT COUNT(*) FROM desainer WHERE foto = :g3)
              + (SELECT COUNT(*) FROM tampilan WHERE logo = :g4)'
    );
    $stmt->execute([':g1' => $namaFile, ':g2' => $namaFile, ':g3' => $namaFile, ':g4' => $namaFile]);

    return (int) $stmt->fetchColumn();
}

/**
 * Menghapus file gambar hasil unggahan jika sudah tidak dipakai.
 * Gambar bawaan (SVG contoh) tidak pernah dihapus karena tidak berawalan "upload-".
 */
function hapusGambarJikaTidakDipakai(?string $namaFile): void
{
    $namaFile = basename((string) $namaFile);

    if (!str_starts_with($namaFile, AWALAN_UPLOAD) || hitungPemakaianGambar($namaFile) > 0) {
        return;
    }

    $path = FOLDER_GAMBAR . $namaFile;
    if (is_file($path)) {
        unlink($path);
    }
}
