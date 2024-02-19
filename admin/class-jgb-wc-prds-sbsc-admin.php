<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://x.com
 * @since      1.0.0
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/admin
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Jgb_Wc_Prds_Sbsc_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jgb-wc-prds-sbsc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jgb-wc-prds-sbsc-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function product_tabs( $tabs ){

		$tabs['jgb_wcp_sbsc'] = array(
			'label'   =>  __( 'Step by step config', 'jgb-wc-prds-sbsc' ),
			'target'  =>  'jgb_wc_prds_sbsc_tab',
			'priority' => 60,
			'class'   => array()
		);

		return $tabs;
	}

	public function wc_prds_sbsc_tab(){
		$template_path = Jgb_Wc_Prds_Sbsc::get_plugin_home_path() . "/admin/partials/product-admin-sbs-editor-base.php";
		if( file_exists( $template_path ) )
			include $template_path;
	}

}
