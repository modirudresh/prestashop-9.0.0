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

<form id="contact_{$id_widget}" class="elementor-form elementor-contact-form ajax-elementor-contact" action="{Context::getContext()->shop->getBaseURL(true, false)}{$smarty.server.REQUEST_URI}#contact_{$id_widget}" method="post" {if $contact.allow_file_upload}enctype="multipart/form-data"{/if}>
	<div class="elementor-form-fields-wrapper form-fields">
		{if $settings.subject_id}
			<input type="hidden" name="id_contact" value="{$settings.subject_id}">
		{else}
			<div class="elementor-field-group elementor-column elementor-field-type-select elementor-col-{$settings.subject_width}">
				{if (bool) $settings.show_labels}
					<label class="elementor-field-label">
						{l s='Subject' d='Shop.Forms.Labels'}
					</label>
				{/if}
				<div class="elementor-select-wrapper">
					<select name="id_contact" class="elementor-field elementor-field-textual elementor-size-sm">
						{foreach from=$contact.contacts item=contact_elt}
							<option value="{$contact_elt.id_contact}">{$contact_elt.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
		{/if}
		<div class="elementor-field-group elementor-column elementor-field-type-email elementor-col-{$settings.email_width}">
			{if (bool) $settings.show_labels}
				<label class="elementor-field-label">
					{l s='Email address' d='Shop.Forms.Labels'}
				</label>
			{/if}
			<input class="elementor-field elementor-field-textual elementor-size-sm" name="from" type="email" value="{$contact.email|escape:'htmlall':'UTF-8'}" placeholder="{l s='your@email.com' d='Shop.Forms.Help'}">
		</div>
		{if $contact.allow_file_upload && $settings.show_upload}
			<div class="elementor-field-group elementor-column elementor-field-type-file elementor-col-{$settings.upload_width}">
				{if (bool) $settings.show_labels}
					<label class="elementor-field-label">
						{l s='Attachment' d='Shop.Forms.Labels'}
					</label>
				{/if}
				<input class="elementor-field" type="file" name="fileUpload">
			</div>
		{/if}
		<div class="elementor-field-group elementor-column elementor-field-type-textarea elementor-col-{$settings.message_width}">
			{if (bool) $settings.show_labels}
				<label class="elementor-field-label">
					{l s='Message' d='Shop.Forms.Labels'}
				</label>
			{/if}
			<textarea class="elementor-field elementor-field-textual elementor-size-sm" name="message" rows="{$settings.message_rows}">{$contact.message|escape:'htmlall':'UTF-8'}</textarea>
		</div>
		{if isset($id_module)}
			<div class="elementor-field-group elementor-column elementor-col-100">
				{hook h='displayNrtCaptcha' id_module=$id_module}
			</div>
		{/if}
        {if isset($id_module) && !$settings.disable_psgdpr}
            <div class="elementor-field-group elementor-column elementor-col-100">
                <div class="elementor_psgdpr_consent_message">
                    {hook h='displayGDPRConsent' id_module=$id_module}
                </div>
            </div>
        {/if}
		<div class="elementor-field-group elementor-column elementor-col-100 send-response"></div>
		<div class="elementor-field-group elementor-field-type-submit elementor-column elementor-col-{$settings.button_width}">
			<input type="hidden" name="url" value=""/>
			<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />
			<button class="elementor-button elementor-size-sm elementor-animation-{$settings.button_hover_animation}" type="submit" name="submitMessage">
				<span>
					<span class="elementor-button-icon elementor-align-icon-{$settings.icon_align}"><i class="icon-loading fa fa-circle-notch"></i>{if $settings.icon}{$settings.icon nofilter}{/if}</span>
					<span class="elementor-button-text">{l s='Send' d='Shop.Theme.Actions'}</span>
				</span>
			</button>
		</div>
	</div>
</form>
