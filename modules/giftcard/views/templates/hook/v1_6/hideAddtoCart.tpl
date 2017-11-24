{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @version   1.4.0
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{literal}
<script type="text/javascript">
$('document').ready( function()
{
	var pid;
	var subURL = baseDir + "?fc=module&module=giftcard&controller=ajax";
	$('.ajax_block_product .product_image, .ajax_block_product .product_img_link').map(function()
	{
		var thisActiveBlock = $(this);
		pid = $(this).attr('href');
		pid = pid.split('/');
		pid = pid[pid.length - 1];
		{/literal}{if $PS_REWRITING_SETTINGS == 1}{literal}
			pid = parseInt(pid, 10);
		{/literal}{else}{literal}
			pid = getUrlVars()['id_product'];

			function getUrlVars()
			{
				var vars = {};
				var parts = pid.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value)
				{
					vars[key] = value;
				});

				return vars;
			}

		{/literal}{/if}{literal}
		
		$.ajax({
					type		: "POST",
					cache		: false,
					dataType 	: "json",
					url			: subURL,
					data		: {
									action 		: 'ProductExists',
									id_product	: pid
								},

					success	: function(data)
					{
						var pid = parseInt(data);
						if(pid > 0)
						{
							{/literal}{if $PS_VERSION == 1}{literal}
								thisActiveBlock.parent().parent().parent().find('.price').text('');
								thisActiveBlock.parent().parent().parent().find('.ajax_add_to_cart_button').hide();
							{/literal}{else}{literal}
								thisActiveBlock.parent().parent().find('.ajax_add_to_cart_button').hide();
							{/literal}{/if}{literal}}
						},

						error : function(XMLHttpRequest, textStatus, errorThrown)
						{
							console.log(errorThrown);
						}
				});
	  });


});

</script>
{/literal}