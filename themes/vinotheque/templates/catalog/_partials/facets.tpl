{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div id="search_filters">
  <div class="facets-title">{l s='Affiner ma sélection' d='Shop.Theme'}</div>
  {foreach from=$facets item="facet" name="facetnumber"}
    {if $facet.displayed}
      <section class="facet">
        <h1 class="h3"><a class="collapsed" data-toggle="collapse" href="#collapse{$smarty.foreach.facetnumber.iteration}" aria-expanded="false" aria-controls="collapse{$smarty.foreach.facetnumber.iteration}">{$facet.label}</a></h1>
        {if $facet.widgetType !== 'dropdown'}
          <ul id="collapse{$smarty.foreach.facetnumber.iteration}" class="collapse" aria-expanded="false">
            {foreach from=$facet.filters item="filter" name="filterindex"}
              {if $filter.displayed}
                <li{if $smarty.foreach.filterindex.index >=15} class="more-hidden"{/if}>
                  <label>
                    {if $facet.multipleSelectionAllowed}
                      <input
                        data-search-url="{$filter.nextEncodedFacetsURL}"
                        {if !$js_enabled} class="ps-shown-by-js" {/if}
                        type="checkbox"
                        {if $filter.active } checked {/if}
                      >
                    {else}
                      <input
                        data-search-url="{$filter.nextEncodedFacetsURL}"
                        {if !$js_enabled} class="ps-shown-by-js" {/if}
                        type="radio"
                        name="filter {$facet.label}"
                        {if $filter.active } checked {/if}
                      >
                    {/if}

                    <a
                      rel="nofollow"
                      href="{$filter.nextEncodedFacetsURL}"
                      class="js-search-link {if $filter.active} active {/if}"
                    >
                      {$filter.label}
                      {if $filter.magnitude}
                        <span class="magnitude">{$filter.magnitude}</span>
                      {/if}
                    </a>
                  </label>
                </li>
              {/if}
              {if $smarty.foreach.filterindex.index == 14}
                <li><a href="#" class="show-more">{l s='Voir plus' d='Shop.Theme'}</a></li>
              {/if}
            {/foreach}
          </ul>
        {else}
          <form>
            <input type="hidden" name="order" value="{$sort_order}">
            <select name="q">
              <option disabled selected hidden>{l s='(no filter)' d='Shop.Theme'}</option>
              {foreach from=$facet.filters item="filter"}
                {if $filter.displayed}
                  <option
                    {if $filter.active}
                      selected
                      value="{$smarty.get.q}"
                    {else}
                      value="{$filter.nextEncodedFacets}"
                    {/if}
                  >
                    {$filter.label}
                    {if $filter.magnitude}
                      ({$filter.magnitude})
                    {/if}
                  </option>
                {/if}
              {/foreach}
            </select>
            {if !$js_enabled}
              <button class="ps-hidden-by-js" type="submit">
                {l s='Filter' d='Shop.Theme.Actions'}
              </button>
            {/if}
          </form>
        {/if}
      </section>
    {/if}
  {/foreach}
</div>
