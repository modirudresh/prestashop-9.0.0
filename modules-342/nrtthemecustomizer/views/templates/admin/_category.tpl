<script type="text/template" id="wrapper-form-category-config">
	<div class="form-group row">
		<label class="form-control-label">
			{l s='Select Category Layout' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="category_layout" class="custom-select form-control">
				{if isset($layouts)}
					{foreach from=$layouts item=layout}
						<option value="{$layout.value}" {if isset($selected.category_layout) && $selected.category_layout == $layout.value}selected="selected"{/if}>
							{$layout.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>

	<div class="form-group row">
		<label class="form-control-label">
			{l s='Width container' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="width_type" class="custom-select form-control">
				{if isset($widthTypes)}
					{foreach from=$widthTypes item=widthType}
						<option value="{$widthType.value}" {if isset($selected.width_type) && $selected.width_type == $widthType.value}selected="selected"{/if}>
							{$widthType.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>

	<div class="form-group row">
		<label class="form-control-label">
			{l s='Page title color' mod='nrtthemecustomizer'}
		</label>
		<div class="col-sm">
			<select name="page_title_color" class="custom-select form-control">
				{if isset($colors)}
					{foreach from=$colors item=color}
						<option value="{$color.value}" {if isset($selected.page_title_color) && $selected.page_title_color == $color.value}selected="selected"{/if}>
							{$color.name}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
</script>

<script type="text/javascript">
	$(document).ready(function () {
		var $wrapperCategoryConfig = $('#category_additional_description').closest('.form-group'),
			$btnTemplateCategoryConfig = $('#wrapper-form-category-config');
			$wrapperCategoryConfig.after($btnTemplateCategoryConfig.html());
	});
</script>
