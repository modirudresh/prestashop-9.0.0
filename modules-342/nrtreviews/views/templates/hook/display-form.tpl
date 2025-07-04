{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row">	
	<div class="col-xs-12 col-lg-12 col-my-reviews">
		<div id="my_reviews">
			<h3>{l s="Reviews" mod='nrtreviews'}</h3>
			{if $reviews}
				<div class="reviews-top">
					<span class="reviews_note">
						<span class="label">{l s='Item Rating: ' mod='nrtreviews'}</span>
						<span class="star_content star_content_avg"><span style="width:{($avgReviews.avg/5)*100}%"></span></span>
					</span>
					<div class="nbr_reviews"> 
						<span>{$avgReviews.avg|round:1}</span>
						{l s='average based on' mod='nrtreviews'} 
						{$avgReviews.nbr}
						<span>
							{if ($avgReviews.nbr) > 1 }
								{l s='ratings.' mod='nrtreviews'} 
							{else}
								{l s='rating.' mod='nrtreviews'} 
							{/if}
						</span>
					</div>
				</div>
				<hr/>
				<div id="reviews-list-comments" class="reviews-list">
					{include file='module:nrtreviews/views/templates/hook/display-list-comments.tpl'}
				</div>
			{else}
				<p class="align_center">
					{l s='No customer reviews for the moment.' mod='nrtreviews'}
				</p>
			{/if}
		</div>
	</div>
	<div class="col-xs-12 col-lg-12 col-reviews-form">
		<div id="reviews_form">
            {if !$isLogged && !$allowGuests}
                <p class="alert alert-warning">
                    {$logginText nofilter}
                </p>
            {else}
                <h3>{l s="You're reviewing" mod='nrtreviews'} “{$nameProduct nofilter}”</h3> 
                <form class="row" action="#">   
                    <div class="col-xs-12">
                        <label class="label required">{l s="Your rating" mod='nrtreviews'}</label>
                        <span class="star_content">
                            <input id="rating_value_1" class="hidden" type="radio" name="rating" value="1"/>
                            <label class="star-label" for="rating_value_1">
                                <span class="star star_on"></span>
                            </label>
                            <input id="rating_value_2" class="hidden" type="radio" name="rating" value="2"/>
                            <label class="star-label" for="rating_value_2">
                                <span class="star star_on"></span>
                            </label>
                            <input id="rating_value_3" class="hidden" type="radio" name="rating" value="3"/>
                            <label class="star-label" for="rating_value_3">
                                <span class="star star_on"></span>
                            </label>
                            <input id="rating_value_4" class="hidden" type="radio" name="rating" value="4"/>
                            <label class="star-label" for="rating_value_4">
                                <span class="star star_on"></span>
                            </label>
                            <input id="rating_value_5" class="hidden" type="radio" name="rating" value="5" checked/>
                            <label class="star-label" for="rating_value_5">
                                <span class="star star_on"></span>
                            </label>
                        </span>
                        <hr/>
                    </div>
                    <div class="form-group col-xs-12{if !$isLogged} col-md-6{/if}">
                        <label class="required">{l s='Title' mod='nrtreviews'}</label>
                        <input name="title" class="form-control" type="text" value="" required/>
                    </div>
                    {if !$isLogged}
                        <div class="form-group col-xs-12 col-md-6">
                            <label class="required">{l s='Name' mod='nrtreviews'}</label>
                            <input class="form-control" name="customer_name" type="text" value="" required/>
                        </div>
                    {/if}
                    <div class="form-group col-xs-12">
                        <label class="required">{l s='Comment' mod='nrtreviews'}</label>
                        <textarea name="comment" class="form-control" rows="10" required></textarea>
                    </div>
                    {if $allowUpload}
                        <div class="form-group col-xs-12">
                            <label>{l s='Image' mod='nrtreviews'}</label>
                            <div class="group-file-style">
                                <input type="file" name="image[]" class="filestyle" data-buttonText="{l s='Choose file' d='Shop.Theme.Actions'}" multiple>
                            </div>
                            {assign var=authExtensions value=' .'|implode:[ 'gif', 'jpg', 'jpeg', 'jpe', 'png', 'webp' ]}
                            <small class="float-xs-right">.{$authExtensions}</small>
                        </div>
                    {/if}
                    {if isset($id_module)}
                        <div class="form-group col-xs-12">
                            {hook h='displayNrtCaptcha' id_module=$id_module}
                        </div>
                    {/if}
                    {if isset($id_module)}
                        <div class="form-group col-xs-12">
                            {hook h='displayGDPRConsent' id_module=$id_module}
                        </div>
                    {/if}
                    <div class="col-xs-12">
                        <input class="form-control" name="id_product" type="hidden" value='{$idProduct}'/>
                        <div id="reviews_form_error" class="alert alert-danger" style="display:none;"></div>
                    </div>
                    <div id="reviews_form_btn" class="col-xs-12">
                        <button class="btn btn-primary" type="submit">
                            {l s='Submit' mod='nrtreviews'}
                        </button>
                    </div>
                </form>
            {/if}
		</div>	
	</div>
</div>