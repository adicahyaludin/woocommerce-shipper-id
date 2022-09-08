<?php

function get_province_name( $country_id, $province_id ){    
    
    $countries_obj = new WC_Countries();   
    $country_states_array = $countries_obj->get_states();

    // Get the state name:    
    $state_name  = $country_states_array[$country_id][$province_id];
    return $state_name;
}

function get_api_province_id( $province_name, $endpoint_province ){
    
    $api_url_province = API_URL.''.$endpoint_province;
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'X-Api-Key' => $api_key
    ));

    $request = wp_remote_get(
        $api_url_province,
        $args
    );

    $body = wp_remote_retrieve_body( $request );

    $data_api       = json_decode($body);
    $data_province  = $data_api->data;

    $selected_province_data = [];

    foreach ($data_province as $d) {
        if( $province_name === $d->name ){
            array_push( $selected_province_data, $d );
        }
    }

    $api_selected_province = $selected_province_data[0]->id;

    return $api_selected_province;
}

function get_list_city( $api_province_id ){

    $endpoint_city = '/v3/location/province/'.$api_province_id.'/cities?limit=100';
    $api_url_city = API_URL.''.$endpoint_city;
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'X-Api-Key' => $api_key
    ));

    $request = wp_remote_get(
        $api_url_city,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);

    return $data_api->data;
}

function get_list_kec( $api_city_id ){

    $endpoint_kec   = '/v3/location/city/'.$api_city_id.'/suburbs?limit=100';
    $api_url_kec    = API_URL.''.$endpoint_kec;
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )
    );
    
    $request = wp_remote_get(
        $api_url_kec,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);

    return $data_api->data;    

}

function get_list_keldesa( $api_kec_id ){
    
    $endpoint_keldesa = '/v3/location/suburb/'.$api_kec_id.'/areas?limit=100';
    $api_url_keldesa = API_URL.''.$endpoint_keldesa;
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )
    );

    $request = wp_remote_get(
        $api_url_keldesa,
        $args
    );

    $body       = wp_remote_retrieve_body( $request );
    $data_api   = json_decode($body);

    return $data_api->data;

}

function get_packages_data(){    
        
    $data = array();

    $total_weight = WC()->cart->get_cart_contents_weight();
    $data['weight'] = $total_weight;
    $i_data['item_data'] = array();

    $items = WC()->cart->get_cart(); 

    //LOOP ALL THE PRODUCTS IN THE CART
    foreach($items as $item => $values) { 
        $_product =  wc_get_product( $values['data']->get_id());

        $item_attribute = $_product->get_attribute( 'pa_lokasi' );
        $item_term      = get_term_by( 'name',  $item_attribute, 'pa_lokasi' );

        $area_id        = get_term_meta( $item_term->term_taxonomy_id, '_origin_area_id', true);
        $area_text      = get_term_meta( $item_term->term_taxonomy_id, '_origin_area_text', true);

        $origin_lat     = get_term_meta( $item_term->term_taxonomy_id, '_shipper_courier_origin_lat', true);
        $origin_lng     = get_term_meta( $item_term->term_taxonomy_id, '_shipper_courier_origin_lng', true);

        // $cost = carbon_get_term_meta( 32, "shipper_courier_origin_area_id");
        
        $item_length    = intval( $_product->get_length() );
        $item_height    = intval( $_product->get_height() );
        $item_width     = intval( $_product->get_width() );
        $cart_subtotal  = intval( WC()->cart->get_subtotal() );
        $origin_id	    = intval( $area_id );
        $origin_text    = strval( $area_text );
       
        $item_data = array(
            'length'   => $item_length,
            'height'   => $item_height,
            'width'    => $item_width
        );

        array_push($i_data['item_data'],  $item_data);

    } 

    $p_data         = $i_data['item_data'];
    $total_length   = 0;
    $total_height   = 0;

    $list_width = array();

    foreach ($p_data as $pd) {
        $p_length = $pd['length'];
        $p_height = $pd['height'];

        $list_width[] = $pd['width'];

        $total_length += $p_length;
        $total_height += $p_height;
    }

    $data['length']         = $total_length;
    $data['width']          = max($list_width);
    $data['height']         = $total_height;
    $data['subtotal']       = $cart_subtotal;
    $data['origin_id']      = $origin_id;
    $data['origin_text']    = $origin_text;
    $data['origin_lat']     = $origin_lat;
    $data['origin_lng']     = $origin_lng;


    return $data;
}

function get_data_list_kurir( $api_d_area_id, $area_id_lat, $area_id_lng, $delivery_id, $data_packages ){
    
    $total_weight   = ( $data_packages['weight'] / 1000 );
    $total_height   = $data_packages['height'];
    $total_width    = $data_packages['width'];
    $total_length   = $data_packages['length'];
    $subtotal       = $data_packages['subtotal'];

    $origin_id      = $data_packages['origin_id'];
    $origin_lat     = $data_packages['origin_lat'];
    $origin_lng     = $data_packages['origin_lng'];

    $dest_area_lat  = $area_id_lat;
    $dest_area_lng  = $area_id_lng;
    
    $delivery_opt   = $delivery_id;

    $endpoint_kurir = '/v3/pricing/domestic/'.$delivery_opt.'?limit=500';
    $endpoint_url   = API_URL.''.$endpoint_kurir;  
    $api_key = carbon_get_theme_option('shipper_api_key');  


    $body = array(
        'cod' => false,
        'destination' => array(
            'area_id' => $api_d_area_id,
            'lat'     => $dest_area_lat,
            'lng'     => $dest_area_lng
        ),
        'origin' => array(
            'area_id' => $origin_id,
            'lat'     => $origin_lat,
            'lng'     => $origin_lng
        ),
        'height' => $total_height,
        'length' => $total_length,
        'weight' => $total_weight,
        'width' => $total_width,
        'item_value' => $subtotal
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
        $endpoint_url,
        $args
    );

    $body               = wp_remote_retrieve_body( $request );
    $data_api           = json_decode($body);
    $data_list_kurir    = $data_api->data->pricings;

    return $data_list_kurir;

}