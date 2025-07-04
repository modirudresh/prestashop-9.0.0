{if $nrt_ppp.NRT_NEWSLETTER == 1}
    <div id="moda_popupnewsletter" class="modal popup-type-1 popup-text-color-{$nrt_ppp.NRT_NEWSLETTER_TEXT_COLOR}" tabindex="-1" role="dialog"><div class="modal-dialog" role="document" style="{if $nrt_ppp.NRT_BG == 1 && !empty($nrt_ppp.NRT_BG_IMAGE)}background-image: url({$nrt_ppp.NRT_BG_IMAGE});{/if}">
        <div class="modal-content">
           <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
             <span aria-hidden="true">×</span>
           </button>
            {if $nrt_ppp.NRT_NEWSLETTER_FORM == 1 && Module::isEnabled('ps_emailsubscription')}
				<div class="wrapper-popup">
					<div class="popup-content">
						<div class="popup_icon">
							<i class="las la-envelope"></i>
						</div>
						{if $nrt_ppp.NRT_TITLE}
							<div class="popup_title h3">
								{$nrt_ppp.NRT_TITLE|stripslashes nofilter}
							</div>
						{/if}
						{if $nrt_ppp.NRT_TEXT}
							<div class="popup_text">
								{$nrt_ppp.NRT_TEXT|stripslashes nofilter}
							</div>
						{/if}
						<form class="ajax-newsletter block_newsletter" action="{$link->getPageLink('index')|escape:'html'}" method="post">
							<input class="hidden" type="hidden" name="action" value="0" checked >
							<div class="form-content">
								<input class="inputNew form-control" type="email" name="email" placeholder="{l s='Enter Email Address Here'  mod='nrtpopupnewsletter'}" required />
								<button class="btn btn-primary"  name="submitNewsletter" type="submit">
									 {l s='SUBSCRIBE' mod='nrtpopupnewsletter'}
								</button>
							</div>
							<div class="send-response"></div>
							{if isset($id_module)}
								{hook h='displayGDPRConsent' id_module=$id_module}
							{/if}
						</form>
						<div class="newsletter_block_popup-bottom custom-checkbox">
							<label>
								<input id="newsletter_popup_dont_show_again" type="checkbox">
								<span><i class="las la-check checkbox-checked"></i></span>
								<span>{l s='Don\'t show this popup again' mod='nrtpopupnewsletter'}</span>
							</label>
						</div>
					</div>
				</div>
            {/if}
        </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog --></div><!-- /.modal -->
{/if}
