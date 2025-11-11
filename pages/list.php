<?php
// Hardening akses: hanya admin dengan kapabilitas manage_options yang boleh mengakses
if ( ! current_user_can( 'manage_options' ) ) {
    exit;
}

if ( ! class_exists( 'APIvro_ElNotify_List_Table' ) ) {
    require_once APIVRO_ELNOTIFY_PATH . 'pages/class-list-table.php';
}

// Handle delete action with nonce, then redirect back to list
if ( isset($_GET['action']) && $_GET['action'] === 'hapus' && ! empty($_GET['id']) ) {
    $id = absint( $_GET['id'] );
    if ( ! isset($_GET['_wpnonce']) || ! wp_verify_nonce( $_GET['_wpnonce'], 'apivro_elnotify_del_nonce' ) ) {
        wp_die( __('Nonce tidak valid.', 'apivro-elnotify') );
    }
    wp_delete_post( $id, true );
    // Tambah flash notice sukses sebelum redirect
    apivro_elnotify_add_notice( 'Formulir berhasil dihapus.', 'success' );
    // Gunakan helper redirect yang aman dan memiliki fallback jika header sudah terkirim
    apivro_elnotify_redirect( admin_url('admin.php?page=apivro-elnotif') );
    exit;
}

$table = new APIvro_ElNotify_List_Table();
$table->prepare_items();

// Page header with "Tambah Formulir" button next to title and API key inline form on the right
echo '<div class="wrap">';
echo '<h1 class="wp-heading-inline">APIvro ElNotify</h1>';
echo ' <a href="' . esc_url( admin_url('admin.php?page=apivro-elnotif-add') ) . '" class="page-title-action">' . esc_html__('Tambah Formulir','apivro-elnotify') . '</a>';
echo '<hr class="wp-header-end">';

// Tidak membuat tablenav kustom; gunakan bawaan WP_List_Table agar search inline dengan jumlah item

// Inline CSS untuk ellipsis dan memastikan search inline dengan displaying-num
echo '<style>
/* Stabilkan layout tabel agar kolom tidak saling menimpa */
.wp-list-table{table-layout:fixed;width:100%}

/* Text 1-baris dengan ellipsis untuk kolom sempit */
.apivro-inline-text{display:inline-block; max-width:380px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}
@media (max-width: 1280px){.apivro-inline-text{max-width:260px}}
@media (max-width: 960px){.apivro-inline-text{max-width:180px}}

/* Text 2-baris terpotong rapi (multiline clamp) untuk kolom pesan */
.apivro-multiline{display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; white-space:normal; line-height:1.4;}

.search-box{float:right;margin-top:0;}
/* Persempit kolom WhatsApp Key dan Nomor Admin agar tidak melebar saat konten panjang */
.wp-list-table .column-wa_key{width:18ch;max-width:20ch;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wp-list-table .column-nomor_admin{width:22ch;max-width:24ch;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* Lebar kolom pesan – cukup untuk 2 baris, tidak bertumpuk */
.wp-list-table .column-pesan_user{width:48ch;max-width:50ch}
.wp-list-table .column-pesan_admin{width:48ch;max-width:50ch}

/* Pastikan style juga berlaku pada header (th) karena WP_List_Table memberi class yang sama */
.wp-list-table thead .column-wa_key,.wp-list-table thead .column-nomor_admin,.wp-list-table thead .column-pesan_user,.wp-list-table thead .column-pesan_admin{white-space:nowrap}
</style>';

// Display table
// Tempatkan search_box di dalam form yang sama dengan table->display()
echo '<form method="get">';
echo '<input type="hidden" name="page" value="apivro-elnotif" />';
$table->search_box( __( 'Cari Formulir', 'apivro-elnotify' ), 'apivro-elnotify-search' );
$table->display();
echo '</form>';
echo '</div>';
