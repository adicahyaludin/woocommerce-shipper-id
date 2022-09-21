<?php

    $order_id   = $post_id;
    $order      = wc_get_order( $order_id );
    $get_item   = $order->get_items();

    //$detail_shipping =  $order->get_shipping_methods();
    $detail_shipping = $order->get_items( 'shipping' );

    foreach ($order->get_items() as $item_id => $item ) {
        $product_name   = $item->get_name(); // Get the item name (product name)
        $item_quantity  = $item->get_quantity(); // Get the item quantity
        
        echo '<p>'.$product_name.' x '.$item_quantity.'</p>';
        
    }

?>


<?php
    if( 
        $order->is_paid() 
        || 
        $order->has_status('processing') 
    ):
?>

    <?php
        if( ! get_post_meta( $order_id, 'order_shipper_id', true ) ):
    ?>

    <p style="margin-top:8px;">
        <a class="button button-secondary" href="<?php echo wp_nonce_url( admin_url('admin.php?action=shipper_create_order&order_id='.$order_id), 'create_order_shipper_nonce', 'nonce' ); ?>">Buat Order di Shipper</a>        
    </p>

    <?php else: ?>

        <?php 

            
            $order_shipper_id = get_post_meta( $order_id, 'order_shipper_id', true );
        ?>
        <p><strong>Tracking ID:</strong> <?php echo $order_shipper_id; ?></p> 
        
        <?php
            $get_order_data = get_shipper_order_data( $order_shipper_id );           

            $awb_number     = $get_order_data['awb_number'];
            $tracking_status = $get_order_data['tracking_status'];
            
            update_post_meta( $order_id, 'status_tracking',  $tracking_status );

            $no_resi = get_post_meta( $order_id, 'no_resi', true );

            if( ! $no_resi | '' == $no_resi){
                update_post_meta( $order_id, 'no_resi',  $awb_number );
            }else{
                // do nothing
            }
            //update_post_meta( $order_id, 'no_resi',  $awb_number );

            $tracking_status = get_post_meta( $order_id, 'status_tracking', true );
            

        ?>

        <p><strong>AWB:</strong> <?php echo $no_resi; ?></p>
        <p class="shipper-status-<?php echo $order_id; ?>"><strong>Status:</strong> <?php echo $tracking_status; ?></p>

        <?php 
            $shipper_order_status = get_post_meta( $order_id, 'is_activate', true );
            if( '0' === $shipper_order_status ): ?>

            <p style="margin-top:8px;">                
                <a href="#" class="button wc-action-button open-dialog" data_order_id="<?php echo $order_id; ?>">Aktifkan Pickup Order</a>
            </p>

        <?php else: ?>

            <p style="margin-top:8px;">
                <a class="button button-secondary update-order-status" data_order_id="<?php echo $order_id; ?>" href="#">Update Status</a>        
            </p>
            
        <?php endif; ?>
                
            

    <?php endif; ?>

    


<?php else: ?>
    <!-- do nothing --> 
<?php endif; ?>



