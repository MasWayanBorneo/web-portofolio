<?php
/**
 * Bagian bawah halaman admin: penutup konten, footer, dan script.
 */
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
<script src="assets/adminlte/adminlte.min.js"></script>
<script src="assets/admin.js"></script>
</body>
</html>
