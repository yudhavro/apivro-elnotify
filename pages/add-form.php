<?php
// Hardening akses: hanya admin dengan kapabilitas manage_options yang boleh mengakses
if ( ! current_user_can( 'manage_options' ) ) {
    exit;
}

// Halaman ini khusus untuk membuat/mengedit template. Daftar dipindahkan ke tab terpisah.
// Data untuk edit akan diisi bila parameter id + action=edit tersedia.

$curren_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

if ( isset( $_GET['id'] ) ) {
    $idpost = (int) $_GET['id'];
    $getPost = get_post( $idpost );
    if ( ! $getPost ) {
        $balik = apivro_elnotify_hapus_parameter( $curren_url, array( 'id', 'action', 'menu' ) );
        apivro_elnotify_redirect( $balik ); exit;
    }

    $idpost         = $getPost->ID;
    $nama_form      = get_post_meta( $idpost, '_apivro_elnotify_form', true );
    $key_whatsapp   = get_post_meta( $idpost, '_apivro_elnotify_whatsapp', true );
    $format_pesan   = get_post_meta( $idpost, '_apivro_elnotify_pesan', true );
    $nomor_admin    = get_post_meta( $idpost, '_apivro_elnotify_nomor_admin', true );
    $pesan_admin    = get_post_meta( $idpost, '_apivro_elnotify_pesan_admin', true );
}

?>
<div class="wrap">
  <h1 class="wp-heading-inline">
    <?php echo isset($_GET['action']) && $_GET['action']==='edit' ? esc_html__('Sunting Formulir','apivro-elnotify') : esc_html__('Tambah Formulir','apivro-elnotify'); ?>
  </h1>
  <hr class="wp-header-end" />

<form enctype="multipart/form-data" method="POST">
    <?php wp_nonce_field( 'apivro_elnotify_save_form', 'apivro_elnotify_nonce' ); ?>
    <input type="hidden" name="apivro_elnotify_add_form" />
    <table class="form-table">
        <tr>
            <th scope="row"><label for="apivro_elnotify_form">Judul Formulir</label></th>
            <td><input type="text" id="apivro_elnotify_form" name="apivro_elnotify_form" value="<?= esc_attr( $nama_form ?? '' ); ?>" placeholder="FORMULIR KONSULTASI" class="regular-text" required /></td>
        </tr>
        <tr>
            <th scope="row"><label for="apivro_elnotify_whatsapp">ID Kolom WhatsApp</label></th>
            <td><input type="text" id="apivro_elnotify_whatsapp" name="apivro_elnotify_whatsapp" value="<?= esc_attr( $key_whatsapp ?? '' ); ?>" placeholder="[nomor_whatsapp]" class="regular-text" required /></td>
        </tr>
        <tr>
            <th scope="row"><label for="apivro_elnotify_pesan">Pesan ke Pengguna</label></th>
            <td><textarea id="apivro_elnotify_pesan" name="apivro_elnotify_pesan" cols="30" rows="6" class="large-text" placeholder="👋 Halo [nama_lengkap]&#10;Permintaan konsultasi sudah kami terima.&#10;Tim kami segera menghubungi Anda secepatnya."><?= esc_textarea( $format_pesan ?? '' ); ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="apivro_elnotify_nomor_admin">WhatsApp Admin / ID Grup</label></th>
            <td><input type="text" id="apivro_elnotify_nomor_admin" name="apivro_elnotify_nomor_admin" value="<?= esc_attr( $nomor_admin ?? '' ); ?>" placeholder="6281212345678 atau 123456789999524319@g.us" class="regular-text" required /></td>
        </tr>
        <tr>
            <th scope="row"><label for="apivro_elnotify_pesan_admin">Pesan ke Admin</label></th>
            <td><textarea id="apivro_elnotify_pesan_admin" name="apivro_elnotify_pesan_admin" cols="30" rows="6" class="large-text" placeholder="🚨 *Permintaan Konsultasi*&#10;&#10;Halo tim Konsultan Hebat...&#10;Ada permintaan konsultasi dari website."><?= esc_textarea( $pesan_admin ?? '' ); ?></textarea></td>
        </tr>
    </table>
    <?php submit_button( 'Simpan' ); ?>
</form>

<div class="jarak"></div>
</div>
