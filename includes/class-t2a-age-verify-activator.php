<?php

/**
 * Fired during plugin activation
 *
 * @link       ageverifyuk.com
 * @since      1.0.0
 *
 * @package    T2a_Age_Verify
 * @subpackage T2a_Age_Verify/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    T2a_Age_Verify
 * @subpackage T2a_Age_Verify/includes
 * @author     AVUK <info@ageverifyuk.com>
 */
class T2a_Age_Verify_Activator {

	public static function activate() {
        add_option('t2a_age_verify_api_key');
        add_option('t2a_age_verify_sandbox_env');
        add_option('t2a_age_verify_full_site');
        add_option('t2a_age_verify_product_verify');
        add_option('t2a_age_verify_ocr_enabled');
	}

}
