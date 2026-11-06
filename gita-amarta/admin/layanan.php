<?php
/**
 * Daftar layanan (semua status) + aksi ubah status dan hapus.
 * Aksi dikirim lewat POST ke halaman ini juga, lalu di-redirect (pola Post/Redirect/Get).
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $idLayanan = ambilAngka($_POST, 'id_layanan', 1);
    $aksi      = ambilTeks($_POST, 'aksi');
    $layanan   = $idLayanan !== null ? getLayananById($idLayanan) : null;

    if ($layanan === null) {
        setFlash('danger', 'Layanan tidak ditemukan.');
    } elseif ($aksi === 'status') {
        $statusBaru = $layanan['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        ubahStatusLayanan($idLayanan, $statusBaru);
        setFlash('success', 'Layanan "' . $layanan['nama_layanan'] . '" sekarang ' . $statusBaru . '.');
    } elseif ($aksi === 'hapus') {
        hapusLayanan($idLayanan);
        hapusGambarJikaTidakDipakai($layanan['gambar']);
        setFlash('success', 'Layanan "' . $layanan['nama_layanan'] . '" sudah dihapus.');
    }

    redirect('layanan.php');
}

$daftarLayanan = getSemuaLayananAdmin();

$judulHalaman = 'Layanan';
$menuAktif    = 'layanan';

require __DIR__ . '/includes/atas.php';
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h2 class="card-title fs-6 fw-bold mb-0">Semua layanan (<?= count($daftarLayanan) ?>)</h2>
        <a href="layanan-form.php" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Tambah layanan</a>
    </div>

    <div class="card-body p-0">
        <?php if ($daftarLayanan): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 84px">Gambar</th>
                            <th scope="col">Nama layanan</th>
                            <th scope="col">Kisaran harga</th>
                            <th scope="col">Estimasi</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarLayanan as $layanan): ?>
                            <?php $aktif = $layanan['status'] === 'aktif'; ?>
                            <tr>
                                <td><img src="<?= e(urlGambarAdmin($layanan['gambar'])) ?>" alt="" class="thumb gambar-utuh" width="64" height="48" loading="lazy"></td>
                                <td>
                                    <span class="fw-semibold d-block"><?= e($layanan['nama_layanan']) ?></span>
                                    <small class="text-body-secondary"><?= e($layanan['nama_kategori']) ?></small>
                                </td>
                                <td class="text-nowrap fw-semibold"><?= e(formatKisaranHarga((int) $layanan['harga_min'], (int) $layanan['harga_max'])) ?></td>
                                <td class="text-nowrap"><?= e($layanan['estimasi_hari']) ?> hari</td>
                                <td>
                                    <span class="badge rounded-pill <?= $aktif ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                        <?= $aktif ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="layanan-form.php?id=<?= e($layanan['id_layanan']) ?>" class="btn btn-outline-primary btn-sm"
                                       aria-label="Edit <?= e($layanan['nama_layanan']) ?>"><i class="bi bi-pencil"></i></a>

                                    <form action="layanan.php" method="post" class="d-inline">
                                        <?= inputCsrf() ?>
                                        <input type="hidden" name="id_layanan" value="<?= e($layanan['id_layanan']) ?>">
                                        <input type="hidden" name="aksi" value="status">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm"
                                                title="<?= $aktif ? 'Nonaktifkan (sembunyikan dari website)' : 'Aktifkan (tampilkan di website)' ?>"
                                                aria-label="<?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?> <?= e($layanan['nama_layanan']) ?>">
                                            <i class="bi <?= $aktif ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                        </button>
                                    </form>

                                    <form action="layanan.php" method="post" class="d-inline"
                                          data-konfirmasi="Hapus layanan &quot;<?= e($layanan['nama_layanan']) ?>&quot;? Tindakan ini tidak bisa dibatalkan.">
                                        <?= inputCsrf() ?>
                                        <input type="hidden" name="id_layanan" value="<?= e($layanan['id_layanan']) ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Hapus <?= e($layanan['nama_layanan']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-body-secondary text-center py-5 mb-0">Belum ada layanan. Klik <strong>Tambah layanan</strong> untuk membuat yang pertama.</p>
        <?php endif; ?>
    </div>

    <div class="card-footer small text-body-secondary">
        <i class="bi bi-info-circle"></i> Layanan <strong>nonaktif</strong> tidak tampil di website (beranda, katalog, maupun detail).
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
