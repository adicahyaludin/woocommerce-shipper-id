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
	 * Enqueue scripts
	 * Hooked via action admin_enqueue_scripts, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();

		if ( $screen->base === 'term' ) :

			wp_enqueue_script( $this->plugin_name.'-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], $this->version, true );
			wp_enqueue_script( $this->plugin_name, ORDV_SHIPPER_URI.'admin/js/ordv-shipper-admin.js', ['jquery',$this->plugin_name.'-select2'], $this->version, true );
			wp_localize_script( $this->plugin_name, 'osa_vars',[
				'get_locations_nonce' => wp_create_nonce('get-locations-by-ajax' )
			] );
		endif;

	}

	/**
	 * Enqueue styles
	 * Hooked via action admin_enqueue_scripts, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();

		if ( $screen->base === 'term' ) :

			wp_enqueue_style( $this->plugin_name.'-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' );
			wp_enqueue_style( $this->plugin_name, ORDV_SHIPPER_URI.'admin/css/ordv-shipper-admin.css' );

		endif;

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
					->set_help_text( __("Select product attribute that defines location", "ordv-shipper")),

				Field::make( "text", "shipper_api_key", __("API Key", "ordv-shipper"))
					->set_default_value( 'l13MjiFynGWgWeT8ACZuDeG8SxqCeoG2eOJs6TF0YUTy5cs4PIn6CisaRqVjnb59' ),
			]);

	}

	/**
	 * Add location options based on product attribute selected on plugin options
	 * Hooekd via action carbon_fields_register_fields, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function add_location_options() {

		$shipper_courier_origin_area = '<h3>Origin - Area</h3>';
		$shipper_courier_origin_area .= '<select class="origin-area" name="origin_area">';

		$area_text = '';
		if ( isset( $_GET['tag_ID'] ) ) :
			$area_id   = get_term_meta( $_GET['tag_ID'], '_origin_area_id', true);
			$area_text = get_term_meta( $_GET['tag_ID'], '_origin_area_text', true);
			if ( $area_id && $area_text ) :
				$shipper_courier_origin_area .= '<option value="'.$area_id.'" selected>'.$area_text.'</option>';
			endif;	
		endif;
		
		$shipper_courier_origin_area .= '</select>';
		$shipper_courier_origin_area .= '<input type="hidden" id="origin_area_text" name="origin_area_text" value="'.$area_text.'">';

		Container::make( "term_meta", __("Location Setup", "ordv-shipper"))
			->where( "term_taxonomy", "=", "pa_" . carbon_get_theme_option("shipper_location_term") )
			->add_fields([
				Field::make( "html", "shipper_courier_origin_area", __("Origin - Area", "ordv-shipper"))
					->set_html( $shipper_courier_origin_area ),
				Field::make( "text", "shipper_courier_origin_lat", __("Origin - Lat", "ordv-shipper")),
				Field::make( "text", "shipper_courier_origin_lng", __("Origin - Lng", "ordv-shipper")),
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

	/**
	 * get_locations_by_ajax
	 * hooked via action wp_ajax_get-locations, priority 10
	 * @return json
	 */
	public function get_locations_by_ajax() {

		if ( isset( $_GET['nonce'] ) && 
			wp_verify_nonce($_GET['nonce'],'get-locations-by-ajax' ) ) :

			$data = [];

			$_request = wp_parse_args($_GET,[
				'search' => '',
			] );

			if ( $_request['search'] ) :

				$locations = ordv_shipper_get_locations( $_request['search'] );
			
				foreach ( $locations as $key => $location ) :
			
					if ( isset( $location->adm_level_cur->id ) ) :

						$data[] = [
							'id' 	=> $location->adm_level_cur->id,
							'text' 	=> $location->display_txt,
						];

					endif;

				endforeach;
			
			endif;

			wp_send_json( $data );

		endif;

	}

	/**
	 * save custom term meta
	 * hooked via action carbon_fields_term_meta_container_saved, priority 10
	 * @return void
	 */
	public function save_custom_term_meta_area() {

		if ( isset( $_POST['origin_area'] ) ) :

			update_term_meta( $_POST['tag_ID'],'_origin_area_id', $_POST['origin_area'] );
			update_term_meta( $_POST['tag_ID'],'_origin_area_text', $_POST['origin_area_text'] );

		endif;

	}
}
