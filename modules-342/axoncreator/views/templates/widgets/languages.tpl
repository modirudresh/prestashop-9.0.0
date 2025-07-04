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

<div class="axps-dropdown-wrapper">
	<div class="axps-dropdown-toggle" data-toggle="axps-dropdown-widget">
		<img src="{Context::getContext()->link->getMediaLink(_THEME_LANG_DIR_)}{$current_language.id_lang}.jpg" alt="{$current_language.name_simple}" width="16" height="11"/>
		<span class="axps-dropdown-toggle-text">{$current_language.name_simple}</span>
		<span class="icon-toggle fa fa-angle-down"></span>
	</div>
	<div class="axps-dropdown-menu">
		{foreach from=$languages item=language}
			<a data-btn-lang="{$language.id_lang}" href="javascript:void(0)"{if $language.id_lang == $current_language.id_lang} class="selected"{/if}>
				<img src="{Context::getContext()->link->getMediaLink(_THEME_LANG_DIR_)}{$language.id_lang}.jpg" alt="{$language.iso_code}" width="16" height="11"/>
				{$language.name_simple}
			</a>
		{/foreach}
	</div>
</div>