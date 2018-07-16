<?php if ( ! defined( 'ABSPATH' ) ) exit;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

use Freshbooks\FreshBooksApi;

class Clients_WP_FreshBooks{
    
    private static $instance;

    public static function get_instance()
    {
        if( null == self::$instance ) {
            self::$instance = new Clients_WP_FreshBooks();
        }

        return self::$instance;
    }

    function __construct(){
        add_action('admin_init', array($this, 'register_integration'));
        add_action('admin_init', array($this, 'sync_clients_list'));
        add_action('admin_enqueue_scripts', array( $this, 'cwp_freshbooks_add_admin_scripts' ));
        add_action('wp_enqueue_scripts', array($this, 'cwp_freshbooks_add_wp_scripts'), 20, 1);
        add_action('wp_ajax_get_freshbooks_client_list', array($this, 'get_freshbooks_client_list_ajax'));
        add_filter('the_content', array($this, 'folder_content_table'));
        if(isset($_SESSION['freshbooks_error_msg'])) {
            $this->freshbooks_error($_SESSION['freshbooks_error_msg']);
            unset($_SESSION['freshbooks_error_msg']);
        }
    }

    public function cwp_freshbooks_add_admin_scripts() {
        wp_register_script('cwp_freshbooks_admin_scripts', CWPFB_URL . '/assets/js/cwp-freshbooks-admin-scripts.js', '1.0', true);
        $cwpfb_admin_script = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        );
        wp_localize_script('cwp_freshbooks_admin_scripts', 'cwpfb_admin_script', $cwpfb_admin_script );
        wp_enqueue_script('cwp_freshbooks_admin_scripts');
    }

    public function cwp_freshbooks_add_wp_scripts() {
        wp_register_script('cwp_freshbooks_wp_scripts', CWPFB_URL . '/assets/js/cwp-freshbooks-scripts.js', '1.0', true);
        wp_enqueue_script('cwp_freshbooks_wp_scripts');

        wp_register_style('cwp_freshbooks_wp_styles', CWPFB_URL . '/assets/css/cwp-freshbooks-styles.css', '1.0', true);
        wp_enqueue_style('cwp_freshbooks_wp_styles');
    }

    public function register_integration($array) {
        $freshbooks = array(
            'freshbooks' => array(
                'key'       => 'freshbooks',
                'label'     => 'FreshBooks'
            )
        );

        $clients_wp_integrations = get_option('clients_wp_integrations');

        if(is_array($clients_wp_integrations)) {
            $merge_integrations = array_merge($clients_wp_integrations, $freshbooks);
            update_option('clients_wp_integrations', $merge_integrations);
        } else {
            update_option('clients_wp_integrations', $freshbooks);
        }
        
    }

    /*public function fbooks_get_client($email, $domain, $token) {
        $fb = new FreshBooksApi($domain, $token);
        $fb->setMethod('client.list');
        $fb->post(array(
            'email' => $email
        ));
        $fb->request();

        if($fb->success()) {
            return $fb->getResponse();
        } else {
            return $fb->getError();
        }
    }*/

    public function fbooks_get_client_invoices($client_id, $domain, $token) {
        $fb = new FreshBooksApi($domain, $token);
        $fb->setMethod('invoice.list');
        $fb->post(array(
            'client_id' => $client_id
        ));
        $fb->request();

        if($fb->success()) {
            return $fb->getResponse();
        } else {
            return $fb->getError();
        }
    }

    public function fbooks_get_client_estimates($client_id, $domain, $token) {
        $fb = new FreshBooksApi($domain, $token);
        $fb->setMethod('estimate.list');
        $fb->post(array(
            'client_id' => $client_id
        ));
        $fb->request();

        if($fb->success()) {
            return $fb->getResponse();
        } else {
            return $fb->getError();
        }
    }

    public function sync_clients_list() {
        if (isset($_REQUEST['cwpintegration']) && $_REQUEST['cwpintegration'] == 'freshbooks' ):
            $cwpfreshbooks_settings_options = get_option('cwpfreshbooks_settings_options');
            $app_domain = isset($cwpfreshbooks_settings_options['app_domain']) ? $cwpfreshbooks_settings_options['app_domain'] : '';
            $app_token = isset($cwpfreshbooks_settings_options['app_token']) ? $cwpfreshbooks_settings_options['app_token'] : '';

            if(!empty($app_domain) && !empty($app_token)) {
                $fb = new FreshBooksApi($app_domain, $app_token);
                $fb->setMethod('client.list');
                $fb->request();

                if($fb->success()) {
                    unset($_SESSION['freshbooks_error_msg']);
                    $result = $fb->getResponse();

                    $clients = array();
                    foreach($result['clients']['client'] as $client) {
                        $c['id'] = $client['client_id'];
                        $c['fname'] = $client['first_name'];
                        $c['lname'] = $client['last_name'];
                        $clients[] = $c;
                    }

                    $cwpfreshbooks_settings_options['clients_list'] = serialize($clients);
                } else {
                    $result = $fb->getError();
                    $_SESSION['freshbooks_error_msg'] = $result;
                    $cwpfreshbooks_settings_options['clients_list'] = '';
                }

                update_option( 'cwpfreshbooks_settings_options', $cwpfreshbooks_settings_options );
                header('Location: ' . admin_url( 'edit.php?post_type=bt_client&page=cwp-freshbooks' ));
            }
        endif;
    }

    public function get_freshbooks_client_list_ajax() {
        $cwpfreshbooks_settings_options = get_option('cwpfreshbooks_settings_options');
        $app_domain = isset($cwpfreshbooks_settings_options['app_domain']) ? $cwpfreshbooks_settings_options['app_domain'] : '';
        $app_token = isset($cwpfreshbooks_settings_options['app_token']) ? $cwpfreshbooks_settings_options['app_token'] : '';
        $clients_list = isset($cwpfreshbooks_settings_options['clients_list']) ? $cwpfreshbooks_settings_options['clients_list'] : '';

        if(!empty($app_domain) && !empty($app_token) && !empty($clients_list)) {
            echo json_encode(unserialize($clients_list));
        }
        die();
    }

    public function freshbooks_error($msg) {
        ?>
        <div class="notice error is-dismissible" >
            <p><b>FreshBooks Error:</b> <?php _e( $msg, 'my-text-domain' ); ?></p>
        </div>
        <?php
    }

    public function folder_content_table() {
        global $pages;

        foreach($pages as $page) {
            if (strpos($page, '[clientswp_user_register_form]') !== FALSE) {
                return nl2br($page);
            }

            if (strpos($page, '[clientswp_group_add_user_form]') !== FALSE) {
                return nl2br($page);
            }

            if (strpos($page, '[cwp_') !== FALSE) {
                $args = array(
                    'meta_key' => '_clients_page_shortcode',
                    'meta_value' => $page,
                    'post_type' => 'bt_client_page',
                    'post_status' => 'any',
                    'posts_per_page' => -1
                );
                $posts = get_posts($args);

                foreach($posts as $post) {
                    echo $post->post_content;

                    $integration = get_post_meta($post->ID, '_clients_page_integration', true);
                    $fb_client_id = get_post_meta($post->ID, '_clients_page_integration_folder', true);

                    if (isset($integration) && isset($fb_client_id)) {
                        if((!empty($integration) && $integration == 'freshbooks') && !empty($fb_client_id)) {
                            $cwpfreshbooks_settings_options = get_option('cwpfreshbooks_settings_options');
                            $app_domain = isset($cwpfreshbooks_settings_options['app_domain']) ? $cwpfreshbooks_settings_options['app_domain'] : '';
                            $app_token = isset($cwpfreshbooks_settings_options['app_token']) ? $cwpfreshbooks_settings_options['app_token'] : '';

                            if (!is_user_logged_in()) {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            $user_groups = cwp_get_current_user_groups();
                            if (empty($user_groups)) {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            if(!empty($app_domain) && !empty($app_token)) {
                                $invoices_result = $this->fbooks_get_client_invoices($fb_client_id, $app_domain, $app_token);
                                $estimates_result = $this->fbooks_get_client_estimates($fb_client_id, $app_domain, $app_token);

                                $invoices = null;
                                if(isset($invoices_result['invoices']['invoice'])) {
                                    $invoices = $invoices_result['invoices'];
                                }

                                $estimates = null;
                                if(isset($estimates_result['estimates']['estimate'])) {
                                    $estimates = $estimates_result['estimates'];
                                }

                                include_once(CWPFB_PATH_INCLUDES . '/cwp-freshbooks-table.php');
                            }
                        }
                    }
                }
            } else {
                return nl2br($page);
            }
        }
    }
}