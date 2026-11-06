<?php
/**
 * Keluar dari panel admin. Hanya menerima POST + token CSRF (dari menu akun di navbar).
 */

require_once __DIR__ . '/includes/auth.php';

mulaiSessionAdmin();
wajibPostDenganCsrf();

keluarAdmin();
setFlash('success', 'Kamu sudah keluar dari panel admin.');

redirect('login.php');
