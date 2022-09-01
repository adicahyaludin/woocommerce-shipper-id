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

    public function change_province_name( $states ){
        $states['ID']['AC'] = 'Aceh';
        $states['ID']['YO'] = 'DI Yogyakarta';
        
        return $states;
        
    }

	

    public function show_all_fields( $fields ){
    
        $api_url_province = API_URL.''.$this->endpoint_province;

        $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => API_KEY
        ));

        $request = wp_remote_get(
            $api_url_province,
            $args
        );

        $body = wp_remote_retrieve_body( $request );

        $data_api       = json_decode($body);
        $data_province  = $data_api->data;



        $select_province = 'DI Yogyakarta';

        $selected_province_data = [];

        foreach ($data_province as $d) {
            if( $select_province === $d->name ){
                array_push( $selected_province_data, $d );
            }
        }

        echo '<pre>';
        print_r( $selected_province_data[0]->id );
        echo '</pre>';

    }

    public function remove_checkout_field( $fields ){

        
        unset($fields['billing']['billing_company']);           // remove company field
        unset($fields['billing']['billing_address_1']);         // remove billing address 1 field
        unset($fields['billing']['billing_address_2']);         // remove billing address 2 field
        unset($fields['billing']['billing_city']);              // remove billing city field
        
        // return field
        return $fields;

    }

    public function add_checkout_fields( $fields ){
        $fields['billing']['ordv-complete-address'] = array(
            'type'      => 'textarea',
            'label'     => __('Address', 'woocommerce'),
            'placeholder'   => _x('Nama jalan dan nomor rumah', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'priority'  => 35
         );

         $fields['billing']['ordv-kotakab'] = array(
            'type'      => 'select',
            'label'     => __('Kota / Kabupaten', 'woocommerce'),
            'placeholder'   => _x('Pilih Kota / Kabupaten...', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'options'   => array(
                            'opt_1' => '',
                           ),
            'priority'  => 82
         );

         $fields['billing']['ordv-kecamatan'] = array(
            'type'      => 'select',
            'label'     => __('Kecamatan', 'woocommerce'),
            'placeholder'   => _x('Pilih Kecamatan...', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'options'   => array(
                'opt_1' => '',
            ),
            'priority'  => 83
         );

         $fields['billing']['ordv-keldesa'] = array(
            'type'      => 'select',
            'label'     => __('Kelurahan / Desa', 'woocommerce'),
            'placeholder'   => _x('Pilih Kelurahan / Desa...', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'options'   => array(
                'opt_1' => '',
            ),
            'priority'  => 84
         );
         
    
         return $fields;
    }

    public function load_checkout_scripts(){

        $style = '#billing_country_field, #shipping_country_field { display: none; }';
        echo '<style>'.$style.'</style>'."\n";

        if ( is_checkout() ) {
            wp_enqueue_script( 'checkout-script', plugin_dir_url( __DIR__ ). 'public/js/ordv-shipper-checkout.js', array( 'jquery' ), ORDV_SHIPPER_VERSION, true );
            
            $settings = array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
                'city'      => [
                    'action'    => 'get_data_cities',
                    'nonce'     => wp_create_nonce('ajax-nonce')
                ],
                'kec'       => [
                    'action'    => 'get_data_kec',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ],
                'keldes'    => [
                    'action'    => 'get_data_keldes',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ],
                'area'      => [
                    'action'    => 'get_data_kurir',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ]            

            );

            wp_localize_script( 'checkout-script', 'checkout_ajax', $settings);
        }        
    }

    public function get_data_cities(){

        // Check for nonce security      
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

        $country_id     = $_POST['c'];
        $province_id    = $_POST['s'];

        $endpoint_province = $this->endpoint_province;

        $province_name      = get_province_name( $country_id, $province_id );
        $api_province_id    = get_api_province_id( $province_name, $endpoint_province );        
        $get_data_cities    = get_list_city( $api_province_id );
        
        echo json_encode( $get_data_cities );
        wp_die();

    }

    public function get_data_kec(){

        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

        $api_city_id    = $_POST['k'];
        $get_data_kec   = get_list_kec( $api_city_id );

        echo json_encode( $get_data_kec );
        wp_die();
    }

    public function get_data_keldes(){

        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

        $api_kec_id    = $_POST['d'];
        $get_data_keldesa = get_list_keldesa( $api_kec_id );

        echo json_encode( $get_data_keldesa ); 
        wp_die();

    }

    public function get_data_kurir(){

        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            die( 'Close The Door!');
        }

        $api_o_area_id  = 4711; // ganti dengan origin area id dari setting mas Adi
        $api_d_area_id  = $_POST['a'];

        $data_packages  = get_packages_data();

        $total_weight   = $data_packages['weight'];
        $total_height   = $data_packages['height'];
        $total_width    = $data_packages['width'];
        $total_length   = $data_packages['length'];
        $subtotal       = $data_packages['subtotal'];

        $endpoint_kurir = '/v3/pricing/domestic';
        $endpoint_url   = API_URL.''.$endpoint_kurir;

        $body = array(
            'cod' => false,
            'destination' => array(
                'area_id' => 25946,
            ),
            'origin' => array(
                'area_id' => 4711
            ),
            'height' => 10,
            'length' => 10,
            'weight' => 1,
            'width' => 10,
            'item_value' => 15
        );

        $body = wp_json_encode( $body );

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Api-Key' => API_KEY
            ),
            'body' => $body
            
        );


        $request = wp_remote_post(
            $endpoint_url,
            $args
        );

        $body               = wp_remote_retrieve_body( $request );
        $data_api           = json_decode($body);
        $data_list_kurir    = $data_api->data->pricings;

        // ob_start();
        // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/list-opsi-kurir.php';
        // $result = ob_get_contents();
        // ob_end_clean();

        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/list-opsi-kurir.php';
        $result = ob_get_clean();


        //echo json_encode( $result );
        wp_send_json( $result );
        wp_die();

    }

    public function custom_shipping_package_name( $name ){
        
        $name           = 'Pengiriman';
        $packages       = get_packages_data();

        $total_weight   = $packages['weight'];
        $total_height   = $packages['height'];
        $total_width    = $packages['width'];
        $total_length   = $packages['length'];

        $name       .= '<br/><small>dari </small>';
        $name       .= '<br/><small>berat '.$total_weight.' '.get_option( 'woocommerce_weight_unit' );
        $name       .= '</small>';
        $name       .= '<br/><small>ukuran '.$total_length.'x'.$total_width.'x'.$total_height.'cm</small>';
        
        return $name;
        
    }


}
