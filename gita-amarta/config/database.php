<?php
/**
 * Konfigurasi koneksi database (PDO).
 *
 * Kredensial di bawah adalah bawaan XAMPP: user "root" tanpa password.
 * Ubah nilainya jika pengaturan MySQL/MariaDB di komputermu berbeda.
 */

const DB_HOST = '127.0.0.1'; // sama dengan "localhost", tetapi memaksa koneksi TCP
const DB_PORT = '3306';
const DB_NAME = 'gita_amarta';
const DB_USER = 'root';
const DB_PASS = '';

// Zona waktu dipakai untuk validasi tanggal (tenggat tidak boleh lewat).
date_default_timezone_set('Asia/Jakarta');

/**
 * Mengembalikan satu objek PDO yang dipakai bersama oleh seluruh query.
 * Koneksi hanya dibuat sekali per request (disimpan di variabel static).
 */
function getKoneksi(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error dilempar sebagai exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil berupa array asosiatif
                PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statement asli dari MySQL
            ]);
        } catch (PDOException $e) {
            // Jangan tampilkan detail error (bisa berisi info sensitif) ke pengunjung.
            error_log('Koneksi database gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('<!doctype html><html lang="id"><meta charset="utf-8"><title>Database tidak terhubung</title>'
                . '<body style="font-family:sans-serif;padding:2rem;line-height:1.6">'
                . '<h1>Database tidak terhubung</h1>'
                . '<p>Pastikan MySQL di XAMPP sudah berjalan dan database <b>gita_amarta</b> sudah di-import '
                . 'dari file <code>database/gita_amarta.sql</code>.</p></body></html>');
        }
    }

    return $pdo;
}
