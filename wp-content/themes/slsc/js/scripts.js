// Custom scripts file

jQuery( document ).ready(function( $ ) {

  'use strict';

  // Generic function that runs on window resize.
  function resizeStuff() {
  }

  // Runs function once on window resize.
  var TO = false;
  $(window).resize(function () {
    if (TO !== false) {
      clearTimeout(TO);
    }

    // 200 is time in miliseconds.
    TO = setTimeout(resizeStuff, 200);
  }).resize();

  if ($('body').hasClass('home')) {
    var mySwiper = new Swiper('.swiper-container', {
      // Optional parameters
      direction: 'horizontal',
      loop: false,
      preloadImages: true,
      slidesPerView: 1,
      spaceBetween: 40,


      // If we need pagination
      pagination: {
        el: '.swiper-pagination',
      },

      // Navigation arrows
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },

      // And if we need scrollbar
      scrollbar: {
        el: '.swiper-scrollbar',
      },
    });
  }

  var sticky = new Sticky('.sticky');

  $('.cbp-vm-icon').click(function(e) {
    e.preventDefault();
    if($(this).hasClass('cbp-vm-selected')) {

    }
    else {
      $('.cbp-vm-icon').toggleClass('cbp-vm-selected');
      if($(this).attr('data-view') == 'cbp-vm-view-grid') {
        $('#cbp-vm').removeClass('cbp-vm-view-list');
        $('.cbp-vm-list img').attr('src', '/wp-content/themes/slsc/images/list-view-icon.svg');
        $('.cbp-vm-grid img').attr('src', '/wp-content/themes/slsc/images/grid-view-icon-active.svg');
      }
      else {
        $('#cbp-vm').removeClass('cbp-vm-view-grid');
        $('.cbp-vm-grid img').attr('src', '/wp-content/themes/slsc/images/grid-view-icon.svg');
        $('.cbp-vm-list img').attr('src', '/wp-content/themes/slsc/images/list-view-icon-active.svg');
      }
      $('#cbp-vm').addClass($(this).attr( 'data-view' ));
    }
  });

  function _switch( opt ) {
    // remove other view classes and any any selected option
    optionSwitch.forEach(function(el) { 
      classie.remove( container, el.getAttribute( 'data-view' ) );
      classie.remove( el, 'cbp-vm-selected' );
    });
    // add the view class for this option
    classie.add( container, opt.getAttribute( 'data-view' ) );
    // this option stays selected
    classie.add( opt, 'cbp-vm-selected' );
  }

  if ($('body').hasClass('single-story')) {
    $('.timeline').timeline({
      verticalStartPosition: 'right',
      verticalTrigger: '150px'
    });
  }
});
