(function( $ ) {
	'use strict';

    $(document.body).on("change", "#billing_state", function() {
        var c = $("#billing_country").val();
        var s = $("#billing_state").val();

        $.ajax({
			type: 'POST',
			url: checkout_ajax.ajax_url,
			data:{
				'c' : c,				
				's' : s,				
				'action' : checkout_ajax.city.action,
				'nonce' : checkout_ajax.city.nonce		
			},
			dataType: 'json',
			success: function (data) {
				var options = '';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'">'+o.name+'</option>';
				});
		 
				$('#ordv-kotakab').html(options);
			}
		});

    });

	$(document.body).on( "change", "#ordv-kotakab", function(){
		var k = $("#ordv-kotakab").val();

		$.ajax({
			type: 'POST',
			url: checkout_ajax.ajax_url,
			data: {
				'k' : k,
				'action' : checkout_ajax.kec.action,
				'nonce' : checkout_ajax.kec.nonce
			},
			dataType: 'json',
			success: function ( data ) {
				var options = '';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'">'+o.name+'</option>';
				});
		 
				$('#ordv-kecamatan').html(options);
			}
		});
		
	});

	$(document.body).on( "change", "#ordv-kecamatan", function(){
		var d = $("#ordv-kecamatan").val();
		$.ajax({
			type: 'POST',
			url: checkout_ajax.ajax_url,
			data:{
				'd' : d,
				'action' : checkout_ajax.keldes.action,
				'nonce': checkout_ajax.keldes.nonce
			},
			dataType: 'json',
			success: function ( data ){
				//console.log(data);
				var options = '';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'" data-postcode="'+o.postcode+'">'+o.name+'</option>';
				});
		 
				$('#ordv-keldesa').html( options );
			}
		});
	});

	$(document.body).on( "change", "#ordv-keldesa", function(){

		var a = $('#ordv-keldesa').val();

		var pc = $('#ordv-keldesa').find(':selected').attr('data-postcode');
		$('#billing_postcode').val(pc);

		

		$('#shipping_method').html('hello');

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
