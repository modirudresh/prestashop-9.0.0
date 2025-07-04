<ul id="group_{$id_attribute_group}">
    {foreach from=$attributes key=id_attribute item=group_attribute}
        <li class="input-container">
            <label aria-label="{$group_attribute.name}">
                <input class="input-color" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} checked="checked"{/if}>
                <span {if $group_attribute.texture}
                        class="color texture ax-lazy-load" data-src="{$group_attribute.texture}"
                      {elseif $group_attribute.html_color_code}
                        class="color" style="background-color: {$group_attribute.html_color_code}"
                      {/if}
                >				  
                    <span class="corlor-tooltip">
                        <span {if $group_attribute.texture} 
                                class="bg-tooltip {$v_imageType} ax-lazy-load" data-src="{$group_attribute.texture}" 
                              {elseif $group_attribute.html_color_code} 
                                class="bg-tooltip" style="background-color: {$group_attribute.html_color_code}" 
                              {/if}
                        >
                        </span>
                        <span class="name-tooltip">{$group_attribute.name}</span>
                    </span>
                </span>
            </label>
        </li>
    {/foreach}
</ul>