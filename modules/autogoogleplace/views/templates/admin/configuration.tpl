{**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2016 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 *}
 
<div class="bootstrap panel">
	
		<h2>{l s='Auto Google Place' mod='autogoogleplace'}</h2>

		<hr>

<form action="{$link_form|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset style="max-width: 700px; margin: 2%; padding:1%;">
		<label for="autogoogleplace_check_all" style="width: 100%;"><span>{l s='Google Map API Key' mod='autogoogleplace'}</span>
			<input type="input" name="autogoogleplace_key" value="{$key|escape:'htmlall':'UTF-8'}"></label>
			<div class="clearfix"></div>
			<hr>

			<div>
				<p>{l s='To get an Google API Key, this is really simple :' mod='autogoogleplace'}</p>

				<ul>
				<li>{l s='1 - Go to Google Developper Console (https://console.developers.google.com/apis/).' mod='autogoogleplace'}</li>
				<li>{l s='2 - Click on "Create Project" and create yours.' mod='autogoogleplace'}</li>
				<li>{l s='3 - Click on "Credentials" then "Create credentials".' mod='autogoogleplace'}</li>
				<li><b>{l s='4 - Indicate "API Key", then select HTTP referer, indicate your domain name to protect from others.' mod='autogoogleplace'}</b> (ex: *.domain.com/*)</li>
				<li>{l s='5 - That\'s it, you have your API, just past it here !' mod='autogoogleplace'}</li>
				</ul>

				<p>{l s='Please note that the free limit is up to 250 000 asking per month and you must have activated Google Maps (or Places) API. It must be activated by default when you first login.' mod='autogoogleplace'}</p>

			</div>

			<div>
				<label for="autogoogleplace_force_15">1.5 Theme ? </label>
				<input type="checkbox" name="autogoogleplace_force_15" {if (bool) Configuration::get('AUTOGOOGLEPLACE_FORCE_15') == true} checked="checked"{/if} value="1" />
			</div>

	</fieldset>
	<fieldset style="max-width: 700px; margin: 2%; padding:1%;">
		<h3>{l s='Where do you want to display automatic suggestion' mod='autogoogleplace'}</h3>
		<p class="alert alert-info">{l s='Please note that you must register a module page in Preference > SEO & URLs if you want to see it here' mod='autogoogleplace'}</p>

		<label for="autogoogleplace_check_all" style="width: 100%;"><span>{l s='Check all' mod='autogoogleplace'}</span>
			<input type="checkbox" name="autogoogleplace_check_all" value="1" class="check"></label>
		<div class="clearfix"></div>
		<p for="autogoogleplace_meta">{l s='Indicate the pages that you do not want to include automatic address suggestions from Google Map :' mod='autogoogleplace'}</p>
		<hr>
		<ul class="list-unstyled">
			{foreach from=$metas item=meta}
				<li class="col-md-4 text-left" style="margin-top: 2%;">
					<input type="checkbox" class="autogoogleplace_metas" name="autogoogleplace_meta[]"{if in_array($meta.id_meta, $included_metas)} checked="checked"{/if} value="{$meta.id_meta|intval}" /> {$meta.title|escape:'htmlall':'UTF-8'} [{$meta.page|escape:'htmlall':'UTF-8'}]
				</li>
			{/foreach}
		</ul>
		<br/>

		<div class="clearfix"></div>
			<hr>
		<p class="text-center">
			<input type="submit" style="margin: 2%;" class="btn btn-default button" name="SubmitAutogoogleplace" value="{l s='Confirm' mod='autogoogleplace'}" />
		</p>

	</fieldset>
</form>

<script type="text/javascript">
$(document).ready(function() {
	
	if ($('.autogoogleplace_metas:checked').length == $('.autogoogleplace_metas').length)
		$('.check').parent('label').children('span').html("{l s='uncheck all' mod='autogoogleplace'}");
	
	
	$('.check').toggle(function() {
		$('.autogoogleplace_metas').attr('checked', 'checked');
		$(this).parent('label').children('span').html("{l s='Uncheck all' mod='autogoogleplace'}");
	}, function() {
		$('.autogoogleplace_metas').removeAttr('checked');
		$(this).parent('label').children('span').html("{l s='Check all' mod='autogoogleplace'}");
	});
});
</script>

</div>