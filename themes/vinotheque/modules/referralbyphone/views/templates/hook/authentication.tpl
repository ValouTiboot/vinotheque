{*
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Anastasia Basova <site@web-esse.ru>
 * @link    http://web-esse.ru/
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
var referralbyphone_controller_url = '{$referralbyphone_controller_url|escape:"html":"UTF-8"}';
</script>

<!-- MODULE referralbyphone -->
<fieldset class="account_creation">
    <h3>{l s='Referral program' mod='referralbyphone'}</h3>
    <div class="form-group row {if isset($referal_error)}has-error{/if}">
        <label class="col-md-3" for="referralbyphone">{if $sponsor_data == 'sponsorby_email'}{l s='E-mail or Sponsor Code' mod='referralbyphone'}{else}{l s='Phone' mod='referralbyphone'}{/if}</label>
        <div class="col-md-6">
            <input class="form-control" type="text" size="52" maxlength="128" id="referralbyphone" name="referralbyphone" value="{if isset($smarty.post.referralbyphone)}{$smarty.post.referralbyphone|escape:'html':'UTF-8'}{/if}" />
            {if isset($referal_error)}
            <div class="help-block">
            	<ul>
            		<li>{$referal_error}</li>
            	</ul>
            </div>
            {/if}
        </div>
    </div>
</fieldset>
<div id="submitAccountjs" style="display:none">Submit</div>
<!-- END : MODULE referralbyphone -->