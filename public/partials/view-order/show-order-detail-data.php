<?php
    $data_tracking  = $detail_data->trackings;
    $data_tracking = array_reverse($data_tracking);
?>

<div class="data-paket">
        <h4><strong>Detail Status Paket</strong></h4>
        <table class="table is-striped">
            <thead>
                <tr>
                    <th>Tanggal & Waktu</th>
                    <th>Shipper Status</th>
                    <th>Logistic Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_tracking as $key => $data): ?>
                    <tr>                    
                        <td class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo ($key === 0) ? 'is-danger': ''; ?>"></div>
                                <div class="timeline-content">
                                    <?php 
                                        $str_time = $data->created_date; 
                                        $str_time = substr( $str_time, 0, 19 );
                                        $str_time = str_replace('T', ',', $str_time);
                                        echo $str_time;
                                    ?>
                                </div>
                            </div>                                                
                        </td>
                        <td>
                            <p style="font-size:13px;">
                                <?php
                                    echo $data->shipper_status->description; 
                                ?>
                            </p>
                        </td>
                        <td>
                            <p style="font-size:13px;">
                                <?php echo $data->logistic_status->description; ?>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>