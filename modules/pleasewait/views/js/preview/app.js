/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
$(function() {
  var menuVisible = false;

  // Load background images
  $("[data-image]").each(function(i, elem) {
    var $elem = $(elem),
    url = "/images/portfolio/" + $elem.attr('data-image');
    if (url == null || url.length <= 0 ) { return; }

    $elem.addClass('image-loading');
    $('<img/>').attr('src', url).load(function() {
      $(this).remove();
      $bg = $('<div class="case-item-bg"/>');
      $bg.css( 'background-image', 'url(' + url + ')');

      $elem.prepend($bg);
      $elem
        .removeClass('image-loading')
        .addClass('image-ready');
    });
  });

  // Top menu
  $('.menu').click(function(e) {
    e.preventDefault();
    !menuVisible ? revealMenu() : hideMenu();
  });

  // Hide nav if clicked outside of a menu alternative
  $('.nav').click(function(e) {
    hideMenu();
  });
  // Make sure that links don't close the menu
  $('.nav a').click(function(e) {
    e.stopPropagation();
  });

  // Listen to ESC, close menu if visible
  var keyCodeESC = 27;
  $(document).keyup(function(e) {
    if (e.keyCode == keyCodeESC) handleESCKey();
  });

  function handleESCKey() {
    if (menuVisible) hideMenu();
  }

  function toggleMenuStates() {
    $('body').toggleClass('no-scroll');
    $('.menu').toggleClass('menu-active');
    $('.nav').toggleClass('nav-active');
  }

  function hideMenu() {
    menuVisible = false;
    toggleMenuStates();

    var containerDelay = 200;
    animate({
      el: $('.menu-animated-background'),
      scale: [3,0],
      opacity: [1,0],
      easing: "easeInExpo",
      duration: 300
    });

    animate({
      el: $('.js-nav'),
      opacity: [1, 0],
      easing: "easeInOutExpo",
      duration: 200
    });

    animate({
      el: $('.js-nav-header-line'),
      scale: [1, 0.5],
      easing: "easeInExpo",
      duration: 300
    });

    $(".js-nav-animate").each(function(i) {
      animate.stop($(this));

      animate({
        el: $(this),
        translateY: "10px",
        scale: [1, 0.9],
        opacity: [1, 0],
        easing: "easeInExpo",
        duration: 200,
        complete: hideNavElement
      });
    });
  }

  function hideNavElement(elements) {
    elements.forEach(function(el) {
      $(el).css({
        "opacity": "0",
        "transform" : "scale(0.9)"
      });
    });
  }

  function revealMenu() {
    menuVisible = true;
    toggleMenuStates();

    animate({
      el: $('.menu-animated-background'),
      scale: [0.2, 3],
      opacity: [0.2,1],
      easing: "easeInCubic",
      duration: 300
    });

    var containerDelay = 200;
    animate({
      el: $('.js-nav'),
      opacity: [0, 1],
      delay: containerDelay,
      easing: "easeInOutExpo",
      duration: 200
    });

    var menuItemDelay = 90;
    containerDelay += 75;
    $(".js-nav-header").css("opacity", "0");
    animate({
      el: $(".js-nav-header"),
      opacity: [0,1],
      delay: containerDelay,
      easing: "easeInOutExpo",
      duration: 200
    });

    $(".js-nav-header-line").css("transform", "scale(0.2)");
    animate({
      el: $('.js-nav-header-line'),
      scale: [0.2, 1],
      delay: containerDelay,
      easing: "easeInOutExpo",
      duration: 500
    });
    containerDelay += 300;

    $(".js-nav-animate").each(function(i) {
      $(this).css({
        "opacity": "0",
        "transform" : "scale(0.9)"
      });
    });

    $(".js-nav-animate").each(function(i) {
      animate({
        el: $(this),
        translateY: ["-7px", 0],
        scale: [0.9, 1],
        opacity: [0, 1],
        delay: containerDelay + menuItemDelay * (i+1),
        duration: 1100,
        easing: "easeOutExpo"
      });
    });
  }

});
