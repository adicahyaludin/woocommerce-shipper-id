<?php

/**
 * Get detail data tracking from shipper
 * @since   1.0.0
 * @uses    Ordv_Shipper_Check_Awb::ordv_shipper_cek_resi_data
 * @uses    Ordv_Shipper_Check_Awb::ordv_shipper_get_resi_detail
 * @uses    Ordv_Shipper_View_Order::ordv_shipper_add_delivery_details
 * @param   int $order_shipper_id
 * @return  mixed
 */
function ordv_shipper_fn_detail_data_tracking( $order_shipper_id ){
    $endpoint_get_data_shipper      = '/v3/order/'.$order_shipper_id;
    $endpoint_url_get_data_shipper  = get_url_api().''.$endpoint_get_data_shipper;

    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )           
    );

    $request = wp_remote_get( 
        $endpoint_url_get_data_shipper,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);
    $data       = $data_api->data;

    return $data;

}

/**
 * Update order status when reload view order page
 * @uses    Ordv_Shipper_View_Order::ordv_shipper_add_delivery_details
 * @since   1.0.0
 * @param   int     $order_id
 * @param   mixed   $detail_data
 * @return  mixed
 */
function ordv_shipper_fn_update_data_tracking( $order_id, $detail_data){

    $order              = wc_get_order( $order_id );
    $no_resi            = $detail_data->awb_number;

    $data_tracking      = $detail_data->trackings;
    $n_data             = count($data_tracking);
    $latest_data_n      = ($n_data - 1);

    $pickup_code        = $detail_data->pickup_code;

    $latest_code        = $data_tracking[$latest_data_n]->shipper_status->code;
    $latest_status      = $data_tracking[$latest_data_n]->shipper_status->description;


    update_post_meta( $order_id, 'no_resi',         $no_resi );
    update_post_meta( $order_id, 'pickup_code',     $pickup_code );
    update_post_meta( $order_id, 'status_code',     $latest_code );
    update_post_meta( $order_id, 'status_tracking', $latest_status );

    if( 1190 === $latest_code || 1180 === $latest_code || 1170 === $latest_code || 1160 === $latest_code ){

        $order->update_status('wc-in-shipping');
        $order->save();
    }

    if( 2000 === $latest_code ){

        $order->update_status('wc-completed');
        $order->save();
    }

    

}