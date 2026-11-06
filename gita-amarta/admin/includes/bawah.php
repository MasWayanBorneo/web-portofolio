<?php
/**
 * Bagian bawah halaman admin: penutup konten, footer, dan script.
 */

/** @var array $profilUsaha  dibuat di atas.php */
?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Panel Admin</div>
        <strong>&copy; <?= date('Y') ?> <?= e($profilUsaha['nama_usaha']) ?></strong> – Jasa Desain Grafis.
    </footer>
</div>

<!-- Bootstrap 5 (bundle sudah termasuk Popper untuk dropdown) lalu AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= e(urlAset('assets/adminlte/adminlte.min.js', __DIR__ . '/..')) ?>"></script>
<script src="<?= e(urlAset('assets/admin.js', __DIR__ . '/..')) ?>"></script>
</body>
</html>
