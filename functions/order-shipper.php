<?php

add_action("admin_init", function(){
    if(isset($_GET['nolan'])) :
        ?><pre><?php
        print_r(create_order_shipper(1357));
        ?></pre><?php
        exit;
    endif;
});

function create_order_shipper( $order_id ){

    $endpoint_create_order_shipper      = '/v3/order';
    $endpoint_url_create_order_shipper  = API_URL.''.$endpoint_create_order_shipper;

    $api_key = carbon_get_theme_option('shipper_api_key');

    // get data order detail
    $order = wc_get_order( $order_id );
    $order_data = $order->get_data();

    $first_name = $order_data['billing']['first_name'];
    $last_name = $order_data['billing']['last_name'];

    $raw_phone_number = $order_data['billing']['phone'];
    $ptn = "/^0/";  // Regex
    $rpltxt = "62";  // Replacement string

    $phone_number =  preg_replace($ptn, $rpltxt, $raw_phone_number);
    $rate_id = intval( get_post_meta($order_id, 'rate_id', true) );

    // destination
    $d_address = $order_data['billing']['address_1'];
    $d_area_id = intval( get_post_meta($order_id, 'd_area_id', true) );

    $list_product = $order->get_items();

    $items = array();
    foreach( $list_product as $item_id => $item_data ){

        // get data  product
        $product   = $item_data->get_product();

        $product_id = $item_data['product_id'];
        $product_name = $item_data["name"];
        $product_price = $product->get_price();
        $product_qty = $item_data->get_quantity();

        $list = array(
            'id' =>  $product_id,
            'name' => $product_name,
            'price' => intval( $product_price ),
            'qty' => $product_qty
        );

        $items[] = $list;

    }

    $s_pid          = $items[0]['id'];
    $_product       = wc_get_product( $s_pid );
    $term           = carbon_get_theme_option("shipper_location_term");
    $item_attribute = $item_data->get_meta("pa_" . $term);
    $item_term      = get_term_by( 'name',  $item_attribute, 'pa_' . $term );

    $area_id        = get_term_meta( $item_term->term_taxonomy_id, '_origin_area_id', true);
    $area_text      = get_term_meta( $item_term->term_taxonomy_id, '_origin_area_text', true);

    $origin_lat     = get_term_meta( $item_term->term_taxonomy_id, '_shipper_courier_origin_lat', true);
    $origin_lng     = get_term_meta( $item_term->term_taxonomy_id, '_shipper_courier_origin_lng', true);

    // destination cordinates
    $dest_lat = strval( get_post_meta( $order_id, 'd_lat_area_id', true ));
    $dest_lng = strval( get_post_meta( $order_id, 'd_lng_area_id', true ));

    // origin
    $o_address = $area_text;
    $o_area_id = intval( $area_id );

    $height = 0;
    $length = 0;
    $weight = 0;
    $width = 0;
    $package_type = 2;
    $price = 0; // total price package

    foreach( $list_product as $i_id => $i_data ){
        $product    = $i_data->get_product();

        $length += $product->get_length();
        $width += $product->get_width();
        $height += $product->get_height();
        $weight += $product->get_weight();
        $price += $product->get_price();

    }

    $weight = floatval( $weight );
    $weight = ( $weight / 1000 );


    $body = array(

        'consignee' => array(
            'name'          => $first_name.' '.$last_name,
            'phone_number'  => $phone_number
        ),
        'courier' => array(
            'rate_id'   => $rate_id
        ),
        'destination' =>  array(
            'address'   => $d_address,
            'area_id'   => $d_area_id,
            'lat'       => $dest_lat,
            'lng'       => $dest_lng

        ),
        'origin'    => array(
            'address'   => $o_address,
            'area_id'   => $o_area_id,
            'lat'       => $origin_lat,
            'lng'       => $origin_lng
        ),
        'package'   => array(
            'items'         => $items,
            'height'        => $height,
            'length'        => $length,
            'weight'        => $weight,
            'width'         => $width,
            'package_type'  => $package_type,
            'price'         => $price
        ),
        'coverage'      => 'domestic',
        'payment_type'  => 'postpay'
    );

    $send_body = $body = wp_json_encode( $body );

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        ),
        'body' => $body
    );

    $request = wp_remote_post(
        $endpoint_url_create_order_shipper,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);
    $data       = $data_api->data;

    print_r(array(
        json_decode($send_body),
        $data_api
    ));

    return $data;

}

function get_shipper_order_data( $order_shipper_id ){

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

    $latest_status = $data_tracking[$latest_data_n]->shipper_status->description;

    $data = array(
        'awb_number'            => $data_api->data->awb_number,
        'shipper_order_status'  => $data_api->data->is_active, // true or false
        'pickup_code'           => $data_api->data->pickup_code,
        'tracking_status'       => $latest_status
    );


    return $data;

}

function get_pickup_time(){

    date_default_timezone_set('Asia/Jakarta');

    $date = date('Y-m-d');
    $time = date('H:i:s');

    //$date_time_req = strval($date.'T'.$time.'+07:00');
    //$date_time_req = $date.'T'.$time.'Z';
    $date_time_zone = 'Asia/Jakarta';


    $endpoint_get_time_slot      = '/v3/pickup/timeslot';
    $endpoint_url_get_time_slot  = API_URL.''.$endpoint_get_time_slot;

    $api_url = add_query_arg( array(
        'time_zone'     => urlencode($date_time_zone),
        //'request_time'  => urlencode($date_time_req),
    ), $endpoint_url_get_time_slot );

    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )
    );

    $request = wp_remote_get(
        $api_url,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);

    $slot_time = $data_api->data->time_slots;

    return $slot_time;
}

function do_pickup_order( $id_shipper_order, $date_start, $date_end ){

    $endpoint_do_pickup_order      = '/v3/pickup/timeslot';
    $endpoint_url_do_pickup_order  = API_URL.''.$endpoint_do_pickup_order;

    $api_key = carbon_get_theme_option('shipper_api_key');

    $body = array(
        'data' => array(
            'order_activation' => array(
                'order_id'  => array(
                    $id_shipper_order
                ),
                'end_time' => $date_end,
                'start_time'=> $date_start,
                'timezone' => 'Asia/Jakarta'
            )
        )

    );


    $body = wp_json_encode( $body );

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        ),
        'body' => $body
    );

    $request = wp_remote_post(
        $endpoint_url_do_pickup_order,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);

    $data       = $data_api->data->order_activations[0];

    return $data;

}

function get_updated_status( $order_id ){

    $order_shipper_id = get_post_meta($order_id, 'order_shipper_id', true);

    $endpoint_get_status      = '/v3/order/'.$order_shipper_id;
    $endpoint_url_get_status  = API_URL.''.$endpoint_get_status;

    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )
    );

    $request = wp_remote_get(
        $endpoint_url_get_status,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);
    $data       = $data_api->data->trackings;

    $n_data = count($data);
    $latest_data_n = ($n_data - 1);

    $latest_code = $data[$latest_data_n]->shipper_status->code;
    $latest_status = $data[$latest_data_n]->shipper_status->description;

    $arr_data = array(
        'latest_code'   => $latest_code,
        'latest_status' => $latest_status
    );

    //return $latest_status;
    return $arr_data;

}
