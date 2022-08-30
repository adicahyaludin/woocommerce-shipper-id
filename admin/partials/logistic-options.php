<tr valign="top">
    <td class="forminp" colspan="2">
        <h3><?php _e( 'Logistics', 'ordv-shipper' ); ?></h3>
        <table class="logistic-options" style="width:100%">
            <thead>
                <tr>
                    <th style="width:5%"></th>
                    <th style="width:10%"><?php esc_attr_e( 'Active', 'ordv-shipper' ); ?></th>
                    <th style="width:15%"><?php esc_attr_e( 'Logo', 'ordv-shipper' ); ?></th>
                    <th style="width:20%"><?php esc_attr_e( 'Name', 'ordv-shipper' ); ?></th>
                    <th style="width:20%"><?php esc_attr_e( 'Type', 'ordv-shipper' ); ?></th>
                    <th style="width:15%"><?php esc_attr_e( 'Volumetric', 'ordv-shipper' ); ?></th>
                    <th style="width:15%"><?php esc_attr_e( 'Weight (kg)', 'ordv-shipper' ); ?></th>
                </tr>
            </thead>
            <tbody class="logistics-sortable">
                <?php
                if ( $logistics ) :
                    foreach ( $logistics as $key => $logistic ) :
                    ?>
                        <tr>
                            <td><span class="logistic-sort dashicons dashicons-menu"></span></td>
                            <td>
                                <input type="hidden" name="logistics_order[]" value="<?php echo $logistic->id; ?>">
                                <input type="checkbox" name="logistics_enabled[]" value="<?php echo $logistic->id; ?>" <?php echo in_array($logistic->id,$logistics_enabled) ? 'checked' : ''; ?>>
                            </td>
                            <td><img src="<?php echo $logistic->logistic->logo_url; ?>" alt="logo" style="max-height:35px;max-width:100%"></td>
                            <td><?php echo $logistic->logistic->name.' '.$logistic->name; ?></td>
                            <td><?php echo $logistic->type_name; ?></td>
                            <td><?php echo $logistic->volumetric; ?></td>
                            <td><?php echo $logistic->min_kg; ?> - <?php echo $logistic->max_kg; ?></td>
                        </tr>
                    <?php
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="7"><p style="text-align:center">Empty Data</p></td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
    </td>
</tr>
<script>
jQuery(document).ready(function($) {
    $( ".logistics-sortable" ).sortable();
} );
</script>