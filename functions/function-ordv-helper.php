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

function get_list_area( $keyword ){

    $endpoint_listarea = '/v3/location?adm_level=5&keyword='.$keyword;
    $api_url_listarea = API_URL.''.$endpoint_listarea;
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => $api_key
        )
    );

    $request = wp_remote_get(
        $api_url_listarea,
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

function get_data_list_kurir( $api_d_area_id, $area_id_lat, $area_id_lng, $data_packages ){
    
    // add filter weight ( in gr ) and lenght ( in cm )
    
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
    
    // array delivery options

    //$delivery_options = array( 'instant', 'regular', 'express', 'trucking', 'same-day' );

    $endpoint_kurir_instant = '/v3/pricing/domestic/instant?limit=500';
    $endpoint_url_instant   = API_URL.''.$endpoint_kurir_instant;  

    $endpoint_kurir_regular = '/v3/pricing/domestic/regular?limit=500';
    $endpoint_url_regular   = API_URL.''.$endpoint_kurir_regular;

    $endpoint_kurir_express = '/v3/pricing/domestic/express?limit=500';
    $endpoint_url_express   = API_URL.''.$endpoint_kurir_express;


    $endpoint_kurir_trucking = '/v3/pricing/domestic/trucking?limit=500';
    $endpoint_url_trucking   = API_URL.''.$endpoint_kurir_trucking;

    $endpoint_kurir_same_day = '/v3/pricing/domestic/same-day?limit=500';
    $endpoint_url_same_day   = API_URL.''.$endpoint_kurir_same_day;
    
    //$endpoint_kurir = '/v3/pricing/domestic?limit=500';
    //$endpoint_url   = API_URL.''.$endpoint_kurir;  
    
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

    //'instant'
    $request_instant = wp_remote_post(
        $endpoint_url_instant,
        $args
    );
    $body_instant               = wp_remote_retrieve_body( $request_instant );
    $data_api_instant           = json_decode($body_instant);
    $data_list_kurir_instant    = $data_api_instant->data->pricings;


    // 'regular'
    $request_regular = wp_remote_post(
        $endpoint_url_regular,
        $args
    );
    $body_regular               = wp_remote_retrieve_body( $request_regular );
    $data_api_regular           = json_decode($body_regular);
    $data_list_kurir_regular    = $data_api_regular->data->pricings;

    //'express'
    $request_express = wp_remote_post(
        $endpoint_url_express,
        $args
    );
    $body_express               = wp_remote_retrieve_body( $request_express );
    $data_api_express           = json_decode($body_express);
    $data_list_kurir_express    = $data_api_express->data->pricings;

    //'trucking'
    $request_trucking = wp_remote_post(
        $endpoint_url_trucking,
        $args
    );
    $body_trucking               = wp_remote_retrieve_body( $request_trucking );
    $data_api_trucking           = json_decode($body_trucking);
    $data_list_kurir_trucking    = $data_api_trucking->data->pricings;

    //'same-day'
    $request_same_day = wp_remote_post(
        $endpoint_url_same_day,
        $args
    );
    $body_same_day               = wp_remote_retrieve_body( $request_same_day );
    $data_api_same_day           = json_decode($body_same_day);
    $data_list_kurir_same_day    = $data_api_same_day->data->pricings;


    $data_list_kurir = array_merge( $data_list_kurir_instant, $data_list_kurir_regular, $data_list_kurir_express, $data_list_kurir_trucking, $data_list_kurir_same_day );
    
    // get shipping method options
    $delivery_zones = WC_Shipping_Zones::get_zones();
    $arr_shipping_method = array();
    
    foreach ($delivery_zones as $zone) {

        foreach( $zone['shipping_methods'] as $shipping_method ){

            $list = array(
                'id' => $shipping_method->id,
                'name' => $shipping_method->method_title,
                'enabled' => $shipping_method->enabled,
                'instance_setting' => $shipping_method->instance_settings
            );

            $arr_shipping_method[] = $list;

        }
    }



    $data_shipping_method = array();
    foreach ($arr_shipping_method as $shipping_method) {
        
        if( 'ordv-shipper' === $shipping_method['id'] && 'yes' === $shipping_method['enabled']){

            $list_data = array(
                'enable_kurir'  => $shipping_method['instance_setting']['logistic']['enabled'],
                'order_kurir'   => $shipping_method['instance_setting']['logistic']['order'],
            );

            $data_shipping_method[] = $list_data;

        }else{

        }

    }

    $enable_kurir       = $data_shipping_method[0]['enable_kurir'];
    $order_kurir        = $data_shipping_method[0]['order_kurir'];

    //create index for kurir data
    $kurir_order_value = array();
    foreach ($order_kurir as $key => $kurir) {
        
        $list = array(
            'service_id' => strval( $kurir ),
            'order' => $key
        );
        
        $kurir_order_value[] = $list;

    }

    // create new array data for filtered 
    $list_available_kurir =  array();
    foreach ($data_list_kurir as $key => $kurir) {
        $list = array(
            'logistic_id'   => strval($kurir->logistic->id),
            'logistic_code' => $kurir->logistic->code,
            'logistic_name' => $kurir->logistic->name,
            'rate_id'       => strval($kurir->rate->id),
            'rate_name'     => $kurir->rate->name,
            'final_price'   => $kurir->final_price
        );

        $list_available_kurir[] = $list; 
    }

    // filtered available kurir
    $new_list_available_kurir = array_filter($list_available_kurir, function($e) use ($enable_kurir){
        return in_array($e['rate_id'], $enable_kurir);
    });

    // insert re-order data    
    foreach ($new_list_available_kurir as $key_kurir => $kurir) {
        
        $kurir_rate_id = $kurir['rate_id'];        

        foreach ( $kurir_order_value as $order ) {
            if( $order['service_id'] == $kurir_rate_id ){
                $new_list_available_kurir[$key_kurir]['order'] = $order['order'];
            }
        }

    }

    // get sorted array
    usort($new_list_available_kurir, function($a, $b){
        return $a['order'] <=> $b['order'];
    });

    return $new_list_available_kurir;


}