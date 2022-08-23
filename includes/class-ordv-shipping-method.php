<?php

class Ordv_Shipper_Shipping_Method extends \WC_Shipping_Method {
    /**
     * Constructor
     *
     * @since   1.0.0
     * @return  void
     */
    public function __construct( $instance_id = 0 ) {

        $this->id                 = 'ordv-shipper';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'Shipper.id', 'ordv-shipper' );
        $this->method_description = __( 'Shipper.id method for WooCommerce', 'ordv-shipper' );

        $this->enabled  = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
        $this->title    = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Shipper.id', 'ordv-shipper' );
        $this->supports = array (
            "shipping-zones",
            "instance-settings",
            "instance-settings-modal"
        );

        $this->instance_form_field = [
            'enabled' => array(
                'title'         => __( 'Enable', 'ordv-shipper' ),
                'type'          => 'checkbox',
                'description'   => __( 'Enable this shipping.', 'ordv-shipper' ),
                'default'       => 'yes'
            ),

            'title'     => array(
                'title'         => __( 'Title', 'ordv-shipper' ),
                'type'          => 'text',
                'description'   => __( 'Title to be display on site', 'ordv-shipper' ),
                'default'       => __( 'Shipper.id Method', 'ordv-shipper' )
            ),
        ];

        add_action( "woocommerce_update_options_shipping_" . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Calculate shipping based on this method
     * @since   1.0.0
     * @param   array   $package
     * @return  void
     */
    public function calculate_shipping( $package = array() ) {

        $cost       = 0;
        $location   = null;
        $title      = $this->title;
        $location_term = "pa_" . carbon_get_theme_option( "shipper_location_term" );
        $location_product_attribute = "attribute_" . $location_term;

        foreach( $package["contents"] as $hash => $item ) :
            if(
                array_key_exists("variation", $item) &&
                array_key_exists($location_product_attribute, $item["variation"])
            ) :
                $term = get_term_by( "slug", $item["variation"][$location_product_attribute], $location_term );

                if( is_a($term, "WP_Term")) :

                    // Later we will get this from shipper
                    $cost = carbon_get_term_meta( $term->term_id, "shipper_courier_cost");
                    $title .= "- Dikirim dari " . $term->name;

                    break;
                endif;

            endif;
        endforeach;

        $this->add_rate( array(
            "id"    => $this->id . $this->instance_id,
            "label" => $title,
            "cost"  => $cost
        ));
    }
}

add_action( "woocommerce_shipping_init", "Ordv_Shipper_Shipping_Method");
