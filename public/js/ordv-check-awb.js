(function( $ ) {
	'use strict';



    $(document).on('submit','#check-resi',function(e){
        e.preventDefault();

        $('#notices-area').removeClass();
        $('#notices-area').html('');
        $('#hasil-cek-resi').html('');

        var data = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: cek_resi_ajax.ajax_url,
            data:{
                'data' : data,
                'action' : cek_resi_ajax.cek_resi.action
            },
            dataType: 'json',
            success: function (data) {
                $('#notices-area').addClass(data.class).html(data.notice);
                if(data.status == 1){
                    $('#hasil-cek-resi').html(data.content);
                }else{
                    // do nothing
                }
            }
        });

    });

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
