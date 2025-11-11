<?php
/*
Plugin Name: APIvro ElNotify
Plugin URI: https://yudhavro.com
Description: Kirim notifikasi instan di WhatsApp setiap kali ada yang mengisi formulir Elementor! Kustomisasi isi pesan notifikasi tanpa batas!
Version: 1.0.0
Author: Yudhavro
Author URI: https://yudhavro.com
Text Domain: apivro-elnotify
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'APIVRO_ELNOTIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'APIVRO_ELNOTIFY_URL', plugin_dir_url( __FILE__ ) );

// Load main functions
require_once APIVRO_ELNOTIFY_PATH . 'function.php';

// Admin menu
add_action( 'admin_menu', 'apivro_elnotify_menu' );

// Load textdomain if provided later
if ( ! function_exists( 'apivro_elnotify_load_textdomain' ) ) {
    function apivro_elnotify_load_textdomain() {
        $plugin_dir = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'apivro-elnotify', false, $plugin_dir . '/languages' );
    }
    add_action( 'plugins_loaded', 'apivro_elnotify_load_textdomain', 1 );
}

?>