{*
 * 2008 - 2017 (c) HDClic
 *
 * MODULE PrestaBlog
 *
 * @author    HDClic <prestashop@hdclic.com>
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 * @version    4.0.1
 * @link    http://www.hdclic.com
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 *}

<!-- Module Presta Blog -->
<div id="prestablog_menu_cat" class="col-12">
	<div id="menu-mobile">
		<span>{l s="Catégories" mod="prestablog"}</span>
	</div>
	<nav>
		{PrestaBlogContent return=$MenuCatNews}
	</nav>
</div>
<!-- Module Presta Blog -->
