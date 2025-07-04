<div id="ModuleNrtCustomTab">
	<input type="hidden" name="submitted_tabs[]" value="ModuleNrtCustomTab" />
	<h2>{l s='Add or modify customizable properties' mod='nrtcustomtab'}</h2>
	{if isset($display_common_field) && $display_common_field}
		<div class="alert alert-info">{l s='Warning, if you change the value of fields with an orange bullet %s, the value will be changed for all other shops for this product'  mod='nrtcustomtab' sprintf=$bullet_common_field}</div>
	{/if}
    <div class="form-group">
    	<div class="row">
			<div class="col-xs-12 col-md-12">
				<label for="id_nrtcustomtab">{l s='Select from created customtabs' mod='nrtcustomtab'}</label>
			</div>
			<div class="col-xs-12 col-md-12">
				<select class="form-control" name="id_nrtcustomtab[]" id="id_nrtcustomtab[]" multiple="multiple" style="height: 360px;">
					{if isset($customtabs)}
						{foreach from=$customtabs item=customtab}
							<option value="{$customtab.id_customtab}" {if isset($selectedCustomTab) && in_array($customtab.id_customtab, $selectedCustomTab)}selected="selected"{/if}>{$customtab.title_bo}</option>
						{/foreach}
					{/if}
				</select>
			</div>
        </div>
        <p class="help-block" style="margin-top:15px;">
        {l s='Do not select for show tab global.' mod='nrtcustomtab'}
        </p>
    </div>
    <div class="form-group">
        <a class="btn btn-primary" target="_blank" href="{Context::getContext()->link->getAdminLink('AdminModules')}&configure=nrtcustomtab&addCustomTab=1">
            <i class="material-icons">open_in_new</i>
            {l s='Create new customtab' mod='nrtcustomtab'}
        </a>
    </div>
</div>