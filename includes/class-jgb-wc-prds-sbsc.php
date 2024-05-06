<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://x.com
 * @since      1.0.0
 *
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Jgb_Wc_Prds_Sbsc
 * @subpackage Jgb_Wc_Prds_Sbsc/includes
 * @author     Jorge Garrido <jegschl@gmail.com>
 */
class Jgb_Wc_Prds_Sbsc {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Jgb_Wc_Prds_Sbsc_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $tpltr;

	protected $ProductFieldsManager;

	protected $CPT_WcProdSbsc_register;

	protected $short_code_DefPT;

	protected $adm_api_rest;

	public static $static_plugin_name;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'JGB_WC_PRDS_SBSC_VERSION' ) ) {
			$this->version = JGB_WC_PRDS_SBSC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'jgb-wc-prds-sbsc';

		Jgb_Wc_Prds_Sbsc::$static_plugin_name = $this->plugin_name;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Jgb_Wc_Prds_Sbsc_Loader. Orchestrates the hooks of the plugin.
	 * - Jgb_Wc_Prds_Sbsc_i18n. Defines internationalization functionality.
	 * - Jgb_Wc_Prds_Sbsc_Admin. Defines all hooks for the admin area.
	 * - Jgb_Wc_Prds_Sbsc_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		$dir_name = dirname( __FILE__ );
		$plugin_dir_path = plugin_dir_path( $dir_name );

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $plugin_dir_path . 'includes/class-jgb-wc-prds-sbsc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $plugin_dir_path . 'includes/class-jgb-wc-prds-sbsc-i18n.php';

		/**
		 * Carga la clase para registrar el CPT de definición de wc-prod-sbsc.
		 */
		require_once $plugin_dir_path . 'includes/class-wpsbsc-post-type.php';

		/**
		 * Carga la clase para registrar el shortcode de CPT de definición de wc-prod-sbsc.
		 */
		require_once $plugin_dir_path . 'includes/class-wpsbsc-pt-short-code.php';
		
		/**
		 * Carga el gestor de plantillas.
		 */
		require_once $plugin_dir_path . 'includes/class-tpltr.php';

		/**
		 * Carga el Widget Base.
		 */
		require_once $plugin_dir_path . 'includes/widgetsman/widget-base.php';

		$atfs = JGB\FormWidgetBase::get_allowed_types();
		foreach( $atfs as $atf){
			require_once $plugin_dir_path . "includes/widgetsman/widgets/$atf.php";
		}

		require_once $plugin_dir_path . 'includes/widgetsman/widgets-factory.php';

		require_once $plugin_dir_path . 'includes/product-fields-manager.php';

		require_once $plugin_dir_path . 'includes/class-jgb-choice-tree-import-parser.php';

		require_once $plugin_dir_path . 'includes/class-jwps-apirest.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $plugin_dir_path . 'admin/class-jgb-wc-prds-sbsc-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $plugin_dir_path . 'public/class-jgb-wc-prds-sbsc-public.php';

		

		

		$this->loader = new Jgb_Wc_Prds_Sbsc_Loader();

		$this->tpltr = new JgBWPSTemplater();

		$this->ProductFieldsManager = new JGB\WPSBSC\ProductFieldsManager();

		$this->CPT_WcProdSbsc_register = new JGB\WPSBSC\SBSCDefinitionPostType();

		$this->short_code_DefPT = new JGB\WPSBSC\SBSCDefPTShortCode( $plugin_dir_path );

		$this->adm_api_rest = new JWPSAdminApiRest();
		$this->adm_api_rest->set_cti_parser(); 
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Jgb_Wc_Prds_Sbsc_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Jgb_Wc_Prds_Sbsc_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Jgb_Wc_Prds_Sbsc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'product_tabs' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'wc_prds_sbsc_tab' );

		$this->loader->add_action( 'init', $this->CPT_WcProdSbsc_register, 'register' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->CPT_WcProdSbsc_register, 'enqueue_admin_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->CPT_WcProdSbsc_register, 'enqueue_admin_styles' );
		$this->loader->add_action( 'add_meta_boxes', $this->CPT_WcProdSbsc_register, 'add_meta_box_json_editor');
		$this->loader->add_action( 'add_meta_boxes', $this->CPT_WcProdSbsc_register, 'add_meta_box_choices_importer' );
		$this->loader->add_action( 'add_meta_boxes', $this->CPT_WcProdSbsc_register, 'add_meta_box_options' );
		$this->loader->add_action( 'save_post', $this->CPT_WcProdSbsc_register, 'save_post');

		$this->loader->add_Action( 'rest_api_init', $this->adm_api_rest, 'registerEndpoints' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Jgb_Wc_Prds_Sbsc_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_filter( 'wc_get_template', $this->tpltr, 'wc_get_template_single_product_add_to_cart_variable',90,5);
		
		$this->loader->add_filter( 'woocommerce_before_add_to_cart_button', $this->ProductFieldsManager, 'render_fields' );
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $this->ProductFieldsManager, 'process_product_fields' );
		$this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $this->ProductFieldsManager, 'save_order_line_item' );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $this->ProductFieldsManager, 'update_product_price' );
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Jgb_Wc_Prds_Sbsc_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public static function get_plugin_home_path(){
		return WP_PLUGIN_DIR . '/' . Jgb_Wc_Prds_Sbsc::$static_plugin_name;
	}

}
