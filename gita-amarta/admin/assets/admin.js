/**
 * Interaksi kecil panel admin (vanilla JS). Semua fitur tetap berjalan tanpa JS,
 * script ini hanya menambah kenyamanan.
 */
(function () {
  'use strict';

  // 1. Konfirmasi sebelum menghapus: <form data-konfirmasi="Pesan...">
  document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.dataset.konfirmasi)) {
        event.preventDefault();
      }
    });
  });

  // 2. Pratinjau gambar sebelum diunggah: <img data-preview-untuk="id-input-file">
  document.querySelectorAll('img[data-preview-untuk]').forEach(function (img) {
    var input = document.getElementById(img.dataset.previewUntuk);
    var urlAwal = img.src;
    var urlSementara = null;

    if (!input) {
      return;
    }

    input.addEventListener('change', function () {
      if (urlSementara) {
        URL.revokeObjectURL(urlSementara);
        urlSementara = null;
      }

      var file = input.files && input.files[0];
      if (file && file.type.indexOf('image/') === 0) {
        urlSementara = URL.createObjectURL(file);
        img.src = urlSementara;
      } else {
        img.src = urlAwal;
      }
    });
  });

  // 3. Tombol tampilkan/sembunyikan password: <button data-toggle-password="#id-input">
  document.querySelectorAll('[data-toggle-password]').forEach(function (tombol) {
    var input = document.querySelector(tombol.dataset.togglePassword);
    var ikon = tombol.querySelector('.bi');

    if (!input) {
      return;
    }

    tombol.addEventListener('click', function () {
      var terlihat = input.type === 'text';
      input.type = terlihat ? 'password' : 'text';
      tombol.setAttribute('aria-pressed', String(!terlihat));
      tombol.setAttribute('aria-label', terlihat ? 'Tampilkan password' : 'Sembunyikan password');
      if (ikon) {
        ikon.className = terlihat ? 'bi bi-eye' : 'bi bi-eye-slash';
      }
    });
  });

  // 4. Dropdown filter yang langsung dikirim saat diganti: <select data-kirim-saat-ganti>
  document.querySelectorAll('select[data-kirim-saat-ganti]').forEach(function (select) {
    select.addEventListener('change', function () {
      select.form.submit();
    });
  });
})();
