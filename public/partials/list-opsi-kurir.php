<?php

    $i = 0;
    foreach ($data_list_kurir as $detail_kurir):
    echo '<ul id="shipping_method" class="woocommerce-shipping-methods">';
?>
    
    <li> 
        <input type="radio" id="shipping_method_<?php echo $i; ?>" class="shipping_method" name="shipping_method" value="<?php echo $detail_kurir->logistic->name.' '.$detail_kurir->rate->name; ?>" data-kurir-price="<?php echo $detail_kurir->final_price;?>">
        <label for="shipping_method_<?php echo $i; ?>">
            <?php echo $detail_kurir->logistic->name.' '.$detail_kurir->rate->name ;?>: 
            <span class="woocommerce-Price-amount amount">
                <bdi>                    
                    <?php echo wc_price( $detail_kurir->final_price ); ?>
                    <?php //echo $detail_kurir->final_price; ?>
                </bdi>
            </span>
        </label>
    </li>  

<?php
    $i++;
    endforeach;
    echo '</ul>';