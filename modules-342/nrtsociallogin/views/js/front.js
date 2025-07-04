$(document).ready(function(){	
	$('body').on('click','.js-social-login',function(e) {
		setCookieSw();
		setCookieSr();
		if(window.innerWidth > 1199 && opSLogin.show_popup){
        	return !window.open(this.href, 'popup','width=450, height=450, left='+((window.screen.width - 450) / 2)+', top='+((window.screen.height - 450) / 2)+'');
		}
    });
});

function setCookieSw() {
	var name = 'cookieSw';
	var value = window.innerWidth;
	var expire = new Date();
	expire.setMonth(expire.getMonth()+12);
	document.cookie = name + "=" + escape(value) +";path=/;" + ((expire==null)?"" : ("; expires=" + expire.toGMTString()));
}

function setCookieSr() {
	var name = 'cookieSr';
	var value = opSLogin.redirect_url ? prestashop.urls.pages.my_account : prestashop.urls.current_url;
	var expire = new Date();
	expire.setMonth(expire.getMonth()+12);
	document.cookie = name + "=" + escape(value) +";path=/;" + ((expire==null)?"" : ("; expires=" + expire.toGMTString()));
}