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

        $this->init();
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

    private function init() {
		$this->init_form_fields();
	}

	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'          => array(
				'title'       => __( 'Title', 'ordv-shipper' ),
				'type'        => 'text',
				'description' => '',
				'default'     => $this->method_title,
			),
			'logistic'       => array(
				'type' => 'logistic',
			),
		);
	}

    public function generate_logistic_html() {

        $logistics_results  = ordv_shipper_get_logistics(); 
        $logistics_option   = $this->get_option( 'logistic', array() );
        $logistics_order    = array_map('intval',$logistics_option['order']);
        $logistics_enabled  = array_map('intval',$logistics_option['enabled']);

        $logistics = [];
        if ( $logistics_order ) :
            foreach ( $logistics_order as $key => $value ) :
                if ( isset( $logistics_results[$value] ) ) :
                    $logistics[$value] = $logistics_results[$value];
                endif;
            endforeach;
            foreach ( $logistics_results as $key => $value) :
                if ( !isset( $logistics[$key] ) ) :
                    $logistics[$key] = $value;
                endif;
            endforeach;
        else:
            $logistics = $logistics_results;
        endif;

		ob_start();
        include ORDV_SHIPPER_PATH.'admin/partials/logistic-options.php';
		return ob_get_clean();

    }

    public function validate_logistic_field( $key ) {

		$logistics = [
            'order'     => [],
            'enabled'   => []
        ];

        if ( isset( $_POST['data']['logistics_order'] ) ) :

            $logistics['order'] = $_POST['data']['logistics_order'];

        endif;
	
        if ( isset( $_POST['data']['logistics_enabled'] ) ) :

            $logistics['enabled'] = $_POST['data']['logistics_enabled'];

        endif;

		return $logistics;

	}

}

add_action( "woocommerce_shipping_init", "Ordv_Shipper_Shipping_Method");
