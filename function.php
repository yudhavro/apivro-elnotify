<?php
date_default_timezone_set('Asia/Jakarta');

// Define CPT slug (must be <= 20 chars per WP schema)
if ( ! defined( 'APIVRO_ELNOTIFY_CPT' ) ) {
    define( 'APIVRO_ELNOTIFY_CPT', 'apivro_elnotify_fmt' );
}

$curren_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

function apivro_elnotify_menu(){
    // Parent menu opens the list page (Semua Formulir)
    add_menu_page(
        'APIvro ElNotify',
        'APIvro ElNotify',
        'manage_options',
        'apivro-elnotif',
        'apivro_elnotify_render_list',
        // Ganti icon sidebar menjadi icon broadcast/megaphone
        'dashicons-megaphone'
    );

    // Submenus: List (same slug as parent to show under parent), Add, Settings
    add_submenu_page(
        'apivro-elnotif',
        'Semua Formulir',
        'Semua Formulir',
        'manage_options',
        'apivro-elnotif',
        'apivro_elnotify_render_list'
    );
    add_submenu_page(
        'apivro-elnotif',
        'Tambah Formulir',
        'Tambah Formulir',
        'manage_options',
        'apivro-elnotif-add',
        'apivro_elnotify_render_add_form'
    );
    // Restore Settings submenu for managing API key
    add_submenu_page(
        'apivro-elnotif',
        'Settings',
        'Settings',
        'manage_options',
        'apivro-elnotif-settings',
        'apivro_elnotify_render_settings'
    );
}

// Back-compat: redirect old slug to new slug
// (Removed) Back-compat redirect untuk slug lama dihapus agar tidak ada jejak nama yang usang.

// Register lightweight CPT to store form templates safely
function apivro_elnotify_register_cpt() {
    $labels = array(
        'name'               => 'ElNotify Forms',
        'singular_name'      => 'ElNotify Form',
        'menu_name'          => 'ElNotify Forms',
        'name_admin_bar'     => 'ElNotify Form',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'exclude_from_search'=> true,
        'publicly_queryable' => false,
        'show_ui'            => false,
        'show_in_menu'       => false,
        'show_in_nav_menus'  => false,
        'show_in_admin_bar'  => false,
        'show_in_rest'       => false,
        'supports'           => array( 'title' ),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    );
    register_post_type( APIVRO_ELNOTIFY_CPT, $args );
}
add_action( 'init', 'apivro_elnotify_register_cpt' );


// A send custom WebHook
add_action( 'elementor_pro/forms/new_record', function( $record, $handler ) {
    //make sure its our form
    $form_name = $record->get_form_settings( 'form_name' );
    
    // Find existing form config by meta (preferred). This allows multiple templates across different forms.
    $configs = get_posts( array(
        'post_type'      => APIVRO_ELNOTIFY_CPT,
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'   => '_apivro_elnotify_form',
                'value' => $form_name,
                'compare' => '='
            )
        )
    ) );
    $cekPost = ! empty( $configs ) ? $configs[0] : null;
    // Tidak ada fallback ke slug lama; gunakan CPT APIVRO_ELNOTIFY_CPT secara konsisten.
    if ( ! $cekPost ) {
        return;
    }

    // Settings
    $api_key        = get_option( 'apivro_elnotify_api_key' );
    $postid         = $cekPost->ID;
    // Read only new meta keys (legacy keys telah dihapus melalui migrasi)
    $nomor          = get_post_meta( $postid, '_apivro_elnotify_whatsapp', true );
    // Normalisasi agar kompatibel jika data lama menyimpan dengan []
    $nomor          = apivro_elnotify_normalize_field_id( $nomor );
    $pesan          = get_post_meta( $postid, '_apivro_elnotify_pesan', true );
    $nomor_admin    = get_post_meta( $postid, '_apivro_elnotify_nomor_admin', true );
    $pesan_admin    = get_post_meta( $postid, '_apivro_elnotify_pesan_admin', true );

    $raw_fields = $record->get( 'fields' );
    $fields = [];
    foreach ( $raw_fields as $id => $field ) {
        $fields[ $id ] = $field['value'];

        if ( $id == $nomor ) {
            $nomor = $field['value'];
        }

        $pesan = str_replace("[".$id."]", $field['value'], $pesan);
        $pesan_admin = str_replace("[".$id."]", $field['value'], $pesan_admin);
    }
	
	$nomor = preg_replace("/[^0-9]/", "", $nomor);
    if(substr(trim($nomor), 0, 2)=='08'){
        $nomor = '62'.substr(trim($nomor), 1);
    }
	
    apivro_elnotify_send_message( $api_key, $nomor, $pesan );
    apivro_elnotify_send_message( $api_key, $nomor_admin, $pesan_admin );

    $output['result'] = "Success";
    $handler->add_response_data(true, $output);

}, 10, 2 );

// Legacy migration telah diselesaikan pada refactor; seluruh data menggunakan penamaan APIVRO

// Migrate any posts from previous too-long CPT slug to the new one
add_action( 'admin_init', function(){
    $old_posts = get_posts( array(
        'post_type'      => 'apivro_elnotify_format',
        'posts_per_page' => -1,
        'post_status'    => array( 'publish', 'draft' ),
    ) );
    foreach ( $old_posts as $post ) {
        wp_update_post( array( 'ID' => $post->ID, 'post_type' => APIVRO_ELNOTIFY_CPT ) );
    }
} );


// Old entry remains for backward-compat (redirect handled below), new renderers per submenu
function apivro_elnotify_render_list(){
    apivro_elnotify_setting_menu();
    include( APIVRO_ELNOTIFY_PATH . 'pages/list.php' );
}

function apivro_elnotify_render_add_form(){
    apivro_elnotify_setting_menu();
    include( APIVRO_ELNOTIFY_PATH . 'pages/add-form.php' );
}

// Optional: still expose settings renderer via direct access if needed (no menu)
function apivro_elnotify_render_settings(){
    apivro_elnotify_setting_menu();
    include( APIVRO_ELNOTIFY_PATH . 'pages/settings.php' );
}

function apivro_elnotify_setting_menu(){
    global $curren_url;
    $balik     = apivro_elnotify_hapus_parameter( $curren_url, array( 'id', 'action', 'menu' ) );
    // Halaman daftar utama plugin
    $list_url  = admin_url( 'admin.php?page=apivro-elnotif' );

    // Explicit capability hardening: only admins (manage_options) may process save/delete here
    if ( ! current_user_can( 'manage_options' ) ) {
        return; // Do not process any admin mutations if user lacks capability
    }

    // Save API key in a WP-standards option name (with nonce)
    if ( isset( $_POST['apivro_elnotify_api_key'] ) ) {
        if ( ! isset( $_POST['apivro_elnotify_nonce'] ) || ! wp_verify_nonce( $_POST['apivro_elnotify_nonce'], 'apivro_elnotify_update_api_key' ) ) {
            apivro_elnotify_settings_notice_failed( 'Sesi tidak valid (nonce).' );
        } else {
            $value = sanitize_text_field( $_POST['apivro_elnotify_api_key'] );
            if ( strpos( $value, '*' ) === false ) {
                update_option( 'apivro_elnotify_api_key', $value );
                apivro_elnotify_add_notice( 'Kunci API berhasil disimpan.', 'success' );
            }
        }
    }

    if ( isset( $_POST['apivro_elnotify_add_form'] ) ) {
        // Nonce verification for secure save
        if ( ! isset( $_POST['apivro_elnotify_nonce'] ) || ! wp_verify_nonce( $_POST['apivro_elnotify_nonce'], 'apivro_elnotify_save_form' ) ) {
            return apivro_elnotify_settings_notice_failed( 'Sesi tidak valid (nonce).' );
        }
        $nama_form      = sanitize_text_field( $_POST['apivro_elnotify_form'] );
        $key_whatsapp   = sanitize_text_field( $_POST['apivro_elnotify_whatsapp'] );
        // Normalisasi: boleh input [nomor_whatsapp] atau nomor_whatsapp – simpan tanpa tanda []
        $key_whatsapp   = apivro_elnotify_normalize_field_id( $key_whatsapp );
        $format_pesan   = sanitize_textarea_field( $_POST['apivro_elnotify_pesan'] );
        $nomor_admin    = sanitize_text_field( $_POST['apivro_elnotify_nomor_admin'] );
        $pesan_admin    = sanitize_textarea_field( $_POST['apivro_elnotify_pesan_admin'] );

        $aksi           = ((isset($_GET['action']) and $_GET['action'] == "edit") ? true : false);

        if ( empty( $nama_form ) || empty( $key_whatsapp ) || empty( $format_pesan ) || empty( $nomor_admin ) || empty( $pesan_admin ) ) {
            return apivro_elnotify_settings_notice_failed( 'Isi semua data' );
        }

        if ( $aksi == false ) {

            // Prevent duplicate template for the same form name
            $cekName = get_posts( array(
                'post_type'      => APIVRO_ELNOTIFY_CPT,
                'posts_per_page' => 1,
                'meta_query'     => array(
                    array(
                        'key'   => '_apivro_elnotify_form',
                        'value' => $nama_form,
                        'compare' => '='
                    )
                )
            ) );
            if ( ! empty( $cekName ) ) {
                return apivro_elnotify_settings_notice_failed( 'Nama Form Sudah Terdaftar' );
            }

            $nomor_admin = preg_replace("/[^0-9]/", "", $nomor_admin);
            if(substr(trim($nomor_admin), 0, 2) == '08'){
                $nomor_admin = '62'.substr(trim($nomor_admin), 1);
            }

            // Insert as lightweight post first (without meta) for maximum reliability,
            // then add meta in a separate step.
            $buat_post = array(
                'post_type'     => APIVRO_ELNOTIFY_CPT,
                'post_title'    => wp_strip_all_tags( $nama_form ),
                // Jangan set post_name manual agar WP membuat slug unik otomatis
                'post_author'   => get_current_user_id(),
                'post_status'   => 'publish',
            );
            // Request detailed error messages from wp_insert_post.
            $postid = wp_insert_post( $buat_post, true );

            if ( is_wp_error( $postid ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'APIvro ElNotify: wp_insert_post error => ' . $postid->get_error_message() );
                }
                return apivro_elnotify_settings_notice_failed( 'Gagal proses data: ' . esc_html( $postid->get_error_message() ) );
            }
            if ( empty( $postid ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'APIvro ElNotify: wp_insert_post returned empty ID.' );
                }
                return apivro_elnotify_settings_notice_failed( 'Gagal proses data: ID kosong dari wp_insert_post' );
            }

            // Add meta only after successful insert
            update_post_meta( $postid, '_apivro_elnotify_form', $nama_form );
            update_post_meta( $postid, '_apivro_elnotify_whatsapp', $key_whatsapp );
            update_post_meta( $postid, '_apivro_elnotify_pesan', $format_pesan );
            update_post_meta( $postid, '_apivro_elnotify_nomor_admin', $nomor_admin );
            update_post_meta( $postid, '_apivro_elnotify_pesan_admin', $pesan_admin );

            apivro_elnotify_add_notice( 'Formulir baru berhasil disimpan.', 'success' );
            return apivro_elnotify_redirect( $list_url );

        } elseif ( $aksi == true ) {

            $idpost = (int) $_GET['id'];
            $getPost = get_post($idpost);
            
            if ( !$getPost ) {
                return apivro_elnotify_redirect( $balik ); exit;
            }

            // Update post title and meta
            wp_update_post( array( 'ID' => $idpost, 'post_title' => $nama_form ) );
            update_post_meta( $idpost, '_apivro_elnotify_form', $nama_form );
            update_post_meta( $idpost, '_apivro_elnotify_whatsapp', $key_whatsapp );
            update_post_meta( $idpost, '_apivro_elnotify_pesan', $format_pesan );
            update_post_meta( $idpost, '_apivro_elnotify_nomor_admin', $nomor_admin );
            update_post_meta( $idpost, '_apivro_elnotify_pesan_admin', $pesan_admin );

            // Catat pesan sukses lalu redirect ke halaman daftar
            apivro_elnotify_add_notice( 'Formulir berhasil diperbarui.', 'success' );
            return apivro_elnotify_redirect( $list_url );
            
        }

    } elseif ( isset($_GET['id']) and isset($_GET['action']) ) {

        $aksi           = ((isset($_GET['action'])) ? $_GET['action'] : "");
        $menu           = ((isset($_GET['menu'])) ? $_GET['menu'] : "");

        $idpost = (int) $_GET['id'];
        $getPost = get_post($idpost);
        
        if ( !$getPost ) {
            return apivro_elnotify_redirect( $balik ); exit;
        }

        if ( $aksi == 'hapus' and $menu == 'update-form' ) {
            // Verify delete nonce for safety
            if ( ! isset( $_GET['del_nonce'] ) || ! wp_verify_nonce( $_GET['del_nonce'], 'apivro_elnotify_delete_' . $idpost ) ) {
                return apivro_elnotify_settings_notice_failed( 'Sesi tidak valid (nonce hapus).' );
            }
            // Double-check capability before destructive action
            if ( ! current_user_can( 'manage_options' ) ) {
                return apivro_elnotify_settings_notice_failed( 'Anda tidak memiliki izin untuk menghapus.' );
            }
            wp_delete_post($idpost);
            delete_post_meta( $idpost, '_apivro_elnotify_form', true );
            delete_post_meta( $idpost, '_apivro_elnotify_whatsapp', true );
            delete_post_meta( $idpost, '_apivro_elnotify_pesan', true );
            delete_post_meta( $idpost, '_apivro_elnotify_nomor_admin', true );
            delete_post_meta( $idpost, '_apivro_elnotify_pesan_admin', true );
            // Catat pesan sukses lalu redirect ke halaman daftar
            apivro_elnotify_add_notice( 'Formulir berhasil dihapus.', 'success' );
            return apivro_elnotify_redirect( $list_url );
        }

    }
}

// Tidak ada enqueue CSS/JS khusus admin yang digunakan saat ini.
// Bersihkan hook admin_enqueue_scripts yang tidak terpakai untuk menjaga kebersihan kode.

// Normalize Elementor field id (mendukung format dengan atau tanpa tanda kurung).
// Contoh: "[nomor_whatsapp]" -> "nomor_whatsapp"
function apivro_elnotify_normalize_field_id( $id ) {
    if ( ! is_string( $id ) ) {
        return '';
    }
    $id = trim( $id );
    if ( $id === '' ) {
        return '';
    }
    if ( $id[0] === '[' && substr( $id, -1 ) === ']' ) {
        $id = substr( $id, 1, -1 );
    }
    return $id;
}

function apivro_elnotify_send_message( $api_key, $to, $message ){
    if ( empty( $api_key ) || empty( $to ) || empty( $message ) ) {
        return false;
    }

    $url = 'https://api.yudhavro.com/api/v1/messages/send';
    $to = sanitize_text_field( $to );

    $body = array(
        'to'      => $to,
        'message' => $message,
    );

    $args = array(
        'timeout' => 30,
        'headers' => array(
            'X-API-Key'   => sanitize_text_field( $api_key ),
            'Content-Type'=> 'application/json',
        ),
        'body' => wp_json_encode( $body ),
        'data_format' => 'body',
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'APIvro ElNotify Error: ' . $response->get_error_message() );
        }
        return false;
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $response_data = json_decode( $response_body, true );

    if ( $http_code === 200 && isset( $response_data['success'] ) && $response_data['success'] ) {
        return true;
    } else {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'APIvro ElNotify: Failed to send to ' . esc_html( $to ) . '. Response: ' . esc_html( $response_body ) );
        }
        return false;
    }
}

// Flash-notice: simpan pesan sementara untuk ditampilkan sekali di admin
function apivro_elnotify_add_notice( $message, $type = 'success' ) {
    $uid = get_current_user_id();
    if ( ! $uid ) { $uid = 0; }
    $key = 'apivro_elnotify_notice_' . $uid;
    $notices = get_transient( $key );
    if ( ! is_array( $notices ) ) { $notices = array(); }
    $notices[] = array(
        'message' => (string) $message,
        'type'    => in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ? $type : 'success',
    );
    // Simpan maksimal 1 menit; cukup untuk siklus redirect
    set_transient( $key, $notices, MINUTE_IN_SECONDS );
}

// Back-compat: panggilan lama akan diarahkan ke flash notice sukses berbahasa Indonesia
function apivro_elnotify_settings_notice( $req = null ) {
    $prefix = $req === null ? 'Tersimpan' : $req;
    $suffix = 'berhasil.';
    apivro_elnotify_add_notice( $prefix . ' ' . $suffix, 'success' );
}

function apivro_elnotify_settings_notice_failed( $error = null ) {
    $message = $error === null ? esc_html__( 'Failed', 'apivro-elnotify' ) : esc_html( $error );
    echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
}

function apivro_elnotify_hapus_parameter($url, $param){
    $parse1 = parse_url($url);
    parse_str($parse1['query'], $parse);
    foreach ($parse as $key => $value) {
        if (in_array($key, $param)) {
            unset($parse[$key]);
        }
    }
    $string = http_build_query($parse);

    return $parse1['scheme']."://".$parse1['host'].$parse1['path']."?".$string; 
}

// Tampilkan semua flash notice yang tersimpan (sekali tampil), dipanggil otomatis di admin
add_action( 'admin_notices', function(){
    $uid = get_current_user_id();
    if ( ! $uid ) { $uid = 0; }
    $key = 'apivro_elnotify_notice_' . $uid;
    $notices = get_transient( $key );
    if ( is_array( $notices ) && ! empty( $notices ) ) {
        foreach ( $notices as $n ) {
            $type = isset( $n['type'] ) ? $n['type'] : 'success';
            $msg  = isset( $n['message'] ) ? $n['message'] : '';
            echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
        }
        delete_transient( $key );
    }
} );

function apivro_elnotify_redirect( $url ) {
    // Validasi tujuan redirect untuk mencegah open redirect
    $fallback = admin_url( 'admin.php?page=apivro-elnotif' );
    $safe_url = wp_validate_redirect( $url, $fallback );
    // Jika header belum dikirim, gunakan wp_redirect agar kompatibel dengan older WP dan tambahkan exit segera.
    if ( ! headers_sent() ) {
        wp_redirect( $safe_url );
        exit;
    }
    // Jika header sudah dikirim (mis. admin sudah menghasilkan output), gunakan redirect sisi-klien.
    echo '<script>window.location.replace(' . wp_json_encode( $safe_url ) . ');</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url( $safe_url ) . '"></noscript>';
    exit;
}
