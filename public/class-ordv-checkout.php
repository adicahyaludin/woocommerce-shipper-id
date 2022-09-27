<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ordv_Shipper
 * @subpackage Ordv_Shipper/public
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Ordv_Shipper_Checkout {

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

		$this->plugin_name          = $plugin_name;
		$this->version              = $version;
        $this->endpoint_province   = '/v3/location/country/'.COUNTRY_ID_NUM.'/provinces';

	}

    public function ordv_shipper_change_province_name( $states ){
        
        $states['ID']['AC'] = 'Aceh';
        $states['ID']['YO'] = 'DI Yogyakarta';
        
        return $states;
        
    }
	
    public function ordv_shipper_remove_checkout_field( $fields ){
        
        unset($fields['billing']['billing_company']);           // remove company field
        unset($fields['billing']['billing_address_1']);         // remove billing address 1 field
        unset($fields['billing']['billing_address_2']);         // remove billing address 2 field
        unset($fields['billing']['billing_city']);              // remove billing city field
        unset($fields['billing']['billing_state']);             // remove billing state field
        
        // return field
        return $fields;

    }

    public function ordv_shipper_add_checkout_fields( $fields ){

        $fields['billing']['billing_address_1'] = array(
            'type'      => 'textarea',
            'label'     => __('Address', 'woocommerce'),
            'placeholder'   => _x('Nama jalan dan nomor rumah', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'priority'  => 35
         );


        if ( is_user_logged_in() ) {

            $user_id = get_current_user_id();


            $user_order_area_id = get_user_meta( $user_id, 'user_order_area_id', true );
            $user_order_area_text = get_user_meta( $user_id, 'user_order_area_text', true );
            $user_order_area_lat = get_user_meta( $user_id, 'user_order_area_lat', true );
            $user_order_area_lng = get_user_meta( $user_id, 'user_order_area_lng', true );

            
            if( $user_order_area_id && $user_order_area_text ){

                $fields['billing']['ordv-area'] = array(
                    'type'      => 'select',
                    'label'     => __('Kelurahan / Desa', 'woocommerce'),
                    'placeholder'   => _x('Pilih Kelurahan / Desa...', 'placeholder', 'woocommerce'),
                    'required'  => true,
                    'class'     => array('form-row-wide'),
                    'clear'     => true,
                    'options'   => array( 
                                    $user_order_area_id => $user_order_area_text
                                ),
                    'priority'  => 82
                );

                $fields['billing']['ordv-area']['data-lat'] = $user_order_area_lat;
                $fields['billing']['ordv-area']['data-lng'] = $user_order_area_lng;


            }else{

                $fields['billing']['ordv-area'] = array(
                    'type'      => 'select',
                    'label'     => __('Kelurahan / Desa', 'woocommerce'),
                    'placeholder'   => _x('Pilih Kelurahan / Desa...', 'placeholder', 'woocommerce'),
                    'required'  => true,
                    'class'     => array('form-row-wide'),
                    'clear'     => true,
                    'options'   => array( '' => '' ),
                    'priority'  => 82
                );
            }
            
        } else {

            $fields['billing']['ordv-area'] = array(
                'type'      => 'select',
                'label'     => __('Kelurahan / Desa', 'woocommerce'),
                'placeholder'   => _x('Pilih Kelurahan / Desa...', 'placeholder', 'woocommerce'),
                'required'  => true,
                'class'     => array('form-row-wide'),
                'clear'     => true,
                'options'   => array( '' => '' ),
                'priority'  => 82
            );            
        }
        
        $fields['billing']['billing_city'] = array(
            'type'      => 'hidden',
            'label'     => __('city', 'woocommerce'),
            'placeholder'   => _x('', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'priority'  => 86
        );

        //$fields['billing']['billing_state']['label'] = false;
        $fields['billing']['billing_city']['label'] = false;
    
        return $fields;
    }

    public function ordv_shipper_override_default_address_fields( $address_fields ) {
        $address_fields['state']['required'] = false;
        return $address_fields;
    }

    public function ordv_shipper_load_checkout_scripts(){

        $style = '#billing_country_field, #shipping_country_field{ display: none !important; }';
        $style .= '#billing_delivery_option_field.radio {display: inline !important; margin-left: 5px;}';
        $style .= '#billing_delivery_option_field input[type="radio"] { margin-left: 0px;}';
        $style .= '#billing_delivery_option_field input[type="radio"] + label { display: inline-block; margin-right:15px; }';
        echo '<style>'.$style.'</style>'."\n";

        if ( is_checkout() ) {

            WC()->session->set( 'data_kurir', null );

            wp_enqueue_script( 'checkout-script', plugin_dir_url( __DIR__ ). 'public/js/ordv-shipper-checkout.js', array( 'jquery', 'selectWoo' ), ORDV_SHIPPER_VERSION, true );
            
            $settings = array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),               
                'area'      => [
                    'action'    => 'get_data_area',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ],
                'get_services_first_time' => [
                    'action'    => 'get_data_services_first_time',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ],
                'get_services' => [
                    'action'    => 'get_data_services',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ]
            );

            wp_localize_script( 'checkout-script', 'checkout_ajax', $settings);
        }        
    }


    public function ordv_shipper_get_data_area(){

        if ( wp_verify_nonce( $_GET['nonce'], 'ajax-nonce' ) ) {


            $data = array();
            $keyword = $_GET['search'];
            
            if( $keyword ){

                $get_data_area = ordv_shipper_fn_get_list_area( $keyword );

                foreach ($get_data_area as $key => $area) {
                    
                    if ( isset( $area->adm_level_cur->id ) ) :

						$data[] = [
                            'id' 	=> $area->adm_level_cur->id,
                            'text' 	=> $area->display_txt,
                            'lat' => $area->adm_level_cur->geo_coord->lat,
                            'lng' => $area->adm_level_cur->geo_coord->lng,
                            'postcode' => $area->adm_level_cur->postcode
						];
                        

					endif;
                }
            }

            WC()->session->__unset( 'data_kurir');
            wp_send_json( $data );

        }
    }

    public function ordv_shipper_get_data_services(){

        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

        $get_action  = $_POST['action'];


        if( 'get_data_services_first_time' === $get_action ){

            if ( is_user_logged_in() ) {

                $user_id = get_current_user_id();                                
                $user_order_area_lat = get_user_meta( $user_id, 'user_order_area_lat', true );
                $user_order_area_lng = get_user_meta( $user_id, 'user_order_area_lng', true );
            
            }

            $api_d_area_id  = intval( $_POST['a'] );
            $area_id_lat    = $user_order_area_lat;
            $area_id_lng    = $user_order_area_lng;

        }else{

            $api_d_area_id  = intval( $_POST['a'] );
            $area_id_lat    = $_POST['a_lat'];
            $area_id_lng    = $_POST['a_lng'];

        }

        $data_packages  = ordv_shipper_fn_get_packages_data();

        $data_list_kurir = ordv_shipper_fn_get_data_list_kurir( $api_d_area_id, $area_id_lat, $area_id_lng, $data_packages );

        // set session data for add_rates
        WC()->session->set( 'data_kurir' , $data_list_kurir );

        $data = array(
            'id'    => $api_d_area_id,
            'lat'   => $area_id_lat,
            'lng'   => $area_id_lng
        );

        // save data for session
        WC()->session->set( 'data_area' , $data );

        $result = 'ok';
        wp_send_json( $result );
        wp_die();

    }


    public function ordv_shipper_custom_shipping_package_name( $name ){
        
        $name           = 'Pengiriman';
        $packages       = ordv_shipper_fn_get_packages_data();

        $active_weight_unit     = get_option('woocommerce_weight_unit');
        $active_dimension_unit  = get_option('woocommerce_dimension_unit');

        $total_weight   = $packages['weight'];
        $total_height   = $packages['height'];
        $total_width    = $packages['width'];
        $total_length   = $packages['length'];
        $origin_text    = $packages['origin_text'];

        $name       .= '<br/><small>dari '.$origin_text.'</small>';
        $name       .= '<br/><small>berat '.$total_weight.' '.$active_weight_unit;
        $name       .= '</small>';
        $name       .= '<br/><small>ukuran '.$total_length.'x'.$total_width.'x'.$total_height.' '.$active_dimension_unit.'</small>';
                
        return $name;
        
    }

    public function update_order_review( $posted_data ){
        
        // Parsing posted data on checkout
        $post = array();
        $vars = explode('&', $posted_data);
        foreach ($vars as $k => $value){
            $v = explode('=', urldecode($value));
            $post[$v[0]] = $v[1];
        }

        $packages = WC()->cart->get_shipping_packages();
        foreach ($packages as $package_key => $package) {
            $session_key = 'shipping_for_package_'.$package_key;
            $stored_rates = WC()->session->__unset($session_key);
        }

    }
    
    public function ordv_shipper_filter_cart_needs_shipping( $needs_shipping ) {
        if ( is_cart() ) {
            $needs_shipping = false;
        }
        return $needs_shipping;
    }

    public function ordv_shipper_save_order_custom_meta_data(  $order, $data  ){
        
                
        if ( isset( $_POST['shipping_method'][0] ) ){
            $order->update_meta_data('rate_id', $_POST['shipping_method'][0] );
        }

        if ( isset( $_POST['ordv-area'] ) ){
            $order->update_meta_data('d_area_id', $_POST['ordv-area'] );
        }

        $data_dest_cord = WC()->session->get( 'dest_cord' );

        if( $data_dest_cord ){
            $order->update_meta_data('d_lat_area_id', $data_dest_cord['lat'] );
            $order->update_meta_data('d_lng_area_id', $data_dest_cord['lng'] );
        }

        WC()->session->__unset( 'dest_cord');

    }

    public function ordv_shipper_shipper_additional_detail( $order ){

        $status_tracking = get_post_meta( $order->get_id(), 'status_tracking', true );
        
        if( $status_tracking ){
            $nama = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $status_tracking = str_replace('[receiver_name]', $nama, $status_tracking);

            ?>
                <tr>
                    <th scope="row">Delivery Status:</th>
                    <td><?php echo esc_html( $status_tracking ) ?></td>
                </tr>
		    <?php

        }else{

            ?>
                <tr>
                    <th scope="row">Delivery Status:</th>
                    <td>-</td>
                </tr>
		    <?php

        }

    }


}
