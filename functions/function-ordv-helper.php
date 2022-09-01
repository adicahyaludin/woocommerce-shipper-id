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

    $endpoint_city = '/v3/location/province/'.$api_province_id.'/cities';
    $api_url_city = API_URL.''.$endpoint_city;

    $args = array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'X-Api-Key' => API_KEY
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

    $endpoint_kec   = '/v3/location/city/'.$api_city_id.'/suburbs';
    $api_url_kec    = API_URL.''.$endpoint_kec;

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => API_KEY
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
    
    $endpoint_keldesa = '/v3/location/suburb/'.$api_kec_id.'/areas';
    $api_url_keldesa = API_URL.''.$endpoint_keldesa;

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key' => API_KEY
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
        
        $item_length    = intval( $_product->get_length());
        $item_height    = intval( $_product->get_height());
        $item_width     = intval( $_product->get_width());

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

    $data['length'] = $total_length;
    $data['width']  = max($list_width);
    $data['height'] = $total_height;


    return $data;
}