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
class Ordv_Shipper_Edit_Address_Billing {

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

	}


    public function ordv_shipper_load_additonal_styles_scripts(){

        global $wp;
        $current_url    = home_url(add_query_arg(array(),$wp->request));
        $billing        = home_url('/my-account/edit-address/billing');

        if( is_wc_endpoint_url('edit-address') && $current_url === $billing ){
            
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __DIR__ ). 'public/js/ordv-shipper-edit-address-billing.js', ['jquery', 'selectWoo'], $this->version, true );
            $settings = array(
                'ajax_url'  => admin_url( 'admin-ajax.php' ),               
                'edit_area'      => [
                    'action'    => 'get_edit_data_area',
                    'nonce'     => wp_create_nonce( 'ajax-nonce' )
                ]
            );

            wp_localize_script( $this->plugin_name, 'edit_billing_data', $settings);            
            
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __DIR__ ). 'public/css/ordv-shipper-edit-address-billing.css' );
        }

    }

    public function ordv_shipper_edit_billing_add_field( $fields ){

        global $wp;
        $current_url    = home_url(add_query_arg(array(),$wp->request));
        $billing        = home_url('/my-account/edit-address/billing');

        if( is_wc_endpoint_url('edit-address') && $current_url === $billing ){

            $user_id = get_current_user_id();

            $user_order_area_id = get_user_meta( $user_id, 'user_order_area_id', true );
            $user_order_area_text = get_user_meta( $user_id, 'user_order_area_text', true );
            // $user_order_area_lat = get_user_meta( $user_id, 'user_order_area_lat', true );
            // $user_order_area_lng = get_user_meta( $user_id, 'user_order_area_lng', true );

                
            if( $user_order_area_id && $user_order_area_text ){

                $fields['ordv-edit-billing-kelurahan'] = array(
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

            }else{

                $fields['ordv-edit-billing-kelurahan'] = array(
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

        }

        return $fields;

    }


    public function ordv_shipper_get_edit_data_area(){

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

    public function ordv_shipper_save_custom_billing_field_data( $user_id ){

        $new_area_id = $_POST['billing_ordv-edit-billing-kelurahan'];
        $new_area_text = $_POST['billing_city'];

        update_user_meta( $user_id, 'user_order_area_id', $new_area_id );
        update_user_meta( $user_id, 'user_order_area_text', $new_area_text  );

    }


}
