<?php

class T2a_Age_Verify_AdminPages{

    public $plugin_name;
    public $menu;
    
    function __construct($plugin_name , $version){
        $this->plugin_name    = $plugin_name;
        $this->version        = $version;
        $this->key            = get_option('t2a_age_verify_api_key');
        $this->full_site      = get_option('t2a_age_verify_full_site');
        $this->ocr_enabled    = get_option('t2a_age_verify_ocr_enabled');
        $this->sandbox_env    = get_option('t2a_age_verify_sandbox_env');
        $this->product_verify = get_option('t2a_age_verify_product_verify');

        if($this->sandbox_env == "true") {
            $this->active_key = "sandbox";
        }
        else {
            $this->active_key = $this->key;
        }

        if($this->key) {
            $this->credit_balance = $this->get_credit_balance();
        }
        else {
            $this->credit_balance = "no_key";
        }

        if($this->key && $this->credit_balance !== "invalid_key")
        {
            add_action( 'admin_menu', array($this,'admin_menu') );
        }
        else {
            add_action( 'admin_menu', array($this,'admin_menu_no_key') );
        }
    }

    function admin_menu(){
         add_menu_page(
            __( 'Age Verify UK'),
            __( 'Age Verify UK'),
            'manage_options',
            't2a-age-verify',
            array($this, 'settings_page'),
            plugin_dir_url( __FILE__ ).'img/avuk.png',
            6
        );

        add_submenu_page('t2a-age-verify', 'Age Verify UK Settings', 'Settings', 'manage_options', 't2a-age-verify', array($this, 'settings_page') );
        add_submenu_page('t2a-age-verify', 'Age Verify UK Users', 'Customers', 'manage_options', 't2a-age-verify-users', array($this, 'user_page'));
        add_submenu_page('t2a-age-verify', 'Age Verify UK Demo', 'Demo', 'manage_options', 't2a-age-verify-demo', array($this, 'demo_page'));
        add_submenu_page('t2a-age-verify', 'Age Verify UK Advanced', 'Advanced', 'manage_options', 't2a-age-verify-advanced', array($this, 'advanced_page'));

    }

    function admin_menu_no_key() {
        add_menu_page(
            __( 'Age Verify UK'),
            __( 'Age Verify UK'),
            'manage_options',
            't2a-age-verify',
            array($this, 'settings_page'),
            plugin_dir_url( __FILE__ ).'img/avuk.png',
            6
        );
    }

    public function bootstrap_style() {
        wp_enqueue_style( $this->plugin_name."_bootstrap", plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );
	}

    function settings_page(){
        if(isset($_POST['form_submitted'])) {
            if(isset($_POST['apikey'])) {
                $this->key = sanitize_text_field( $_POST['apikey']);
            }else {
                $this->key = "";
            }

            if(isset($_POST['odialog'])) {
                $this->odialog = sanitize_text_field( $_POST['odialog']);
            }else {
                $this->odialog = "Your basket contains age-restricted item(s) for which we were unable to verify your age as 18 or over. This can be caused by a recent change of address.";
            }

            if(isset($_POST['full_site'])) {
                $this->full_site = "true";
            }else {
                $this->full_site = "";
            }

            if(isset($_POST['ocr_enabled'])) {
                $this->ocr_enabled = $_POST['ocr_enabled'];
            }else {
                $this->ocr_enabled = "none";
            }

            if(isset($_POST['product_verify'])) {
                $this->product_verify = "true";
            } else {
                $this->product_verify = "";
            }

            if(isset($_POST['products'])) {
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1
                );
                $loop = new WP_Query($args);
                if ($loop->have_posts()) {
                    while ($loop->have_posts()) {
                        $loop->the_post();
                        global $product;
                        if (in_array($product->get_id(), $_POST['products'])) {
                            $tavr = get_post_meta($product->get_id(), 't2a_age_verify_restricted');
                            if(!empty($tavr)) {
                                update_post_meta($product->get_id(), 't2a_age_verify_restricted', '1');
                            } else {
                                add_post_meta($product->get_id(), 't2a_age_verify_restricted', '1');
                            }
                        } else {
                            delete_post_meta($product->get_id(), 't2a_age_verify_restricted');
                        }
                    }
                }
            } else {
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1
                );
                $loop = new WP_Query($args);
                if ($loop->have_posts()) {
                    while ($loop->have_posts()) {
                        $loop->the_post();
                        global $product;
                        delete_post_meta($product->get_id(), 't2a_age_verify_restricted');
                    }
                }
            }

            update_option('t2a_age_verify_product_verify', $this->product_verify);
            update_option('t2a_age_verify_dialog', $this->odialog);
            update_option('t2a_age_verify_api_key', $this->key);
            update_option('t2a_age_verify_full_site', $this->full_site);
            update_option('t2a_age_verify_ocr_enabled', $this->ocr_enabled);
        }

        if($this->key) {
            $this->credit_balance = $this->get_credit_balance();
            if($this->credit_balance === "wp_error") {
                add_action('admin_notices', array($this, 'wp_error_handle'));
                do_action('admin_notices');
            }
            else if($this->credit_balance !== "invalid_key") {
                if($this->credit_balance < 10) {
                    add_action('admin_notices', array($this, 'no_credit_notice'));
                    do_action('admin_notices');
                }
                else if($this->credit_balance <= 120) {
                    add_action('admin_notices', array($this, 'no_ocr_notice'));
                    do_action('admin_notices');
                }
                else if($this->credit_balance <= 1000) {
                    add_action('admin_notices', array($this, 'low_credit_notice'));
                    do_action('admin_notices');
                }
                echo $this->page_with_key();
            }
            else {
                add_action('admin_notices', array($this, 'invalid_key_notice'));
                do_action('admin_notices');

                echo $this->page_no_key();
            }
        }
        else {
            $this->credit_balance = "no_key";
            $this->page_no_key();
        }

    }

    function user_page() {
        global $wpdb;
        $cust_id = isset($_POST['cust_id'])?$_POST['cust_id']:"";
        if(isset($_POST["run_verification"])) {
            $addr2 = get_user_meta( $cust_id, 'billing_address_1', true );
            $surname  = get_user_meta( $cust_id, 'billing_last_name', true );
            $forename = get_user_meta( $cust_id, 'billing_first_name', true );
            $addr1    = isset($addr2) ? $addr2 . " " . get_user_meta( $cust_id, 'billing_address_1', true ) : get_user_meta( $cust_id, 'billing_address_1', true );
            $postcode = get_user_meta( $cust_id, 'billing_postcode', true );
            $this->run_verification($surname, $forename, $addr1, $postcode, $cust_id);
        }
        if(isset($_POST["manual_verification"])) {
            update_user_meta($cust_id, 't2a_age_verified', '1');
        }
        if(isset($_POST["unverify"])) {
            delete_user_meta($cust_id, 't2a_age_verified');
        }

        if(isset($_POST["attempt_override"])) {
            $wpdb->update($wpdb->prefix . "avuk_checkout_attempts", ["validated"=>1], ['id'=>sanitize_text_field($_POST['attempt_id'])]);
        }

        if(isset($_POST["attempt_restore"])) {
            $wpdb->update($wpdb->prefix . "avuk_checkout_attempts", ["validated"=>0], ['id'=>sanitize_text_field($_POST['attempt_id'])]);
        }


        $table_name = $wpdb->prefix . "avuk_guest_checkouts";
        $guests = $wpdb->get_results ( "SELECT * 
                            FROM  $table_name" );
        $table_name2 = $wpdb->prefix . "avuk_checkout_attempts";
        $attempts = $wpdb->get_results ( "SELECT * 
                            FROM  $table_name2" );

        require_once plugin_dir_path(__FILE__) . 'templates/users.php';
    }

    function demo_page() {
        wp_enqueue_style( $this->plugin_name . "_demo", plugin_dir_url( __FILE__ ) . 'css/t2a-age-verify-demo.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name . "_demo", plugin_dir_url( __FILE__ ) . 'js/t2a-age-verify-demo.js', array( 'jquery' ), $this->version, false );
        require_once plugin_dir_path(__FILE__) . 'templates/demo.php';
    }

    function advanced_page() {
        if(isset($_POST['form_submitted'])) {

            if (isset($_POST['sandbox_env'])) {
                $this->sandbox_env = "true";
            } else {
                $this->sandbox_env = "";
            }

            update_option('t2a_age_verify_sandbox_env', $this->sandbox_env);
        }

        require_once plugin_dir_path(__FILE__) . 'templates/advanced.php';
    }

    function page_with_key() {
        require_once plugin_dir_path(__FILE__) . 'templates/admin.php';
    }

    function page_no_key() {
        wp_enqueue_style( $this->plugin_name . "_demo", plugin_dir_url( __FILE__ ) . 'css/t2a-age-verify-demo.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name . "_demo", plugin_dir_url( __FILE__ ) . 'js/t2a-age-verify-demo.js', array( 'jquery' ), $this->version, false );
        require_once plugin_dir_path(__FILE__) . 'templates/nokey.php';
    }


    protected function get_cur_url() {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $CurPageURL;
    }

    protected function run_verification($surname, $forename, $addr1, $postcode, $cust_id) {

        $args = array(
            'method' => 'GET',
            'headers'  => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key"  => $this->active_key,
                "method"   => "age_verification",
                "surname"  => $surname,
                "forename" => $forename,
                "addr1"    => $addr1,
                "postcode" => $postcode,
                "output"   => "json"
            )
        );

        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);
        $resObj = json_decode($response["body"]);
    }

    protected function get_credit_balance() {
        $args = array(
            'method' => 'GET',
            'headers'  => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key"    => $this->key,
                "method"     => "client_info",
                "pluginuser" => "true",
                "output"     => "json"
            )
        );


        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);
        if ( ! empty( $response->errors ) ) {
            return true;
        }
        else {
            $resObj = json_decode($response["body"]);
            if(isset($resObj->error_code)) {
                if ($resObj->error_code == "invalid_api_key" || $resObj->error_code == "missing_api_key") {
                    return "invalid_key";
                }
            }else {
                return (float)$resObj->credit_balance;
            }
        }
    }

    public function low_credit_notice() {
        ?>
        <div class="update-nag notice">
            <p><?php _e( 'You are running low on credits. To continue to use the age verification service uninterrupted please <a href="https://ageverifyuk.com" target="_blank">buy credits</a>', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function invalid_key_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Invalid API key', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function no_credit_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'You do not have enough credits to verify customers. Age Verification is now switched off until you purchase more credits', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function no_ocr_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'You do not have enough credits to use document upload. OCR functionality is now switched off until you purchase more credits', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function wp_error_handle() {
        ?>
        <div class="error notice">
            <p><?php _e( 'There has been an error whilst trying to retrieve your account information. Please try again, if the problem persists please get in touch with <a href="mailto:colin@t2a.io">colin@t2a.io</a>', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function run_verification_pass() {
        ?>
        <div class="update notice">
            <p><?php _e( 'User successfully verified', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function run_verification_fail() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Could not verify user', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }

    public function manual_verification() {
        ?>
        <div class="update notice">
            <p><?php _e( 'User manually verified', 't2a_age_verify' ); ?></p>
        </div>
        <?php
    }


}