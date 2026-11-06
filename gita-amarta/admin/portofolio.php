<?php
/**
 * Daftar portofolio + aksi hapus (POST ke halaman ini, lalu redirect).
 */

require_once __DIR__ . '/includes/auth.php';

$admin = wajibLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cekCsrf();

    $idPortofolio = ambilAngka($_POST, 'id_portofolio', 1);
    $karya        = $idPortofolio !== null ? getPortofolioById($idPortofolio) : null;

    if ($karya === null) {
        setFlash('danger', 'Karya tidak ditemukan.');
    } elseif (ambilTeks($_POST, 'aksi') === 'hapus') {
        hapusPortofolio($idPortofolio);
        hapusGambarJikaTidakDipakai($karya['gambar']);
        setFlash('success', 'Karya "' . $karya['judul'] . '" sudah dihapus.');
    }

    redirect('portofolio.php');
}

// Filter kategori opsional lewat ?kategori=id (fungsi yang sama dengan halaman publik).
$kategoriList    = getSemuaKategori();
$idKategoriPilih = ambilIdDariUrl('kategori');
if ($idKategoriPilih !== null && getKategoriById($idKategoriPilih) === null) {
    $idKategoriPilih = null;
}
$daftarKarya = getSemuaPortofolio($idKategoriPilih);

$judulHalaman = 'Portofolio';
$menuAktif    = 'portofolio';

require __DIR__ . '/includes/atas.php';
?>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h2 class="card-title fs-6 fw-bold mb-0">Karya (<?= count($daftarKarya) ?>)</h2>

        <form action="portofolio.php" method="get" class="d-flex gap-2 ms-auto">
            <label for="filter-kategori" class="visually-hidden">Filter kategori</label>
            <select id="filter-kategori" name="kategori" class="form-select form-select-sm" data-kirim-saat-ganti>
                <option value="">Semua kategori</option>
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= e($kategori['id_kategori']) ?>"<?= $idKategoriPilih === (int) $kategori['id_kategori'] ? ' selected' : '' ?>>
                        <?= e($kategori['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-outline-secondary btn-sm">Terapkan</button></noscript>
        </form>

        <a href="portofolio-form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah karya</a>
    </div>

    <div class="card-body p-0">
        <?php if ($daftarKarya): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 84px">Gambar</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarKarya as $karya): ?>
                            <tr>
                                <td><img src="<?= e(urlGambarAdmin($karya['gambar'])) ?>" alt="" class="thumb" width="64" height="48" loading="lazy"></td>
                                <td class="fw-semibold"><?= e($karya['judul']) ?></td>
                                <td><span class="badge rounded-pill text-bg-light border"><?= e($karya['nama_kategori']) ?></span></td>
                                <td class="text-nowrap"><?= e(formatTanggal($karya['tanggal'])) ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="portofolio-form.php?id=<?= e($karya['id_portofolio']) ?>" class="btn btn-outline-primary btn-sm"
                                       aria-label="Edit <?= e($karya['judul']) ?>"><i class="bi bi-pencil"></i></a>
                                    <form action="portofolio.php" method="post" class="d-inline"
                                          data-konfirmasi="Hapus karya &quot;<?= e($karya['judul']) ?>&quot;? Tindakan ini tidak bisa dibatalkan.">
                                        <?= inputCsrf() ?>
                                        <input type="hidden" name="id_portofolio" value="<?= e($karya['id_portofolio']) ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Hapus <?= e($karya['judul']) ?>">
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
            <p class="text-body-secondary text-center py-5 mb-0">Belum ada karya<?= $idKategoriPilih !== null ? ' di kategori ini' : '' ?>.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/bawah.php'; ?>
