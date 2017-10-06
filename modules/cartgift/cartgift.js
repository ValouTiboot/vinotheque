
$(document).ready(function(){

	$('#js-delivery-cart').on('submit', function(e){
		e.preventDefault();
		$.post($(this).data('url'), $(this).serialize(), function(data){
				if ($('#gift_checkbox').is(':checked'));
					alert(data.msg);
			},
			'json'
		);
	});

	$('#gift_checkbox').on('change', function(){
		$.post($(this).parent().parent().data('url'), $(this).parent().parent().serialize(), function(data){},'json');
	})
});