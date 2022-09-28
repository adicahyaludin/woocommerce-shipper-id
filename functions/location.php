<?php

function ordv_shipper_get_locations( $search = '' ) {

    $data = [];

    $api_url = add_query_arg( array(
        'adm_level' => 5,
        'keyword'   => $search,
    ), get_url_api().'/v3/location' );
    $api_key = carbon_get_theme_option('shipper_api_key');

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Api-Key'    => $api_key
        )
    );

    $response = wp_remote_get(
        $api_url,
        $args
    );

    $responseBody = wp_remote_retrieve_body( $response );
    $result = json_decode( $responseBody );

    if ( ! is_wp_error( $result ) ) :

        $data = $result->data;

    endif;

    return $data;

}