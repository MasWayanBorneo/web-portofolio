<?php
/**
 * Session, login, token CSRF, dan pesan flash untuk halaman admin.
 *
 * Setiap halaman admin (selain login.php) cukup memanggil:
 *     require_once __DIR__ . '/includes/auth.php';
 *     $admin = wajibLogin();
 */

require_once __DIR__ . '/../../includes/functions-admin.php';

/** Admin otomatis keluar jika tidak ada aktivitas selama 2 jam. */
const BATAS_IDLE_DETIK = 2 * 60 * 60;

/** Setelah 5 kali salah password, form login dikunci 5 menit. */
const MAKS_GAGAL_LOGIN = 5;
const LAMA_KUNCI_DETIK = 5 * 60;

/* =====================================================================
 * 1. SESSION
 * ===================================================================== */

function mulaiSessionAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,          // cookie hilang saat browser ditutup
            'path'     => '/',
            'httponly' => true,       // cookie tidak bisa dibaca JavaScript
            'samesite' => 'Lax',      // cookie tidak dikirim dari form situs lain
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* =====================================================================
 * 2. LOGIN & LOGOUT
 * ===================================================================== */

/**
 * Wajib dipanggil di awal setiap halaman admin.
 * Mengembalikan data admin yang sedang login, atau mengalihkan ke login.php.
 */
function wajibLogin(): array
{
    mulaiSessionAdmin();

    $idAdmin  = $_SESSION['admin_id'] ?? null;
    $terakhir = $_SESSION['aktivitas_terakhir'] ?? 0;

    if (!is_int($idAdmin)) {
        redirect('login.php');
    }

    if (time() - $terakhir > BATAS_IDLE_DETIK) {
        keluarAdmin();
        setFlash('warning', 'Sesi berakhir karena tidak ada aktivitas. Silakan masuk lagi.');
        redirect('login.php');
    }

    // Data diambil ulang dari database agar akun yang sudah dihapus langsung tidak bisa masuk.
    $admin = getAdminById($idAdmin);
    if ($admin === null) {
        keluarAdmin();
        redirect('login.php');
    }

    $_SESSION['aktivitas_terakhir'] = time();

    return $admin;
}

/**
 * Mencoba login. Mengembalikan pesan error, atau null jika berhasil.
 */
function cobaLogin(string $username, string $password): ?string
{
    $sisaKunci = sisaWaktuKunciLogin();
    if ($sisaKunci > 0) {
        return 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . ceil($sisaKunci / 60) . ' menit.';
    }

    if ($username === '' || $password === '') {
        return 'Username dan password wajib diisi.';
    }

    $admin = getAdminByUsername($username);

    if ($admin === null || !password_verify($password, $admin['password'])) {
        catatGagalLogin();
        // Pesan dibuat sama untuk username/password salah agar username tidak bisa ditebak.
        return 'Username atau password salah.';
    }

    // Jika algoritma hash bawaan PHP berubah, hash lama diperbarui otomatis.
    if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
        ubahPasswordAdmin((int) $admin['id_admin'], $password);
    }

    // ID session diganti setelah login untuk mencegah session fixation.
    session_regenerate_id(true);
    $_SESSION['admin_id']           = (int) $admin['id_admin'];
    $_SESSION['aktivitas_terakhir'] = time();
    unset($_SESSION['gagal_login'], $_SESSION['kunci_sampai'], $_SESSION['csrf']);

    return null;
}

function keluarAdmin(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function catatGagalLogin(): void
{
    $_SESSION['gagal_login'] = ($_SESSION['gagal_login'] ?? 0) + 1;

    if ($_SESSION['gagal_login'] >= MAKS_GAGAL_LOGIN) {
        $_SESSION['kunci_sampai'] = time() + LAMA_KUNCI_DETIK;
        $_SESSION['gagal_login']  = 0;
    }
}

function sisaWaktuKunciLogin(): int
{
    return max(0, ($_SESSION['kunci_sampai'] ?? 0) - time());
}

/* =====================================================================
 * 3. TOKEN CSRF
 * Setiap form POST membawa token rahasia dari session. Form palsu dari situs
 * lain tidak tahu token ini, sehingga permintaannya ditolak.
 * ===================================================================== */

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function inputCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(tokenCsrf()) . '">';
}

function cekCsrf(): void
{
    $token = $_POST['csrf'] ?? '';

    if (!is_string($token) || !hash_equals(tokenCsrf(), $token)) {
        http_response_code(400);
        exit('Permintaan ditolak karena token keamanan tidak cocok. Kembali, muat ulang halaman, lalu coba lagi.');
    }
}

/**
 * Halaman yang menerima POST memanggil ini: hanya POST yang lolos, dan token dicek.
 */
function wajibPostDenganCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode tidak diizinkan.');
    }

    cekCsrf();
}

/* =====================================================================
 * 4. PESAN FLASH (tampil sekali setelah redirect)
 * ===================================================================== */

/**
 * $tipe mengikuti warna alert Bootstrap: success | danger | warning | info.
 */
function setFlash(string $tipe, string $pesan): void
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function ambilFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flash;
}

/* =====================================================================
 * 5. HELPER TAMPILAN FORM
 * ===================================================================== */

/** Kelas "is-invalid" Bootstrap jika kolom punya error. */
function kelasError(array $error, string $kolom): string
{
    return isset($error[$kolom]) ? ' is-invalid' : '';
}

/** Pesan error di bawah kolom (format Bootstrap). */
function pesanError(array $error, string $kolom): string
{
    return isset($error[$kolom])
        ? '<div class="invalid-feedback">' . e($error[$kolom]) . '</div>'
        : '';
}

/** URL gambar dilihat dari folder admin/ (satu tingkat di bawah root). */
function urlGambarAdmin(?string $namaFile): string
{
    return '../' . urlGambar($namaFile);
}
