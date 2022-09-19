<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/includes
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
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Ordv_Shipper {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ordv_Shipper_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'ORDV_SHIPPER_VERSION' ) ) {
			$this->version = ORDV_SHIPPER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ordv-shipper';

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
	 * - Ordv_Shipper_Loader. Orchestrates the hooks of the plugin.
	 * - Ordv_Shipper_i18n. Defines internationalization functionality.
	 * - Ordv_Shipper_Admin. Defines all hooks for the admin area.
	 * - Ordv_Shipper_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ordv-shipper-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ordv-shipper-i18n.php';

		/**
		 * The files responsible for defining all functions that will work as helper
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'functions/function-ordv-helper.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/logistic.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/location.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/order-shipper.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ordv-shipper-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-shipper-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-checkout.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-thank-you.php';

		$this->loader = new Ordv_Shipper_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ordv_Shipper_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ordv_Shipper_i18n();

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

		$plugin_admin = new Ordv_Shipper_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( "after_setup_theme",					$plugin_admin, "load_carbon_fields",		10);
		$this->loader->add_action( "carbon_fields_register_fields",		$plugin_admin, "add_plugin_options",		10);
		$this->loader->add_action( "carbon_fields_register_fields",		$plugin_admin, "add_location_options",		10);
		$this->loader->add_filter( "woocommerce_shipping_methods",		$plugin_admin, "modify_shipping_methods",	10);
		$this->loader->add_action( "admin_enqueue_scripts",				$plugin_admin, "enqueue_styles",		10);
		$this->loader->add_action( "admin_enqueue_scripts",				$plugin_admin, "enqueue_scripts",		10);
		$this->loader->add_action( "wp_ajax_get-locations",				$plugin_admin, "get_locations_by_ajax",		10);
		$this->loader->add_action( "carbon_fields_term_meta_container_saved", $plugin_admin, "save_custom_term_meta_area",		10);		

		$this->loader->add_filter( 'manage_edit-shop_order_columns',		$plugin_admin,'custom_shop_order_column', 20 );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column',	$plugin_admin,'custom_orders_list_column_content', 20, 2 );

		$this->loader->add_action( 'admin_action_shipper_create_order', 	$plugin_admin, 'action_shipper_create_order' );		
		
		$this->loader->add_filter( 'admin_footer-edit.php', 				$plugin_admin, 'set_pickup_time_form');
		$this->loader->add_action( 'admin_action_set_pickup_time', 			$plugin_admin, 'action_set_pickup_time' );

		$this->loader->add_action( 'wp_ajax_get_data_status',				$plugin_admin, 'get_data_status' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_data_status',		$plugin_admin, 'get_data_status' );	

		$this->loader->add_action( 'wp_ajax_get_time_slots',				$plugin_admin, 'get_time_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_time_slots',			$plugin_admin, 'get_time_slots' );
				
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ordv_Shipper_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( "woocommerce_add_to_cart",		$plugin_public, "check_cart", 1, 6);

		$plugin_checkout = new Ordv_Shipper_Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'woocommerce_checkout_fields',					$plugin_checkout, 'remove_checkout_field' );
		$this->loader->add_filter( 'woocommerce_checkout_fields',					$plugin_checkout, 'add_checkout_fields', 15 );
		$this->loader->add_filter( 'woocommerce_states',							$plugin_checkout, 'change_province_name' );
		$this->loader->add_filter( 'woocommerce_shipping_package_name',				$plugin_checkout, 'custom_shipping_package_name', 10, 3 );
		$this->loader->add_action( 'woocommerce_checkout_billing',					$plugin_checkout, 'load_checkout_scripts' );				
		
		$this->loader->add_filter( 'woocommerce_default_address_fields' , 			$plugin_checkout, 'override_default_address_fields', 999 );
		$this->loader->add_filter( 'woocommerce_cart_needs_shipping',				$plugin_checkout, 'filter_cart_needs_shipping' );

		$this->loader->add_action( 'wp_ajax_get_data_area', 						$plugin_checkout, 'get_data_area' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_data_area',					$plugin_checkout, 'get_data_area' );

		$this->loader->add_action( 'wp_ajax_get_data_services_first_time',			$plugin_checkout, 'get_data_services' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_data_services_first_time',	$plugin_checkout, 'get_data_services' );

		$this->loader->add_action( 'wp_ajax_get_data_services', 					$plugin_checkout, 'get_data_services' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_data_services',				$plugin_checkout, 'get_data_services' );

		$this->loader->add_action( 'woocommerce_checkout_create_order', 			$plugin_checkout, 'save_order_custom_meta_data', 10, 2 );

		$plugin_thank_you = new Ordv_Shipper_Thankyou( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'woocommerce_thankyou', 						$plugin_thank_you, 'wc_register_guests', 10, 1 );
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
	 * @return    Ordv_Shipper_Loader    Orchestrates the hooks of the plugin.
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

}
