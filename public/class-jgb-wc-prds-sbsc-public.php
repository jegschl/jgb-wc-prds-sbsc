<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://x.com
 * @since      1.0.0
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/public
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Jgb_Wc_Prds_Sbsc_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jgb_Wc_Prds_Sbsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jgb_Wc_Prds_Sbsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if( is_product() ){
			$css_script_fl_jcplg = plugin_dir_url( __FILE__ ) . 'js/lib/swiper11/swiper-bundle.min.css';
			wp_enqueue_style( 
				'jgb-wpsbsc-swiper-bundle',
				//"https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
				$css_script_fl_jcplg
			);

			$css_script_fl_jcplg = plugin_dir_url( __FILE__ ) . 'css/jgb-wc-prds-sbsc-public.css';
			$css_script_fl_jcplg_path = plugin_dir_path( __FILE__ ) . 'css/jgb-wc-prds-sbsc-public.css';
			$tversion = filemtime($css_script_fl_jcplg_path);
			wp_enqueue_style( 
				$this->plugin_name, 
				$css_script_fl_jcplg, 
				array('jgb-wpsbsc-swiper-bundle'),
				$tversion,
				'all' 
			);
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jgb_Wc_Prds_Sbsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jgb_Wc_Prds_Sbsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if( is_product() ){

			$js_script_fl_jcplg = plugin_dir_url( __FILE__ ) . 'js/lib/swiper11/swiper-bundle.js';

			wp_enqueue_script( 
				'swiper-bundle', 
				$js_script_fl_jcplg,
				array( 'jquery' ), 
				false,
				false 
			);

			$js_script_fl_jcplg = plugin_dir_url( __FILE__ ) . 'js/input-render-select-color.js';
			$js_script_fl_jcplg_path = plugin_dir_path( __FILE__ ) . 'js/input-render-select-color.js';
			$tversion = filemtime($js_script_fl_jcplg_path);
			wp_enqueue_script( 
				'jgb-ir-select-color', 
				$js_script_fl_jcplg, 
				array( 
					'jquery'
				), 
				$tversion,
				false 
			);

			

		}
	}

}