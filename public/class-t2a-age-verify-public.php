<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    T2a_Age_Verify
 * @subpackage T2a_Age_Verify/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    T2a_Age_Verify
 * @subpackage T2a_Age_Verify/public
 * @author     PI Websolution <sales@piwebsolution.com>
 */
class T2a_Age_Verify_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->key = get_option('t2a_age_verify_api_key');
        $this->product_verify = get_option('t2a_age_verify_product_verify');
        $this->full_site = get_option('t2a_age_verify_full_site');
        $this->credit_balance = $this->get_credit_balance();
        $this->sandbox_env = get_option('t2a_age_verify_sandbox_env');

        if ($this->sandbox_env == "true") {
            $this->active_key = "sandbox";
        } else {
            $this->active_key = $this->key;
        }

        if (($this->product_verify || $this->full_site)) {
            if (($this->credit_balance > 0 && $this->credit_balance != "invalid_key") || $this->sandbox_env == "true") {
                add_action('woocommerce_after_checkout_validation', array($this, 'age_verify_checkout'));
                add_action('woocommerce_thankyou', array($this, 'age_verify_finalise'), 10, 1);
            }
        }

    }

    public function age_verify_checkout()
    {
        global $wpdb;

        $validation = false;

        if ($this->full_site) {
            $validation = true;
        } else {
            foreach (WC()->cart->get_cart() as $item) {
                //Check if item has a parent - if it does use parent id, if not, use the current item id
                if (!empty(wp_get_post_parent_id($item['data']->get_id()))){
                    $id = wp_get_post_parent_id($item['data']->get_id());
                } else {
                    $id = $item['data']->get_id();
                }

                if (!empty(get_post_meta($id, 't2a_age_verify_restricted'))) {
                    $validation = true;
                }
            }
        }

        $forename = sanitize_text_field($_POST['billing_first_name']);
        $surname = sanitize_text_field($_POST["billing_last_name"]);
        $address = (isset($_POST['billing_address_2'])) ? sanitize_text_field($_POST['billing_address_2']) . " " . sanitize_text_field($_POST['billing_address_1']) : sanitize_text_field($_POST['billing_address_1']);
        $postcode = sanitize_text_field($_POST['billing_postcode']);
        $transaction_id = sanitize_text_field($_POST['transaction_id']);

        $overriddenAttempt = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "avuk_checkout_attempts" .
            " WHERE `validated` = 1
              AND `forename` = \"" . $forename . "\" 
              AND `surname` = \"" . $surname . "\" 
              AND `addr1` = \"" . $address . "\" 
              AND `postcode` = \"" . $postcode . "\" 
            ");
        if (isset($overriddenAttempt->id)) {
            $validation = false;
        }

        $validatedGuest = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "avuk_guest_checkouts" .
            " WHERE validated = 1
              AND forename = \"" . $forename . "\" 
              AND surname = \"" . $surname . "\" 
              AND addr1 = \"" . $address . "\" 
              AND postcode = \"" . $postcode . "\"
            ");
        if (isset($validatedGuest->id)) {
            $validation = false;
        }

        if(get_option('t2a_age_verify_ocr_enabled')) {
            $ocr_enabled = get_option('t2a_age_verify_ocr_enabled');
        } else {
            $ocr_enabled = "none";
        }

        if(get_option('t2a_age_verify_ocr_enabled') != "none" && $transaction_id != "") {
            //error_log("transaction_id: " . $transaction_id);
            $checkOcr = $this->check_ocr($transaction_id);
            //error_log($checkOcr->ocr_status);
            if($checkOcr->ocr_status == "VALIDATED") {
                $validation = false;

                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    update_user_meta($user->ID, 't2a_age_verified', '1');
                } else {
                    $table_name = $wpdb->prefix . "avuk_guest_checkouts";
                    $wpdb->insert($table_name, [
                        "forename" => $forename,
                        "surname" => $surname,
                        "addr1" => $address,
                        "postcode" => $postcode,
                        "validated" => 1,
                    ]);
                }
            }
        }


        if ($validation) {
            $resArray = $this->age_verify($surname,
                $forename,
                $address,
                $postcode);

            if ($resArray->validation_status != "FOUND") {
                $table_name = $wpdb->prefix . "avuk_checkout_attempts";

                $sqlInsert = "INSERT IGNORE INTO `{$table_name}` (`forename`, `surname`, `addr1`, `postcode`, `validated`)
                              VALUES ('{$forename}', '{$surname}', '{$address}', '{$postcode}', 0)";
                $wpdb->query($sqlInsert);

                $key = get_option('t2a_age_verify_api_key');

                $errorOutput = "<div id=\"avukMsg\">";
                $errorOutput .= get_option('t2a_age_verify_dialog');

                if(get_option('t2a_age_verify_ocr_enabled') != "none") {
                    $ocrSetup = $this->configure_ocr($resArray->transaction_id);
                    if($ocrSetup->status != "error") {
                        $errorOutput .= "<br/><br/>If you would like to upload proof of identification such as driving license or passport please <a href=\"#\" id=\"processOcr\" data-transaction=\"{$resArray->transaction_id}\" data-key=\"{$key}\">click here</a>";
                    }
                }

                $errorOutput .= "</div>";
                
                wc_add_notice($errorOutput, 'error');
            }
        }
    }

    /**
     * Ensure the validation gets saved to the database once a user has been created
     *
     * @since    1.1.0
     */
    public function age_verify_finalise($order_id)
    {

        $user = wp_get_current_user();
        $order = wc_get_order($order_id);
        global $wpdb;

        if ($this->full_site) {
            $validation = true;
        } else {
            foreach ($order->get_items() as $item) {
                if (!empty(get_post_meta($item->get_product_id(), 't2a_age_verify_restricted'))) {
                    $validation = true;
                }
            }
        }

        //error_log("VALIDATION BEFORE DATA:".$validation);;

        $data = $order->get_data();
        $dataAddress = (isset($data['billing']['address_2'])) ? $data['billing']['address_2'] . " " . $data['billing']['address_1'] : $data['billing']['address_1'];
        $validatedGuest = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "avuk_guest_checkouts" .
            " WHERE validated = 1
              AND forename = \"" . $data['billing']['first_name'] . "\" 
              AND surname = \"" . $data['billing']['last_name'] . "\" 
              AND addr1 = \"" . $dataAddress . "\" 
              AND postcode = \"" . $data['billing']['postcode'] . "\"
            ");

        //error_log("VALIDATED GUEST ID: " . $validatedGuest->id);
        //error_log("VALIDATION BEFORE CHECK:".$validation);;
        if (isset($validatedGuest->id)) {
            $validation = false;
        }

        //error_log("VALIDATION AFTER CHECK:".$validation);

        if ($validation) {
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                if (get_user_meta($user->get_id(), 't2a_age_verified')) {
                    $validation = false;
                }
                if ($validation) {
                    $addr2 = get_user_meta($user->ID, 'billing_address_2', true);
                    $userMetaAddr = isset($addr2) ? $addr2 . " " . get_user_meta($user->ID, 'billing_address_1', true) : get_user_meta($user->ID, 'billing_address_1', true);
                    $resArray = $this->age_verify(get_user_meta($user->ID, 'billing_last_name', true),
                        get_user_meta($user->ID, 'billing_first_name', true),
                        $userMetaAddr,
                        get_user_meta($user->ID, 'billing_postcode', true));
                    if ($resArray->validation_status == "FOUND") {
                        update_user_meta($user->ID, 't2a_age_verified', '1');
                    }
                }
            } else {
                $resArray = $this->age_verify($data['billing']['first_name'],
                    $data['billing']['last_name'],
                    $dataAddress,
                    $data['billing']['postcode']);
                if ($resArray->validation_status == "FOUND") {
                    $table_name = $wpdb->prefix . "avuk_guest_checkouts";
                    $wpdb->insert($table_name, [
                        "forename" => $data['billing']['first_name'],
                        "surname" => $data['billing']['last_name'],
                        "addr1" => $dataAddress,
                        "postcode" => $data['billing']['postcode'],
                        "validated" => 1,
                    ]);
                }
            }
        }


    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in T2a_Age_Verify_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The T2a_Age_Verify_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/extended-flat-rate-shipping-woocommerce-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in T2a_Age_Verify_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The T2a_Age_Verify_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/t2a-age-verify-checkout.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/t2a-age-verify-public.js', array( 'jquery' ), $this->version, true, 5 );
    }

    protected function get_credit_balance()
    {
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Content-type: application/x-wgit dsww-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key" => $this->key,
                "method" => "client_info",
                "output" => "json"
            )
        );

        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);

        $resObj = json_decode($response["body"]);

        if (isset($resObj->error_code)) {
            if ($resObj->error_code == "invalid_api_key" || $resObj->error_code == "missing_api_key") {
                return "invalid_key";
            }
        } else {
            return (float)$resObj->credit_balance;
        }
    }

    /**
     * Reusable customer verification code
     *
     * @since    1.1.0
     */
    private function age_verify($surname, $forename, $addr1, $postcode)
    {
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key" => $this->active_key,
                "method" => "age_verification",
                "surname" => $surname,
                "forename" => $forename,
                "addr1" => $addr1,
                "postcode" => $postcode,
                "output" => "json"
            )
        );


        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);
        return json_decode($response["body"]);
    }

    private function configure_ocr($transaction_id) {
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key" => $this->active_key,
                "method" => "ocr_transaction",
                "output" => "json",
                "cmd" => "set",
                "transaction_id" => $transaction_id,
                "docscan_type" => get_option('t2a_age_verify_ocr_enabled')
            )
        );


        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);
        return json_decode($response["body"]);
    }

    private function check_ocr($transaction_id) {
        $args = array(
            'method' => 'GET',
            'headers' => array(
                'Content-type: application/x-www-form-urlencoded'
            ),
            'sslverify' => false,
            'body' => array(
                "api_key" => $this->active_key,
                "method" => "ocr_transaction",
                "output" => "json",
                "cmd" => "get",
                "transaction_id" => $transaction_id,
            )
        );


        $response = wp_remote_get("https://ageverifyuk.com/rest/", $args);
        return json_decode($response["body"]);
    }

}
