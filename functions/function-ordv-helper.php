<?php

/**
 * Get list area from shipper
 * @uses    Ordv_Shipper_Checkout::ordv_shipper_get_data_area
 * @uses    Ordv_Shipper_Edit_Address_Billing::ordv_shipper_get_edit_data_area
 * @since   1.0.0
 * @param   string  $keyword
 * @return  mixed
 */
function ordv_shipper_fn_get_list_area( $keyword ){

    $endpoint_listarea = '/v3/location?adm_level=5&keyword='.$keyword;
    $api_url_listarea = get_url_api().''.$endpoint_listarea;
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

/**
 * Get detail Package order data
 * @uses    Ordv_Shipper_Checkout::ordv_shipper_get_data_services
 * @uses    Ordv_Shipper_Checkout::ordv_shipper_custom_shipping_package_name
 * @since   1.0.0
 * @return  void
 */
function ordv_shipper_fn_get_packages_data(){    
        
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


        $item_length    = floatval( $_product->get_length() );
        $item_height    = floatval( $_product->get_height() );
        $item_width     = floatval( $_product->get_width() );
        $cart_subtotal  = floatval( WC()->cart->get_subtotal() );
        $origin_id	    = floatval( $area_id );
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

/**
 * Get data list kurir 
 * @since   1.0.0
 * @uses    Ordv_Shipper_Checkout::ordv_shipper_get_data_services
 * @param   int     $api_d_area_id
 * @param   int     $area_id_lat
 * @param   int     $area_id_lng
 * @param   array   $data_packages
 * @return  void
 */
function ordv_shipper_fn_get_data_list_kurir( $api_d_area_id, $area_id_lat, $area_id_lng, $data_packages ){

    // add filter weight ( in gr ) and lenght ( in cm )    
    $active_weight_unit = get_option('woocommerce_weight_unit');
    $total_weight = $data_packages['weight'];

    // convert weight unit to kg.
		if ( $active_weight_unit !== 'kg' ) {
			switch ( $active_weight_unit ) {
				case 'g' :
					//$weight *= 0.001;
                    $total_weight  = ( $data_packages['weight'] * 0.001 );
					break;
				case 'lbs' :
					//$weight *= 0.453592;
                    $total_weight  = ( $data_packages['weight'] * 0.453592 );
					break;
				case 'oz' :
                    $total_weight  = ( $data_packages['weight'] *  0.0283495 );
					//$weight *= 0.0283495;
					break;
			}
        }

    $active_dimension_unit = get_option('woocommerce_dimension_unit');
    $total_height   = $data_packages['height'];
    $total_width    = $data_packages['width'];
    $total_length   = $data_packages['length'];

    if ( $active_dimension_unit !== 'cm' ) {
            switch ( $active_dimension_unit ) {
            case 'm' :                
                $total_height   =  ( $data_packages['height'] * 100);
                $total_width    = ( $data_packages['width'] * 100);
                $total_length   = ( $data_packages['length'] * 100);
                break;
            case 'mm' :
                $total_height   = ( $data_packages['height'] * 0.1 );
                $total_width    = ( $data_packages['width'] * 0.1 );
                $total_length   = ( $data_packages['length'] * 0.1 );
                break;
            case 'in' :
                $total_height   = ( $data_packages['height'] * 2.54);
                $total_width    = ( $data_packages['width'] * 2.54 );
                $total_length   = ( $data_packages['length'] * 2.54 );
                break;
            case 'yd' :
                $total_height   = ( $data_packages['height'] * 91.44);
                $total_width    = ( $data_packages['width'] * 91.44);
                $total_length   = ( $data_packages['length'] * 91.44);
                break;
        }
    }


    $subtotal       = $data_packages['subtotal'];

    $origin_id      = $data_packages['origin_id'];
    $origin_lat     = $data_packages['origin_lat'];
    $origin_lng     = $data_packages['origin_lng'];

    $dest_area_lat  = $area_id_lat;
    $dest_area_lng  = $area_id_lng;

    // set session for destination lat & lng order
    $dest_cord = array(
        'lat'   => strval( $dest_area_lat ),
        'lng'   => strval( $dest_area_lng )
    );

    WC()->session->set( 'dest_cord', $dest_cord );

    //set endpoint url
    $endpoint_kurir_instant = '/v3/pricing/domestic/instant?limit=500';
    $endpoint_url_instant   = get_url_api().''.$endpoint_kurir_instant;  

    $endpoint_kurir_regular = '/v3/pricing/domestic/regular?limit=500';
    $endpoint_url_regular   = get_url_api().''.$endpoint_kurir_regular;

    $endpoint_kurir_express = '/v3/pricing/domestic/express?limit=500';
    $endpoint_url_express   = get_url_api().''.$endpoint_kurir_express;


    $endpoint_kurir_trucking = '/v3/pricing/domestic/trucking?limit=500';
    $endpoint_url_trucking   = get_url_api().''.$endpoint_kurir_trucking;

    $endpoint_kurir_same_day = '/v3/pricing/domestic/same-day?limit=500';
    $endpoint_url_same_day   = get_url_api().''.$endpoint_kurir_same_day;
    
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

/**
 * Get detailed data my account page url endpoint data
 * @uses    Ordv_Shipper_Check_Awb::ordv_shipper_cek_resi_scripts_load
 * @since   1.0.0
 * @param   $endpoint
 * @return  void
 */
function ordv_shipper_fn_is_wc_endpoint($endpoint) {
    // Use the default WC function if the $endpoint is not provided
    if (empty($endpoint)) return is_wc_endpoint_url();
    // Query vars check
    global $wp;
    if (empty($wp->query_vars)) return false;
    $queryVars = $wp->query_vars;
    if (
        !empty($queryVars['pagename'])
        // Check if we are on the Woocommerce my-account page
        && $queryVars['pagename'] == 'my-account'
    ) {
        // Endpoint matched i.e. we are on the endpoint page
        if (isset($queryVars[$endpoint])) return true;
        // Dashboard my-account page special check - check whether the url ends with "my-account"
        if ($endpoint == 'dashboard') {
            $requestParts = explode('/', trim($wp->request, ' \/'));
            if (end($requestParts) == 'my-account') return true;
        }
    }
    return false;
}

/**
 * Get URL API demo or live
 * @since 1.0.0
 * @return void
 */
function get_url_api(){
    
    $demo_active = carbon_get_theme_option('shipper_demo');

    $api_url = NULL;

    if( $demo_active === true ){
        $api_url = 'https://merchant-api-sandbox.shipper.id';
    }else{
        $api_url = 'https://merchant-api.shipper.id';
    }

    return $api_url;

}