<section class="custom-promo-block promo-position-{$promo_position|escape:'htmlall':'UTF-8'}">
    {if $promo_image}
        <div class="custom-promo-image">
            <img src="{$promo_image|escape:'htmlall':'UTF-8'}" alt="Promo Image" loading="lazy" />
        </div>
    {/if}

    {if $promo_message}
        <h2 class="custom-promo-heading">{$promo_message|escape:'htmlall':'UTF-8'}</h2>
    {/if}

    {if $promo_description}
        <div class="custom-promo-description">
            {$promo_description nofilter}
        </div>
    {/if}

    {if $promo_link_text && $promo_link_url}
        <div class="custom-promo-cta">
            <a href="{$promo_link_url|escape:'htmlall':'UTF-8'}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                {$promo_link_text|escape:'htmlall':'UTF-8'}
            </a>
        </div>
    {/if}
</section>
