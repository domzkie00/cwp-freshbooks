<?php
class Clients_WP_FreshBooks_Pages {

    public function __construct() {
        add_action('admin_init', array( $this, 'settings_options_init' ));
        add_action('admin_menu', array( $this, 'admin_menus'), 12 );
    }

    public function settings_options_init() {
        register_setting( 'cwpfreshbooks_settings_options', 'cwpfreshbooks_settings_options', '' );
    }

    public function admin_menus() {
        add_submenu_page ( 'edit.php?post_type=bt_client' , 'FreshBooks' , 'FreshBooks' , 'manage_options' , 'cwp-freshbooks' , array( $this , 'cwp_freshbooks' ));
    }

    public function cwp_freshbooks() {
        include_once(CWPFB_PATH_INCLUDES.'/cwp_freshbooks.php');
    }
}

new Clients_WP_FreshBooks_Pages();