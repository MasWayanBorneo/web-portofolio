<?php
/**
 * Bagian bawah setiap halaman: footer kontak (id="kontak") dan tombol WhatsApp melayang.
 * $profil sudah tersedia dari header.php.
 */

$instagram   = ltrim((string) $profil['instagram'], '@');
$pesanSapaan = 'Halo ' . $profil['nama_usaha'] . ', saya ingin bertanya tentang jasa desain.';
$linkWaUmum  = linkWhatsApp((string) $profil['no_whatsapp'], $pesanSapaan);
?>
</main>

<footer class="site-footer" id="kontak">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand--light" href="index.php">
                <img src="<?= e(urlLogo()) ?>" width="40" height="40" alt="">
                <span><?= e($profil['nama_usaha']) ?></span>
            </a>
            <p><?= e($profil['deskripsi']) ?></p>
        </div>

        <div>
            <h2 class="footer-title">Kontak</h2>
            <ul class="contact-list">
                <?php if ($profil['no_whatsapp'] !== ''): ?>
                    <li>
                        <?= ikon('whatsapp') ?>
                        <a href="<?= e($linkWaUmum) ?>" target="_blank" rel="noopener">
                            <?= e(formatNomorTampil((string) $profil['no_whatsapp'])) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($instagram !== ''): ?>
                    <li>
                        <?= ikon('instagram') ?>
                        <a href="https://www.instagram.com/<?= e(rawurlencode($instagram)) ?>/" target="_blank" rel="noopener">
                            @<?= e($instagram) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($profil['email'] !== ''): ?>
                    <li>
                        <?= ikon('email') ?>
                        <a href="mailto:<?= e($profil['email']) ?>"><?= e($profil['email']) ?></a>
                    </li>
                <?php endif; ?>
                <?php if ($profil['alamat'] !== ''): ?>
                    <li>
                        <?= ikon('lokasi') ?>
                        <span><?= e($profil['alamat']) ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div>
            <h2 class="footer-title">Jelajahi</h2>
            <ul class="footer-links">
                <li><a href="layanan.php">Katalog Layanan</a></li>
                <li><a href="portofolio.php">Portofolio</a></li>
                <li><a href="index.php#cara-pesan">Cara Pesan</a></li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e($profil['nama_usaha']) ?> – Jasa Desain Grafis. Semua hak dilindungi.</p>
    </div>
</footer>

<?php if ($profil['no_whatsapp'] !== ''): ?>
    <a class="wa-float" href="<?= e($linkWaUmum) ?>" target="_blank" rel="noopener"
       aria-label="Chat WhatsApp <?= e($profil['nama_usaha']) ?>">
        <?= ikon('whatsapp') ?>
        <span class="wa-float-label" aria-hidden="true">Chat kami</span>
    </a>
<?php endif; ?>

</body>
</html>
