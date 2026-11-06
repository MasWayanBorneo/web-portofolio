<?php
/**
 * Memproses formulir pemesanan (KF-05, KF-06).
 *
 * Alur:
 * 1. Ambil & rapikan isian formulir.
 * 2. Pastikan layanan ada dan berstatus aktif (data dari database, bukan dari isian readonly).
 * 3. Validasi ulang (aturannya sama dengan validasi JavaScript).
 * 4. Gagal  → simpan error + isian ke session, kembali ke detail.php (WhatsApp tidak dibuka).
 *    Berhasil → susun pesan, encode dengan rawurlencode, redirect ke https://wa.me/...
 *
 * Data pemesanan TIDAK disimpan ke database.
 */

require_once __DIR__ . '/includes/functions.php';

mulaiSession();

// Halaman ini hanya menerima kiriman formulir (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: layanan.php', true, 303);
    exit;
}

$input = ambilInputPesanan($_POST);

if ($input['id_layanan'] === null) {
    header('Location: layanan.php', true, 303);
    exit;
}

$layanan = getLayananAktifById($input['id_layanan']);

if ($layanan === null) {
    // Layanan tidak ada / nonaktif → detail.php akan menampilkan "Layanan tidak ditemukan".
    header('Location: detail.php?id=' . $input['id_layanan'], true, 303);
    exit;
}

$error = validasiPesanan($input);

if ($error) {
    simpanFlashPesanan((int) $layanan['id_layanan'], $error, [
        'nama'      => $input['nama'],
        'whatsapp'  => $input['whatsapp'],
        'kebutuhan' => $input['kebutuhan'],
        'tenggat'   => $input['tenggat'],
    ]);
    header('Location: detail.php?id=' . (int) $layanan['id_layanan'] . '#form-pesan', true, 303);
    exit;
}

$profil = getProfilUsaha();
$pesan  = susunPesanWhatsApp($layanan, $input);

header('Location: ' . linkWhatsApp((string) $profil['no_whatsapp'], $pesan), true, 303);
exit;
