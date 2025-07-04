<div class="form-group row">
	<label class="control-label col-12">
		{l s='Select Product Layout' mod='nrtthemecustomizer'}
	</label>
	<div class="col-12">
		<select name="product_layout" class="custom-select form-control">
            {if isset($layouts)}
                {foreach from=$layouts item=layout}
                    <option value="{$layout.value}" {if isset($selected.product_layout) && $selected.product_layout == $layout.value}selected="selected"{/if}>
						{$layout.name}
					</option>
                {/foreach}
            {/if}
		</select>
	</div>
</div>

<div class="form-group row">
	<label class="control-label col-12">
		{l s='Width container' mod='nrtthemecustomizer'}
	</label>
	<div class="col-12">
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
	<label class="control-label col-12">
		{l s='Tabs Type' mod='nrtthemecustomizer'}
	</label>
	<div class="col-12">
		<select name="tab_type" class="custom-select form-control">
            {if isset($tabsType)}
                {foreach from=$tabsType item=tabType}
                    <option value="{$tabType.value}" {if isset($selected.tab_type) && $selected.tab_type == $tabType.value}selected="selected"{/if}>
						{$tabType.name}
					</option>
                {/foreach}
            {/if}
		</select>
	</div>
</div>