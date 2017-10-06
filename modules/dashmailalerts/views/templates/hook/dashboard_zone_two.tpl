{*
* 2007-2016 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<section id="dashmailalerts" class="panel widget {if $allow_push} allow_push{/if}">
	<header class="panel-heading">
		<i class="icon-exclamation-sign"></i> {l s='Customers who wants to receiving a notification when an out-of-stock product is available again.' mod='dashmailalerts'}
		<span class="panel-heading-action">
			<a class="list-toolbar-btn" href="#"  onclick="refreshDashboard('dashmailalerts'); return false;"  title="refresh">
				<i class="process-icon-refresh"></i>
			</a>
		</span>
	</header>

	<section>
		<div class="table-responsive">
			<table class="table data_table" id="table_customers">
				<thead></thead>
				<tbody></tbody>
			</table>
		</div>
	</section>
</section>