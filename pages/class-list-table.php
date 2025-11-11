<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class APIvro_ElNotify_List_Table extends WP_List_Table {
    private $post_type = 'apivro_elnotify_fmt';

    public function __construct() {
        parent::__construct([
            'singular' => 'formulir',
            'plural'   => 'formulir',
            'ajax'     => false,
        ]);
    }

    public function get_columns() {
        // No checkbox, no ID – compact columns aligned to request
        return [
            'title'         => __( 'Judul Formulir', 'apivro-elnotify' ),
            'wa_key'        => __( 'ID Kolom WhatsApp', 'apivro-elnotify' ),
            'pesan_user'    => __( 'Pesan ke Pengguna', 'apivro-elnotify' ),
            'pesan_admin'   => __( 'Pesan ke Admin', 'apivro-elnotify' ),
            'nomor_admin'   => __( 'WhatsApp Admin', 'apivro-elnotify' ),
        ];
    }

    protected function get_sortable_columns() {
        return [
            'title' => ['title', true],
        ];
    }

    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $orderby = isset($_REQUEST['orderby']) ? sanitize_key( $_REQUEST['orderby'] ) : 'date';
        $order = isset($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), ['ASC', 'DESC']) ? strtoupper($_REQUEST['order']) : 'DESC';
        // Pencarian yang ringan dan handal: hanya berdasarkan judul (post_title)
        $args = [
            'post_type'      => $this->post_type,
            'posts_per_page' => $per_page,
            'paged'          => $current_page,
            'orderby'        => $orderby,
            'order'          => $order,
            's'              => $search,
            // Batasi pencarian hanya pada kolom judul agar spesifik dan akurat
            'search_columns' => [ 'post_title' ],
        ];

        $query = new WP_Query( $args );
        $items = [];
        foreach ( $query->posts as $p ) {
            $items[] = $this->map_post_to_item( $p );
        }

        $this->items = $items;
        // Ensure column headers are initialized to avoid blank tables on some setups
        $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns(), 'title' ];
        $this->set_pagination_args([
            'total_items' => (int) $query->found_posts,
            'per_page'    => $per_page,
            'total_pages' => (int) $query->max_num_pages,
        ]);
    }

    private function get_meta( $post_id, $keys = [] ) {
        foreach ( $keys as $k ) {
            $val = get_post_meta( $post_id, $k, true );
            if ( ! empty( $val ) ) return $val;
        }
        return '';
    }

    private function map_post_to_item( WP_Post $p ) {
        return [
            'ID'           => $p->ID,
            'title'        => $p->post_title,
            'wa_key'       => $this->get_meta( $p->ID, ['_apivro_elnotify_whatsapp','wa_key','whatsapp_key','key_whatsapp'] ),
            'pesan_user'   => $this->get_meta( $p->ID, ['_apivro_elnotify_pesan','pesan_user','message_user'] ),
            'pesan_admin'  => $this->get_meta( $p->ID, ['_apivro_elnotify_pesan_admin','pesan_admin','message_admin'] ),
            'nomor_admin'  => $this->get_meta( $p->ID, ['_apivro_elnotify_nomor_admin','nomor_admin','admin_phone','admin_number'] ),
        ];
    }

    public function column_title( $item ) {
        $edit_link = admin_url( 'admin.php?page=apivro-elnotif-add&action=edit&id=' . absint( $item['ID'] ) );
        $del_link  = wp_nonce_url( admin_url( 'admin.php?page=apivro-elnotif&action=hapus&id=' . absint( $item['ID'] ) ), 'apivro_elnotify_del_nonce' );
        $actions = [
            'edit'  => '<a href="' . esc_url( $edit_link ) . '">' . __( 'Sunting', 'apivro-elnotify' ) . '</a>',
            'trash' => '<a class="submitdelete" href="' . esc_url( $del_link ) . '" onclick="return confirm(\'' . esc_js( __( 'Buang formulir ini?', 'apivro-elnotify' ) ) . '\');">' . __( 'Buang', 'apivro-elnotify' ) . '</a>',
        ];
        return '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $item['title'] ) . '</a></strong>' . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'wa_key':
            case 'nomor_admin':
                return '<span class="apivro-inline-text" title="' . esc_attr( $item[$column_name] ) . '">' . esc_html( $item[$column_name] ) . '</span>';
            case 'pesan_user':
            case 'pesan_admin':
                // Tampilkan 2 baris (line clamp) agar tidak bertumpuk
                return '<span class="apivro-multiline" title="' . esc_attr( $item[$column_name] ) . '">' . esc_html( $item[$column_name] ) . '</span>';
            default:
                return '';
        }
    }

    public function no_items() {
        echo esc_html__( 'Tidak ada formulir.', 'apivro-elnotify' );
    }
}