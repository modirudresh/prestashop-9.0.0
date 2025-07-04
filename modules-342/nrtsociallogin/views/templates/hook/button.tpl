{if !Context::getContext()->customer->isLogged() && ((isset($loginizer_data['facebook']['enable']) && $loginizer_data['facebook']['enable'] == 1) || (isset($loginizer_data['gplus']['enable']) && $loginizer_data['gplus']['enable'] == 1) || (isset($loginizer_data['insta']['enable']) && $loginizer_data['insta']['enable'] == 1) || (isset($loginizer_data['twitter']['enable']) && $loginizer_data['twitter']['enable'] == 1) || (isset($loginizer_data['linked']['enable']) && $loginizer_data['linked']['enable'] == 1) || (isset($loginizer_data['yahoo']['enable']) && $loginizer_data['yahoo']['enable'] == 1) || (isset($loginizer_data['live']['enable']) && $loginizer_data['live']['enable'] == 1) || (isset($loginizer_data['foursquare']['enable']) && $loginizer_data['foursquare']['enable'] == 1) || (isset($loginizer_data['amazon']['enable']) && $loginizer_data['amazon']['enable'] == 1) || (isset($loginizer_data['pay']['enable']) && $loginizer_data['pay']['enable'] == 1) || (isset($loginizer_data['github']['enable']) && $loginizer_data['github']['enable'] == 1) || (isset($loginizer_data['disqus']['enable']) && $loginizer_data['disqus']['enable'] == 1) || (isset($loginizer_data['wordpress']['enable']) && $loginizer_data['wordpress']['enable'] == 1) || (isset($loginizer_data['dropbox']['enable']) && $loginizer_data['dropbox']['enable'] == 1))}
<p>{l s='Or sign in with:' mod='nrtsociallogin'}</p>
<ul class="social_login {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 0}small-button{/if}">
{if isset($loginizer_data['facebook']['enable']) && $loginizer_data['facebook']['enable'] == 1}
    <li>                
        <a  class="js-social-login button-social-login facebook" 
            href="{url entity='module' name='nrtsociallogin' controller='facebook'}" title="Facebook" rel="nofollow">
            <i class="lab la-facebook-f"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Facebook' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['gplus']['enable']) && $loginizer_data['gplus']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login google"   
            href="{url entity='module' name='nrtsociallogin' controller='google'}" title="Google" rel="nofollow">
            <i class="lab la-google"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Google' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['insta']['enable']) && $loginizer_data['insta']['enable'] == 1}
    <li>                
        <a  class="js-social-login button-social-login instagram" 
            href="{url entity='module' name='nrtsociallogin' controller='instagram'}" title="Instagram" rel="nofollow">
            <i class="lab la-instagram"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Instagram' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['twitter']['enable']) && $loginizer_data['twitter']['enable'] == 1}
    <li>                
        <a  class="js-social-login button-social-login twitter" 
            href="{url entity='module' name='nrtsociallogin' controller='twitter'}" title="Twitter" rel="nofollow">
            <i class="lab la-twitter"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Twitter' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['linked']['enable']) && $loginizer_data['linked']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login linked"  
            href="{url entity='module' name='nrtsociallogin' controller='linkedin'}" title="Linkedin" rel="nofollow">
            <i class="lab la-linkedin-in"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Linkedin' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['yahoo']['enable']) && $loginizer_data['yahoo']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login yahoo"  
            href="{url entity='module' name='nrtsociallogin' controller='yahoo'}" title="Yahoo" rel="nofollow">
            <i class="lab la-yahoo"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Yahoo' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['live']['enable']) && $loginizer_data['live']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login live"   
            href="{url entity='module' name='nrtsociallogin' controller='live'}" title="Live" rel="nofollow">
            <i class="lab la-windows"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Live' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['foursquare']['enable']) && $loginizer_data['foursquare']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login foursquare"  
            href="{url entity='module' name='nrtsociallogin' controller='foursquare'}" title="Foursquare" rel="nofollow">
            <i class="lab la-foursquare"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Foursquare' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['amazon']['enable']) && $loginizer_data['amazon']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login amazon"   
            href="{url entity='module' name='nrtsociallogin' controller='amazon'}" title="Amazon" rel="nofollow">
            <i class="lab la-amazon"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Amazon' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['pay']['enable']) && $loginizer_data['pay']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login paypal"  
            href="{url entity='module' name='nrtsociallogin' controller='paypal'}" title="Paypal" rel="nofollow">
            <i class="lab la-paypal"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Paypal' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['github']['enable']) && $loginizer_data['github']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login github"  
            href="{url entity='module' name='nrtsociallogin' controller='github'}" title="Github" rel="nofollow">
            <i class="lab la-github-alt"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Github' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['disqus']['enable']) && $loginizer_data['disqus']['enable'] == 1} 
    <li>                
        <a 	class="js-social-login button-social-login disqus" 
            href="{url entity='module' name='nrtsociallogin' controller='disqus'}" title="Disqus" rel="nofollow">
            <i class="las la-play"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Disqus' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['wordpress']['enable']) && $loginizer_data['wordpress']['enable'] == 1}
    <li>                
        <a 	class="js-social-login button-social-login wordpress"   
            href="{url entity='module' name='nrtsociallogin' controller='wordpress'}" title="Wordpress" rel="nofollow">
            <i class="lab la-wordpress"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Wordpress' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
{if isset($loginizer_data['dropbox']['enable']) && $loginizer_data['dropbox']['enable'] == 1}
    <li>                
        <a  class="js-social-login button-social-login dropbox"  
            href="{url entity='module' name='nrtsociallogin' controller='dropbox'}" title="Dropbox" rel="nofollow">
            <i class="lab la-dropbox"></i>
            {if isset($loginizer_data['display_button']) && $loginizer_data['display_button'] == 1}
				<span>{l s='Dropbox' mod='nrtsociallogin'}</span>
            {/if}
        </a>
    </li>
{/if}
</ul>
{/if}