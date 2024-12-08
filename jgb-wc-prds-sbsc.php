<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://x.com
 * @since             1.0.0
 * @package           Jgb_Wc_Prds_Sbsc
 *
 * @wordpress-plugin
 * Plugin Name:       JGB WC Products Step by Step Configurator
 * Plugin URI:        https://x.com
 * Description:       Set a step by step parameters configurator for WC products in the frontend.
 * Version:           1.0.9
 * Author:            Jorge Garrido
 * Author URI:        https://x.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jgb-wc-prds-sbsc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'JGB_WC_PRDS_SBSC_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jgb-wc-prds-sbsc-activator.php
 */
function activate_jgb_wc_prds_sbsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jgb-wc-prds-sbsc-activator.php';
	Jgb_Wc_Prds_Sbsc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jgb-wc-prds-sbsc-deactivator.php
 */
function deactivate_jgb_wc_prds_sbsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jgb-wc-prds-sbsc-deactivator.php';
	Jgb_Wc_Prds_Sbsc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_jgb_wc_prds_sbsc' );
register_deactivation_hook( __FILE__, 'deactivate_jgb_wc_prds_sbsc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-jgb-wc-prds-sbsc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_jgb_wc_prds_sbsc() {

	$plugin = new Jgb_Wc_Prds_Sbsc();
	$plugin->run();

}

if (!function_exists('write_log')) {    
    /**
     * Enviar mensajes al log de WordPress
     *
     * @param string $message Mensaje que deseas registrar.
     * @param string $context Contexto opcional del log (por ejemplo, un identificador).
     */
    function write_log($message, $context = 'custom-log') {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true); // Convierte arrays y objetos a cadena
            }

            $formatted_message = sprintf("[%s] %s: %s\n", date("Y-m-d H:i:s"), strtoupper($context), $message);

            error_log($formatted_message, 3, WP_CONTENT_DIR . '/debug.log');
        }
    }
}

run_jgb_wc_prds_sbsc();
