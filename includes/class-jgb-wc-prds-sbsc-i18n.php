<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://x.com
 * @since      1.0.0
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/includes
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Jgb_Wc_Prds_Sbsc_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'jgb-wc-prds-sbsc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
