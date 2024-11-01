<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ageverifyuk.com
 * @since             1.0.0
 * @package           T2a_Age_Verify
 *
 * @wordpress-plugin
 * Plugin Name:       Age Verify UK
 * Plugin URI:        https://t2a.io/plugins/wordpress_age_verify
 * Description:       Verify the age of your UK customers at point of registration, point of sale and in bulk. Age restrict your products or your entire shop.
 * Version:           1.4
 * Author:            AVUK
 * Author URI:        https://ageverifyuk.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Check wordpress instance running
if (!defined('WPINC')) {
    die;
}

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Check WooCommerce instance running
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    function no_woo_note()
    {
        ?>
        <div class="error notice">
            <p><?php echo('Please install and activate WooCommerce plugin, without that the Age Verify UK plugin will not work'); ?></p>
        </div>
        <?php
    }

    add_action('admin_notices', 'no_woo_note');
    deactivate_plugins(plugin_basename(__FILE__));
    return;
}


/**
 * Current plugin version.
 */
define('AVUK_AGE_VERIFY_VERSION', '1.4');
define('AVUK_REG_URL', 'https://ageverifyuk.com/sign-up');;


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-t2a-age-verify-activator.php
 */
function activate_t2a_age_verify()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-t2a-age-verify-activator.php';
    T2a_Age_Verify_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-t2a-age-verify-deactivator.php
 */
function deactivate_t2a_age_verify()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-t2a-age-verify-deactivator.php';
    T2a_Age_Verify_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_t2a_age_verify');
register_deactivation_hook(__FILE__, 'deactivate_t2a_age_verify');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-t2a-age-verify.php';

function tav_plugin_link($links)
{
    $links = array_merge(array(
        '<a href="' . esc_url(admin_url('/admin.php?page=t2a-age-verify')) . '">' . __('Settings') . '</a>',
        '<a style="color:#0a9a3e; font-weight:bold;" target="_blank" href="https://ageverifyuk.com/sign-up">' . __('Get an API key') . '</a>'
    ), $links);
    return $links;
}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'tav_plugin_link');

/**
 * Run any scripts relating to version upgrades
 *
 * @since 1.1.0
 */
function upgrade_scripts()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if (AVUK_AGE_VERIFY_VERSION == "1.1.0") {
        add_option('t2a_age_verify_version', '1.1.0');
    }
    else {
        $prevVersion = get_option('t2a_age_verify_version');

        if($prevVersion != AVUK_AGE_VERIFY_VERSION) {
            update_option('t2a_age_verify_version', AVUK_AGE_VERIFY_VERSION);
        }

    }

    //earlier than 1.1.0 to 1.1.0 script
    if (get_option('t2a_age_verify_user_array')) {
        $userArray = unserialize(get_option('t2a_age_verify_user_array'));
        foreach (get_users(['role__in' => ['customer']]) as $customer) {
            if (isset($userArray[$customer->display_name])) {
                update_user_meta($customer->get_id(), 't2a_age_verified', '1');
                delete_option('t2a_age_verify_user_array');
            }
        }
    }
    
    //earlier than 1.1.1 to 1.1.1 script
    if (get_option('t2a_age_verify_verify_array')) {
        $verifyArray = unserialize(get_option('t2a_age_verify_verify_array'));
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1
        );

        if(is_array($verifyArray)) {
            $loop = new WP_Query($args);
            if ($loop->have_posts()) {
                while ($loop->have_posts()) {
                    $loop->the_post();
                    global $product;
                    if (in_array($product->get_id(), $verifyArray)) {
                        $tavr = get_post_meta($product->get_id(), 't2a_age_verify_restricted');
                        if (!empty($tavr)) {
                            update_post_meta($product->get_id(), 't2a_age_verify_restricted', '1');
                        } else {
                            add_post_meta($product->get_id(), 't2a_age_verify_restricted', '1');
                        }
                        delete_option('t2a_age_verify_verify_array');
                    }
                }
            }
        }
    }

    //database script
    if (get_option('t2a_db_version') < 3) {
        global $wpdb;

        $table_name = $wpdb->prefix . "avuk_guest_checkouts";
        $charset_collate = $wpdb->get_charset_collate();

        $sqlCreate1 = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created timestamp NOT NULL default CURRENT_TIMESTAMP,
		  forename varchar(100) NOT NULL,
		  surname varchar(100) NOT NULL,
		  addr1 varchar(255) NOT NULL,
		  postcode varchar(20) NOT NULL,
          validated boolean not null default 0,
		  PRIMARY KEY id (id), 
		  UNIQUE KEY i_{$table_name} (forename, surname, addr1, postcode)
		) $charset_collate;";


        $table_name2 = $wpdb->prefix . "avuk_checkout_attempts";

        $sqlCreate2 = "CREATE TABLE IF NOT EXISTS $table_name2 (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created timestamp NOT NULL default CURRENT_TIMESTAMP,
		  forename varchar(100) NOT NULL,
		  surname varchar(100) NOT NULL,
		  addr1 varchar(255) NOT NULL,
		  postcode varchar(20) NOT NULL,
          validated boolean not null default 0,
		  PRIMARY KEY id (id),
		  UNIQUE KEY i_{$table_name} (forename, surname, addr1, postcode)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sqlCreate1 );
        dbDelta( $sqlCreate2 );

        // Dedupe legacy tables
        $legacy_name = $wpdb->prefix . "t2a_age_verify_guest_checkouts";
        $sqlIsLegacy1 = "SELECT COUNT(*)
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE()
                        AND table_name = '{$legacy_name}';";
        $isLegacy1 = $wpdb->get_var($sqlIsLegacy1);
        if($isLegacy1 > 0) {
            $sqlCopy1 = "INSERT INTO `{$table_name}` (`created`, `forename`, `surname`, `addr1`, `postcode`, `validated`)
                        SELECT min(`created`) AS `created`, `forename`, `surname`, `addr1`, `postcode`, `validated`
                        FROM `{$legacy_name}`
                        GROUP BY forename, surname, addr1, postcode;";
            dbDelta($sqlCopy1);
        }

        $legacy_name2 = $wpdb->prefix . "t2a_age_verify_checkout_attempts";
        $sqlIsLegacy2 = "SELECT COUNT(*)
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE()
                        AND table_name = '{$legacy_name2}';";
        $isLegacy2 = $wpdb->get_var($sqlIsLegacy2);
        if($isLegacy2 > 0) {
            $sqlCopy2 = "INSERT INTO `{$table_name2}` (`created`, `forename`, `surname`, `addr1`, `postcode`, `validated`)
                        SELECT min(`created`) AS `created`, `forename`, `surname`, `addr1`, `postcode`, 0 AS `validated`
                        FROM `{$legacy_name2}`
                        GROUP BY forename, surname, addr1, postcode;";

            dbDelta($sqlCopy2);
        }

        update_option('t2a_db_version', 3);
    }

    // 1.1.2 script
    if(!get_option('t2a_age_verify_dialog')) {
        add_option('t2a_age_verify_dialog', "Your basket contains age-restricted item(s) for which we were unable to verify your age as 18 or over. This can be caused by a recent change of address.");
    }

    // 1.1.3 script


}

add_action('init', 'upgrade_scripts');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.2.9
 */
function run_t2a_age_verify()
{

    $plugin = new T2a_Age_Verify();
    $plugin->run();

}

run_t2a_age_verify();