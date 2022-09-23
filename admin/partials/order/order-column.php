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

    
    $name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
    $order_shipper_id = get_post_meta( $order_id, 'order_shipper_id', true );
    $no_resi = get_post_meta( $order_id, 'no_resi', true );    
    $tracking_status = get_post_meta( $order_id, 'status_tracking', true );    
    $code_status = get_post_meta( $order_id, 'status_code', true );

?>

<?php if( $order_shipper_id): ?>
    <p><strong>Tracking ID:</strong> <?php echo $order_shipper_id; ?></p>
<?php else: ?>
    <!-- do nothing -->
<?php endif; ?>

<?php if( $no_resi): ?>
    <p><strong>AWB:</strong> <?php echo $no_resi; ?></p>
<?php else: ?>
    <!-- do nothing -->
<?php endif; ?> 


<?php 

    if( $tracking_status ): 
    $tracking_status = str_replace('[receiver_name]', $name, $tracking_status);

?>
    <p class="shipper-status-<?php echo $order_id; ?>"><strong>Status:</strong> <?php echo $tracking_status; ?></p>
<?php else: ?>
    <!-- do nothing -->
<?php endif; ?>        


<?php 
    if( $order->is_paid()||$order->has_status('processing')):
?>
    <?php 
        $order_shipper_id = get_post_meta( $order_id, 'order_shipper_id', true );
        if( ! $order_shipper_id ):
    ?>
        <p style="margin-top:8px;">
            <a class="button button-secondary btn-create-order-shipper" data_order_id="<?php echo $order_id; ?>" href="#">Buat Order di Shipper</a>        
        </p>
    <?php endif; ?>

<?php endif; ?>

<?php
    if ( $order->has_status('waiting-delivery')):
        
        $order_shipper_id = get_post_meta( $order_id, 'order_shipper_id', true );
        
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

        $pickup_code = get_post_meta( $order_id, 'pickup_code', true );
        
?>
    <?php if( ! $pickup_code ):?>
        <p style="margin-top:8px;">                
            <a href="#" class="button wc-action-button open-dialog" data_order_id="<?php echo $order_id; ?>">Aktifkan Pickup Order</a>
        </p>
    <?php else: ?>
        <!--  hide button -->
    <?php endif; ?>


<?php endif; ?>





<?php
    $get_pickup_code = get_post_meta( $order_id, 'pickup_code', true );
    if( $get_pickup_code ):

        $code_status = get_post_meta( $order_id, 'status_code', true );
?>

    <?php if( $code_status ): ?>

        <?php if( '2000' !== $code_status ):?> 

            <p style="margin-top:8px;">
                <a class="button button-secondary update-order-status" data_order_id="<?php echo $order_id; ?>" href="#">Update Status</a>        
            </p>         

        <?php endif; ?>
    
    <?php else: ?>

        <p style="margin-top:8px;">
            <a class="button button-secondary update-order-status" data_order_id="<?php echo $order_id; ?>" href="#">Update Status</a>        
        </p>

    <?php endif; ?>

<?php endif; ?>        




