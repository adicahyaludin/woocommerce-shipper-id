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
	public function ordv_shipper_enqueue_scripts() {

		$screen = get_current_screen();

		if ( $screen->base === 'term' ) :

			wp_enqueue_script( $this->plugin_name.'-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], $this->version, true );
			wp_enqueue_script( $this->plugin_name, ORDV_SHIPPER_URI.'admin/js/ordv-shipper-admin.js', ['jquery',$this->plugin_name.'-select2'], $this->version, true );
			wp_localize_script( $this->plugin_name, 'osa_vars',[
				'get_locations_nonce' => wp_create_nonce('get-locations-by-ajax' )
			] );
		endif;

		if( 'edit' === $screen->base && 'shop_order' === $screen->post_type  ):

			wp_enqueue_script( $this->plugin_name, ORDV_SHIPPER_URI.'admin/js/ordv-shipper-order.js', array( 'jquery', 'jquery-ui-dialog' ), $this->version, true );

			$settings = array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
                'update_status'      => [
                    'action'    => 'get_data_status',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
				],
				'get_time_slots'	=> [
					'action'	=> 'get_time_slots',
					'nonce'		=> wp_create_nonce( 'ajax-nonce' )
				],
				'create_order' => [
					'action'	=> 'shipper_create_order',
					'nonce'		=> wp_create_nonce( 'ajax-nonce' )
				],
				'set_pickup_time' => [
					'action'	=> 'set_pickup_time',
					'nonce'		=> wp_create_nonce( 'ajax-nonce' )
				]
            );

            wp_localize_script( $this->plugin_name, 'oso_vars', $settings);

		endif;


	}

	/**
	 * Enqueue styles
	 * Hooked via action admin_enqueue_scripts, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function ordv_shipper_enqueue_styles() {

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
	public function ordv_shipper_load_carbon_fields() {

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
	public function ordv_shipper_add_plugin_options() {

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
	public function ordv_shipper_add_location_options() {

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
	public function ordv_shipper_modify_shipping_methods( $methods ) {

		require_once( plugin_dir_path( dirname( __FILE__ )) . "includes/class-ordv-shipping-method.php");

		$methods["ordv-shipper"] = "Ordv_Shipper_Shipping_Method";

		return $methods;
	}

	/**
	 * get_locations_by_ajax
	 * hooked via action wp_ajax_get-locations, priority 10
	 * @return json
	 */
	public function ordv_shipper_get_locations_by_ajax() {

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
	public function ordv_shipper_save_custom_term_meta_area() {

		if ( isset( $_POST['origin_area'] ) ) :

			update_term_meta( $_POST['tag_ID'],'_origin_area_id', $_POST['origin_area'] );
			update_term_meta( $_POST['tag_ID'],'_origin_area_text', $_POST['origin_area_text'] );

		endif;

	}

	/**
	 * ordv_shipper_custom_shop_order_column
	 * 
	 * Add additional column in list order in woocommerce > orders ( Admin dashboard )
	 * 
	 * @uses hooked via filter manage_edit-shop_order_columns 
	 * @since 1.0.0
	 * @param $columns
	 */

	public function ordv_shipper_custom_shop_order_column($columns)
	{
		$reordered_columns = array();

		// Inserting columns to a specific location
		foreach( $columns as $key => $column){
			$reordered_columns[$key] = $column;
			if( $key ==  'wc_actions' ){
				// Inserting after "Status" column
				$reordered_columns['shipper'] = __( 'Shipper','plugin_domain');
			}
		}
		return $reordered_columns;
	}


	// Adding custom fields meta data for each new column (example)

	public function ordv_shipper_custom_orders_list_column_content( $column, $post_id )
	{
		switch ( $column )
		{
			case 'shipper' :

				ob_start();
				include ORDV_SHIPPER_PATH.'admin/partials/order/order-column.php';
				echo ob_get_clean();

				break;
		}
	}

	public function ordv_shipper_action_shipper_create_order(){

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }
		$order_id = $_POST['i'];

		$data_order_shipper = ordv_shipper_fn_create_order_shipper( $order_id );

		$order = wc_get_order( $order_id );
		$order_shipper_id = $data_order_shipper->order_id;

		// save to meta data order id
		update_post_meta( $order_id, 'order_shipper_id', $order_shipper_id );
		update_post_meta( $order_id, 'is_activate', 0 );

		// If order is "processing" update status to "waiting for delivery"
		if( $order->has_status( 'processing' ) ) {
			$order->update_status('wc-waiting-delivery');
			$order->save();
		}

		$result = 'ok';
		wp_send_json( $result );
		wp_die();


	}


	public function ordv_shipper_set_pickup_time_form(){

		$currentScreen = get_current_screen();
		if( 'woocommerce' === $currentScreen->parent_base && 'shop_order' === $currentScreen->post_type  ){
			?>

				<div id="my-content-id-x" title="Pilih Waktu Penjemputan" style="display:none">
					<div id="div-inside"></div>
				</div>

			<?php

		}else{
			// do nothing
		}

	}


	public function ordv_shipper_action_set_pickup_time(){

		if(isset($_POST['data']))
		{
			parse_str($_POST['data'], $data);

			$order_id = $data['order_id'];
			$data_time = $data['pickup_time'];

			if( $data_time ){

				$id_shipper_order = get_post_meta($order_id, 'order_shipper_id', true);

					$data = explode("|" , $data_time );
					$date_start = $data[0];
					$date_end	= $data[1];

					$get_pickup_data = ordv_shipper_fn_do_pickup_order( $id_shipper_order, $date_start, $date_end );

					// save pickup data
					update_post_meta( $order_id, 'pickup_code', $get_pickup_data->pickup_code );
					update_post_meta( $order_id, 'is_activate', $get_pickup_data->is_activate );
					update_post_meta( $order_id, 'pickup_time', $get_pickup_data->pickup_time );

					// $order = wc_get_order( $order_id );

					// if( $order->has_status( 'waiting-delivery' ) ) {
					// 	$order->update_status('wc-in-shipping');
					// 	$order->save();
					// }

			}else{

				// do nothing

			}

		}else{

			// do nothing

		}

		$result = 'ok';
		wp_send_json( $result );
		wp_die();

	}

	public function ordv_shipper_get_data_status(){

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

		$order_id = $_POST['o'];
		$data_status = ordv_shipper_fn_get_updated_status( $order_id );

		$data_order_code = $data_status['latest_code'];
		$data_order_status = $data_status['latest_status'];

		update_post_meta( $order_id, 'status_code', $data_order_code );
		update_post_meta( $order_id, 'status_tracking', $data_order_status );

		$order = wc_get_order( $order_id );

		if( 1190 === $data_order_code || 1180 === $data_order_code || 1170 === $data_order_code ){

			$order->update_status('wc-in-shipping');
        	$order->save();

		}

		if( 2000 === $data_order_code ){

			$order->update_status('wc-completed');
        	$order->save();

		}

		$arr_data = array(
			'order_code' => $data_order_code,
			'order_status' => $data_order_status
		);

		wp_send_json( $arr_data );
        wp_die();

	}

	public function ordv_shipper_get_time_slots(){

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

		$order_id = $_POST['o_id'];

		ob_start();
		include ORDV_SHIPPER_PATH.'admin/partials/order/time-slots.php';
		echo ob_get_clean();

		wp_die();

	}

	public function ordv_shipper_register_custom_shipping_status(){
		register_post_status(
			'wc-waiting-delivery',
			array(
				'label'		=> 'Waiting for delivery',
				'public'	=> true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				'exclude_from_search'       => false,
				'label_count'	=> _n_noop( 'Waiting for delivery (%s)', 'Waiting for delivery (%s)' )
			)
		);

		register_post_status(
			'wc-in-shipping',
			array(
				'label'		=> 'In Shipping',
				'public'	=> true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				'exclude_from_search'       => false,
				'label_count'	=> _n_noop( 'In Shipping (%s)', 'In Shipping (%s)' )
			)
		);

	}

	public function ordv_shipper_shipper_add_status_to_list( $order_statuses ){

		$new = array();

		foreach ( $order_statuses as $id => $label ) {

			if ( 'wc-completed' === $id ) { // before "Completed" status
				$new[ 'wc-waiting-delivery' ]	= 'Waiting for delivery';
				$new[ 'wc-in-shipping' ] 		= 'In Shipping';
			}

			$new[ $id ] = $label;

		}

		return $new;

	}

	public function get_check_code(){

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

		$order_id = $_POST['i'];

		$test_code = get_post_meta( $order_id, 'test_code', true );

		if( !$test_code){

			update_post_meta( $order_id, 'test_code', 1 );

		}else{
			// do nothing
		}
		wp_send_json('ok');
		wp_die();

	}

}
