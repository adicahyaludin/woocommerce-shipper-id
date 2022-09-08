(function( $ ) {
	'use strict';

	$('#ordv-kotakab, #ordv-kecamatan, #ordv-keldesa').selectWoo().prop('disabled',true);
	$('#billing_delivery_option_field input[type=radio]').prop("disabled",true);

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
				options += '<option></option>';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'" data-label="'+o.name+'">'+o.name+'</option>';
				});
		 
				$('#ordv-kotakab').html(options);
				$('#ordv-kotakab').selectWoo().prop('disabled',false);

				$('#ordv-kecamatan, #ordv-keldesa').selectWoo().prop('disabled',true);
				$('#ordv-kecamatan, #ordv-keldesa').html('');
				$('#billing_delivery_option_field input[type=radio]').html('');
				$('body').trigger('update_checkout');

				$('#billing_delivery_option_field input[type=radio]').prop("disabled",true);

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
				options += '<option></option>';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'" data-label="'+o.name+'">'+o.name+'</option>';
				});
		 
				$('#ordv-kecamatan').html(options);
				$('#ordv-kecamatan').selectWoo().prop('disabled',false);

				$('#ordv-keldesa').selectWoo().prop('disabled',true);
				$('#ordv-keldesa').html('');
				$('#billing_delivery_option_field input[type=radio]').html('');
				$('body').trigger('update_checkout');

				$('#billing_delivery_option_field input[type=radio]').prop("disabled",true);
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
				var options = '';
				options += '<option></option>';
				$.each(data,function(i,o){
					options += '<option value="'+o.id+'" data-label="'+o.name+'" data-postcode="'+o.postcode+'" data-lat="'+o.lat+'" data-lng="'+o.lng+'">'+o.name+'</option>';
				});
		 
				$('#ordv-keldesa').html( options );
				$('#ordv-keldesa').selectWoo().prop('disabled',false);
				$('#billing_delivery_option_field input[type=radio]').html('');
				$('body').trigger('update_checkout');

				$('#billing_delivery_option_field input[type=radio]').prop("disabled",true);
			}
		});
	});

	$(document.body).on( "change", "#ordv-keldesa", function(){

		$('body').trigger('update_checkout');
		$('#billing_delivery_option_field input[type=radio]').html('');	
		$('#billing_delivery_option_field input[type=radio]').prop("disabled",false);

	});

	
	$(document.body).on( "change", "input[type=radio][name=billing_delivery_option]", function(){
		
		var dlvr_id = $('input:radio[name=billing_delivery_option]:checked').val();

		var a = $('#ordv-keldesa').find(':selected').val();
		var a_lat = $('#ordv-keldesa').find(':selected').attr('data-lat');
		var a_lng = $('#ordv-keldesa').find(':selected').attr('data-lng');

		var pc = $('#ordv-keldesa').find(':selected').attr('data-postcode');
		$('#billing_postcode').val(pc);

		// set data city
		var dk = $('#ordv-keldesa').find(':selected').attr('data-label');
		var kc = $('#ordv-kecamatan').find(':selected').attr('data-label');
		var kk = $('#ordv-kotakab').find(':selected').attr('data-label');

		var city_label = dk+', '+kc+', '+kk;
		$('#billing_city').val(city_label);
		
		$.ajax({
			type: 'POST',
			url: checkout_ajax.ajax_url,
			data: {
				'a' : a,
				'a_lat' : a_lat,
				'a_lng' : a_lng, 
				'dlvr_id' : dlvr_id,
				'action' : checkout_ajax.area.action,
				'nonce': checkout_ajax.area.nonce
			},
			dataType: "json",
			success: function (data) {
				$('body').trigger('update_checkout');			
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
