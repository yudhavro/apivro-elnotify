<?php
// Hardening akses: hanya admin dengan kapabilitas manage_options yang boleh mengakses
if ( ! current_user_can( 'manage_options' ) ) {
    exit;
}

echo '<div class="wrap">';
echo '<h1 class="wp-heading-inline">Pengaturan APIvro ElNotify</h1>';
echo '<hr class="wp-header-end">';

$current = get_option( 'apivro_elnotify_api_key', '' );
$masked = '';
if ( ! empty( $current ) ) {
    $len = strlen( $current );
    $masked = substr( $current, 0, min(4,$len) ) . str_repeat('*', max(0,$len-8)) . substr( $current, max(0,$len-4) );
}

echo '<form method="post">';
wp_nonce_field( 'apivro_elnotify_update_api_key', 'apivro_elnotify_nonce' );
echo '<table class="form-table">';
echo '<tr><th scope="row"><label for="apivro_elnotify_api_key">API Key</label></th>';
echo '<td>';
// Input disembunyikan seperti plugin APIvro WP Notify (type=password) dan tampilkan nilai yang disamarkan di bawahnya
echo '<input type="password" id="apivro_elnotify_api_key" name="apivro_elnotify_api_key" class="regular-text code" value="' . esc_attr( $current ) . '" placeholder="Masukkan API key" autocomplete="off" />';
if ( $masked ) {
    echo '<p class="description" style="color:#666;">Saat ini: <code>' . esc_html( $masked ) . '</code></p>';
}
echo '</td></tr>';
echo '</table>';
submit_button( __( 'Simpan Pengaturan', 'apivro-elnotify' ) );
echo '</form>';

// Hook untuk backward compatibility jika ada renderer lama
do_action('apivro_elnotify_settings_page');

echo '</div>';