<?php

function ordv_shipper_get_logistics() {

    $data = [];

    $api_url = 'https://merchant-api-sandbox.shipper.id/v3/logistic';
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

        $logistics = $result->data;

        foreach ( $logistics as $key => $logistic ) :

            $data[$logistic->id] = $logistic;

        endforeach;

    endif;

    return $data;

}