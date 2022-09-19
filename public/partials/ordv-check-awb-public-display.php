<?php
    
?>

<div id="notices-area"></div>

<div id="cek-resi-area">
    <label>Masukan Nomor Resi / AWB anda</label>
    <form method="POST" id="check-resi" class="check-resi" name="check-resi">
        <input type="text" name="no_resi" id="no-resi" placeholder="Contoh: JD1236457654"  style="width:78%;" />        
        <?php wp_nonce_field( 'cek_no_resi', 'cek_no_resi_nonce' ); ?>
        <input type="submit" class="button button-primary" style="width:20%;" value="Cek Resi">
    </form>
</div>

<div id="hasil-cek-resi"></div>