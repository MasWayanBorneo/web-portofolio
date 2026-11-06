/* =====================================================================
   Gita Amarta – JavaScript utama (vanilla, tanpa library).

   Modul:
   1. Navbar      : solid + blur saat discroll, hamburger di mobile
   2. Reveal      : elemen muncul (fade-up) saat masuk layar, sekali saja
   3. Filter      : filter kategori di katalog & portofolio (dengan animasi)
   4. Lightbox    : gambar besar portofolio, sebelumnya/berikutnya, Esc
   5. Form pesan  : validasi sebelum dikirim ke pesan.php
   ===================================================================== */

(function () {
  'use strict';

  var motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

  function kurangiGerak() {
    return motionQuery.matches;
  }

  /** Menunggu sekian milidetik (langsung selesai jika pengguna memilih reduced motion). */
  function tunggu(ms) {
    return new Promise(function (selesai) {
      setTimeout(selesai, kurangiGerak() ? 0 : ms);
    });
  }

  /* -------------------------------------------------------------------
     1. NAVBAR
     ------------------------------------------------------------------- */
  function initNavbar() {
    var header = document.querySelector('[data-header]');
    if (!header) return;

    var tombol = header.querySelector('[data-nav-toggle]');
    var menu = header.querySelector('[data-nav-menu]');

    function cekScroll() {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    }
    cekScroll();
    window.addEventListener('scroll', cekScroll, { passive: true });

    if (!tombol || !menu) return;

    function setMenu(buka) {
      header.classList.toggle('is-open', buka);
      tombol.setAttribute('aria-expanded', String(buka));
      tombol.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu');
    }

    tombol.addEventListener('click', function () {
      setMenu(!header.classList.contains('is-open'));
    });

    // Tutup menu setelah link diklik, klik di luar, atau tombol Esc.
    menu.addEventListener('click', function (e) {
      if (e.target.closest('a')) setMenu(false);
    });
    document.addEventListener('click', function (e) {
      if (header.classList.contains('is-open') && !header.contains(e.target)) setMenu(false);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('is-open')) {
        setMenu(false);
        tombol.focus();
      }
    });

    // Jika layar diperlebar ke ukuran desktop, pastikan status menu direset.
    var desktop = window.matchMedia('(min-width: 861px)');
    desktop.addEventListener('change', function (e) {
      if (e.matches) setMenu(false);
    });
  }

  /* -------------------------------------------------------------------
     2. SCROLL REVEAL (IntersectionObserver, hanya sekali)
     ------------------------------------------------------------------- */
  var revealObserver = null;

  /** Tandai elemen sudah tampil (dipakai juga oleh filter). */
  function tampilkanLangsung(el) {
    el.classList.add('is-visible');
    if (revealObserver) revealObserver.unobserve(el);
  }

  function initReveal() {
    var elemen = document.querySelectorAll('.reveal');
    if (!elemen.length) return;

    if (!('IntersectionObserver' in window) || kurangiGerak()) {
      elemen.forEach(tampilkanLangsung);
      return;
    }

    revealObserver = new IntersectionObserver(function (entries) {
      var masuk = entries.filter(function (entry) { return entry.isIntersecting; });

      // Stagger: elemen yang masuk layar bersamaan diberi jeda bertahap.
      masuk.forEach(function (entry, i) {
        var el = entry.target;
        var jeda = Math.min(i, 6) * 90;
        el.style.transitionDelay = jeda + 'ms';
        el.classList.add('is-visible');
        revealObserver.unobserve(el);
        setTimeout(function () { el.style.transitionDelay = ''; }, 650 + jeda);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elemen.forEach(function (el) { revealObserver.observe(el); });
  }

  /* -------------------------------------------------------------------
     3. FILTER KATEGORI
     Markup: <nav data-filter-group="ID_GRID"> berisi <a data-filter="semua|id">
             <div id="ID_GRID"> berisi item dengan data-kategori="id">
     Tanpa JS, link chip (?kategori=id) tetap bekerja lewat server.
     ------------------------------------------------------------------- */
  function initFilter(grup) {
    var idGrid = grup.getAttribute('data-filter-group');
    var grid = document.getElementById(idGrid);
    if (!grid) return;

    var chips = Array.prototype.slice.call(grup.querySelectorAll('[data-filter]'));
    var items = Array.prototype.slice.call(grid.querySelectorAll('[data-kategori]'));
    var kosong = document.querySelector('[data-filter-empty="' + idGrid + '"]');
    var info = document.querySelector('[data-filter-count="' + idGrid + '"]');
    var aktif = chips.filter(function (c) { return c.classList.contains('is-active'); })[0] || chips[0];
    var giliran = 0; // mencegah animasi lama menimpa klik terbaru

    // Indikator yang meluncur di belakang chip aktif.
    var indikator = document.createElement('span');
    indikator.className = 'filter-indicator';
    indikator.setAttribute('aria-hidden', 'true');
    grup.insertBefore(indikator, grup.firstChild);
    grup.classList.add('has-indicator');

    // Ukuran dasar indikator 100×42 px, lalu di-scale agar pas chip (hanya transform).
    function geserIndikator(chip, animasi) {
      if (!animasi) indikator.style.transition = 'none';
      var sx = chip.offsetWidth / 100;
      var sy = chip.offsetHeight / 42;
      indikator.style.transform =
        'translate(' + chip.offsetLeft + 'px, ' + chip.offsetTop + 'px) scale(' + sx + ', ' + sy + ')';
      if (!animasi) {
        void indikator.offsetWidth; // paksa reflow sebelum transisi diaktifkan lagi
        indikator.style.transition = '';
      }
    }

    geserIndikator(aktif, false);
    window.addEventListener('resize', function () { geserIndikator(aktif, false); });
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () { geserIndikator(aktif, false); });
    }

    function cocok(item, filter) {
      return filter === 'semua' || item.getAttribute('data-kategori') === filter;
    }

    function perbaruiInfo(filter) {
      var jumlah = items.filter(function (item) { return cocok(item, filter); }).length;
      if (kosong) kosong.hidden = jumlah > 0;
      if (info) info.textContent = 'Menampilkan ' + jumlah + ' ' + (info.getAttribute('data-noun') || 'item');
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function (e) {
        e.preventDefault();
        if (chip === aktif) return;

        aktif.classList.remove('is-active');
        aktif.removeAttribute('aria-current');
        chip.classList.add('is-active');
        chip.setAttribute('aria-current', 'true');
        aktif = chip;
        geserIndikator(chip, true);

        // URL ikut berubah, jadi halaman bisa di-refresh / dibagikan dengan filter yang sama.
        if (window.history && history.replaceState) history.replaceState(null, '', chip.href);

        jalankanFilter(chip.getAttribute('data-filter'));
      });
    });

    function jalankanFilter(filter) {
      var saya = ++giliran;

      // a) Item yang tidak cocok: mengecil & memudar, lalu disembunyikan.
      var keluar = items.filter(function (item) { return !item.hidden && !cocok(item, filter); });
      keluar.forEach(function (item) {
        item.style.transitionDelay = '';
        item.classList.add('is-hiding');
      });

      tunggu(keluar.length ? 260 : 0).then(function () {
        if (saya !== giliran) return;

        keluar.forEach(function (item) { item.hidden = true; });

        // b) Item yang cocok tetapi belum tampil: masuk dengan stagger.
        var masuk = items.filter(function (item) {
          return cocok(item, filter) && (item.hidden || item.classList.contains('is-hiding'));
        });

        masuk.forEach(function (item) {
          item.hidden = false;
          item.classList.add('is-hiding');
          tampilkanLangsung(item);
        });

        void grid.offsetWidth; // reflow agar keadaan awal (mengecil) tercatat

        masuk.forEach(function (item, i) {
          item.style.transitionDelay = (i * 70) + 'ms';
          item.classList.remove('is-hiding');
        });

        setTimeout(function () {
          masuk.forEach(function (item) { item.style.transitionDelay = ''; });
        }, 450 + masuk.length * 70);

        perbaruiInfo(filter);
      });
    }
  }

  function initSemuaFilter() {
    document.querySelectorAll('[data-filter-group]').forEach(initFilter);
  }

  /* -------------------------------------------------------------------
     4. LIGHTBOX PORTOFOLIO
     Item: <a data-lightbox href="gambar" data-judul data-kategori-nama data-tanggal data-deskripsi>
     ------------------------------------------------------------------- */
  function initLightbox() {
    if (!document.querySelector('[data-lightbox]')) return;

    var root = document.createElement('div');
    root.className = 'lightbox';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-labelledby', 'lightbox-judul');
    root.innerHTML =
      '<div class="lightbox-backdrop" data-lb-tutup></div>' +
      '<div class="lightbox-dialog">' +
        '<button type="button" class="lightbox-btn lightbox-close" aria-label="Tutup" data-lb-tutup>✕</button>' +
        '<div class="lightbox-stage">' +
          '<figure class="lightbox-figure"><img alt="" width="800" height="600"></figure>' +
          '<div class="lightbox-info">' +
            '<span class="tag" data-lb-kategori></span>' +
            '<h2 id="lightbox-judul"></h2>' +
            '<p class="lightbox-date" data-lb-tanggal></p>' +
            '<p class="lightbox-desc" data-lb-deskripsi></p>' +
            '<p class="lightbox-counter" data-lb-urutan aria-live="polite"></p>' +
          '</div>' +
        '</div>' +
        '<button type="button" class="lightbox-btn lightbox-prev" aria-label="Karya sebelumnya" data-lb-prev>‹</button>' +
        '<button type="button" class="lightbox-btn lightbox-next" aria-label="Karya berikutnya" data-lb-next>›</button>' +
      '</div>';
    document.body.appendChild(root);

    var stage = root.querySelector('.lightbox-stage');
    var gambar = root.querySelector('img');
    var judul = root.querySelector('#lightbox-judul');
    var kategori = root.querySelector('[data-lb-kategori]');
    var tanggal = root.querySelector('[data-lb-tanggal]');
    var deskripsi = root.querySelector('[data-lb-deskripsi]');
    var urutan = root.querySelector('[data-lb-urutan]');
    var tombolTutup = root.querySelector('.lightbox-close');
    var tombolPrev = root.querySelector('[data-lb-prev]');
    var tombolNext = root.querySelector('[data-lb-next]');

    var daftar = [];
    var indeks = 0;
    var pemicu = null;
    var sibuk = false;

    // Isi konten memakai textContent (aman dari XSS).
    function isi(item) {
      gambar.src = item.getAttribute('href');
      gambar.alt = item.getAttribute('data-judul') || '';
      judul.textContent = item.getAttribute('data-judul') || '';
      kategori.textContent = item.getAttribute('data-kategori-nama') || '';
      tanggal.textContent = item.getAttribute('data-tanggal') || '';
      deskripsi.textContent = item.getAttribute('data-deskripsi') || '';
      urutan.textContent = (indeks + 1) + ' / ' + daftar.length;
    }

    function buka(item) {
      var galeri = item.closest('[data-gallery]') || document;
      daftar = Array.prototype.slice.call(galeri.querySelectorAll('[data-lightbox]'))
        .filter(function (el) { return !el.hidden; });
      indeks = Math.max(0, daftar.indexOf(item));
      pemicu = item;

      var lebihDariSatu = daftar.length > 1;
      tombolPrev.hidden = !lebihDariSatu;
      tombolNext.hidden = !lebihDariSatu;

      isi(daftar[indeks]);
      root.classList.add('is-open');
      document.body.classList.add('no-scroll');
      tombolTutup.focus();
    }

    function tutup() {
      if (!root.classList.contains('is-open')) return;
      root.classList.remove('is-open');
      document.body.classList.remove('no-scroll');
      if (pemicu) pemicu.focus();
    }

    function ganti(arah) {
      if (sibuk || daftar.length < 2) return;
      sibuk = true;
      indeks = (indeks + arah + daftar.length) % daftar.length;
      stage.style.setProperty('--arah', String(arah));

      // Keluar ke satu sisi → ganti isi → masuk dari sisi berlawanan.
      stage.classList.add('is-out');
      tunggu(220).then(function () {
        isi(daftar[indeks]);
        stage.classList.remove('is-out');
        stage.classList.add('is-in');
        void stage.offsetWidth;
        stage.classList.remove('is-in');
        sibuk = false;
      });
    }

    document.addEventListener('click', function (e) {
      var item = e.target.closest('[data-lightbox]');
      if (!item) return;
      // Biarkan Ctrl/Cmd + klik membuka gambar di tab baru.
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
      e.preventDefault();
      buka(item);
    });

    root.addEventListener('click', function (e) {
      // Klik di luar kotak (backdrop / area kosong) atau tombol ✕ menutup lightbox.
      if (e.target === root || e.target.closest('[data-lb-tutup]')) tutup();
    });
    tombolPrev.addEventListener('click', function () { ganti(-1); });
    tombolNext.addEventListener('click', function () { ganti(1); });

    document.addEventListener('keydown', function (e) {
      if (!root.classList.contains('is-open')) return;

      if (e.key === 'Escape') {
        e.preventDefault();
        tutup();
      } else if (e.key === 'ArrowLeft') {
        ganti(-1);
      } else if (e.key === 'ArrowRight') {
        ganti(1);
      } else if (e.key === 'Tab') {
        // Jaga fokus tetap di dalam lightbox.
        var fokus = [tombolTutup, tombolPrev, tombolNext].filter(function (b) { return !b.hidden; });
        var posisi = fokus.indexOf(document.activeElement);
        e.preventDefault();
        var berikut = e.shiftKey ? posisi - 1 : posisi + 1;
        fokus[(berikut + fokus.length) % fokus.length].focus();
      }
    });
  }

  /* -------------------------------------------------------------------
     5. VALIDASI FORMULIR PEMESANAN
     Aturan SAMA dengan validasiPesanan() di includes/functions.php.
     ------------------------------------------------------------------- */
  function tanggalHariIni() {
    var d = new Date();
    var bulan = String(d.getMonth() + 1).padStart(2, '0');
    var hari = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + bulan + '-' + hari;
  }

  var aturan = {
    nama: function (v) {
      if (v === '') return 'Nama lengkap wajib diisi.';
      if (v.length > 100) return 'Nama lengkap maksimal 100 karakter.';
      return '';
    },
    whatsapp: function (v) {
      if (v === '') return 'Nomor WhatsApp wajib diisi.';
      if (!/^[0-9]+$/.test(v)) return 'Nomor WhatsApp hanya boleh berisi angka (tanpa huruf, spasi, atau tanda +).';
      if (v.length < 10 || v.length > 15) return 'Nomor WhatsApp harus terdiri dari 10–15 digit.';
      return '';
    },
    kebutuhan: function (v) {
      if (v === '') return 'Deskripsi kebutuhan wajib diisi.';
      if (v.length > 1000) return 'Deskripsi kebutuhan maksimal 1000 karakter.';
      return '';
    },
    tenggat: function (v) {
      if (v === '') return '';
      if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return 'Format tanggal tidak valid.';
      if (v < tanggalHariIni()) return 'Tenggat waktu tidak boleh tanggal yang sudah lewat.';
      return '';
    }
  };

  function initFormPesan() {
    var form = document.querySelector('[data-order-form]');
    if (!form) return;

    var kotakPeringatan = form.querySelector('[data-form-alert]');
    var tombolKirim = form.querySelector('[data-submit]');
    var labelTombol = tombolKirim ? tombolKirim.innerHTML : '';

    function tampilkanError(input, pesan, getar) {
      var field = input.closest('.field');
      var teksError = form.querySelector('[data-error-for="' + input.name + '"]');

      if (teksError) teksError.textContent = pesan;

      if (pesan) {
        input.setAttribute('aria-invalid', 'true');
        if (getar) {
          // Hapus lalu pasang lagi kelasnya agar animasi shake diputar ulang (sekali).
          field.classList.remove('is-invalid');
          void field.offsetWidth;
        }
        field.classList.add('is-invalid');
      } else {
        input.removeAttribute('aria-invalid');
        field.classList.remove('is-invalid');
      }
    }

    function validasiKolom(nama, getar) {
      var input = form.elements[nama];
      if (!input) return '';
      var pesan = aturan[nama](input.value.trim());
      tampilkanError(input, pesan, getar);
      return pesan;
    }

    form.addEventListener('submit', function (e) {
      var kolomSalah = [];

      Object.keys(aturan).forEach(function (nama) {
        if (validasiKolom(nama, true)) kolomSalah.push(form.elements[nama]);
      });

      if (kolomSalah.length) {
        // Gagal: tampilkan peringatan, WhatsApp TIDAK dibuka.
        e.preventDefault();
        kotakPeringatan.hidden = false;
        kotakPeringatan.scrollIntoView({ behavior: kurangiGerak() ? 'auto' : 'smooth', block: 'center' });
        kolomSalah[0].focus({ preventScroll: true });
        return;
      }

      kotakPeringatan.hidden = true;
      if (tombolKirim) {
        tombolKirim.disabled = true;
        tombolKirim.textContent = 'Membuka WhatsApp…';
      }
    });

    // Setelah kolom ditandai salah, perbarui pesannya sambil pengguna mengetik.
    Object.keys(aturan).forEach(function (nama) {
      var input = form.elements[nama];
      if (!input) return;
      input.addEventListener('input', function () {
        if (input.closest('.field').classList.contains('is-invalid')) {
          validasiKolom(nama, false);
          if (!form.querySelector('.field.is-invalid')) kotakPeringatan.hidden = true;
        }
      });
    });

    // Jika pengguna kembali dari WhatsApp lewat tombol Back, aktifkan lagi tombolnya.
    window.addEventListener('pageshow', function () {
      if (tombolKirim) {
        tombolKirim.disabled = false;
        tombolKirim.innerHTML = labelTombol;
      }
    });

    // Fokus ke kolom salah pertama jika error berasal dari validasi server.
    var salahDariServer = form.querySelector('.field.is-invalid input, .field.is-invalid textarea');
    if (salahDariServer) salahDariServer.focus({ preventScroll: true });
  }

  /* ------------------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initReveal();
    initSemuaFilter();
    initLightbox();
    initFormPesan();
  });
})();
