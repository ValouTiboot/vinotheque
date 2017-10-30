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
{extends file='page.tpl'}

{block name='breadcrumb'}
{/block}

{block name='page_header_container'}
{/block}

{block name='top_column'}
  <div id="top_column">
	  {hook h="displayTopColumn"}
  </div>
  <div id="top_column_after_responsive" class="hidden-sm-down">
    <div class="container">
      <div id="top_column_after" class="row">
        <div class="col-md-6 col-lg-3 reassurance reassurance_header">
          <img src="{$urls.img_url|escape:'html':'UTF-8'}selection.png" alt="{l s='La sélection' d='Shop.Theme'}">
          <p class="reassurance_title">{l s='La sélection' d='Shop.Theme'}</p>
          <p>{l s='Les vins sont dégustés et sélectionnés avec beaucoup de rigueur.' d='Shop.Theme'}</p>
        </div>
        <div class="col-md-6 col-lg-3 reassurance reassurance_header">
          <img src="{$urls.img_url|escape:'html':'UTF-8'}conseil.png" alt="{l s='Le conseil' d='Shop.Theme'}">
          <p class="reassurance_title">{l s='Le conseil' d='Shop.Theme'}</p>
          <p>{l s='Nous sommes à votre écoute au' d='Shop.Theme'} <b>05 57 10 41 41</b>.</p>
        </div>
        <div class="col-md-6 col-lg-3 reassurance reassurance_header">
          <img src="{$urls.img_url|escape:'html':'UTF-8'}livraison.png" alt="{l s='Une livraison sur-mesure' d='Shop.Theme'}">
          <p class="reassurance_title">{l s='Une livraison sur-mesure' d='Shop.Theme'}</p>
          <p>{l s='Plusieurs modes de livraison selon vos besoins.' d='Shop.Theme'}</p>
        </div>
        <div class="col-md-6 col-lg-3 reassurance reassurance_header">
          <img src="{$urls.img_url|escape:'html':'UTF-8'}fidelite.png" alt="{l s='Programme fidélité' d='Shop.Theme'}">
          <p class="reassurance_title">{l s='Programme fidélité' d='Shop.Theme'}</p>
          <p>{l s='Convertissez vos points fidélité en bon d\'achat.' d='Shop.Theme'}</p>
        </div>
      </div>
    </div>
  </div>
  <div id="top_faceted">
	  {hook h="displayTopFaceted"}
  </div>
  {*<div id="search_form_responsive">*}
    {*<div class="container">*}
      {*<div id="search_form" class="row">*}
        {*<div class="col-lg-5 input-group">*}
          {*<span class="input-group-addon"><i class="icon-v-search"></i></span>*}
          {*<input type="text" id="search_input" class="form-control" placeholder="{l s='Je recherche' d='Shop.Theme'}">*}
        {*</div>*}
        {*<div class="col-lg-2">*}
          {*<select name="" id="appellation" class="form-control form-control-select">*}
            {*<option value="0">Appellation</option>*}
            {*<option value="1">Rouge</option>*}
            {*<option value="2">Blanc</option>*}
            {*<option value="3">Rosé</option>*}
          {*</select>*}
        {*</div>*}
        {*<div class="col-lg-2">*}
          {*<select name="" id="couleur" class="form-control form-control-select">*}
            {*<option value="0">Couleur</option>*}
            {*<option value="1">Rouge</option>*}
            {*<option value="2">Blanc</option>*}
            {*<option value="3">Rosé</option>*}
          {*</select>*}
        {*</div>*}
        {*<div class="col-lg-2">*}
          {*<select name="" id="budget" class="form-control form-control-select">*}
            {*<option value="0">Budget</option>*}
            {*<option value="1">Rouge</option>*}
            {*<option value="2">Blanc</option>*}
            {*<option value="3">Rosé</option>*}
          {*</select>*}
        {*</div>*}
        {*<div class="col-lg-1">*}
          {*<input class="btn btn-primary" type="submit" id="search_button" value="{l s='OK' d='Shop.Theme'}">*}
        {*</div>*}
      {*</div>*}
    {*</div>*}
  {*</div>*}
{/block}


{block name='page_content_container'}
  <section id="content" class="page-content">
	  {block name='page_content'}
		  {block name='hook_home'}
			  {$HOOK_HOME nofilter}
		  {/block}
	  {/block}
  </section>
{/block}
