{*
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<table width="100%">
    {foreach from=$products item=product}
        <tr>
            <td height="10">&nbsp;</td>
        </tr>
        <tr>
            <td width="100">
                <a target="_blank" style="text-decoration: none;" href="{$product['link']|escape:'quotes':'UTF-8'}">
                    <img width="100" valign="center" src="http://{$product['img']|escape:'quotes':'UTF-8'}">
                </a>
            </td>
            <td align="left" valign="center" style="padding-left:10px;">
                <p>
                    <a style="text-decoration: none;" target="_blank" href="{$product['link']|escape:'quotes':'UTF-8'}">{$product['name']|escape:'htmlall':'UTF-8'}</a>
                </p>
                {if isset($product['attributes'])}
                    <p style="font-size:11px;">
                        {$product['attributes']|escape:'htmlall':'UTF-8'}
                    </p>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>
