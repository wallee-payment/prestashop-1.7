<tr>
	<td class="fixed-width-sm center">
		<img class="img-thumbnail" alt="{$method.configurationName}" src="{$method.imageUrl}" />
	</td>
	<td>
		<div id="anchor{$method.configurationName}">
			<div class="method_name">
				{$method.configurationName}
			</div>
		</div>
	</td>
	<td class="actions">
		<div class="btn-group-action">
			<div class="btn-group">
				<a class=" btn btn-default" href={$link->getAdminLink('AdminWalleeMethodSettings')|escape:'htmlall':'UTF-8'}&method_id={$method.id} title="{l s='Configure'}"><i class="icon-wrench"></i> {l s='Configure'}</a>
			</div>
		</div>
	</td>
</tr>