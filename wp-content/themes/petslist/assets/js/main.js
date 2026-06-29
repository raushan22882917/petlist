(function($) {
    "use strict";

    /* Main Functions */
    var Petslist = {
        init: function() {
            this.Basic.init();
        },
        Basic: {
            init: function() {
                this.MobileMenu();
                this.PriceRange();
                this.Select2Run();
                this.PhotoSwip();
                this.PetslistOthers();
            },
            MobileMenu: function(){
                var a = $('.offscreen-navigation .menu');
                if (a.length) {
                    a.children("li").addClass("menu-item-parent");
                    a.find(".menu-item-has-children > a").on("click", function (e) {
                        e.preventDefault();
                        $(this).toggleClass("opened");
                        var n = $(this).next(".sub-menu"),
                            s = $(this).closest(".menu-item-parent").find(".sub-menu");
                        a.find(".sub-menu").not(s).slideUp(250).prev('a').removeClass('opened'), n.slideToggle(250)
                    });
                    a.find('.menu-item:not(.menu-item-has-children) > a').on('click', function (e) {
                        $('.rt-slide-nav').slideUp();
                        $('body').removeClass('slidemenuon');
                    });
                }

                $('.sidebarBtn').on('click', function (e) {
                    e.preventDefault();
                    if ($('.rt-slide-nav').is(":visible")) {
                        $('.rt-slide-nav').slideUp();
                        $('body').removeClass('slidemenuon');
                    } else {
                        $('.rt-slide-nav').slideDown();
                        $('body').addClass('slidemenuon');
                    }

                });
            },
            PriceRange: function(){
                if ($.fn.ionRangeSlider) {
                    $(".ion-rangeslider").each(function () {
                        var $this = $(this);
                        var rangeType = $this.data('type');
                        $this.ionRangeSlider({
                            type: rangeType || "double",
                            drag_interval: true,
                            min_interval: null,
                            max_interval: null,
                            onChange: function (data) {
                                var $inp = data.input;
                                $inp.parent().find('.min-volumn').val(data.from);
                                $inp.parent().find('.max-volumn').val(data.to);
                            },
                        });
                    });
        
        
                    $(".rtcl-range-slider-input").each(function () {
                        var $this = $(this);
                        var rangeType = $this.data('type');
                        $this.ionRangeSlider({
                            drag_interval: true,
                            min_interval: null,
                            max_interval: null,
                            onChange: function (data) {
                                var $inp = data.input;
                                $inp.parent().find('.min-volumn').val(data.from);
                                $inp.parent().find('.max-volumn').val(data.to);
                            },
                        });
                    });
                }
            },
            Select2Run: function(){
                if ($('select.select2').length) {
                    $('select.select2').select2({
                        theme: 'classic',
                        dropdownAutoWidth: true,
                        width: '100%',
                    });
                }
                if ($('.rtcl-widget-search-form select').length) {
                    $('.rtcl-widget-search-form select').each(function(i, el) {
                        console.log(el);
                        $(el).select2({
                            theme: 'classic',
                            dropdownAutoWidth: true,
                            width: '100%',
                        });
                    });
                }
                if ($('.rtcl-ordering select').length) {
                    $('.rtcl-ordering select').select2({
                        theme: 'classic',
                        dropdownAutoWidth: true,
                        width: '100%',
                    });
                }                
                
            },
            PhotoSwip: function(){
                // Init empty gallery array
                var container = [];
                // Loop over gallery items and push it to the array
                $('.photo-swip-gallery-wrap').find('.photoswip-item').each(function() {
                    var $link = $(this).find('a'),
                    item = {
                        src: $link.attr('href'),
                        w: $link.attr('data-width'),
                        h: $link.attr('data-height')
                    };
                    container.push(item);
                });
            
                // Define click event on gallery item
                $('.photo-swip-gallery-wrap .photoswip-item a').click(function(event) {
            
                    // Prevent location change
                    event.preventDefault();
            
                    // Define object and gallery options
                    var $pswp = $('.pswp')[0],
                    options = {
                        index: $(this).parent('.photoswip-item').index(),
                        bgOpacity: 0.85,
                        showHideOpacity: true
                    };
            
                    // Initialize PhotoSwipe
                    var gallery = new PhotoSwipe($pswp, PhotoSwipeUI_Default, container, options);
                    gallery.init();
                });
            },
            PetslistOthers: function(){
                // Scroll Top Button
                $(".scrollToTop").on("click", function () {
                    $("body,html").animate(
                        {
                            scrollTop: 0,
                        },
                        360
                    );
                });

                $(window).on("scroll", function () {
                    var scrollBar = $(this).scrollTop();
                    if (scrollBar > 200) {
                        $(".scrollToTop").fadeIn();
                    } else {
                        $(".scrollToTop").fadeOut();
                    }
                });
                
                // Advanced Search Revel
                $(".advanced-btn").on("click", function () {
                    $(this).toggleClass("collapsed");
                    $("#advanced-search").toggleClass("show");
                });

                //Page Preloader
                if ($('#pageoverlay').length) {
                    document.getElementById('pageoverlay').className = 'pageoverlay';
                    $('#pageoverlay').delay(800).fadeOut();
                }
                //Header Search
                $('.mobile-search-icon').on('click', function() {
                    $('.rt-mobile-menu.header-style-2 .header-search-area').slideToggle();
                    $( "input[type='search']" ).focus();
                });
            }
        }
    }

    // Window Load+Resize
    $(window).on('load resize', function (){
        // Elementor Frontend Load
        $(window).on('elementor/frontend/init', function () {
            if (elementorFrontend.isEditMode()) {
                elementorFrontend.hooks.addAction('frontend/element_ready/widget', function () {
                    Petslist.init();
                });
            }
        });
    });

    $( document ).ready( function () {
        Petslist.init(); 
    });

})(jQuery);