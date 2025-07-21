{if isset($gift_wrapping_price)}

<div class="gift-wrapping-box card mt-3 p-3">
    <h4 class="h5 mb-3">{l s='Gift Wrapping' mod='giftwrapping'}</h4>

    <div class="gift-wrapping-content d-flex align-items-center justify-content-between">
        <p class="mb-0">
            {l s='Add beautiful gift wrapping to your order for just' mod='giftwrapping'} <strong>{$gift_wrapping_price}</strong>
        </p>

        <button type="button" class="btn btn-outline-primary btn-sm" onclick="alert('{l s='Gift wrapping will be added at checkout.' mod='giftwrapping'}')">
            {l s='Add Gift Wrapping' mod='giftwrapping'}
        </button>
    </div>
</div>

{/if}
