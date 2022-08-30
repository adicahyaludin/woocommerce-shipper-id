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

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	// /**
	//  * Check cart, remove any items in if not related to current item location
	//  * Hooked via action woocommerce_add_to_cart, priority 1
	//  * @since 	1.0.0
	//  * @param  	string 		$cart_item_key
	//  * @param  	integer 	$product_id
	//  * @param  	integer 	$quantity
	//  * @param  	integer 	$variation_id
	//  * @param  	[type] 		$variation
	//  * @param  	array 		$cart_item_data
	//  * @return 	void
	//  */
	// public function check_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

	// 	if( 0 !== absint($variation_id) ) :
	// 		$term = carbon_get_theme_option("shipper_location_term");
	// 		$location = wc_get_product( $variation_id )->get_attribute( "pa_" . $term );

	// 		foreach( WC()->cart->get_cart() as $cart_item ) :
	// 			if(
	// 				array_key_exists("variation", $cart_item) &&
	// 				array_key_exists("attribute_pa_" . $term, $cart_item["variation"]) &&
	// 				strtoupper(sanitize_url($location)) !== strtoupper(sanitize_url($cart_item["variation"]["attribute_pa_" . $term]))
	// 			) :
	// 				WC()->cart->remove_cart_item( $cart_item["key"] );
	// 			endif;
	// 		endforeach;

	// 	endif;
	// }

    public function show_all_fields( $fields ){
        
        echo '<pre>';
        print_r( $fields ); 
        echo '</pre>';
        
       
    }

    public function remove_checkout_field( $fields ){

        
        unset($fields['billing']['billing_company']);           // remove company field
        unset($fields['billing']['billing_country']);           // remove country field
        unset($fields['billing']['billing_address_1']);         // remove billing address 1 field
        unset($fields['billing']['billing_address_2']);         // remove billing address 2 field
        unset($fields['billing']['billing_city']);              // remove billing city field

        $fields['billing']['billing_state']['priority'] = 61; // re-order province field
        
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
            'priority'  => 62
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
            'priority'  => 64
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
            'priority'  => 66
         );
         
    
         return $fields;
    }

}
