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

<form class="elementor-newsletter ajax-elementor-subscription block_newsletter" action="{$urls.current_url}#footer" method="post">
	<input name="email" type="email" value="{$value}" placeholder="{if !empty($settings.placeholder)}{$settings.placeholder}{else}{l s='Your email address' d='Shop.Forms.Labels'}{/if}" required
	><button class="elementor-button elementor-animation-{$settings.hover_animation}" name="submitNewsletter" value="1" type="submit">
		<span class="elementor-button-content-wrapper">
			<span class="elementor-button-icon elementor-align-icon-{$settings.icon_align}"><i class="icon-loading fa fa-circle-notch"></i>{if $settings.icon}{$settings.icon nofilter}{/if}</span>
			<span class="elementor-button-text">{if empty($settings.button)}{l s='Subscribe' d='Shop.Theme.Actions'}{else}{$settings.button}{/if}</span>
		</span>
	</button>
	<input type="hidden" name="action" value="0">
	<div class="send-response">
		{if $msg}
			<div class="alert {if $nw_error}alert-danger{else}alert-success{/if}">
			  {$msg}
			</div>
		{/if}
	</div>
    {hook h='displayNewsletterRegistration'}
    {if isset($id_module) && !$settings.disable_psgdpr}
        <div class="elementor_psgdpr_consent_message">
            {hook h='displayGDPRConsent' id_module=$id_module}
        </div>
    {/if}
</form>