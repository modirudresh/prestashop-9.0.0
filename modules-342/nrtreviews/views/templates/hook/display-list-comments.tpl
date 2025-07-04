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

<div id="reviews-list-comments-item">
    {foreach from=$reviews item="review"}
        <div class="item-review">
            <div class="item-review-top">
                <span class="star_content star_content_avg">
                    <span style="width:{($review.rating/5)*100}%"></span>
                </span>
                <span class="by-author">
                    <span class="author">
                        <span>{$review.customer_name}</span>
                    </span>
                    <span class="date">{dateFormat date=$review.date_add full=0}</span>
                </span>
            </div>
            <div class="comment-details">
                <h4>{$review.title}</h4>
                <p>{$review.comment nofilter}</p>
                {if isset($review.images) && is_array($review.images) && count($review.images)}
                    <div class="img-group" data-lightbox-wrapper="wrapper">
                        {foreach from=$review.images item="image"}
                            <a href="{$image}" class="js-open-lightbox" data-elementor-open-lightbox="no" data-lightbox-item="image">
                                <img
                                    class="img-responsive"
                                    src="{$image}"
                                    alt=""
                                    loading="lazy"
                                >
                            </a>
                        {/foreach}
                    </div>	
                {/if}
                {if $useFulness}
                    <div class="use-fulness-btn">
                        <span>{l s='Was this review helpful?' mod='nrtreviews'}</span>
                        <button class="js-review-fulness js-review-fulness-{$review.id_nrt_review_product}" data-id-review="{$review.id_nrt_review_product}" data-value="1">
                            <i class="las la-thumbs-up"></i>
                            <span id="js-fulness-text-{$review.id_nrt_review_product}">{$review.fulness}</span>
                        </button>
                        <button class="js-review-fulness js-review-fulness-{$review.id_nrt_review_product}" data-id-review="{$review.id_nrt_review_product}" data-value="0">
                            <i class="las la-thumbs-down"></i>
                            <span id="js-nofulness-text-{$review.id_nrt_review_product}">{$review.no_fulness}</span>
                        </button>
                    </div>
                {/if}
            </div>
        </div>
        <hr/>
    {/foreach}
</div>

{include file='module:nrtreviews/views/templates/hook/pagination.tpl'}