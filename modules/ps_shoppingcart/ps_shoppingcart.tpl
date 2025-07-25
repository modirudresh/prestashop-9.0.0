{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div id="blockcart-wrapper">
  <div class="blockcart cart-preview" data-refresh-url="{$refresh_url}">
    <div class="header">
      <a rel="nofollow" href="{$cart_url}">
        <span>{l s='Cart' d='Shop.Theme.Actions'}</span>
        <span>{$cart.summary_string}</span>
      </a>
    </div>
    <div class="body">
      {if $cart.products|count}
        <button id="remove-all-from-cart" type="button" class="btn btn-danger btn-sm" style="width:100%;margin-bottom:10px;">
          <i class="material-icons">&#xE872;</i>
        </button>
      {/if}
      <ul>
        {foreach from=$cart.products item=product}
          <li>{include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}</li>
        {/foreach}
      </ul>

      {* REMOVE ALL BUTTON *}
      {if $cart.products|count}
        <form method="post" action="{$cart_url}" class="remove-all-form mt-3">
          <input type="hidden" name="delete_all" value="1" />
          <input type="hidden" name="token" value="{$static_token}" />
          <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 remove-all-btn" title="{l s='Remove all items from cart' d='Shop.Theme.Actions'}">
            <i class="material-icons">delete_forever</i>
            <span>{l s='Remove All Items' d='Shop.Theme.Actions'}</span>
          </button>
        </form>
      {/if}


      <div class="cart-subtotals">
        {foreach from=$cart.subtotals item="subtotal"}
          {if isset($subtotal.type, $subtotal.label, $subtotal.amount)}
            <div class="{$subtotal.type}">
              <span class="label">{$subtotal.label}</span>
              <span class="value">{$subtotal.amount}</span>
            </div>
          {/if}
        {/foreach}
      </div>
      <div class="cart-total">
        <span class="label">{$cart.totals.total.label}</span>
        <span class="value">{$cart.totals.total.amount}</span>
      </div>
    </div>
  </div>
</div>
