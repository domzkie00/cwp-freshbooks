<?php if ( ! defined( 'ABSPATH' ) ) exit;
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
        add_action('admin_enqueue_scripts', array( $this, 'cwp_freshbooks_add_admin_scripts' ));
        add_action('wp_enqueue_scripts', array($this, 'cwp_freshbooks_add_wp_scripts'), 20, 1);
        add_filter('the_content', array($this, 'folder_content_table'));
    }

    public function cwp_freshbooks_add_admin_scripts() {
        wp_register_script('cwp_freshbooks_admin_scripts', CWPFB_URL . '/assets/js/cwp-freshbooks-admin-scripts.js', '1.0', true);
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

    public function fbooks_get_client($email, $domain, $token) {
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
    }

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

    public function folder_content_table() {
        global $pages;

        foreach($pages as $page) {
            $page_content = nl2br($page);
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

                    if (isset($integration)) {
                        if((!empty($integration) && $integration == 'freshbooks')) {
                            $cwpfreshbooks_settings_options = get_option('cwpfreshbooks_settings_options');
                            $app_domain = isset($cwpfreshbooks_settings_options['app_domain']) ? $cwpfreshbooks_settings_options['app_domain'] : '';
                            $app_token = isset($cwpfreshbooks_settings_options['app_token']) ? $cwpfreshbooks_settings_options['app_token'] : '';

                            $linked_client_id = get_post_meta($post->ID, '_clients_page_client', true);
                            $client_email = get_post_meta($linked_client_id, '_bt_client_group_owner', true);

                            if(is_user_logged_in()) {
                                $current_user = wp_get_current_user();
                                if(!current_user_can('administrator')) {
                                    if($current_user->user_email != $client_email) {
                                        echo 'You are not allowed to see this contents.';
                                        return;
                                    }
                                } else {
                                    if($current_user->user_email != $client_email) {
                                        echo 'You are not allowed to see this contents.';
                                        return;
                                    }
                                }
                            } else {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            if(!empty($app_domain) && !empty($app_token)) {
                                $client_response = $this->fbooks_get_client($client_email, $app_domain, $app_token);

                                if(isset($client_response['clients']['client'])) {
                                    $client_id = $client_response['clients']['client']['client_id'];
                                    $invoices_result = $this->fbooks_get_client_invoices($client_id, $app_domain, $app_token);
                                    $estimates_result = $this->fbooks_get_client_estimates($client_id, $app_domain, $app_token);

                                    $invoices = null;
                                    if(isset($invoices_result['invoices']['invoice'])) {
                                        $invoices = $invoices_result['invoices'];
                                    }

                                    $estimates = null;
                                    if(isset($estimates_result['estimates']['estimate'])) {
                                        $estimates = $estimates_result['estimates'];
                                    }

                                    ob_start();
                                    include_once(CWPFB_PATH_INCLUDES . '/cwp-freshbooks-table.php');
                                    $page_content .= ob_get_clean();
                                }
                            }
                        }
                    }
                }
            }

            return $page_content;
        }
    }
}