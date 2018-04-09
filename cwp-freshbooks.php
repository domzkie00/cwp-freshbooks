<?php
/**
 * Plugin Name: Clients WP - FreshBooks
 * Plugin URI:  https://www.gravity2pdf.com
 * Description: Connect Freshbooks with Clients WP
 * Version:     1.0
 * Author:      gravity2pdf
 * Author URI:  https://github.com/raphcadiz
 * Text Domain: cl-wp-freshbooks
 */

if (!class_exists('Clients_WP_FreshBooks')):

    define( 'CWPFB_PATH', dirname( __FILE__ ) );
    define( 'CWPFB_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
    define( 'CWPFB_PATH_CLASS', dirname( __FILE__ ) . '/class' );
    define( 'CWPFB_FOLDER', basename( CWPFB_PATH ) );
    define( 'CWPFB_URL', plugins_url() . '/' . CWPFB_FOLDER );
    define( 'CWPFB_URL_INCLUDES', CWPFB_URL . '/includes' );
    define( 'CWPFB_URL_CLASS', CWPFB_URL . '/class' );
    define( 'CWPFB_VERSION', 1.0 );

    register_activation_hook( __FILE__, 'clients_wp_freshbooks_activation' );
    function clients_wp_freshbooks_activation(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the Restrict Content Pro and Clients WP to be installed and active.');
        }

    }

    add_action( 'admin_init', 'clients_wp_freshbooks_activate' );
    function clients_wp_freshbooks_activate(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    /*
     * include necessary files
     */
    require_once(CWPFB_PATH.'/vendor/autoload.php');
    require_once(CWPFB_PATH_CLASS . '/cwp-freshbooks-main.class.php');
    require_once(CWPFB_PATH_CLASS . '/cwp-freshbooks-pages.class.php');

    /* Intitialize licensing
     * for this plugin.
     */
    if( class_exists( 'Clients_WP_License_Handler' ) ) {
        $cwp_freshbooks = new Clients_WP_License_Handler( __FILE__, 'Clients WP - FreshBooks', CWPFB_VERSION, 'gravity2pdf', null, null, 7540);
    }

    add_action( 'plugins_loaded', array( 'Clients_WP_FreshBooks', 'get_instance' ) );
endif;