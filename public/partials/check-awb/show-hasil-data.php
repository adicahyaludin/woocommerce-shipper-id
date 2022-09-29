<?php

    $data_tracking      = $detail_data->trackings;
    $n_data             = count($data_tracking);
    $latest_data_n      = ($n_data - 1);

    $latest_status = $data_tracking[$latest_data_n]->logistic_status->description;

    $hasil_cek_resi = array(
        'no_resi'       => $detail_data->awb_number,
        'order_id'      => $detail_data->order_id,
        'pengirim'      => $detail_data->consigner->name,
        'o_suburb'      => $detail_data->origin->suburb_name,
        'o_city'        => $detail_data->origin->city_name,
        'penerima'      => $detail_data->consignee->name,
        'd_suburb'      => $detail_data->destination->suburb_name,
        'd_city'        => $detail_data->destination->city_name,
        'kurir'         => $detail_data->courier->name,
        'tgl_kirim'     => $detail_data->creation_date,
        'latest_status' => $latest_status
    );

?>

    <div class="hasil-cek-resi">

            <table class="table">
                <tbody>
                    <tr>
                        <td>
                            <h4><strong><?php echo $hasil_cek_resi['latest_status']?></strong></h4>
                        </td>
                        <td class="text-center">
                            <h4><strong><?php echo $hasil_cek_resi['kurir']; ?></strong></h4>
                        </td>
                    </tr>
                </tbody>
            </table>

    </div>

    <div class="status-paket">

        <table class="table">
            <tbody>
                <tr>
                    <td>
                        No Resi/AWB<br />
                        <strong><?php echo $hasil_cek_resi['no_resi']; ?></strong>
                    </td>
                    <td>
                        Tanggal Pengiriman<br />
                        <strong>
                            <?php 
                                $str_time = $hasil_cek_resi['tgl_kirim']; 
                                $str_time = substr( $str_time, 0, 19 );
                                $str_time = str_replace('T', ' ', $str_time);
                                echo $str_time;
                            ?>
                        </strong>
                    </td>
                    <td>
                        Pengirim
                        <br />
                        <strong><?php echo $hasil_cek_resi['pengirim'];?></strong><br />
                        <strong><?php echo $hasil_cek_resi['o_suburb'].', '.$hasil_cek_resi['o_city']; ?></strong>
                       
                    </td>
                    <td>
                        Penerima
                        <br />
                        <strong><?php echo $hasil_cek_resi['penerima'];?></strong><br />
                        <strong><?php echo $hasil_cek_resi['d_suburb'].', '.$hasil_cek_resi['d_city']; ?></strong>                        
                    </td>
                    <td class="text-right">
                        <a href="#" class="button is-danger is-outlined detail-resi" data_order_id=<?php echo $hasil_cek_resi['order_id'];?>>Detail</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


