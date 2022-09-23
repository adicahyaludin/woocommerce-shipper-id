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
class Ordv_Shipper_Check_Awb {

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

    /**
     * Register New Endpoint For Check Resi.
     *
     * @return void.
     */

	public function cek_resi_scripts_load(){

		if( shipper_is_wc_endpoint( 'check-awb') ){

			wp_enqueue_script( 'cek-resi-script', ORDV_SHIPPER_URI.'public/js/ordv-check-awb.js', array( 'jquery' ), ORDV_SHIPPER_VERSION, true );
			$settings = array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),               
				'cek_resi'      => [
					'action'    => 'cek_resi_data',
				]
			);

			wp_localize_script( 'cek-resi-script', 'cek_resi_ajax', $settings);

		}



	}

    public function register_check_awb_endpoint(){
        add_rewrite_endpoint( 'check-awb', EP_ROOT | EP_PAGES );
    }

    public function check_awb_query_vars( $vars ){
        $vars[] = 'check-awb';
	    return $vars;
    }

    public function add_check_awb_tab( $items ){
        $items['check-awb'] = 'Cek Resi';
	    return $items;
    }

    public function reorder_account_menu( $items ){
        return array(
	        'dashboard'          => __( 'Dashboard', 'woocommerce' ),
	        'orders'             => __( 'Orders', 'woocommerce' ),
            'check-awb'          => __( 'Cek Resi', 'woocommerce' ),
	        'downloads'          => __( 'Downloads', 'woocommerce' ),
	        'edit-account'       => __( 'Edit Account', 'woocommerce' ),	        
	        'edit-address'       => __( 'Addresses', 'woocommerce' ),
	        'customer-logout'    => __( 'Logout', 'woocommerce' ),
        );
    }

    public function add_check_awb_content(){

        ob_start();
        include ORDV_SHIPPER_PATH.'public/partials/ordv-check-awb-public-display.php';
        echo ob_get_clean();        

    }

    public function handle_order_number_custom_query_var( $query, $query_vars ){
        
        if ( ! empty( $query_vars['no_resi'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'no_resi',
                'value' => esc_attr( $query_vars['no_resi'] ),
            );
        }
    
        return $query;
    }

	public function cek_resi_data(){

		if(isset($_POST['data']))
		{
			parse_str($_POST['data'], $data);

			if ( ! wp_verify_nonce( $data['cek_no_resi_nonce'], 'cek_no_resi' ) ) 
			{
				die( 'Close The Door!');
			}
			
			$no_resi = $data['no_resi'];

			if( $no_resi )
			{
				$order 	= wc_get_orders( array( 'no_resi' => $no_resi ) );
				if($order)
				{
					$order_id			= $order[0]->get_id();
					$get_tracking_id	= get_post_meta($order_id, 'order_shipper_id', true);

					// do ajax here
					$detail_data = detail_data_tracking( $get_tracking_id );					

					ob_start();
					include ORDV_SHIPPER_PATH.'public/partials/check-awb/show-data.php';
					$set_data =  ob_get_clean();

					$response = array(
						'status'	=> 1,
						'content'	=> $set_data,
						'notice'	=> 'Data Nomer Resi ditemukan.',
						'class'		=> 'woocommerce-message',
					);

				}
				else
				{
					$response = array(
						'status'	=> 0,					
						'content'	=> '',
						'notice'	=> 'Data Nomer Resi tidak ditemukan.',
						'class'		=> 'woocommerce-error',
					);
				}
			}
			else
			{
				$response = array(
					'status'	=> 0,					
					'content'	=> '',
					'notice'	=> 'Data Nomer Resi tidak ditemukan.',
					'class'		=> 'woocommerce-error',
				);
			}

			wp_send_json($response);
			wp_die();
			
		}
		
	}


}
