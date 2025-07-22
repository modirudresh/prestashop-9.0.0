{if $promo_image || $promo_message || $promo_description || ($promo_link_text && $promo_link_url)}
<section class="custom-promo-block promo-position-{$promo_position|escape:'htmlall':'UTF-8'}">

 {if $promo_message}
        <h2 class="custom-promo-heading">{$promo_message|escape:'htmlall':'UTF-8'}</h2>
    {/if}

    {if $promo_description}
        <div class="custom-promo-description">
            {$promo_description nofilter}
        </div>
    {/if}

    {if $promo_link_url && $promo_image}
        <a href="{$promo_link_url|escape:'htmlall':'UTF-8'}" class="custom-promo-image" target="_blank" rel="noopener noreferrer">
            <img src="{$promo_image|escape:'htmlall':'UTF-8'}" alt="Promo Image" loading="lazy" />
        </a>
    {/if}
</section>
{/if}
