
<div id="search_form_responsive">
    <div class="container">
      <div id="search_form" class="row">
        <div class="col-lg-5 input-group">
          <span class="input-group-addon"><i class="icon-v-search"></i></span>
          <input type="text" name="facet_search" id="search_input" class="form-control" placeholder="{l s='Je recherche' d='Shop.Theme'}">
        </div>
        {foreach from=$filters item=filter}
        	{if in_array($filter.name, array('Appellation'))}
				<div class="col-lg-2">
					<select name="{$filter.name}" id="{$filter.name}" class="form-control form-control-select">
						<option selected disabled value="">{$filter.name}</option>
						{foreach from=$filter.values item=value}
						<option value="{$value.name}">{$value.name}</option>
						{/foreach}
					</select>
				</div>
	        {/if}
		{/foreach}
		  {foreach from=$filters item=filter}
        	{if in_array($filter.name, array('Couleur'))}
				<div class="col-lg-2">
					<select name="{$filter.name}" id="{$filter.name}" class="form-control form-control-select">
						<option selected disabled value="">{$filter.name}</option>
						{foreach from=$filter.values item=value}
						<option value="{$value.name}">{$value.name}</option>
						{/foreach}
					</select>
				</div>
	        {/if}
		  {/foreach}
		  {foreach from=$filters item=filter}
        	{if in_array($filter.name, array('Prix'))}
				<div class="col-lg-2">
					<select name="{$filter.name}" id="{$filter.name}" class="form-control form-control-select">
						<option selected disabled value="">{l s='Budget' d='Shop.Theme'}</option>
						{foreach from=$filter.list_of_values item=value}
						<option value="{$value.0}-{$value.1}">{Tools::displayPrice($value.0)} - {Tools::displayPrice($value.1)}</option>
						{/foreach}
					</select>
				</div>
	        {/if}
        {/foreach}
        <div class="col-lg-1">
          <input class="btn btn-primary" type="submit" id="search_button" value="{l s='OK' d='Shop.Theme'}">
        </div>
      </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#search_button').click(function(){
			var search = ''
			var params = [];
			var url = window.location.href + '2-accueil?';

			if ($('#search_input').val() != '')
				search += 'search='+$('#search_input').val()+'&';

			if ($('#Couleur').val() != null)
				params.push('Couleur-'+$('#Couleur').val());

			if ($('#Appellation').val() != null)
				params.push('Appellation-'+$('#Appellation').val());

			if ($('#Prix').val() != null)
				params.push('Prix-€-'+$('#Prix').val());

			// console.log(url+(search != '' ? search : '')+(params.length ? params.join("/") : ''));
			if (params.length == 0 && search == '')
				return false;
			window.location = url+(search != '' ? search : '')+(params.length ? 'q='+params.join("/") : '');
		});
	});
</script>