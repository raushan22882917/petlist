(function ($) {
  "user strict";

  /**
   * Listing gallery class.
   */
  var RtclListingGallery = function RtclListingGallery($slider_wrapper, args) {
    var _this$sliderThumbs;
    this.$sliderWrapper = $slider_wrapper;
    this.$slider = $(".rtcl-slider", this.$sliderWrapper);
    this.$sliderThumbs = $(".rtcl-slider-nav", this.$sliderWrapper);
    if (!this.$slider.length) {
      return;
    }
    this.slider = this.$slider.get(0);
    this.swiperSlider = this.slider.swiper || null;
    this.sliderThumbs = this.$sliderThumbs.get(0);
    this.swiperThumbsSlider = (this === null || this === void 0 || (_this$sliderThumbs = this.sliderThumbs) === null || _this$sliderThumbs === void 0 ? void 0 : _this$sliderThumbs.swiper) || null;
    this.$slider_images = $(".rtcl-slider-item", this.$slider);
    this.settings = Object.assign({}, rtcl_single_listing_localized_params || {}, this.$sliderWrapper.data("options") || {});
    this.args = args || {};
    this.options = Object.assign({}, this.args, this.settings.slider_options, this.$sliderWrapper.data("options") || {});

    //if rtl value was not passed and html is in rtl..enable it by default.
    if (this.options.rtl && $("html").attr("dir") === "rtl") {
      this.options.rtl = true;
    }

    // Pick functionality to initialize...
    this.slider_enabled = "function" === typeof Swiper && this.settings.slider_enabled;
    this.zoom_enabled = $.isFunction($.fn.zoom) && this.settings.zoom_enabled;
    this.photoswipe_enabled = typeof PhotoSwipe !== "undefined" && this.settings.photoswipe_enabled;

    // ...also taking args into account.
    if (args) {
      this.slider_enabled = false === args.slider_enabled ? false : this.slider_enabled;
      this.zoom_enabled = false === args.zoom_enabled ? false : this.zoom_enabled;
      this.photoswipe_enabled = false === args.photoswipe_enabled ? false : this.photoswipe_enabled;
    }
    if (1 === this.$slider_images.length) {
      this.slider_enabled = false;
    }
    this.initSlider = function () {
      if (!this.slider_enabled) {
        return;
      }
      var $slider = this.$slider;
      var $sliderThumbs = this.$sliderThumbs;
      var $sliderThumbsGap = this.$sliderThumbs.data('slider-options') || {};
      if (this.options.rtl) {
        $slider.attr("dir", "rtl");
        $sliderThumbs.attr("dir", "rtl");
      }
      var that = this;
      var swiperThumbsSlider;
      if (this.swiperThumbsSlider) {
        swiperThumbsSlider = this.swiperThumbsSlider;
        this.swiperThumbsSlider.update();
      } else {
        var _rtcl_single_listing_;
        swiperThumbsSlider = new Swiper(this.sliderThumbs, {
          watchSlidesVisibility: true,
          spaceBetween: ($sliderThumbsGap === null || $sliderThumbsGap === void 0 ? void 0 : $sliderThumbsGap.spaceBetween) || 5,
          slidesPerView: 5,
          navigation: {
            nextEl: $sliderThumbs.find(".swiper-button-next").get(0),
            prevEl: $sliderThumbs.find(".swiper-button-prev").get(0)
          },
          direction: $sliderThumbsGap.direction || 'horizontal',
          breakpoints: {
            0: {
              slidesPerView: 3
            },
            576: {
              slidesPerView: 4
            },
            768: {
              allowTouchMove: true
            },
            1024: {
              allowTouchMove: ((_rtcl_single_listing_ = rtcl_single_listing_localized_params.slider_options) === null || _rtcl_single_listing_ === void 0 || (_rtcl_single_listing_ = _rtcl_single_listing_.nav) === null || _rtcl_single_listing_ === void 0 ? void 0 : _rtcl_single_listing_.allowTouchMove.l) || false
            }
          }
        });
        this.swiperThumbsSlider = swiperThumbsSlider;
        // ✅ Add click event to thumbnail slides
        swiperThumbsSlider.slides.forEach(function (slide, index) {
          slide.addEventListener("click", function () {
            if (that.swiperSlider) {
              that.swiperSlider.slideTo(index);
            }
            that.$sliderWrapper.trigger("rtcl_thumbnail_clicked", [index, slide]);
          });
        });
      }
      var swiperSlider;
      var swiperSliderDefaultParams = {
        navigation: {
          nextEl: $slider.find(".swiper-button-next").get(0),
          prevEl: $slider.find(".swiper-button-prev").get(0)
        },
        on: {
          init: function init(e) {
            if (e.slides[e.activeIndex].querySelector("iframe")) {
              e.el.classList.add("active-video-slider");
            }
          }
        }
      };
      if (this.$sliderThumbs.length) {
        swiperSliderDefaultParams.thumbs = {
          swiper: swiperThumbsSlider
        };
      }
      var swiperSliderParams = Object.assign({}, swiperSliderDefaultParams, this.options);
      if (this.swiperSlider) {
        swiperSlider = this.swiperSlider;
        this.swiperSlider.parents = swiperSliderParams;
        this.swiperSlider.update();
      } else {
        swiperSlider = new Swiper(this.slider, swiperSliderParams);
        this.swiperSlider = swiperSlider;
      }
      swiperSlider.on("init slideChange", function (e) {
        that.initZoomForTarget(swiperSlider.activeIndex);
        var activeIndex = swiperSlider.activeIndex;
        swiperSlider.slides.forEach(function (slide, index) {
          var $iframes = $(slide).find("iframe");
          if ($iframes.length) {
            if (index === activeIndex) {
              $iframes.each(function () {
                var dataSrc = $(this).attr("data-src");
                var currentSrc = $(this).attr("src");
                if (!dataSrc && currentSrc) {
                  $(this).attr("data-src", currentSrc);
                }
                if (!currentSrc || currentSrc !== dataSrc) {
                  $(this).attr("src", dataSrc);
                }
              });
            } else {
              $iframes.each(function () {
                var dataSrc = $(this).attr("data-src");
                var currentSrc = $(this).attr("src");
                $(this).attr("src", "");
                if (currentSrc && !dataSrc) {
                  $(this).attr("data-src", currentSrc);
                }
              });
            }
          }
        });
        if (e.slides[e.activeIndex].querySelector("iframe")) {
          e.el.classList.add("active-video-slider");
        } else {
          e.el.classList.remove("active-video-slider");
        }
      });
    };
    this.imagesLoaded = function () {
      var that = this;
      if ($.fn.imagesLoaded.done) {
        this.$sliderWrapper.trigger("rtcl_gallery_loading", this);
        this.$sliderWrapper.trigger("rtcl_gallery_loaded", this);
        return;
      }
      this.$sliderWrapper.imagesLoaded().progress(function (instance, image) {
        that.$sliderWrapper.trigger("rtcl_gallery_loading", [that]);
      }).done(function (instance) {
        that.$sliderWrapper.trigger("rtcl_gallery_loaded", [that]);
      });
    };
    this.initZoom = function () {
      if (!this.zoom_enabled) {
        return;
      }
      this.initZoomForTarget(0);
    };
    this.initZoomForTarget = function (sliderIndex) {
      if (!this.zoom_enabled) {
        return;
      }
      var galleryWidth = this.$slider.width(),
        zoomEnabled = false,
        zoomTarget = this.$slider_images.eq(sliderIndex);
      $(zoomTarget).each(function (index, element) {
        var image = $(element).find("img");
        if (parseInt(image.data("large_image_width")) > galleryWidth) {
          zoomEnabled = true;
          return false;
        }
      });

      // But only zoom if the img is larger than its container.
      if (zoomEnabled) {
        var zoom_options = $.extend({
          touch: false
        }, this.settings.zoom_options);
        if ("ontouchstart" in document.documentElement) {
          zoom_options.on = "click";
        }
        zoomTarget.trigger("zoom.destroy");
        zoomTarget.zoom(zoom_options);
        this.$sliderWrapper.on("rtcl_gallery_init_zoom", this.initZoom);
      }
    };
    this.initPhotoswipe = function () {
      if (!this.photoswipe_enabled) {
        return;
      }
      this.$slider.prepend('<a href="#" class="rtcl-listing-gallery__trigger"><i class="rtcl-icon-search"></i></i> </a>');
      this.$slider.on("click", ".rtcl-listing-gallery__trigger", this.openPhotoswipe.bind(this));
    };
    this.getGalleryItems = function () {
      var $slides = this.$slider_images,
        items = [];
      if ($slides.length > 0) {
        $slides.each(function (i, el) {
          var img = $(el).find("img");
          if (img.length) {
            var large_image_src = img.attr("data-large_image"),
              large_image_w = img.attr("data-large_image_width"),
              large_image_h = img.attr("data-large_image_height"),
              item = {
                src: large_image_src,
                w: large_image_w,
                h: large_image_h,
                title: img.attr("data-caption") ? img.attr("data-caption") : img.attr("title")
              };
            items.push(item);
          }
        });
      }
      return items;
    };
    this.openPhotoswipe = function (e) {
      e.preventDefault();
      var pswpElement = $(".pswp")[0],
        items = this.getGalleryItems(),
        eventTarget = $(e.target),
        clicked;
      if ($(e.currentTarget).hasClass('rtcl-listing-gallery__trigger') || $(e.currentTarget).closest(".rtcl-listing-gallery__trigger") || eventTarget.is(".rtcl-listing-gallery__trigger") || eventTarget.is(".rtcl-listing-gallery__trigger img")) {
        clicked = this.$slider.find(".swiper-slide.swiper-slide-active");
      } else {
        clicked = eventTarget.closest(".rtcl-slider-item");
      }
      var options = $.extend({
        index: $(clicked).index()
      }, this.settings.photoswipe_options);

      // Initializes and opens PhotoSwipe.
      var photoswipe = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
      photoswipe.init();
    };
    this.start = function () {
      var that = this;
      this.init();
      this.$sliderWrapper.on("rtcl_gallery_loaded", this.init.bind(this));
      setTimeout(function () {
        that.imagesLoaded();
      }, 1);
    };
    this.init = function () {
      this.initSlider();
      this.initZoom();
      this.initPhotoswipe();
    };
    this.start();
  };
  $.fn.rtcl_listing_gallery = function (args) {
    new RtclListingGallery(this, args);
    return this;
  };
  $(document).ready(function () {
    $(".rtcl-slider-wrapper").each(function () {
      $(this).rtcl_listing_gallery();
    });
    $('.rtcl-sl-section-columns').each(function () {
      var hasContent = false;
      $(this).find('.rtcl-sl-element-wrap').each(function () {
        if ($(this).text().trim() !== '' || $(this).children().length > 0) {
          hasContent = true;
          return false; // break loop
        }
      });
      if (!hasContent) {
        $(this).closest('.rtcl-sl-section').hide();
      }
    });
    $('.rtcl-slf-repeater-item').each(function () {
      var $items = $(this).children().length;
      if (1 == $items) {
        $(this).addClass('has-one-item');
      } else {
        $(this).addClass('has-multiple-items');
      }
    });
    $('.rtcl-sl-section').each(function () {
      var $section = $(this);
      var $columns = $section.find('.rtcl-sl-section-columns > .rtcl-sl-section-column');

      // Check if every column is empty or has only .has-no-value
      var allEmpty = true;
      $columns.each(function () {
        var $col = $(this);
        var hasText = $col.text().trim().length > 0;
        var hasMedia = $col.find('img, video, iframe').length > 0;
        var onlyEmptyValue = $col.find('.has-no-value').length > 0;
        if (hasText || hasMedia || !onlyEmptyValue) {
          allEmpty = false;
          return false; // Stop loop
        }
      });
      if (allEmpty) {
        $section.hide();
      }
    });
  });
  jQuery(document).ready(function ($) {
    var $repeater = $('.rtcl-is-collapsable');
    if (!$repeater.length) return;
    $repeater.each(function () {
      $(this).find('.rtcl-slf-repeater-item').each(function (index) {
        var $item = $(this);
        var $fields = $item.find('> .rtcl-slf-repeater-field'); // direct children only
        if (!$fields.length) return;
        var $title = $fields.first(); // first div = heading
        var $contents = $fields.slice(1); // rest = collapsible

        // Wrap all content fields in one container
        var $contentWrapper = $('<div class="rtcl-repeater-content"></div>');
        $contents.appendTo($contentWrapper);
        $item.append($contentWrapper);

        // Make title clickable
        $title.css('cursor', 'pointer');
        $title.addClass('item-heading item-' + index);

        // Show first item by default
        if (index === 0) {
          $contentWrapper.show();
          $item.addClass('open');
        } else {
          $contentWrapper.hide();
        }

        // Toggle on click
        $title.on('click', function () {
          $contentWrapper.slideToggle(200);
          $item.toggleClass('open');
        });
      });
    });
  });
})(jQuery);
