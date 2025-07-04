{extends file=$layout}

{block name='content'}
  <section id="main">
    <div class="alert alert-danger">
      {l s='Credentials not found. Please contact site admin.' mod='nrtsociallogin'}
    </div>
    {block name='my_account_links'}
      <footer class="page-footer">
        <a href="{$urls.pages.authentication}" class="btn btn-primary-r" title="{l s='Go to Login Page' mod='nrtsociallogin'}">
          <i class="las la-reply"></i>
          {l s='Go to Login Page' mod='nrtsociallogin'}
        </a>
      </footer>
    {/block}
  </section>
{/block}
