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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/check-awb.php';

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

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-check-awb.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-edit-address-billing.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ordv-view-orders.php';

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
		
		$this->loader->add_action( "after_setup_theme",							$plugin_admin, "ordv_shipper_load_carbon_fields", 10);
		$this->loader->add_action( "carbon_fields_register_fields",				$plugin_admin, "ordv_shipper_add_plugin_options", 10);
		$this->loader->add_action( "carbon_fields_register_fields",				$plugin_admin, "ordv_shipper_add_location_options", 10);
		$this->loader->add_filter( "woocommerce_shipping_methods",				$plugin_admin, "ordv_shipper_modify_shipping_methods", 10);
		$this->loader->add_action( "admin_enqueue_scripts",						$plugin_admin, "ordv_shipper_enqueue_styles", 10);
		$this->loader->add_action( "admin_enqueue_scripts",						$plugin_admin, "ordv_shipper_enqueue_scripts", 10);
		$this->loader->add_action( "wp_ajax_get-locations",						$plugin_admin, "ordv_shipper_get_locations_by_ajax", 10);
		$this->loader->add_action( "carbon_fields_term_meta_container_saved", 	$plugin_admin, "ordv_shipper_save_custom_term_meta_area", 10);		

		$this->loader->add_filter( 'manage_edit-shop_order_columns',			$plugin_admin,'ordv_shipper_custom_shop_order_column', 20 );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column',		$plugin_admin,'ordv_shipper_custom_orders_list_column_content', 20, 2 );

		$this->loader->add_action( 'wp_ajax_shipper_create_order',				$plugin_admin, 'ordv_shipper_action_shipper_create_order' );		
		$this->loader->add_filter( 'admin_footer-edit.php', 					$plugin_admin, 'ordv_shipper_set_pickup_time_form');		
		$this->loader->add_action( 'wp_ajax_set_pickup_time',					$plugin_admin, 'ordv_shipper_action_set_pickup_time' );
		$this->loader->add_action( 'wp_ajax_get_data_status',					$plugin_admin, 'ordv_shipper_get_data_status' );
		$this->loader->add_action( 'wp_ajax_get_time_slots',					$plugin_admin, 'ordv_shipper_get_time_slots' );

		$this->loader->add_action( 'init', 										$plugin_admin, 'ordv_shipper_register_custom_shipping_status' );
		$this->loader->add_filter( 'wc_order_statuses',							$plugin_admin, 'ordv_shipper_shipper_add_status_to_list' );
		
		


			
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

		$this->loader->add_action( "woocommerce_add_to_cart",				$plugin_public, "ordv_shipper_check_cart", 1, 6);

		$plugin_checkout = new Ordv_Shipper_Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'woocommerce_checkout_fields',						$plugin_checkout, 'ordv_shipper_remove_checkout_field' );
		$this->loader->add_filter( 'woocommerce_checkout_fields',						$plugin_checkout, 'ordv_shipper_add_checkout_fields', 15 );
		$this->loader->add_filter( 'woocommerce_states',								$plugin_checkout, 'ordv_shipper_change_province_name' );
		$this->loader->add_filter( 'woocommerce_shipping_package_name',					$plugin_checkout, 'ordv_shipper_custom_shipping_package_name', 10, 3 );
		$this->loader->add_action( 'woocommerce_checkout_billing',						$plugin_checkout, 'ordv_shipper_load_checkout_scripts' );				
		
		$this->loader->add_filter( 'woocommerce_default_address_fields' , 				$plugin_checkout, 'ordv_shipper_override_default_address_fields', 999 );
		$this->loader->add_filter( 'woocommerce_cart_needs_shipping',					$plugin_checkout, 'ordv_shipper_filter_cart_needs_shipping' );

		$this->loader->add_action( 'wp_ajax_get_data_area', 							$plugin_checkout, 'ordv_shipper_get_data_area' );
		$this->loader->add_action( 'wp_ajax_get_data_services_first_time',				$plugin_checkout, 'ordv_shipper_get_data_services' );
		$this->loader->add_action( 'wp_ajax_get_data_services', 						$plugin_checkout, 'ordv_shipper_get_data_services' );
		$this->loader->add_action( 'woocommerce_checkout_create_order', 				$plugin_checkout, 'ordv_shipper_save_order_custom_meta_data', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_details_after_order_table_items', $plugin_checkout, 'ordv_shipper_shipper_additional_detail' );


		$plugin_thank_you = new Ordv_Shipper_Thankyou( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'woocommerce_thankyou', 						$plugin_thank_you, 'ordv_shipper_wc_register_guests', 10, 1 );

		$plugin_check_awb = new Ordv_Shipper_Check_Awb( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts',						$plugin_check_awb, 'ordv_shipper_cek_resi_scripts_load' );
		$this->loader->add_action( 'rest_api_init', 							$plugin_check_awb, 'ordv_add_callback_url_endpoint', 10 );
		$this->loader->add_action( 'init', 										$plugin_check_awb, 'ordv_shipper_register_check_awb_endpoint');
		$this->loader->add_filter( 'query_vars',								$plugin_check_awb, 'ordv_shipper_check_awb_query_vars' );
		$this->loader->add_filter( 'woocommerce_account_menu_items',			$plugin_check_awb, 'ordv_shipper_add_check_awb_tab' );
		$this->loader->add_action( 'woocommerce_account_check-awb_endpoint', 	$plugin_check_awb, 'ordv_shipper_add_check_awb_content' );
		$this->loader->add_filter ( 'woocommerce_account_menu_items',			$plugin_check_awb, 'ordv_shipper_reorder_account_menu' );

		$this->loader->add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', $plugin_check_awb, 'ordv_shipper_handle_order_number_custom_query_var', 10, 2 );
		
		$this->loader->add_action( 'wp_ajax_cek_resi_data',						$plugin_check_awb, 'ordv_shipper_cek_resi_data' );
		$this->loader->add_action( 'wp_ajax_get_resi_detail',					$plugin_check_awb, 'ordv_shipper_get_resi_detail' );

		$plugin_edit_address_billing = new Ordv_Shipper_Edit_Address_Billing( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts',					$plugin_edit_address_billing, 'ordv_shipper_load_additonal_styles_scripts' );
		$this->loader->add_filter( 'woocommerce_default_address_fields',	$plugin_edit_address_billing, 'ordv_shipper_edit_billing_add_field' );
		$this->loader->add_action( 'wp_ajax_get_edit_data_area',			$plugin_edit_address_billing, 'ordv_shipper_get_edit_data_area' );
		$this->loader->add_action( 'woocommerce_customer_save_address', 	$plugin_edit_address_billing,'ordv_shipper_save_custom_billing_field_data');
				
		$plugin_view_order = new Ordv_Shipper_View_Order( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'woocommerce_view_order', 			$plugin_view_order, 'ordv_shipper_add_style');
		$this->loader->add_action( 'woocommerce_after_order_details',	$plugin_view_order, 'ordv_shipper_add_delivery_details' );

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
