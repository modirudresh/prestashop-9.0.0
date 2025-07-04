{if $archive_type==1}
	<div class="widget block-categories">
		<div class="widget-content">
			<div class="widget-title h3"><span>{l s='Blog Archive' mod='smartblogarchive'}</span></div>
			<div class="block_content">
				 <ul>
					  {foreach from=$archives item="archive"}
						{foreach from=$archive.month item="months"}

						  {$options.year = $archive.year}
						  {$options.month = $months.month}

						  {assign var="monthname" value=null}
						  {if $months.month == 1}
							{$monthname = {l s='January' mod='smartblogarchive'}}
						  {elseif $months.month == 2}
							{$monthname = {l s='February' mod='smartblogarchive'}}
						  {elseif $months.month == 3}
							{$monthname = {l s='March' mod='smartblogarchive'}} 
						  {elseif $months.month == 4} 
							{$monthname = {l s='Aprill' mod='smartblogarchive'}}
						  {elseif $months.month == 5}
							{$monthname = {l s='May' mod='smartblogarchive'}}
						  {elseif $months.month == 6}
							{$monthname = {l s='June' mod='smartblogarchive'}}
						  {elseif $months.month == 7}
							{$monthname = {l s='July' mod='smartblogarchive'}} 
						  {elseif $months.month == 8}
							{$monthname = {l s='August' mod='smartblogarchive'}} 
						  {elseif $months.month == 9}
							{$monthname = {l s='September' mod='smartblogarchive'}}
						  {elseif $months.month == 10} 
							{$monthname = {l s='October' mod='smartblogarchive'}}
						  {elseif $months.month == 11}
							{$monthname = {l s='November' mod='smartblogarchive'}}
						  {elseif $months.month == 12} 
							{$monthname = {l s='December' mod='smartblogarchive'}}
						  {/if}

						  <li data-depth="0">
							<a title="{$monthname}-{$archive.year}" href="{smartblog::GetSmartBlogLink('smartblog_archive_rule', $options)}">
								{$monthname}-{$archive.year}
							</a>
						  </li>
						{/foreach}
					  {/foreach}
				</ul>
			</div>
		</div>
	</div>
{else}
    {function name="archives" nodes=[] depth=0}
      {strip}
        {if $nodes|count}
          <ul>
            {foreach from=$nodes item=node}
              {if $node.name != ''}
                <li data-depth="{$depth}">
                  {if $depth===0}
                    <a href="{$node.link}" title="{l s=$node.name mod='smartblogarchive'}">{l s=$node.name mod='smartblogarchive'}</a>
                    {if $node.children}
                      
                        {if $isDhtml}
							<span class="navbar-toggler collapse-icons" data-target="#exBlogArchiveCollapsingNavbar{$node.id}" data-toggle="collapse">
							  <i class="las la-plus add"></i>
							  <i class="las la-minus remove"></i>
							</span>
                        {/if}
                    
                      <div class="category-sub-menu{if $isDhtml} collapse{/if}" id="exBlogArchiveCollapsingNavbar{$node.id}">
                        {archives nodes=$node.children depth=$depth+1}
                      </div>
                    {/if}
                  {else}
                    <a class="category-sub-link" href="{$node.link}" title="{l s=$node.name mod='smartblogarchive'}">{l s=$node.name mod='smartblogarchive'}</a>
                    {if $node.children}
                      {if $isDhtml}
                        <span class="arrows" data-toggle="collapse" data-target="#exBlogArchiveCollapsingNavbar{$node.id}">
                          <i class="las la-plus arrow-right"></i>
                          <i class="las la-minus arrow-down"></i>
                        </span>
                    {/if}
                      <div class="category-sub-menu{if $isDhtml} collapse{/if}" id="exBlogArchiveCollapsingNavbar{$node.id}">
                        {archives nodes=$node.children depth=$depth+1}
                      </div>
                    {/if}
                  {/if}
                </li>
              {/if}
            {/foreach}
          </ul>
        {/if}
      {/strip}
    {/function}
    
	{if $archives.children}
		<div class="widget block-categories">
			<div class="widget-content">
				<div class="widget-title h3"><span>{l s='Blog Archive' mod='smartblogarchive'}</span></div>
				<div class="block_content">{archives nodes=$archives.children}</div>
			</div>
		</div>
	{/if}

{/if}
