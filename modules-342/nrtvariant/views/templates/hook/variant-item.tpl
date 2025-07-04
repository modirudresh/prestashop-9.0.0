{if count($main_variants) > 1}
    {foreach from=$main_variants key=key_variant item=variant}
        <a  href="{$variant.url}"
            class="{if $variant.texture}ax-lazy-load {/if}{$variant.type}{if $id_product_attribute == $variant.id_product_attribute && $axs_variant} active{/if} ax-swatch-inner js-variant{if isset($NRT_variant_limit) && $NRT_variant_limit && count($main_variants) > $NRT_variant_limit && $key_variant >= $NRT_variant_limit} hidden{/if}"
            title="{$variant.name}"
            data-url="{url entity='module' name='nrtvariant' controller='actions'}"
            data-tpl-product="{$tpl_product}"
            data-image-type="{$imageType}"
            data-id-product="{$variant.id_product}"
            data-id-product-attribute="{$variant.id_product_attribute}"
            {if $variant.texture} data-src="{$variant.texture}" 
            {elseif $variant.html_color_code} style="background-color: {$variant.html_color_code}" {/if}
        >
            <span class="corlor-tooltip">
                <span {if $variant.texture} 
                        class="bg-tooltip {$v_imageType} ax-lazy-load" data-src="{$variant.texture}" 
                      {elseif $variant.html_color_code} 
                        class="bg-tooltip" style="background-color: {$variant.html_color_code}" 
                      {/if}
                >
                </span>
                <span class="name-tooltip">{$variant.name}</span>
            </span>
        </a>
    {/foreach}
    {if isset($NRT_variant_limit) && $NRT_variant_limit && count($main_variants) > $NRT_variant_limit}
        <span class="ax-swatches-more">+{count($main_variants) - $NRT_variant_limit}</span>
    {/if}
{/if}