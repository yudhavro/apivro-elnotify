# Changelog

Semua perubahan penting pada plugin dicatat di file ini.

## 1.0.0 — Rilis awal yang stabil

### Fitur
- Plugin notifikasi WhatsApp untuk Elementor Pro: kirim pesan ke pengguna dan admin saat formulir dikirim.
- Format pesan kustom untuk pengguna dan admin.

### Peningkatan
- Helper redirect yang aman (apivro_elnotify_redirect) dengan fallback JS/meta refresh untuk menghindari peringatan "headers already sent".
- Sistem notifikasi (admin notice) berbahasa Indonesia untuk aksi: simpan kunci API, tambah formulir, edit formulir, hapus formulir.
- Validasi nonce pada operasi simpan dan hapus.

### Perbaikan
- Mengatasi peringatan "Cannot modify header information" dari output yang sudah terkirim sebelumnya dengan pola redirect yang robust.
- Penanganan error yang lebih jelas untuk sesi tidak valid, data tidak lengkap, dan duplikasi nama formulir.

### Catatan
- Untuk menggunakan plugin ini Anda memerlukan API Key dari layanan APIvro. Ambil API Key di https://api.yudhavro.com.