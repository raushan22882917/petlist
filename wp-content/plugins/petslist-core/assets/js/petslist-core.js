(function($) {
  "use strict";

  /* Main Functions */
  var PetslistCore = {
      init: function() {
          this.Basic.init();
      },
      Basic: {
          init: function() {
              this.HeadroomStickyHeader();
              this.PetslistCoreCategoriesSlider();
              this.PetslistCoreOthers();
          },
          HeadroomStickyHeader: function(){
            var header = document.querySelector(".headroom-sticky-header");
            var headroom = new Headroom(header, {
              tolerance: {
                down: 10,
                up: 20
              },
              offset: 15
            });
            headroom.init();

            var mobile_header = document.querySelector(".headroom-mobile-sticky-header");
            var mobile_headroom = new Headroom(mobile_header, {
              tolerance: {
                down: 10,
                up: 20
              },
              offset: 15
            });
            mobile_headroom.init();
          },
          PetslistCoreCategoriesSlider: function(){
            
            $('.petslist-core-categories-slider').each(function() {
              var $this = $(this);
              $this.fadeIn();
              var settings = $this.data('slider-options');
              var autoplayconditon = settings['auto'];
              var $pagination = $this.find('.swiper-pagination')[0];
              var $next = $this.find('.swiper-button-next')[0];
              var $prev = $this.find('.swiper-button-prev')[0];
              new Swiper( $this[0], {
                speed: settings['speed'],
                slidesPerGroup: settings['slidesPerGroup'] ? settings['slidesPerGroup']:1,
                autoplay: autoplayconditon ? {delay:settings['autoplay']['delay']}:false,
                pagination: {
                  el: $pagination,
                  clickable: true,
                  type: 'bullets',
                },
                navigation: {
                  nextEl: $next,
                  prevEl: $prev,
                },
                breakpoints: {
                  0: {
                    slidesPerView: settings['breakpoints']['0']['slidesPerView'],
                  },
                  576: {
                    slidesPerView: settings['breakpoints']['575']['slidesPerView'],
                  },
                  768: {
                    slidesPerView: settings['breakpoints']['768']['slidesPerView'],
                  },
                  992: {
                    slidesPerView: settings['breakpoints']['992']['slidesPerView'],
                  },
                  1200: {
                    slidesPerView: settings['breakpoints']['1200']['slidesPerView'],
                  },
                },
              });
      
            });
          },
          PetslistCoreOthers: function(){
            $(window).on("scroll", function () {
              if ($(window).scrollTop() >= $("body").offset().top + 50) {
                $("body").addClass("mn-top");
              } else {
                $("body").removeClass("mn-top");
              }
            })
          }
      }
  }

  // Window Load+Resize
  $(window).on('load resize', function (){
      // Elementor Frontend Load
      $(window).on('elementor/frontend/init', function () {
          if (elementorFrontend.isEditMode()) {
              elementorFrontend.hooks.addAction('frontend/element_ready/widget', function () {
                  PetslistCore.init();
              });
          }
      });
  });

  $( document ).ready( function () {
      PetslistCore.init(); 
  });

})(jQuery);