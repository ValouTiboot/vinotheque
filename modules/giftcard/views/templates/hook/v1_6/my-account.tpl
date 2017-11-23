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

<!-- MODULE Giftcard -->
<li class="lnk_giftcard">
	<a href="{$link->getModuleLink('giftcard', 'mygiftcards', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='My giftcards' mod='giftcard'}">
		{if $ps_version < 1.6}
		<img src="{$module_template_dir|escape:'htmlall':'UTF-8'}views/img/gift.png" alt="{l s='My Gift Cards' mod='giftcard'}" class="icon" />
		{/if}
		<span>{l s='My Giftcards' mod='giftcard'}</span>
		{if $ps_version >= 1.6}
		<i class="icon-gift"></i>
		{/if}
	</a>
</li>
<!-- END : MODULE Giftcard -->