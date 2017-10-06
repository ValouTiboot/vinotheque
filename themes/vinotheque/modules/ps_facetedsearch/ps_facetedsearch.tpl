{if isset($listing.rendered_facets)}
<div class="col-12 hidden-md-up filter-button">
    <button id="search_filter_toggler" class="btn btn-secondary">
        {l s='Filter' d='Shop.Theme.Actions'}
    </button>
</div>
<div id="search_filters_wrapper" class="col-12 hidden-sm-down">
  <div id="search_filter_controls" class="hidden-md-up">
      <span id="_mobile_search_filters_clear_all"></span>
      <button class="btn btn-secondary ok">
        <i class="material-icons">&#xE876;</i>
        {l s='OK' d='Shop.Theme.Actions'}
      </button>
  </div>
  {$listing.rendered_facets nofilter}
</div>
{/if}
