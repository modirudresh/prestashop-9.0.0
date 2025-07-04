<div id="ModuleNrtProductVideo">
	<input type="hidden" name="submitted_tabs[]" value="ModuleNrtProductVideo" />
	<h2>{l s='Add or modify customizable properties' mod='nrtproductvideo'}</h2>
	{if isset($display_common_field) && $display_common_field}
		<div class="alert alert-info">{l s='Warning, if you change the value of fields with an orange bullet %s, the value will be changed for all other shops for this product'  mod='nrtproductvideo' sprintf=$bullet_common_field}</div>
	{/if}
    <div class="form-group">
    	<div class="row">
			<div class="col-xs-12 col-md-12">
				<label for="id_nrtproductvideo">{l s='Select from created productvideos' mod='nrtproductvideo'}</label>
			</div>
			<div class="col-xs-12 col-md-12">
				<select class="custom-select form-control" name="id_nrtproductvideo[]" id="id_nrtproductvideo[]">
					<option value="0">- {l s='Choose (optional)' mod='nrtproductvideo'} -</option>
					{if isset($productvideos)}
						{foreach from=$productvideos item=productvideo}
							<option value="{$productvideo.id_productvideo}" {if isset($selectedProductVideo) && in_array($productvideo.id_productvideo, $selectedProductVideo)}selected="selected"{/if}>{$productvideo.title_bo}</option>
						{/foreach}
					{/if}
				</select>
			</div>
        </div>
        <p class="help-block" style="margin-top:15px;">
        {l s='Do not select for show tab global.' mod='nrtproductvideo'}
        </p>
    </div>
    <div class="form-group">
        <a class="btn btn-primary" target="_blank" href="{Context::getContext()->link->getAdminLink('AdminModules')}&configure=nrtproductvideo&addProductVideo=1">
            <i class="material-icons">open_in_new</i>
            {l s='Create new productvideo' mod='nrtproductvideo'}
        </a>
    </div>
</div>