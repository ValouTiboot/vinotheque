
$(document).ready(function(){

	$('#js-delivery-cart').on('submit', function(e){
		e.preventDefault();
		
		if (!$('#gift_checkbox').is(':checked'))
			return;
		
		$.post($(this).data('url'), $(this).serialize(), function(data){
				$('#modal_cartgift .modal-body p').html(data.msg);
				$('#modal_cartgift').modal('show');
			},
			'json'
		);
	});

	$('#gift_checkbox').on('change', function(){
		$.post($(this).parent().parent().data('url'), $(this).parent().parent().serialize(), function(data){prestashop.emit('updateCart');},'json');
	});
});