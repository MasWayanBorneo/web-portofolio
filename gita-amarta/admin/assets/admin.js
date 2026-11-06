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

  // 5. Halaman Tampilan: pratinjau tema & cek kontras langsung saat warna diganti.
  //    Rumusnya SAMA dengan variabelCssTema() dan rasioKontras() di includes/functions.php.
  //    Penentu akhir tetap validasi PHP saat disimpan.
  var formTema = document.querySelector('[data-form-tema]');
  if (formTema) {
    var KOLOM = ['warna_utama', 'warna_aksen', 'warna_sorot', 'warna_latar', 'warna_teks'];
    var pratinjau = formTema.querySelector('[data-pratinjau]');
    var radioKustom = formTema.querySelector('input[name="tema"][value="kustom"]');

    var keRgb = function (hex) {
      return [1, 3, 5].map(function (i) { return parseInt(hex.substr(i, 2), 16); });
    };
    var keHex = function (rgb) {
      return '#' + rgb.map(function (v) {
        return Math.round(Math.max(0, Math.min(255, v))).toString(16).padStart(2, '0');
      }).join('').toUpperCase();
    };
    var campur = function (a, b, porsiB) {
      var x = keRgb(a);
      var y = keRgb(b);
      return keHex(x.map(function (v, i) { return v + (y[i] - v) * porsiB; }));
    };
    var luminansi = function (hex) {
      var k = keRgb(hex).map(function (v) {
        var c = v / 255;
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
      });
      return 0.2126 * k[0] + 0.7152 * k[1] + 0.0722 * k[2];
    };
    var kontras = function (a, b) {
      var la = luminansi(a);
      var lb = luminansi(b);
      return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05);
    };

    var ambilWarna = function () {
      var w = {};
      KOLOM.forEach(function (k) { w[k] = formTema.elements[k].value.toUpperCase(); });
      return w;
    };

    var hitungVariabel = function (w) {
      var rgb = function (hex) { return keRgb(hex).join(', '); };
      return {
        '--bg': w.warna_latar,
        '--ink': w.warna_teks,
        '--primary': w.warna_utama,
        '--accent': w.warna_aksen,
        '--sun': w.warna_sorot,
        '--muted': campur(w.warna_teks, w.warna_latar, 0.35),
        '--primary-dark': campur(w.warna_utama, '#000000', 0.18),
        '--primary-soft': campur(w.warna_utama, '#FFFFFF', 0.9),
        '--field-border': campur(w.warna_teks, '#FFFFFF', 0.5),
        '--bg-rgb': rgb(w.warna_latar),
        '--ink-rgb': rgb(w.warna_teks),
        '--primary-rgb': rgb(w.warna_utama),
        '--accent-rgb': rgb(w.warna_aksen),
        '--sun-rgb': rgb(w.warna_sorot),
        'putih': '#FFFFFF'
      };
    };

    var perbarui = function () {
      var w = ambilWarna();
      var v = hitungVariabel(w);

      Object.keys(v).forEach(function (nama) {
        if (nama.indexOf('--') === 0) {
          pratinjau.style.setProperty(nama, v[nama]);
        }
      });

      KOLOM.forEach(function (k) {
        var kode = formTema.querySelector('[data-kode-untuk="' + k + '"]');
        if (kode) { kode.textContent = w[k]; }
      });

      formTema.querySelectorAll('[data-daftar-kontras] li').forEach(function (li) {
        var rasio = kontras(v[li.dataset.depan], v[li.dataset.belakang]);
        var lolos = rasio >= parseFloat(li.dataset.minimal);
        var badge = li.querySelector('[data-status]');
        li.querySelector('[data-rasio]').textContent = rasio.toFixed(2).replace('.', ',') + ':1';
        badge.textContent = lolos ? 'Lolos' : 'Kurang';
        badge.className = 'badge ' + (lolos ? 'text-bg-success' : 'text-bg-danger');
      });
    };

    // Memilih tema bawaan: isi kolom warna dengan warna tema tersebut.
    formTema.querySelectorAll('input[name="tema"][data-warna]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        var warna = JSON.parse(radio.dataset.warna);
        KOLOM.forEach(function (k) { formTema.elements[k].value = warna[k].toLowerCase(); });
        perbarui();
      });
    });

    // Mengubah salah satu warna: tema otomatis menjadi "Kustom".
    KOLOM.forEach(function (k) {
      formTema.elements[k].addEventListener('input', function () {
        if (radioKustom) { radioKustom.checked = true; }
        perbarui();
      });
    });
  }
})();
