<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Ordv_Shipper_Admin {

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
	 * Load carbon field library
	 * Hooked via action after_setup_theme, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function load_carbon_fields() {

		\Carbon_Fields\Carbon_Fields::boot();

	}

	/**
	 * Get attribute term options
	 * @since 	1.0.0
	 * @return 	array
	 */
	public function get_location_term_options() {

		$options = array();

		foreach( wc_get_attribute_taxonomies() as $id => $taxo ) :
			$options[$taxo->attribute_name] = $taxo->attribute_label;
		endforeach;

		return $options;
	}

	/**
	 * Add plugin options
	 * Hooked via action carbon_fields_register_fields, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function add_plugin_options() {

		Container::make( "theme_options", __("Shipper.id", "ordv-shipper"))
			->add_fields([
				Field::make( "checkbox", "shipper_demo", __("Demo Site", "ordv-shipper"))
					->set_help_text( __("If activated, it will use static cost field, not from shipper.id", "ordv-shipper")),

				Field::make( "select",	 "shipper_location_term", __("Produk Attribute", "ordv-shipper"))
					->add_options(array($this, "get_location_term_options"))
					->set_help_text( __("Select product attribute that defines location", "ordv-shipper"))
			]);

	}

	/**
	 * Add location options based on product attribute selected on plugin options
	 * Hooekd via action carbon_fields_register_fields, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function add_location_options() {

		Container::make( "term_meta", __("Location Setup", "ordv-shipper"))
			->where( "term_taxonomy", "=", "pa_" . carbon_get_theme_option("shipper_location_term") )
			->add_fields([
				Field::make( "text", "shipper_courier_cost", __("Courier Cost", "ordv-shipper"))
					->set_attribute( "type", "number")
					->set_default_value(0)
					->set_help_text( __("Only used if demo site is activated in plugin options", "ordv-shipper"))
			]);

	}

	/**
	 * Add custom shipping method
	 * Hooked via filter woocommerce_shipping_methods
	 * @since 	1.0.0
	 * @param  	array 	$methods
	 * @return 	array
	 */
	public function modify_shipping_methods( $methods ) {

		require_once( plugin_dir_path( dirname( __FILE__ )) . "includes/class-ordv-shipping-method.php");

		$methods["ordv-shipper"] = "Ordv_Shipper_Shipping_Method";

		return $methods;
	}
}
