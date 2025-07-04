{**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 **}
	
<script type="text/template" id="btn-edit-page-builder-category">
    <div>
		{if $urlPageBuilder }
			<br />
			<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i> 
				{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
			</a>
		{else}
			<br />
			<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
		{/if}
    </div>
</script>

<script type="text/template" id="btn-edit-page-builder-product">
    <div>
		{if $urlPageBuilder }
			<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i> 
				{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
			</a>
		{else}
			<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
		{/if}
    </div>
	<br />
</script>
	
<script type="text/template" id="btn-edit-page-builder-cms">
    <div>
		{if $urlPageBuilder }
			<br />
			<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i> 
				{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
			</a>
		{else}
			<br />
			<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
		{/if}
    </div>
</script>

<script type="text/template" id="btn-edit-page-builder-blog">
    <div class="form-group">
        <label class="control-label col-lg-3"></label>
        <div class="col-lg-9">
			{if $urlPageBuilder }
				<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i>
					{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
				</a>
			{else}
				<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
			{/if}
		</div>
    </div>
</script>
	 
<script type="text/template" id="btn-edit-page-builder-manufacturer">
    <div>
		{if $urlPageBuilder }
			<br />
			<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i> 
				{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
			</a>
		{else}
			<br />
			<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
		{/if}
    </div>
</script>
	 
<script type="text/template" id="btn-edit-page-builder-supplier">
    <div>
		{if $urlPageBuilder }
			<br />
			<a href="{$urlPageBuilder}" class="btn btn-info axps-btn-edit"><i class="icon-external-link"></i> 
				{l s='Add extendend content with - AxonCreator' mod='axoncreator'}
			</a>
		{else}
			<br />
			<div class="alert alert-info">&nbsp;{l s='Save page first to enable AxonCreator' mod='axoncreator'}</div>
		{/if}
    </div>
</script>

<script type="text/javascript">
	$(document).ready(function () {
		var $wrapperCategory = $('div#category_description, div#root_category_description').closest('.col-sm'),
			$wrapperProduct = $('#features'),
			$wrapperProduct_2 = $('#product_description_description'),
			$wrapperCms = $('#cms_page_content'),
			$wrapperBlog = $('#smart_blog_post_form').find("[name^=content_]").first().parents('.form-group').last(),
			$wrapperManufacturer = $('div#manufacturer_description').closest('.col-sm'),
			$wrapperSupplier = $('div#supplier_description').closest('.col-sm'),

			$btnTemplateCategory = $('#btn-edit-page-builder-category'),
			$btnTemplateProduct = $('#btn-edit-page-builder-product'),
			$btnTemplateCms = $('#btn-edit-page-builder-cms'),
			$btnTemplateBlog = $('#btn-edit-page-builder-blog'),
			$btnTemplateManufacturer = $('#btn-edit-page-builder-manufacturer'),
			$btnTemplateSupplier = $('#btn-edit-page-builder-supplier');

			$wrapperCategory.append($btnTemplateCategory.html());
			$wrapperProduct.prepend($btnTemplateProduct.html());
			$wrapperProduct_2.append($btnTemplateProduct.html());
			$wrapperCms.after($btnTemplateCms.html());
			$wrapperBlog.after($btnTemplateBlog.html());
			$wrapperManufacturer.append($btnTemplateManufacturer.html());
			$wrapperSupplier.append($btnTemplateSupplier.html());
	});
</script>
