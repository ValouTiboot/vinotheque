{**
 * pm_multiplefeatures
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *}

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'html'}
		{$input.html_content}{* HTML *}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}