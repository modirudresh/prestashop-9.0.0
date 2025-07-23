{if isset($cart.products) && $cart.products|@count > 0 && $gift_wrapping_enabled && $gift_wrapping_price > 0}
<div class="customgiftwrapping-form p-4 container-fluid">
    <h4 class="card-title">{l s='Gift Wrapping Options' mod='customgiftwrapping'}</h4>

    {if $gift_wrap_applied}
        <div class="alert alert-success gift-wrap-status">
            <p class="mb-2">
                <strong>{l s='Gift wrapping has already been applied to your order.' mod='customgiftwrapping'}</strong>
            </p>
            <div class="gift-wrap-details mb-3">
                <p>
                    {l s='Gift Wrapping Fee:' mod='customgiftwrapping'}
                    {$gift_wrapping_price|number_format:2:'.':','} {$currency.sign}
                </p>
            </div>

            {if isset($selected_wrap_image)}
                <p>{l s='Your selected gift wrap:' mod='customgiftwrapping'}</p>
                <img src="{$selected_wrap_image}"
                     alt="{l s='Selected Gift Wrap' mod='customgiftwrapping'}"
                     class="img-thumbnail selected-wrap-image mb-3"
                     style="max-height: 80px; height: auto;" />
            {/if}

            <form method="post" class="remove-gift-wrap-form">
                <button type="submit" name="removeGiftWrapping" class="btn btn-outline-danger w-100 w-sm-auto">
                    {l s='Remove Gift Wrapping' mod='customgiftwrapping'}
                </button>
            </form>

    {elseif isset($wrapper_images) && $wrapper_images|@count > 0}
        <div class="gift-wrap-selection">
            <p>{l s='Please select a gift wrap option below:' mod='customgiftwrapping'}</p>

            <form method="post" class="gift-wrap-form">
                <div class="customgiftwrapping-options overflow-auto d-flex px-auto">
                    {foreach from=$wrapper_images item=img key=idx}
                        <div class="gift-wrap-option text-center flex-shrink-0" style="width: 60px;">
                            <input type="radio" 
                                   name="gift_wrap_selection" 
                                   id="giftwrap-{$idx}" 
                                   value="{$img}" 
                                   class="giftwrap-radio" 
                                   required />

                            <label for="giftwrap-{$idx}" class="d-block gift-wrap-label">
                                <img src="{$img}"
                                     alt="{l s='Gift Wrap Option' mod='customgiftwrapping'}"
                                     class="img-fluid img-thumbnail gift-wrap-img"
                                     style="max-height: 100px;" />
                            </label>
                        </div>
                    {/foreach}
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" name="submitGiftWrapping" class="btn btn-primary">
                        <i class="material-icons">gift</i>
                        <span>
                            {l s='Add Gift Wrapping' mod='customgiftwrapping'} 
                            {$gift_wrapping_price|number_format:2:'.':','} {$currency.sign}
                        </span>
                    </button>
                </div>
            </form>
        </div>

    {else}
        <div class="alert alert-warning">
            {l s='No gift wrapping options available at the moment.' mod='customgiftwrapping'}
        </div>
    {/if}
</div>
{/if}
