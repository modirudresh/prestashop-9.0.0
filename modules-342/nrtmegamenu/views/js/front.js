
$(document).ready(function() {
		
	$('body').on('click','.menu-vertical-title',(function(e) {
		e.preventDefault();
		$('.wrapper-menu-vertical').not($(this).closest('.wrapper-menu-vertical')).removeClass('active');
		$(this).closest('.wrapper-menu-vertical').toggleClass('active');
	}));

	$('body').click(function(e) {
		var target = $(e.target);
		if(!target.is('.wrapper-menu-vertical') && !target.closest('.wrapper-menu-vertical').length) {
		   $('.wrapper-menu-vertical').removeClass('active');
		}
	});
	
	$('body').on('click','.show-more-cat',(function(e) {
		$(this).toggleClass('menu-show');
		$(this).siblings('.more-cate').slideToggle(200);
	}));
	
	axpsUnitActiveItem();
	axpsInitMobileMegamenu();
	
});

$(window).load(function() {
	axpsInitHorizontalMegamenu();
});

function axpsInitHorizontalMegamenu() {
	
	var $menuHorizontal = $('.menu-horizontal');
	var	$list = $menuHorizontal.find(' > li.dropdown-is-mega');
	
	$list.hover(function(){
		setOffset($(this));
	});

	var setOffset = function ($li) {
		var $dropdown = $li.find(' > .sub-menu-dropdown');
				
		$dropdown.css({ 'right' : '', 'left' : '', 'width' : $dropdown.data('width') });
				
		var dropdownWidth = $dropdown.outerWidth();	
		var dropdownOffset = $dropdown.offset();
		var toRight;
		var viewportWidth;
		var dropdownOffsetRight;
		var $window = $(window);
		var $body = $('body');	
		var screenWidth = $window.width();

		if (!dropdownWidth || !dropdownOffset) {
			return;
		}

		if(dropdownWidth > screenWidth){
			dropdownWidth = screenWidth;
		}
		
		$dropdown.css({ 'width' : dropdownWidth });
		
		$dropdown.find('.container-parent').css({ 'padding-left' : '', 'padding-right' : '' });
		
		if(dropdownWidth <= 1200){
			$dropdown.find('.container-parent').css({
				'padding-left': 0,
				'padding-right': 0,
			});
		}
		
		if ($li.hasClass('dropdown-is-mega') && dropdownWidth > 1200) {
			viewportWidth = $window.width();

			if ($body.hasClass('rtl')) {
				dropdownOffsetRight = viewportWidth - dropdownOffset.left - dropdownWidth;

				if (dropdownOffsetRight + dropdownWidth >= viewportWidth) {
					toRight = dropdownOffsetRight + dropdownWidth - viewportWidth;

					$dropdown.css({
						right: -toRight
					});
				}
			} else {
				if (dropdownOffset.left + dropdownWidth >= viewportWidth) {
					toRight = dropdownOffset.left + dropdownWidth - viewportWidth;

					$dropdown.css({
						left: -toRight
					});
				}
			}
			
			$li.addClass('menu_initialized');
		} else if ($li.hasClass('dropdown-is-mega')) {
			viewportWidth = $('#site_width').innerWidth();

			dropdownOffsetRight = viewportWidth - dropdownOffset.left - dropdownWidth;

			var extraSpace = 0;
			var containerOffset = ($window.width() - viewportWidth) / 2;
			var dropdownOffsetLeft;
			
			if (dropdownWidth >= viewportWidth) {
				extraSpace = (viewportWidth - dropdownWidth)/2;
			}

			if ($body.hasClass('rtl')) {
				dropdownOffsetLeft = containerOffset + dropdownOffsetRight;

				if (dropdownOffsetLeft + dropdownWidth >= viewportWidth) {
					toRight = dropdownOffsetLeft + dropdownWidth - viewportWidth;

					$dropdown.css({
						right: -toRight - extraSpace
					});
				}
			} else {
				dropdownOffsetLeft = dropdownOffset.left - containerOffset;

				if (dropdownOffsetLeft + dropdownWidth >= viewportWidth) {
					toRight = dropdownOffsetLeft + dropdownWidth - viewportWidth;

					$dropdown.css({
						left: -toRight - extraSpace
					});
				}
			}
			
			$li.addClass('menu_initialized');
		}else{
			$li.addClass('menu_initialized');
		}
	};

	$list.each(function () {
		setOffset($(this));
	});
}

function axpsInitMobileMegamenu() {
	$('.wrapper-menu-mobile .mo_element_ul_depth_0, .wrapper-menu-column .col_element_ul_depth_0').each(function () {
		var $ul = $(this),
			elementDataKey = 'accordiated',
			activeClassName = 'active',
			panelSelector = '.mo_sub_ul, .col_sub_ul',
			itemSelector = 'li';

		if ($ul.data(elementDataKey)) return false;

		$.each($ul.find('.mo_sub_ul, .col_sub_ul'), function () {
			$(this).data(elementDataKey, true);
			$(this).hide();
		});

		$.each($ul.find('.js-opener-menu'), function () {
			$(this).click(function (e) {
				activate($(this).parent());
				return void 0;
			});
		});

		function activate($el) {
			$el.siblings(panelSelector).slideToggle(200, function () {
				if ($el.siblings(panelSelector).is(':visible')) {
					$el.parents(itemSelector).not($ul.parents()).addClass(activeClassName);
				} else {
					$el.parent(itemSelector).removeClass(activeClassName);
				}
				$el.parents().show();
			});
		}
	});
}

function axpsUnitActiveItem() {
	$(".wrapper-menu-horizontal .element_a_depth_0, .wrapper-menu-vertical .element_a_depth_0").each(function() {
		if( this.href === prestashop.urls.current_url){
			$(this).parent().addClass('current-menu-item');
		}
	});
}
			