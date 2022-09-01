<?php
    $i = 0;
    foreach ($data_list_kurir as $detail_kurir):
?>
    
    <li> 
        <input type="radio" id="x_shipping_method_<?php echo $i; ?>" name="x_shipping_method[<?php echo $api_o_area_id; ?>]" value="">
        <label for="x_shipping_method_<?php echo $i; ?>">
            <?php echo $detail_kurir->logistic->name.' '.$detail_kurir->rate->name ;?>: 
            <span class="woocommerce-Price-amount amount">
                <bdi>
                    
                    <?php echo wc_price( $detail_kurir->final_price ); ?>
                </bdi>
            </span>
        </label>
    </li>  

<?php
    $i++;
    endforeach;