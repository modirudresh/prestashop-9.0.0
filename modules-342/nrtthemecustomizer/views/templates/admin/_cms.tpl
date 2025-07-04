<script type="text/template" id="wrapper-form-cms-config">
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Select Header Layout' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="header_layout" class="custom-select form-control">
				{if isset($headers)}
					{foreach from=$headers item=header}
						<option value="{$header.id}" {if isset($selected.header_layout) && $selected.header_layout == $header.id}selected="selected"{/if}>
							{$header.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
	
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Select Header Sticky Layout' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="header_sticky_layout" class="custom-select form-control">
				{if isset($headers)}
					{foreach from=$headers item=header}
						<option value="{$header.id}" {if isset($selected.header_sticky_layout) && $selected.header_sticky_layout == $header.id}selected="selected"{/if}>
							{$header.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
	
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Header overlap' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="header_overlap" class="custom-select form-control">
				{if isset($status)}
					{foreach from=$status item=statu}
						<option value="{$statu.value}" {if isset($selected.header_overlap) && $selected.header_overlap == $statu.value}selected="selected"{/if}>
							{$statu.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
	
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Select Footer Layout' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="footer_layout" class="custom-select form-control">
				{if isset($footers)}
					{foreach from=$footers item=footer}
						<option value="{$footer.id}" {if isset($selected.footer_layout) && $selected.footer_layout == $footer.id}selected="selected"{/if}>
							{$footer.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>

	<div class="form-group row">
		<label class="form-control-label">
			{l s='Page title' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="page_title_layout" class="custom-select form-control">
				{if isset($titles)}
					{foreach from=$titles item=title}
						<option value="{$title.value}" {if isset($selected.page_title_layout) && $selected.page_title_layout == $title.value}selected="selected"{/if}>
							{$title.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
	
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Open vertical menu' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="open_vertical_menu" class="custom-select form-control">
				{if isset($status)}
					{foreach from=$status item=statu}
						<option value="{$statu.value}" {if isset($selected.open_vertical_menu) && $selected.open_vertical_menu == $statu.value}selected="selected"{/if}>
							{$statu.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
</script>

<script type="text/javascript">
	$(document).ready(function () {
		var $wrapperCmsConfig = $('#cms_page_content').closest('.form-group'),
			$btnTemplateCmsConfig = $('#wrapper-form-cms-config');
			$wrapperCmsConfig.after($btnTemplateCmsConfig.html());
	});
</script>
