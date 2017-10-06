{foreach $pictos_link as $picto}
	<div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
		<div>
			<i class="pictoicon-{$picto.url}"></i>
			<span>{$picto.name}</span>
		</div>
	</div>
{/foreach}
