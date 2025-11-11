# Plugin Notifikasi WhatsApp di Elementor Pro — APIvro ElNotify

APIvro ElNotify adalah plugin WordPress ringan yang menghubungkan formulir Elementor Pro dengan WhatsApp. Setiap kali ada pengisian formulir, plugin ini mengirimkan notifikasi otomatis ke pengguna dan/atau admin melalui WhatsApp, dengan isi pesan yang bisa Anda kustom sesuai kebutuhan.

## Kenapa plugin ini?
- Cocok untuk bisnis yang mengandalkan WhatsApp sebagai kanal komunikasi utama.
- Mempercepat follow-up tanpa harus membuka email.
- Pesan notifikasi bisa Anda atur menggunakan placeholder field dari Elementor Pro.

## Fitur Utama
- Kirim notifikasi WhatsApp ke pengguna dan admin setelah form terkirim.
- Format pesan kustom, mendukung placeholder field Elementor, misalnya `[nama_lengkap]`, `[no_wa]`, `[jenis_promo]`.
- Daftar formulir dengan tampilan tabel dan pencarian.
- Full bahasa Indonesia.

## Cara Pakai Mudah
1. Install dan aktifkan plugin.
2. Buka menu “APIvro ElNotify” di admin WordPress.
3. Masukkan Kunci API.
4. Tambah Formulir: isi Judul, ID Kolom WhatsApp, format pesan untuk pengguna/admin, dan nomor admin.
5. Di Elementor Pro, pastikan field ID yang menyimpan nomor WhatsApp sesuai dengan yang Anda set (contoh: `no_wa` atau `[nomor_whatsapp]`).

## Persyaratan
- WordPress terbaru (disarankan).
- Elementor Pro (untuk sumber data form).
- Akun APIvro aktif untuk mengirim pesan WhatsApp.

## Ambil Kunci API (CTA)
Dapatkan API Key sekarang di: https://api.yudhavro.com

Dengan API Key aktif, plugin siap mengirim notifikasi WhatsApp secara instan setiap kali ada pengisian formulir.

## Catatan Keamanan
- Semua aksi simpan/hapus dilindungi dengan nonce.
- Redirect menggunakan helper aman dan fallback jika header telah terkirim.

## Changelog
Lihat detail perubahan pada file `CHANGELOG.md`.

Plugin notifikasi WhatsApp di Elementor Pro. Fokus kami: kecepatan, keandalan, dan kemudahan pengaturan.