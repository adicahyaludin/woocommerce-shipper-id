<?php

function ordv_shipper_fn_detail_data_tracking( $order_shipper_id ){
    $endpoint_get_data_shipper      = '/v3/order/'.$order_shipper_id;
    $endpoint_url_get_data_shipper  = API_URL.''.$endpoint_get_data_shipper;

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

    $body               = wp_remote_retrieve_body( $request );
    $data_api           = json_decode($body);

    $data_tracking      = $data_api->data->trackings;
    $n_data             = count($data_tracking);
    $latest_data_n      = ($n_data - 1);

    $latest_status = $data_tracking[$latest_data_n]->logistic_status->description;

    $data = array(
        'awb_number'            => $data_api->data->awb_number,
        'shipper_order_status'  => $data_api->data->is_active, // true or false
        'pickup_code'           => $data_api->data->pickup_code,
        'tracking_status'       => $latest_status
    );
    

    return $data;

}