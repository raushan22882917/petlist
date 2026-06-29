/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/classes/RtclAjaxFilter.js"
/*!******************************************!*\
  !*** ./src/js/classes/RtclAjaxFilter.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global jQuery, rtcl, rtclAjaxFilterObj */
var RtclAjaxFilter = /*#__PURE__*/_createClass(function RtclAjaxFilter() {
  var _this = this,
    _this$options2;
  _classCallCheck(this, RtclAjaxFilter);
  _defineProperty(this, "onLoadUpdateParams", function () {
    var _this$options;
    var url = new URL(window.location.href);
    if ((_this$options = _this.options) !== null && _this$options !== void 0 && _this$options.items && Array.isArray(_this.options.items)) {
      var that = _this;
      _this.options.items.map(function (_item) {
        if (_item.id === "price_range") {
          if (url.searchParams.has('filter_price')) {
            var filter_price = decodeURI(url.searchParams.get('filter_price'));
            if (filter_price.includes(',')) {
              filter_price = filter_price.split(',');
            }
            _this.data.params.filter_price = filter_price;
          }
        } else if (_item.id === "radius_filter") {
          if (url.searchParams.has('center_lat')) {
            _this.data.params.center_lat = url.searchParams.get('center_lat');
          }
          if (url.searchParams.has('center_lng')) {
            _this.data.params.center_lng = url.searchParams.get('center_lng');
          }
          if (url.searchParams.has('geo_address')) {
            _this.data.params.geo_address = url.searchParams.get('geo_address');
          }
          if (url.searchParams.has('distance')) {
            _this.data.params.distancem = url.searchParams.get('distance');
          }
        } else if (that.withOutFilterPrefix.includes(_item.id)) {
          if (url.searchParams.has(_item.id)) {
            _this.data.params[_item.id] = decodeURI(url.searchParams.get(_item.id)).split(',');
          } else {
            if (_item.selected) {
              that.initLoading = false;
              _this.data.params[_item.id] = Array.isArray(_item.selected) ? _item.selected : url.searchParams.get(_item.selected).split(',');
              _this.addParam(_item.id, _this.data.params[_item.id], true);
            }
          }
        } else {
          var foundTerm = null;
          if (['location', 'category', 'tag'].includes(_item.id) && Array.isArray(rtcl.activeTerms) && rtcl.activeTerms.length && (foundTerm = rtcl.activeTerms.find(function (element) {
            return element.taxonomy.replace("rtcl_", '') === _item.id;
          }))) {
            // Add current term 
            var filterName = 'filter_' + _item.id;
            var terms;
            if (url.searchParams.has(filterName)) {
              terms = decodeURI(url.searchParams.get(filterName)).split(',');
              terms.push(foundTerm.term_id);
              _this.addParam(filterName, foundTerm.term_id, true);
            } else {
              terms = [foundTerm.term_id];
              _this.addParam(filterName, foundTerm.term_id, true);
            }
            _this.data.params[filterName] = terms;
          } else {
            var paramName = 'filter_' + _item.id;
            if (url.searchParams.has(paramName)) {
              _this.data.params[paramName] = ['checkbox', 'radio'].includes(_item.type) ? decodeURI(url.searchParams.get(paramName)).split(',') : url.searchParams.get(paramName);
            } else {
              if (_item.selected) {
                that.initLoading = false;
                if (['checkbox', 'radio'].includes(_item.type)) {
                  _this.data.params[_item.id] = Array.isArray(_item.selected) ? _item.selected : url.searchParams.get(_item.selected).split(',');
                  _this.addParam(_item.id, _this.data.params[_item.id], true);
                } else {
                  _this.data.params[_item.id] = url.searchParams.get(_item.selected);
                  _this.addParam(_item.id, _this.data.params[_item.id]);
                }
              } else {
                if ('ad_type' === _item.id && url.searchParams.has('filters[ad_type]')) {
                  that.initLoading = false;
                  var adType = url.searchParams.get('filters[ad_type]');
                  _this.data.params[paramName] = adType;
                  _this.addParam(paramName, adType);
                }
              }
            }
          }
        }
      });
    }
    if (url.searchParams.has('page')) {
      _this.data.params['page'] = url.searchParams.get('page');
    }
    if (url.searchParams.has('orderby')) {
      _this.data.params['orderby'] = url.searchParams.get('orderby');
    }
    if (url.searchParams.has('view')) {
      _this.data.params['view'] = url.searchParams.get('view');
    }
    if (url.searchParams.has('q')) {
      _this.data.params['q'] = url.searchParams.get('q');
    }
    // Remove custom filed params
    Array.from(url.searchParams).map(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
        _key = _ref2[0],
        _value = _ref2[1];
      if (_key.startsWith('cf_')) {
        _value = decodeURI(url.searchParams.get(_key));
        if (_value.includes(',')) {
          _value = _value.split(',');
        }
        _this.data.params[_key] = _value;
      }
    });
    _this.renderActiveFilter();
  });
  _defineProperty(this, "init", function () {
    if (!_this.$(_this.filterWraperClass).length) {
      return;
    }
    _this.onLoadUpdateParams();
    _this.handleEvents();
    _this.loadAjaxData();
    _this.priceRangeSliderInit();
    _this.radiusDistanceSliderInit();
    _this.dateInit();
    _this.loadInitData();
  });
  _defineProperty(this, "loadInitData", function () {
    if (_this.isArchive) {
      _this.$(_this.archivePaginationClass).remove();
      _this.$(_this.noListingFoundClass).remove();
    }
    if (_this.isTaxArchive) {
      var targetSelector = _this.$('body').hasClass('tax-rtcl_category') ? 'rtcl-filter_category' : _this.$('body').hasClass('tax-rtcl_location') ? 'rtcl-filter_location' : _this.$('body').hasClass('tax-rtcl_tag') ? 'rtcl-filter_tag' : '';
      var $targetSelector = _this.$('body').find('.' + targetSelector);
      if ($targetSelector.length) {
        var targetOptions = $targetSelector.find('.rtcl-filter-content').data('options');
        if (targetOptions && targetOptions.field_type === "checkbox") {
          var $showAll = _this.$('<div class="rtcl-show-all">' + rtclAjaxFilterObj.show_all + '</div>');
          $showAll.on('click', function (e) {
            window.location.replace(rtclAjaxFilterObj.listings_archive_url);
          });
          $targetSelector.append($showAll);
        }
      }
    }
    _this.callAjax();
  });
  /**
   * Scrolls the page to the top of the products' container.
   *
   * @function
   */
  _defineProperty(this, "scrollToTop", function () {
    if (_this.$(_this.filterContainerClass).hasClass('no-scroll-mode')) {
      return false;
    }
    var scrollTarget = _this.$('body .rtclScrollTarget');
    var dataScrollOffset = parseInt(rtclAjaxFilterObj.filter_scroll_offset, 10);
    var scrollOffset = isNaN(dataScrollOffset) ? 50 : dataScrollOffset;
    var targetPosition;
    if (scrollTarget.length) {
      targetPosition = scrollTarget.offset().top - scrollOffset;
    } else {
      targetPosition = _this.$(_this.filterContainerClass).parent().offset().top - scrollOffset;
    }
    _this.smoothScrollTo(targetPosition, 1200);
  });
  _defineProperty(this, "handleEvents", function () {
    _this.$(_this.filterContainerClass).on('click', '.rtcl-reset', function (e) {
      e.stopPropagation();
      var $self = _this.$(e.currentTarget),
        $wrap = $self.closest('.rtcl-ajax-filter-item'),
        $content = $wrap.find('.rtcl-filter-content'),
        options = $content.data('options');
      if ($wrap.hasClass('rtcl-filter_radius_filter')) {
        $wrap.removeClass('is-active');
        _this.reset = true;
        _this.$(document).trigger('rtcl_ajax_filter_reset_radius_distance');
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      } else if ($wrap.hasClass('rtcl-filter_price_range')) {
        _this.reset = true;
        _this.$(document).trigger('rtcl_ajax_filter_reset_price_range');
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      } else if ($wrap.hasClass('rtcl-filter_rating')) {
        $wrap.removeClass('is-active');
        $content.find('.rtcl-filter-ratings-item').removeClass('selected');
        _this.removeParam(options.name);
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      } else {
        _this.removeParam(options.name);
        $wrap.removeClass('is-active');
        $content.find('input.rtcl-filter-number-field, input.rtcl-filter-date-field, input.rtcl-filter-text-field').val('');
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      }
    }).on('click keydown', '.rtcl-more-less-btn', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }
      var $self = _this.$(e.currentTarget);
      var $wrap = $self.closest('.rtcl-ajax-filter-data');
      if ($self.hasClass('active')) {
        $wrap.find('.rtcl-ajax-filter-data-item.hideAble').removeClass('active');
        $self.removeClass('active');
      } else {
        $wrap.find('.rtcl-ajax-filter-data-item.hideAble').addClass('active');
        $self.addClass('active');
      }
    }).on('keydown', '.rtcl-ajax-filter-data .rtcl-filter-checkbox-label', function (e) {
      if (e.key === 'Enter') {
        var inputId = this.getAttribute('for');
        var $input = jQuery('#' + inputId);
        if ($input.length) {
          $input.trigger('click').trigger('change');
        }
      }
    }).on('change', 'input.rtcl-filter-checkbox, select.rtcl-filter-select-item', _this.handleFilter).on('click', '.rtcl-filter-ratings-item', _this.handleFilter).on('click keydown', '.rtcl-ajax-filter-data.filter-list .is-parent.has-sub .rtcl-load-sub-list', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }
      _this.loadSubListData(e);
    });
    _this.$('.rtcl-listings-actions .rtcl-view-switcher a.rtcl-view-trigger', document).on('click', function (event) {
      event.preventDefault();
      var $self = _this.$(event.currentTarget);
      var view = $self.data('type') || 'list';
      _this.addParam('view', view);
      location.replace(location.href);
    });
    _this.$("body").off("change", ".rtcl-ordering select.orderby");
    _this.$('.rtcl-listings-actions .rtcl-ordering select.orderby', document).off('change').on('change', function (event) {
      var $self = _this.$(event.currentTarget);
      var orderBy = $self.val();
      delete _this.data.params.page;
      _this.removeParam('page');
      if (orderBy) {
        _this.data.params.orderby = orderBy;
        _this.addParam('orderby', orderBy);
      } else {
        delete _this.data.params.orderby;
        _this.removeParam('orderby');
      }
      _this.$(document).trigger('rtcl_ajax_filter_update_params');
    });
    _this.$(document).on('keydown', '.rtcl-ajax-pagination-container .rtcl-ajax-pagination-item.page-item:not(.active)', function (e) {
      if (e.key === 'Enter') {
        jQuery(this).trigger('click');
      }
    });
    _this.$(document).on('keyup', '.rtcl-ajax-filter-item .rtcl-ajax-filter-text input[type=text]', _this.handleFilter).on('keyup', '.rtcl-ajax-filter-item .rtcl-filter-number-field-wrap input[type=number]', _this.handleFilter).on('click', '.rtcl-ajax-filter-text .rtcl-clear-text', function (e) {
      var $self = _this.$(e.currentTarget),
        $wrap = $self.closest('.rtcl-ajax-filter-item'),
        $content = $self.closest('.rtcl-filter-content'),
        $textField = $self.closest('.rtcl-ajax-filter-text').find('input[type=text]'),
        options = $content.data('options');
      $wrap.removeClass('is-active');
      $textField.val('');
      var filterName = options.filter_key || options.name;
      if (filterName && _this.data.params[filterName]) {
        delete _this.data.params[filterName];
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      }
    }).on('click', '.rtcl-ajax-pagination-container .rtcl-ajax-pagination-item.page-item:not(.active)', _this.handlePagination).on('click keydown', '.rtcl-active-filters-container .rtcl-clear-filters', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }
      _this.resetFilter(e);
    }).on('click', '.rtcl-ajax-filter-text-search .rtcl-ajax-filter-search-icon', function (e) {
      _this.handleFilter(e);
    }).on('click keydown', '.rtcl-active-filters-container .af-items .afi', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }
      _this.removeFilterItem(e);
    }).on('click', _this.filterTitleWrapClass, function (e) {
      var $self = _this.$(e.currentTarget),
        $wrap = $self.closest('.rtcl-ajax-filter-item'),
        $content = $wrap.find('.rtcl-filter-content');
      if ($wrap.hasClass('is-open')) {
        $content.slideUp(function () {
          $wrap.removeClass('is-open');
        });
      } else {
        $content.slideDown(function () {
          $wrap.addClass('is-open');
        });
      }
    }).on('rtcl_ajax_filter_update_params', function (event, data) {
      delete _this.data.params.page;
      _this.removeParam('page');
      _this.callAjax();
    }).on('rtcl_ajax_filter_reset', function () {
      _this.reset = true;
      _this.$(_this.filterContainerClass).find('input.rtcl-filter-checkbox').prop('checked', false).end().find('select.rtcl-filter-select-item').val('').end().find('input.rtcl-filter-text-field').val('').closest('.rtcl-ajax-filter-item').removeClass('is-active').end().end().find('.rtcl-geo-address-field input').val('').closest('.rtcl-ajax-filter-item').removeClass('is-active').end().end().find('.rtcl-filter_rating').removeClass('is-active').find('.rtcl-filter-ratings .rtcl-filter-ratings-item').removeClass('selected');
      _this.$('.rtcl-filter-text-field', '.rtcl-ajax-filter-date-field', document).val('').closest('.rtcl-ajax-filter-item').removeClass('is-active');
      _this.$('.rtcl-number-field-wrap input', document).val('').closest('.rtcl-ajax-filter-item').removeClass('is-active');
      _this.$(document).trigger('rtcl_ajax_filter_reset_price_range');
      _this.$(document).trigger('rtcl_ajax_filter_reset_radius_distance');
      _this.callAjax();
    }).on('rtcl_map_retrieve_geocode', function (e, data) {
      if (data.target) {
        var $target = _this.$(data.target);
        var $itemWrap = $target.closest('.rtcl-filter_radius_filter'),
          $distanceSlider = $itemWrap.find('.rtcl-radius-distance-slider');
        if ($itemWrap.length && $distanceSlider.length) {
          var distance = $distanceSlider[0].noUiSlider.get();
          var _distance = Number(distance);
          _this.data.params = _objectSpread(_objectSpread({}, _this.data.params), {}, {
            center_lat: data.lat,
            center_lng: data.lng,
            distance: _distance
          });
          _this.addParam('center_lat', data.lat);
          _this.addParam('center_lng', data.lng);
          _this.addParam('distance', _distance);
          _this.addParam('geo_address', data.address);
          $itemWrap.addClass('is-active');
          _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
        }
      }
    });
  });
  _defineProperty(this, "loadSubListData", function (e) {
    e.preventDefault();
    var self = jQuery(e.currentTarget),
      item = self.closest(".rtcl-ajax-filter-data-item"),
      parent = self.closest(".rtcl-filter-content"),
      is_ajax_load = parent.hasClass("rtcl-ajax"),
      options = parent.data("options") || {},
      target = item.find("> .sub-list");
    if (item.hasClass("is-open")) {
      target.slideUp(function () {
        item.removeClass("is-open");
      });
    } else {
      if (is_ajax_load && options.taxonomy && item.hasClass("has-sub") && !item.hasClass("is-loaded")) {
        if (!parent.hasClass("rtcl-loading")) {
          options.parent = item.data("id") || 0;
          options.__rtcl_wpnonce = rtcl.__rtcl_wpnonce;
          options.action = "rtcl_ajax_filter_get_sub_terms_html";
          _this.$.ajax({
            url: rtcl.ajaxurl,
            type: "POST",
            dataType: "json",
            data: options,
            beforeSend: function beforeSend() {
              parent.rtclBlock();
            },
            success: function success(response) {
              item.append(response.data);
              parent.rtclUnblock();
              item.addClass("is-open is-loaded");
              item.find("> .sub-list").slideDown('slow', function () {
                jQuery(this).css('display', 'flex');
              });
            },
            complete: function complete() {
              parent.rtclUnblock();
            },
            error: function error(request, status, _error) {
              console.error(_error);
            }
          });
        }
      } else {
        target.slideDown();
        item.addClass("is-open");
      }
    }
  });
  _defineProperty(this, "loadAjaxData", function () {
    _this.$(_this.filterContainerClass).find('.rtcl-ajax-filter-item .rtcl-filter-content.rtcl-ajax').each(function (index, item) {
      var _rtcl$listing_term;
      var _self = _this.$(item),
        options = _self.data("options") || {};
      options.action = "rtcl_ajax_filter_get_sub_terms_html";
      options.__rtcl_wpnonce = rtcl.__rtcl_wpnonce;
      if (((_rtcl$listing_term = rtcl.listing_term) === null || _rtcl$listing_term === void 0 ? void 0 : _rtcl$listing_term.taxonomy) === options.taxonomy && Array.isArray(options.values) && !options.values.includes(rtcl.listing_term.term_id.toString())) {
        options.values.push(rtcl.listing_term.term_id);
      }
      _this.$.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: "json",
        data: options,
        beforeSend: function beforeSend() {
          _self.rtclBlock();
        },
        success: function success(response) {
          _self.html(response.data).rtclUnblock();
          var parentWrap = _self.closest(_this.filterContainerClass);
        },
        complete: function complete() {
          _self.rtclUnblock();
        },
        error: function error(request, status, _error2) {
          _self.rtclUnblock();
          if (status === 500) {
            console.error("Error while adding comment");
          } else if (status === "timeout") {
            console.error("Error: Server doesn't respond.");
          } else {}
        }
      });
    });
  });
  _defineProperty(this, "addParam", function (filterName, value, multiple) {
    if (!filterName) {
      return;
    }
    if (!value) {
      _this.removeParam(filterName);
      return;
    }
    var url = new URL(window.location.href);
    var filterValue = value;
    if (url.searchParams.has(filterName)) {
      var _value = decodeURI(url.searchParams.get(filterName));
      if (multiple) {
        filterValue = Array.from(new Set(_value.split(',')));
        filterValue.push(value);
      }
    }
    _this.data.params[filterName] = filterValue;
    url.searchParams.set(filterName, Array.isArray(filterValue) ? filterValue.join(',') : filterValue);
    window.history.pushState('', document.title, url.toString());
  });
  _defineProperty(this, "removeParam", function (filterName, value, multiple) {
    if (!filterName) {
      return;
    }
    var url = new URL(window.location.href);
    if (!value || !multiple) {
      delete _this.data.params[filterName];
      url.searchParams["delete"](filterName);
    } else {
      var filterValue = value;
      if (url.searchParams.has(filterName)) {
        var _value = decodeURI(url.searchParams.get(filterName));
        filterValue = Array.from(new Set(_value.split(','))).filter(function (_i) {
          return _i.toString() !== value.toString();
        });
        if (filterValue.length) {
          _this.data.params[filterName] = filterValue;
          url.searchParams.set(filterName, Array.isArray(filterValue) ? filterValue.join(',') : filterValue);
        } else {
          delete _this.data.params[filterName];
          url.searchParams["delete"](filterName);
        }
      }
    }
    window.history.pushState('', document.title, url.toString());
  });
  _defineProperty(this, "handleFilter", function (event, data) {
    if (_this.reset) {
      return;
    }
    var $self = _this.$(event.currentTarget),
      $wrap = $self.closest('.rtcl-ajax-filter-item'),
      $targetContainer = $self.closest('.rtcl-filter-content'),
      options = $targetContainer.data('options'),
      option_name = options.filter_key || options.name;
    if (event.type === 'change') {
      if (option_name) {
        var inputValue = event.currentTarget.value;
        if (event.currentTarget.type === "text") {
          if (inputValue) {
            _this.data.params[option_name] = inputValue;
            $wrap.addClass('is-active');
            _this.addParam(option_name, inputValue);
          } else {
            delete _this.data.params[option_name];
            $wrap.removeClass('is-active');
            _this.removeParam(option_name);
          }
        } else {
          if (options && ['checkbox', 'radio', 'select'].includes(options.field_type)) {
            if (event.currentTarget.checked) {
              _this.addParam(option_name, inputValue, event.currentTarget.type === 'checkbox');
            } else if ('select' === options.field_type && inputValue) {
              //this.addParam(option_name, inputValue);
              if (event.currentTarget.checked) {
                _this.addParam(option_name, inputValue, event.currentTarget.type === 'checkbox');
              } else {
                _this.removeParam(option_name, inputValue, event.currentTarget.type === 'checkbox');
              }
            } else {
              _this.removeParam(option_name, inputValue, event.currentTarget.type === 'checkbox');
            }
          } else {
            _this.addParam(option_name, inputValue);
          }
        }
        _this.$(document).trigger('rtcl_ajax_filter_update_params');
      }
    } else if (event.type === 'keyup') {
      if (event.currentTarget.tagName === "INPUT") {
        if (event.currentTarget.type === 'number' && $wrap.find('.rtcl-filter-number-field-wrap').hasClass('min-max')) {
          var _$self = _this.$(event.currentTarget);
          var _$wrap = _$self.closest('.rtcl-filter-number-field-wrap');
          var maxValue = _$wrap.find('input.max').val() || null;
          var minValue = _$wrap.find('input.min').val() || 0;
          if (event.key === 'Enter' || event.keyCode === 13) {
            var _value = [minValue, maxValue];
            _this.data.params[option_name] = _value;
            _this.addParam(option_name, _value);
            _this.$(document).trigger('rtcl_ajax_filter_update_params');
          } else {
            if (minValue || maxValue) {
              _$wrap.addClass('is-active');
            } else {
              _$wrap.removeClass('is-active');
            }
          }
        } else {
          var _value2 = event.currentTarget.value;
          if (event.key === 'Enter' || event.keyCode === 13) {
            _this.data.params[option_name] = event.currentTarget.value;
            _this.addParam(option_name, _value2);
            _this.$(document).trigger('rtcl_ajax_filter_update_params');
          } else {
            if (_value2) {
              $wrap.addClass('is-active');
            } else {
              $wrap.removeClass('is-active');
            }
          }
        }
      }
    } else if (event.type === 'click') {
      var _$self2 = _this.$(event.currentTarget);
      if (_$self2.hasClass('rtcl-filter-ratings-item')) {
        var rating = parseFloat(_$self2.data('id'));
        if (!isNaN(rating)) {
          _$self2.closest('.rtcl-filter-ratings').find('.rtcl-filter-ratings-item').removeClass('selected');
          _$self2.addClass('selected');
          $wrap.addClass('is-active');
          _this.addParam(option_name, rating);
          _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
        }
      }
      if (_$self2.closest('.rtcl-ajax-filter-text-search').length) {
        var $textField = _$self2.closest('.rtcl-ajax-filter-text').find('input[type=text]');
        _this.addParam(option_name, $textField.val());
        _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
      }
    }
  });
  _defineProperty(this, "dateInit", function () {
    if (_this.$.fn.daterangepicker) {
      _this.$(".rtcl-filter-date-field", _this.$(_this.filterWraperClass)).each(function (_i, _item) {
        var $input = _this.$(_item);
        var options = $input.data("options") || {};
        options = rtclFilter.apply('dateRangePickerOptions', options);
        if (window.innerWidth <= 767) {
          var _options$autoApply;
          options.opens = options.opens || 'center';
          options.drops = options.drops || 'auto';
          options.autoApply = (_options$autoApply = options.autoApply) !== null && _options$autoApply !== void 0 ? _options$autoApply : false;
        }
        if (Array.isArray(options.invalidDateList) && options.invalidDateList.length) {
          options.isInvalidDate = function (param) {
            return options.invalidDateList.includes(param.format(options.locale.format));
          };
        }
        $input.daterangepicker(options);
        if (options.autoUpdateInput === false) {
          $input.on("apply.daterangepicker", function (event, picker) {
            var $self = _this.$(event.currentTarget),
              $wrap = $self.closest('.rtcl-ajax-filter-item'),
              $targetContainer = $self.closest('.rtcl-filter-content'),
              options = $targetContainer.data('options'),
              option_name = options.name;
            var inputValue;
            if (picker.singleDatePicker) {
              inputValue = picker.startDate.format(picker.locale.format);
              $self.val(inputValue);
            } else {
              inputValue = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);
              $self.val(inputValue);
            }
            _this.addParam(option_name, inputValue);
            _this.data.params[option_name] = inputValue;
            $wrap.addClass('is-active');
            _this.$(document).trigger('rtcl_ajax_filter_update_params');
          });
          $input.on("cancel.daterangepicker", function (event, picker) {
            _this.$(event.currentTarget).val("");
          });
        }
      });
    }
  });
  _defineProperty(this, "priceRangeSliderInit", function () {
    var priceContainers = _this.$(_this.filterContainerClass + ' .rtcl-price-range-wrap');
    if (!priceContainers.length) {
      return false;
    }
    var $itemWrap = priceContainers.closest('.rtcl-ajax-filter-item');
    priceContainers.each(function (i, container) {
      var $container = _this.$(container),
        $priceRangeSlider = $container.find('.rtcl-price-range-slider'),
        priceRangeSlider = $priceRangeSlider[0],
        $filterPriceInputWrap = $container.find('.rtcl-range-slider-input-wrap'),
        $maxPriceInput = $filterPriceInputWrap.find('.rtcl-range-slider-input.max'),
        $minPriceInput = $filterPriceInputWrap.find('.rtcl-range-slider-input.min');
      var maxPrice = parseInt($priceRangeSlider.attr('data-max'), 10) || 50000;
      var minPrice = parseInt($priceRangeSlider.attr('data-min'), 10) || 0;
      var currentMaxPrice = parseInt($maxPriceInput.val(), 10) || maxPrice;
      var currentMinPrice = parseInt($minPriceInput.val(), 10) || minPrice;
      var filterStep = parseInt($priceRangeSlider.attr('data-step'), 10) || 1000;
      var slider = noUiSlider.create(priceRangeSlider, {
        range: {
          min: minPrice,
          max: maxPrice
        },
        behaviour: 'drag',
        connect: true,
        start: [currentMinPrice, currentMaxPrice],
        step: filterStep
      });
      priceRangeSlider.noUiSlider.on('update', function (values, e) {
        var $targetInput = e === 0 ? $minPriceInput : $maxPriceInput;
        $targetInput.val(Number(values[e]));
      });
      priceRangeSlider.noUiSlider.on('change', function (values, e) {
        if (!_this.reset) {
          $itemWrap.addClass('is-active');
          var prices = [Number(values[0]), Number(values[1])];
          _this.data.params = _objectSpread(_objectSpread({}, _this.data.params), {}, {
            filter_price: prices
          });
          _this.addParam('filter_price', prices.filter(function (e) {
            return e === 0 ? true : e;
          }).join(','));
          _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
        }
      });
      _this.$(document).on('rtcl_ajax_filter_reset_price_range', function () {
        $minPriceInput.val(minPrice).trigger('change');
        $maxPriceInput.val(maxPrice).trigger('change');
        delete _this.data.params['filter_price'];
        _this.removeParam('filter_price');
        $itemWrap.removeClass('is-active');
      });
      function setSliderValue(index, value) {
        var values = [null, null];
        values[index] = value;
        priceRangeSlider.noUiSlider.set(values);
      }
      $filterPriceInputWrap.find('.rtcl-range-slider-input').on('change', function (event) {
        var value = event.currentTarget.value;
        value = Number(value);
        value = parseInt(value, 10);
        if (isNaN(value) || value < 0) {
          value = 0;
          event.currentTarget.value = value;
        }
        var e = _this.$(event.currentTarget).hasClass('min') ? 0 : 1;
        setSliderValue(e, value);
        var values = priceRangeSlider.noUiSlider.get();
        if (!_this.reset) {
          var prices = [Number(values[0]), Number(values[1])];
          _this.data.params = _objectSpread(_objectSpread({}, _this.data.params), {}, {
            filter_price: prices
          });
          _this.addParam('filter_price', prices.filter(function (e) {
            return e;
          }).join(','));
          _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
        }
      }).on("keydown", function (c) {
        var blockedKeys = ['-', 'e', 'E', '+'];
        if (blockedKeys.includes(c.key)) {
          c.preventDefault();
          return;
        }
        var values = priceRangeSlider.noUiSlider.get();
        var index = jQuery(this).hasClass('min') ? 0 : 1;
        var value = Number(values[index]);
        var steps = priceRangeSlider.noUiSlider.steps()[index];
        var step;
        switch (c.which) {
          case 13:
            if (this.dirty) {
              this.dirty = !1;
              this.trigger("change");
            }
            break;
          case 38:
            // up arrow
            step = steps[1];
            if (!1 === step) {
              step = 1;
            }
            if (null !== step) {
              this.dirty = !0;
              setSliderValue(index, value + step);
            }
            break;
          case 40:
            // down arrow
            step = steps[0];
            if (!1 === step) {
              c = 1;
            }
            if (null !== step) {
              this.dirty = !0;
              setSliderValue(index, value - step);
            }
        }
      }).on("blur", function () {
        if (this.dirty) {
          jQuery(this).trigger("change");
        }
        this.dirty = !1;
      }).on("paste", function (e) {
        var _this2 = this;
        setTimeout(function () {
          var val = _this2.value.replace(/[^0-9]/g, '');
          _this2.value = val;
        }, 0);
      });
    });
  });
  _defineProperty(this, "radiusDistanceSliderInit", function () {
    var distanceContainers = _this.$(_this.filterContainerClass + ' .rtcl-radius-distance-slider-wrap');
    if (!distanceContainers.length) {
      return false;
    }
    var $itemWrap = distanceContainers.closest('.rtcl-ajax-filter-item');
    distanceContainers.each(function (i, container) {
      var $container = _this.$(container),
        $distanceSlider = $container.find('.rtcl-radius-distance-slider'),
        distanceSlider = $distanceSlider[0],
        $distanceInput = $container.find('.rtcl-radius-distance-input'),
        rangeDefault = parseInt($distanceSlider.attr('data-default'), 10) || 30,
        currentDistance = parseInt($distanceSlider.attr('data-current'), 10) || rangeDefault,
        rangeMax = parseInt($distanceSlider.attr('data-max'), 10) || 300,
        rangeMin = parseInt($distanceSlider.attr('data-min'), 10) || 0,
        rangeStep = parseInt($distanceSlider.attr('data-step'), 10) || 5;
      var slider = noUiSlider.create(distanceSlider, {
        range: {
          min: rangeMin,
          max: rangeMax
        },
        behaviour: 'drag',
        connect: [true, false],
        start: currentDistance,
        step: rangeStep
      });
      distanceSlider.noUiSlider.on('change', function (values, index) {
        if (!_this.reset && _this.data.params.center_lat && _this.data.params.center_lng) {
          $itemWrap.addClass('is-active');
          var distance = Number(values[index]);
          _this.data.params = _objectSpread(_objectSpread({}, _this.data.params), {}, {
            distance: distance
          });
          _this.addParam('distance', distance);
          _this.$(document).trigger('rtcl_ajax_filter_update_params', [_this.data.params]);
        }
      });
      distanceSlider.noUiSlider.on('update', function (values, index) {
        var value = Number(values[index]);
        $container.find('.rtcl-range-value').text(value);
      });
      _this.$(document).on('rtcl_ajax_filter_reset_radius_distance', function () {
        delete _this.data.params['distance'];
        delete _this.data.params['center_lat'];
        delete _this.data.params['center_lng'];
        delete _this.data.params['geo_address'];
        _this.removeParam('distance');
        _this.removeParam('center_lat');
        _this.removeParam('center_lng');
        _this.removeParam('geo_address');
        $itemWrap.find('.rtcl-geo-address-input').val('');
        $itemWrap.removeClass('is-active');
        distanceSlider.noUiSlider.set([rangeDefault, null]);
      });
    });
  });
  _defineProperty(this, "callAjax", function () {
    var $filterWrap = _this.$(_this.filterWraperClass);
    var $listingWrap = _this.$(_this.listingsContainerClass);
    _this.$.ajax({
      type: "POST",
      url: rtcl.ajaxurl,
      data: _this.data,
      beforeSend: function beforeSend() {
        $filterWrap.rtclBlock();
        $listingWrap.rtclBlock();
      },
      success: function success(res) {
        if (res.success) {
          _this.data.params.page = res.data.pagination.current_page;
          _this.renderData(_objectSpread(_objectSpread({}, res.data), {}, {
            actionData: _this.data
          }));
          delete _this.data.filterData.initLoad;
        }
      },
      error: function error(jqXHR, exception, _error3) {
        console.error(_error3);
      },
      complete: function complete() {
        $filterWrap.rtclUnblock();
        $listingWrap.rtclUnblock();
        _this.reset = false;
        _this.initLoading = false;
      }
    });
  });
  _defineProperty(this, "removeFilterItem", function (event) {
    var $self = _this.$(event.currentTarget),
      itemId = $self.data('item-id'),
      filterName = $self.data('filter-name'),
      filterValue = $self.data('filter-value'),
      $item = _this.withOutFilterPrefix.includes(itemId) ? _this.$('.rtcl-ajax-filter-item.rtcl-' + itemId) : _this.$('.rtcl-ajax-filter-item.rtcl-filter_' + itemId),
      $container = $item.find('.rtcl-filter-content'),
      options = $container.data('options');
    if (!$item.length) {
      return;
    }
    var needToTrigger = false;
    if ('price_range' === itemId) {
      _this.removeParam('filter_price');
      _this.reset = true;
      _this.$(document).trigger('rtcl_ajax_filter_reset_price_range');
      needToTrigger = true;
    } else if ('radius_filter' === itemId) {
      _this.removeParam('distance');
      _this.removeParam('center_lat');
      _this.removeParam('center_lng');
      _this.removeParam('geo_address');
      _this.reset = true;
      _this.$(document).trigger('rtcl_ajax_filter_reset_radius_distance');
      needToTrigger = true;
    } else if ('rating' === itemId) {
      _this.removeParam(filterName);
      $item.removeClass('is-active');
      $item.find('.rtcl-filter-ratings-item').removeClass('selected');
      needToTrigger = true;
    } else {
      if (options) {
        if (['checkbox', 'radio'].includes(options.field_type)) {
          _this.removeParam(filterName, filterValue, true);
          $item.find('input[value="' + filterValue + '"]').prop('checked', false);
        } else {
          if (options.field_type === 'number') {
            _this.removeParam(filterName);
            $item.removeClass('is-active');
            $item.find('input.rtcl-filter-number-field').val('');
          } else {
            _this.removeParam(filterName);
            $item.find('input[name="' + filterName + '"], select[name="' + filterName + '"]').val('');
            $item.removeClass('is-active');
          }
        }
        needToTrigger = true;
      }
    }
    if (needToTrigger) {
      delete _this.data.params.page;
      _this.removeParam('page');
      _this.$(document).trigger('rtcl_ajax_filter_update_params');
    }
  });
  _defineProperty(this, "resetFilter", function () {
    var that = _this;
    var view = _this.data.params.view;
    _this.data.params = {};
    if (view) {
      _this.data.params.view = view;
    }
    var url = new URL(window.location.href);
    if (_this.options.items && Array.isArray(_this.options.items)) {
      _this.options.items.map(function (_item) {
        if (_item.id === "price_range") {
          url.searchParams["delete"]('filter_price');
        } else if (_item.id === "radius_filter") {
          url.searchParams["delete"]('center_lat');
          url.searchParams["delete"]('center_lng');
          url.searchParams["delete"]('geo_address');
          url.searchParams["delete"]('distance');
        } else {
          var paramName = that.withOutFilterPrefix.includes(_item.id) ? _item.id : 'filter_' + _item.id;
          url.searchParams["delete"](paramName);
        }
      });
    }
    url.searchParams["delete"]('page');
    url.searchParams["delete"]('orderby');
    url.searchParams["delete"]('q');
    Array.from(url.searchParams).map(function (_ref3) {
      var _ref4 = _slicedToArray(_ref3, 1),
        _key = _ref4[0];
      if (_key.startsWith('cf_')) {
        url.searchParams["delete"](_key);
      }
    });
    window.history.pushState('', document.title, url.toString());
    _this.$(document).trigger('rtcl_ajax_filter_reset');
  });
  _defineProperty(this, "handlePagination", function (e) {
    var target;
    if (e.target.tagName === 'SPAN') {
      target = e.target.parentNode;
    } else {
      target = e.target;
    }
    var pageNumber = _this.$(target).data('id') || 1;
    _this.data.params['page'] = pageNumber;
    _this.addParam('page', pageNumber);
    _this.scrollToTop();
    _this.callAjax();
  });
  _defineProperty(this, "renderData", function (data) {
    _this.renderActiveFilter(data.active_filters);
    _this.renderCfFilterItems(data.cf_items);
    _this.renderListings(data.listings);
    _this.renderPagination(data.pagination);
    _this.renderResultCount(data.pagination);
    _this.$(document).trigger('rtcl_ajax_filter_after_render', [data]);
  });
  _defineProperty(this, "renderListings", function (listings) {
    if (_this.isArchive && _this.initLoading && !_this.$(_this.resultWrapClass).length) {
      return;
    }
    var $wrap = _this.$(document).find(_this.listingsContainerClass);
    if (!$wrap.length && _this.$(_this.resultWrapClass).length) {
      $wrap = _this.$('<div class="rtcl-ajax-listings"></div>');
      _this.$(_this.resultWrapClass).append($wrap);
    }
    if (!listings) {
      $wrap.addClass('no-listing-found');
      listings = _this.$('<div class="rtcl-info no-listing-found"></div>');
      listings.text(rtclAjaxFilterObj.no_result_found);
    } else {
      $wrap.removeClass('no-listing-found');
    }
    $wrap.html(listings);
  });
  _defineProperty(this, "renderCfFilterItems", function (cfItems) {
    if (_this.initLoading) {
      return;
    }
    var cfWrap = _this.$(_this.cfWrapperClass);
    cfWrap.empty();
    if (cfItems && Array.isArray(cfItems) && cfWrap.length) {
      cfItems.map(function (_cfItem) {
        cfWrap.append(_this.$(_cfItem.html));
      });
      _this.dateInit();
    }
  });
  _defineProperty(this, "renderActiveFilter", function (filters) {
    var $filterContainer = _this.$('<div class="rtcl-active-filters-container"></div>');
    if (filters && Array.isArray(filters) && filters.length) {
      var $filterWrap = _this.$('<div class="rtcl-active-filters-wrap"></div>');
      var $filters = _this.$('<div class="rtcl-active-filters"></div>');
      filters.map(function (_filter) {
        var $filter = _this.$('<div class="rtcl-active-filter"><div class="af-name"></div><div class="af-items"></div></div>');
        $filter.find('.af-name').text(_filter.label);
        Object.keys(_filter.selected).map(function (_id) {
          var $item = _this.$('<div class="afi" tabindex="0"><span class="rtcl-remove-filter"><i class="remove-icon"></i></span></div>');
          $item.attr('data-item-id', _filter.itemId);
          $item.attr('data-filter-name', _filter.id);
          $item.attr('data-filter-value', _id);
          $item.prepend(document.createTextNode(_filter.selected[_id]));
          $filter.find('.af-items').append($item);
        });
        $filters.append($filter);
      });
      var $restBtn = _this.$('<div class="rtcl-clear-filters" tabindex="0"><span class="icon-wrap"><i class="rtcl-icon rtcl-icon-trash"></i></span><span></span></div>');
      $restBtn.find('span:last').text(rtclAjaxFilterObj.clear_all_filter);
      $filterWrap.append($filters, $restBtn);
      $filterContainer.append($filterWrap);
    }
    var $container = _this.$(document).find('.rtcl-active-filters-container');
    if ($container.length) {
      $container.replaceWith($filterContainer);
    } else {
      if (_this.$(_this.listingsContainerClass).length) {
        $filterContainer.insertBefore(_this.$(_this.listingsContainerClass));
      } else if (_this.$(_this.resultWrapClass).length) {
        $filterContainer.insertBefore(_this.$(_this.resultWrapClass).find('.rtcl-listings'));
      }
    }
  });
  _defineProperty(this, "range", function (start, end) {
    var length = end - start + 1;
    return Array.from({
      length: length
    }, function (_, idx) {
      return idx + start;
    });
  });
  _defineProperty(this, "getPageNumberArray", function (currentPage, pages) {
    var totalPageCount = pages;
    var siblingCount = 1;
    var totalPageNumbers = siblingCount + 5;
    var DOTS = '...';
    if (totalPageNumbers >= totalPageCount) {
      return _this.range(1, totalPageCount);
    }
    var leftSiblingIndex = Math.max(currentPage - siblingCount, 1);
    var rightSiblingIndex = Math.min(currentPage + siblingCount, totalPageCount);
    var shouldShowLeftDots = leftSiblingIndex > 2;
    var shouldShowRightDots = rightSiblingIndex < totalPageCount - 2;
    var firstPageIndex = 1;
    var lastPageIndex = totalPageCount;

    /*
    	Case 2: No left dots to show, but rights dots to be shown
    */
    if (!shouldShowLeftDots && shouldShowRightDots) {
      var leftItemCount = 3 + 2 * siblingCount;
      var leftRange = _this.range(1, leftItemCount);
      return [].concat(_toConsumableArray(leftRange), [DOTS, totalPageCount]);
    }
    /*
      Case 3: No right dots to show, but left dots to be shown
     */
    if (shouldShowLeftDots && !shouldShowRightDots) {
      var rightItemCount = 3 + 2 * siblingCount;
      var rightRange = _this.range(totalPageCount - rightItemCount + 1, totalPageCount);
      return [firstPageIndex, DOTS].concat(_toConsumableArray(rightRange));
    }

    /*
    	Case 4: Both left and right dots to be shown
    */
    if (shouldShowLeftDots && shouldShowRightDots) {
      var middleRange = _this.range(leftSiblingIndex, rightSiblingIndex);
      return [firstPageIndex, DOTS].concat(_toConsumableArray(middleRange), [DOTS, lastPageIndex]);
    }
  });
  _defineProperty(this, "renderPagination", function (data) {
    var $paginationContainer = _this.$('<div class="rtcl-ajax-pagination-container"></div>');
    if (data && data.pages > 1) {
      var pages = _this.getPageNumberArray(data.current_page, data.pages);
      var $paginationWrap = _this.$('<div class="rtcl-ajax-pagination-wrap"></div>');
      var $pagination = _this.$('<ul class="rtcl-ajax-pagination"></ul>');
      pages.map(function (i, index) {
        var $pageItem;
        if (i === '...') {
          $pageItem = _this.$('<li class="rtcl-ajax-pagination-item dots"><span>&#8230;</span></li>');
        } else {
          $pageItem = _this.$('<li class="rtcl-ajax-pagination-item page-item" data-id="' + i + '"><span>' + i + '</span></li>');
          if (i === data.current_page) {
            $pageItem.addClass('active').attr('aria-current', 'page');
          } else {
            $pageItem.attr('tabindex', '0');
          }
        }
        $pagination.append($pageItem);
      });
      $paginationWrap.append($pagination);
      $paginationContainer.append($paginationWrap);
    }
    var $container = _this.$(document).find('.rtcl-ajax-pagination-container');
    if ($container.length) {
      $container.replaceWith($paginationContainer);
    } else {
      if (_this.$(_this.listingsContainerClass).length) {
        $paginationContainer.insertAfter(_this.$(_this.listingsContainerClass));
      } else if (_this.$(_this.resultWrapClass).length) {
        $paginationContainer.insertAfter(_this.$(_this.resultWrapClass).find(_this.listingsContainerClass));
      }
    }
  });
  _defineProperty(this, "renderResultCount", function (data) {
    var $listingResultWrap = _this.$('.rtcl-listings-actions');
    if (!$listingResultWrap.length) {
      return;
    }
    var $resultCount = $listingResultWrap.find('.rtcl-result-count');
    $resultCount.attr("data-options", JSON.stringify({
      items: data.items
    }));
    if (!data.current_items || data.items <= data.per_page) {
      $resultCount.text(rtclAjaxFilterObj.result_count.all.replace("%", data.items));
    } else {
      var fromCount = (data.current_page - 1) * data.per_page;
      var showing = "".concat(fromCount + 1, "\u2013").concat(fromCount + data.current_items);
      $resultCount.text(rtclAjaxFilterObj.result_count.part.replace("_", showing).replace("%", data.items));
    }
  });
  /**
   * Smoothly scrolls the page to a specified target position.
   *
   * @param {number} targetPosition - The target scroll position to scroll to.
   * @param {number} duration       - The duration of the smooth scrolling animation in milliseconds.
   */
  _defineProperty(this, "smoothScrollTo", function (targetPosition, duration) {
    var start = window.scrollY || window.pageYOffset;
    var startTime = 'now' in window.performance ? performance.now() : new Date().getTime();
    function easeInOutExpo(x) {
      return x === 0 ? 0 : x === 1 ? 1 : x < 0.5 ? Math.pow(2, 20 * x - 10) / 2 : (2 - Math.pow(2, -20 * x + 10)) / 2;
    }
    function scrollAnimation(currentTime) {
      var timeElapsed = currentTime - startTime;
      var scrollProgress = Math.min(1, timeElapsed / duration);

      // Apply the cubic bezier easing function to the scroll progress
      var easedProgress = easeInOutExpo(scrollProgress);

      // Calculate and set the new scroll position with eased progress
      window.scrollTo(0, start + (targetPosition - start) * easedProgress);
      if (scrollProgress < 1) {
        requestAnimationFrame(scrollAnimation);
      }
    }
    requestAnimationFrame(scrollAnimation);
  });
  this.$ = jQuery;
  this.filterWraperClass = '.rtcl-widget-ajax-filter-wrapper';
  this.filterContainerClass = '.rtcl-ajax-filter-wrap';
  this.filterTitleWrapClass = '.rtcl-filter-title-wrap';
  this.resultWrapClass = '.rtcl-ajax-filter-result-wrap';
  this.listingsContainerClass = '.rtcl-ajax-listings';
  this.archivePaginationClass = '.rtcl-pagination';
  this.noListingFoundClass = '.no-listing-found';
  this.cfWrapperClass = '.rtcl-ajax-filter-cf-wrap';
  this.options = this.$(this.filterWraperClass).data("options");
  this.isTaxArchive = this.$('body').hasClass('tax-rtcl_category') || this.$('body').hasClass('tax-rtcl_location') || this.$('body').hasClass('tax-rtcl_tag');
  this.isListingArchive = this.$('body').hasClass('post-type-archive-rtcl_listing');
  this.isStoreSingle = this.$('body').hasClass('single-store');
  this.isArchive = this.isListingArchive || this.isTaxArchive || this.isStoreSingle;
  this.store_id = this.$('body.single-store').find('#rtcl_store_id').val();
  this.initLoading = true;
  this.withOutFilterPrefix = ['directory'];
  this.reset = false;
  this.data = {
    filterData: _objectSpread(_objectSpread({}, this.options), {}, {
      itemKeys: (_this$options2 = this.options) === null || _this$options2 === void 0 || (_this$options2 = _this$options2.items) === null || _this$options2 === void 0 ? void 0 : _this$options2.map(function (_i) {
        return _i.id;
      }),
      initLoad: true
    }),
    params: {},
    is_listings: rtcl.is_listings,
    is_listing: rtcl.is_listing,
    listing_term: rtcl.listing_term,
    rtcl_store_id: this.store_id,
    activeTerms: rtcl.active_terms || [],
    hasMap: this.$('.rtcl-listings-sc-wrapper.has-map').length ? 1 : '',
    action: 'rtcl_ajax_filter_load_data',
    __rtcl_wpnonce: rtcl.__rtcl_wpnonce
  };
});
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (RtclAjaxFilter);

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**************************!*\
  !*** ./src/js/public.js ***!
  \**************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _classes_RtclAjaxFilter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./classes/RtclAjaxFilter */ "./src/js/classes/RtclAjaxFilter.js");
/* global rtcl, rtclAjaxFilterObj */

;
(function ($) {
  "use strict";

  // user settings form
  $(document).on('submit', '#rtcl-user-profile-settings', function (e) {
    e.preventDefault();
    var $form = $(this),
      targetBtn = $form.find("button[type=submit]"),
      responseHolder = $form.find(".rtcl-response"),
      msgHolder = $("<div class='alert'></div>"),
      fromData = new FormData(this);
    fromData.append("action", "rtcl_update_profile_settings");
    fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
    $.ajax({
      url: rtcl.ajaxurl,
      data: fromData,
      dataType: "json",
      cache: false,
      processData: false,
      contentType: false,
      type: "POST",
      beforeSend: function beforeSend() {
        $form.addClass("rtcl-loading");
        targetBtn.prop("disabled", true);
        responseHolder.html("");
        $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
      },
      success: function success(response) {
        targetBtn.prop("disabled", false).next(".rtcl-icon-spinner").remove();
        $form.removeClass("rtcl-loading");
        if (response.success) {
          msgHolder.removeClass("alert-danger").addClass("alert-success").html(response.data.message).appendTo(responseHolder);
          setTimeout(function () {
            responseHolder.html("");
          }, 1000);
        } else {
          msgHolder.removeClass("alert-success").addClass("alert-danger").html(response.data.error).appendTo(responseHolder);
        }
      },
      error: function error(e) {
        msgHolder.removeClass("alert-success").addClass("alert-danger").html(e.responseText).appendTo(responseHolder);
        targetBtn.prop("disabled", false).next(".rtcl-icon-spinner").remove();
        $form.removeClass("rtcl-loading");
      }
    });
  });
  // Single listing Comment form
  $("body")
  // Star ratings for comments
  .on("init", "#rating", function () {
    $(".single-rtcl_listing #rating").hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');
  }).on("click", "#respond p.stars a", function () {
    var $star = $(this),
      $rating = $star.closest("#respond").find("#rating"),
      ratingWrap = $rating.parent(".form-group"),
      $container = $star.closest(".stars");
    $rating.val($star.text());
    $star.siblings("a").removeClass("active");
    $star.addClass("active");
    $container.addClass("selected");
    ratingWrap.removeClass("has-danger");
    ratingWrap.find(".with-errors").remove();
    return false;
  }).on("change", ".rtcl-ordering select.orderby", function () {
    $(this).closest("form").submit();
  })
  // single page animate scroll
  .on("click", ".rtcl-animate", function (e) {
    e.preventDefault();
    var position = $($(this).attr("href")).offset();
    $("html,body").stop().animate({
      scrollTop: position.top - 120
    }, 500);
  }).on("input", ".rtcl-password", function () {
    var pass_input = $(this),
      pass = pass_input.val(),
      element_wrap = pass_input.parent(),
      pass_status_wrap = element_wrap.find(".rtcl-pass-strength-result"),
      strength;
    if (!pass_status_wrap.length) {
      pass_status_wrap = $('<div class="rtcl-pass-strength-result" />');
      element_wrap.append(pass_status_wrap);
    }
    pass_status_wrap.removeClass("short bad good strong empty");
    if (!pass || "" === pass.trim() || pass.trim().length < rtcl_validator.pw_min_length) {
      pass_status_wrap.addClass("empty").html("&nbsp;");
      return;
    }
    strength = rtclCheckPasswordStrength(pass);
    switch (strength) {
      case -1:
        pass_status_wrap.addClass("bad").html(rtcl_validator.pwsL10n.unknown);
        break;
      case 1:
      case 2:
        pass_status_wrap.addClass("bad").html(rtcl_validator.pwsL10n.bad);
        break;
      case 3:
      case 4:
        pass_status_wrap.addClass("good").html(rtcl_validator.pwsL10n.good);
        break;
      case 5:
      case 6:
        pass_status_wrap.addClass("strong").html(rtcl_validator.pwsL10n.strong);
        break;
      // case 5:
      //     pass_status_wrap.addClass('short').html(rtcl_validator.pwsL10n.mismatch);
      // break;
      default:
        pass_status_wrap.addClass("short").html(rtcl_validator.pwsL10n["short"]);
    }
  }).on("click", ".rtcl-toggle-pass", function () {
    var $_self = $(this);
    var $_input = $_self.parent().find('input');
    var isOff = $_self.hasClass('rtcl-icon-eye-off');
    if (!$_input.length) {
      return;
    }
    if (isOff) {
      $_input.attr('type', 'text');
      $_self.removeClass('rtcl-icon-eye-off').addClass('rtcl-icon-eye');
    } else {
      $_input.attr('type', 'password');
      $_self.removeClass('rtcl-icon-eye').addClass('rtcl-icon-eye-off');
    }
  }).on("input focusout", "#rtcl-reg-confirm-password", function () {
    var $confirm_input = $(this);

    /*const promise = new Promise((resolve, reject) => {
    	let valid = $confirm_input.attr('aria-invalid') !== undefined && $confirm_input.attr('aria-invalid') != 'true';
    	return resolve(valid);
    });
    	Promise.all([promise]).then((result) => {
    	const $element_wrap = $confirm_input.closest('.confirm-password-wrap');
    	const $checkmark = $element_wrap.find('.rtcl-checkmark');
    	$checkmark.toggle($confirm_input.val().length && result);
    }).catch((error) => console.log(error));*/

    setTimeout(function () {
      var valid = $confirm_input.attr('aria-invalid') !== undefined && $confirm_input.attr('aria-invalid') != 'true';
      var $element_wrap = $confirm_input.closest('.confirm-password-wrap');
      var $checkmark = $element_wrap.find('.rtcl-checkmark');
      $checkmark.toggle($confirm_input.val().length > 0 && valid);
    }, 100);
  }).on('click', '.rtcl-renew-btn', function (e) {
    e.preventDefault();
    var $self = $(this);
    var listingId = $self.data('id') || 0;
    if (!listingId) {
      toastr.error(rtcl_store.lng.error);
      return false;
    }
    var parentWrap = $self.parents('.rtcl-listing-item');
    if (confirm(rtcl.confirm_text)) {
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        data: {
          listingId: listingId,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
          action: 'rtcl_ajax_renew_listing'
        },
        beforeSend: function beforeSend() {
          parentWrap.rtclBlock();
        },
        success: function success(res) {
          if (res.success) {
            $self.slideUp();
            parentWrap.find('.rtcl-status-wrap .rtcl-status').html(res.data.status);
            parentWrap.find('.rtcl-expire-wrap .rtcl-expire').html(res.data.expire_at);
            toastr.success(res.data.message);
          } else {
            toastr.error(res.data);
          }
          parentWrap.rtclUnblock();
        },
        error: function error(e) {
          parentWrap.rtclUnblock();
          toastr.error('Server Error.');
        }
      });
    }
    return false;
  });

  // Init Tabs and Star Ratings
  $("#rating").trigger("init");

  // Listing - Toggle Filter
  $('#rtcl-toggle-filter-mobile').on('click', function (e) {
    e.preventDefault();
    var $this = $(this),
      $filter = $this.closest('.rtcl-widget-filter-wrapper').find('.rtcl-widget-filter-class');
    $filter.toggle();
  });
  $(document).on("click", "#rtcl-resend-verify-link", function (e) {
    e.preventDefault();
    if (confirm(rtcl.re_send_confirm_text)) {
      var login = $(this).data("login"),
        parent = $(this).parent();
      $.ajax({
        url: rtcl.ajaxurl,
        data: {
          action: "rtcl_resend_verify",
          user_login: login,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        },
        type: "POST",
        dataType: "JSON",
        beforeSend: function beforeSend() {
          parent.rtclBlock();
        },
        success: function success(response) {
          parent.rtclUnblock();
          alert(response.data.message);
        },
        error: function error(e) {
          parent.rtclUnblock();
          alert("Server Error!!!");
        }
      });
    }
    return false;
  });
  $(document).on("click", function (e) {
    var $container = $(".rtcl-ai-search-result-container");
    if (!$container.is(e.target) && $container.has(e.target).length === 0 && !$(e.target).closest(".rtcl-ai-quick-search").length) {
      $container.slideUp(300);
    }
  });
  $(document).on('click', '.rtcl-tab-nav li a', function (e) {
    e.preventDefault();
    var tabId = $(this).data('target'),
      $li = $(this).closest('li');
    $li.addClass('active').siblings().removeClass('active');
    $('#' + tabId).addClass('active').siblings('.rtcl-tab-pane').removeClass('active');
  });
  $(document).on("click", ".rtcl-ai-quick-search-inner", function () {
    var $this = $(this),
      $wrapper = $this.closest('.rtcl-ai-search-field'),
      $resultWrap = $wrapper.next('.rtcl-ai-search-result-container'),
      keyword = $wrapper.find("input[name='q']").val();
    var data = {
      action: 'rtcl_ai_quick_search',
      __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
      keyword: keyword
    };
    $.ajax({
      url: rtcl.ajaxurl,
      data: data,
      type: "POST",
      dataType: "JSON",
      beforeSend: function beforeSend() {
        $resultWrap.addClass('loading');
        $resultWrap.find('.rtcl-ai-search-result-header h4').text(rtcl.i18n.ai_quick_search_loading);
        $resultWrap.find('.rtcl-ai-search-result-content').html("");
        $resultWrap.slideDown(250);
        $this.css("cursor", "wait");
      },
      success: function success(res) {
        $this.css("cursor", "pointer");
        if (res.success) {
          $resultWrap.find('.rtcl-ai-search-result-header h4').text(rtcl.i18n.ai_quick_search_heading + keyword);
          $resultWrap.find('.rtcl-ai-search-result-content').html(res.data.html);
        } else {
          $resultWrap.find('.rtcl-ai-search-result-header h4').text(res.data.message);
        }
        $resultWrap.removeClass('loading');
      },
      error: function error(e) {
        $this.css("cursor", "pointer");
        $resultWrap.removeClass('loading');
        $resultWrap.find('.rtcl-ai-search-result-header h4').text(e.errorText);
      }
    });
  });
  $(document).on('click', '.rtcl-payment-table-wrap .rtcl-payment-popup-link', function (e) {
    e.preventDefault();
    var $this = $(this),
      $wrapper = $this.closest('.rtcl-payment-history-wrap'),
      $popupWrapper = $wrapper.find('.rtcl-popup-wrapper'),
      orderId = $this.data('order-id');
    var data = {
      action: 'rtcl_payment_details_popup',
      __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
      order_id: orderId
    };
    $.ajax({
      url: rtcl.ajaxurl,
      data: data,
      type: "POST",
      dataType: "JSON",
      beforeSend: function beforeSend() {
        $popupWrapper.animate({
          opacity: 0
        }, 10);
        $popupWrapper.removeClass('show');
      },
      success: function success(response) {
        if (response.success) {
          $popupWrapper.find('.rtcl-popup-body').html(response.data.html);
          $popupWrapper.animate({
            opacity: 1
          }, 300);
          $popupWrapper.addClass('show');
        }
      },
      error: function error(e) {
        console.log(e.errorText);
      }
    });
  }).on('click', '#rtcl-report-abuse-modal-link', function (e) {
    e.preventDefault();
    var $this = $(this),
      $wrapper = $this.closest('.single-listing-custom-fields-action'),
      $popupWrapper = $wrapper.find('#rtcl-report-abuse-modal');
    if ($popupWrapper.length) {
      $("#rtcl-report-abuse-message").val("");
      $("#rtcl-report-abuse-message-display").html("");
      $popupWrapper.animate({
        opacity: 1
      }, 300);
      $popupWrapper.addClass('show');
    }
  }).on('click', '.rtcl-popup-close', function (e) {
    e.preventDefault();
    var $wrapper = $(this).closest('.rtcl-popup-wrapper');
    $wrapper.animate({
      opacity: 0
    }, 300);
    setTimeout(function () {
      $wrapper.removeClass('show');
    }, 500);
  }).on('click', '.rtcl-MyAccount-open-menu', function (e) {
    e.preventDefault();
    var $this = $(this),
      $navWrapper = $(".rtcl-MyAccount-navigation"),
      $contentWrapper = $(".rtcl-MyAccount-content"),
      $html = '<div class="sidebar-menu-opened"></div>';
    if ($this.hasClass('sidebar-open')) {
      $navWrapper.css('left', '-225px');
      $contentWrapper.find('.sidebar-menu-opened').remove();
    } else {
      $navWrapper.css('left', '0');
      $contentWrapper.prepend($html);
    }
    $this.toggleClass('sidebar-open');
  }).on('click', '.rtcl-MyAccount-content .sidebar-menu-opened', function (e) {
    var $navWrapper = $(".rtcl-MyAccount-navigation"),
      $collapseButton = $(".rtcl-MyAccount-open-menu");
    $navWrapper.css('left', '-225px');
    $(this).remove();
    $collapseButton.removeClass('sidebar-open');
  }).on('click', '.rtcl-ajax-filter-floating-mobile .rtcl-ajax-filter-open-filter', function (e) {
    e.preventDefault();
    var $this = $(this),
      $floatingWrapper = $this.closest(".rtcl-ajax-filter-floating-mobile"),
      $mainWrapper = $floatingWrapper.closest(".rtcl-widget-ajax-filter-wrapper"),
      $filterWrapper = $mainWrapper.find('.rtcl-widget-ajax-filter-class'),
      $body = $("body"),
      $html = '<div class="sidebar-ajax-filter-opened"></div>';
    if ($mainWrapper.hasClass('sidebar-filter-open')) {
      $filterWrapper.css('left', '-265px');
      $body.find('.sidebar-ajax-filter-opened').remove();
    } else {
      $filterWrapper.css('left', '0');
      $body.prepend($html);
    }
    $mainWrapper.toggleClass('sidebar-filter-open');
  }).on('click', '.sidebar-ajax-filter-opened', function (e) {
    e.preventDefault();
    var $filterWrapper = $(".rtcl-widget-ajax-filter-class"),
      $wrapper = $(".rtcl-widget-ajax-filter-wrapper");
    $filterWrapper.css('left', '-265px');
    $(this).remove();
    $wrapper.removeClass('sidebar-filter-open');
  }).on("submit", ".rtcl-my-listings-search-form form", function (e) {
    e.preventDefault();
    my_account_listings_ajax();
  }).on("change", "#rtcl-my-listings-directory", function () {
    my_account_listings_ajax();
  }).on("change", "#rtcl-my-listings-status", function () {
    my_account_listings_ajax();
  }).on("click", ".rtcl-my-listings-content .rtcl-pagination a", function (e) {
    e.preventDefault();
    var $this = $(this),
      $wrapper = $this.closest(".rtcl-my-listings-content"),
      currentPage = parseInt($wrapper.find(".current").text()) || 1,
      page;
    if ($this.hasClass("next")) {
      page = currentPage + 1;
    } else if ($this.hasClass("prev")) {
      page = currentPage - 1;
    } else {
      page = parseInt($this.text());
    }
    if (page < 1) {
      page = 1;
    }
    my_account_listings_ajax(page);
  }).on('click', '.rtcl-my-listing-table .rtcl-actions-wrap .actions-dot', function (e) {
    $('.rtcl-my-listing-table').find('.rtcl-actions').removeClass('opened').addClass('closed');
    $(this).closest('.rtcl-actions-wrap').find('.rtcl-actions').removeClass('closed').addClass('opened');
  }).on('click', function (e) {
    if ($(e.target).closest('.rtcl-actions-wrap').find('.rtcl-actions').length === 0) {
      $('.rtcl-my-listing-table').find('.rtcl-actions').removeClass('opened');
    }
  }).on('click', '.rtcl-my-listings-table-toggle-info', function (e) {
    var $this = $(this),
      $tr = $this.closest('tr'),
      $hideCell = $tr.find('.list-on-responsive');
    $hideCell.toggleClass('show');
    $tr.find('.title-cell').toggleClass('showed-info');
  });
  window.rtcl_make_checkout_request = function (form, callback) {
    var $form = $(form),
      $submitBtn = $("button[type=submit]", $form),
      msgHolder = $("<div class='alert rtcl-response'></div>"),
      data = $form.serialize();
    $.ajax({
      url: rtcl.ajaxurl,
      data: data,
      type: "POST",
      dataType: "JSON",
      beforeSend: function beforeSend() {
        $submitBtn.prop("disabled", true);
        $form.find(".alert.rtcl-response").remove();
        $form.rtclBlock();
      },
      success: function success(response) {
        $submitBtn.prop("disabled", false);
        $form.rtclUnblock();
        var msg = "";
        if (response.success) {
          if (response.success_message.length) {
            response.success_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }
          if (msg) {
            msgHolder.removeClass("alert-danger").addClass("alert-success").html(msg).appendTo($form);
          }
        } else {
          if (response.error_message.length) {
            response.error_message.map(function (message) {
              msg += "<p>" + message + "</p>";
            });
          }
          if (msg) {
            msgHolder.removeClass("alert-success").addClass("alert-danger").html(msg).appendTo($form);
          }
        }
        if (typeof callback === "function") {
          callback(response);
        } else {
          setTimeout(function () {
            if (response.redirect_url) {
              window.location = response.redirect_url;
            }
          }, 600);
        }
      },
      error: function error(e) {
        $submitBtn.prop("disabled", false);
        $form.rtclUnblock();
        if (typeof callback === "function") {
          callback(e);
        }
      }
    });
  };
  window.rtcl_on_recaptcha_load = function () {
    if (rtcl.recaptcha && rtcl.recaptcha.v === 2) {
      rtcl.recaptcha.response = {};
      var args = {
        sitekey: rtcl.recaptcha.site_key
      };
      // Add reCAPTCHA in login form
      var $loginForms = $("form.rtcl-login-form, form#rtcl-login-form");
      if ($loginForms.length && $.inArray("login", rtcl.recaptcha.on) !== -1) {
        $loginForms.each(function (index, form) {
          var $form = $(form);
          if (!$form.data("reCaptchaId")) {
            if ($form.find("#rtcl-login-g-recaptcha").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find("#rtcl-login-g-recaptcha")[0], args));
            } else if ($form.find(".rtcl-g-recaptcha-wrap").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find(".rtcl-g-recaptcha-wrap")[0], args));
            }
          }
        });
      }

      // Add reCAPTCHA in registration form
      var $regForms = $("form#rtcl-register-form, form.rtcl-register-form");
      if ($regForms.length && $.inArray("registration", rtcl.recaptcha.on) !== -1) {
        $regForms.each(function (index, form) {
          var $form = $(form);
          if (!$form.data("reCaptchaId")) {
            if ($form.find("#rtcl-registration-g-recaptcha").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find("#rtcl-registration-g-recaptcha")[0], args));
            } else if ($form.find(".rtcl-g-recaptcha-wrap").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find(".rtcl-g-recaptcha-wrap")[0], args));
            }
          }
        });
      }

      // Add reCAPTCHA in listing form
      var $submitForm = $("form#rtcl-post-form");
      if ($submitForm.length && $.inArray("listing", rtcl.recaptcha.on) !== -1) {
        if (!$submitForm.data("reCaptchaId")) {
          if ($submitForm.find("#rtcl-listing-g-recaptcha").length) {
            $submitForm.data("reCaptchaId", grecaptcha.render($submitForm.find("#rtcl-listing-g-recaptcha")[0], args));
          } else if ($submitForm.find(".rtcl-g-recaptcha-wrap").length) {
            $submitForm.data("reCaptchaId", grecaptcha.render($submitForm.find(".rtcl-g-recaptcha-wrap")[0], args));
          }
        }
      }

      // Add reCAPTCHA in contact form
      var $contactForms = $("form.rtcl-contact-form, form#rtcl-contact-form");
      if ($contactForms.length && $.inArray("contact", rtcl.recaptcha.on) !== -1) {
        $contactForms.each(function (index, form) {
          var $form = $(form);
          if (!$form.data("reCaptchaId")) {
            if ($form.find("#rtcl-contact-g-recaptcha").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find("#rtcl-contact-g-recaptcha")[0], args));
            } else if ($form.find(".rtcl-g-recaptcha-wrap").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find(".rtcl-g-recaptcha-wrap")[0], args));
            }
          }
        });
      }
      // Add reCAPTCHA in report abuse form
      var $reportForms = $("form.rtcl-report-abuse-form, form#rtcl-report-abuse-form");
      if ($reportForms.length && $.inArray("report_abuse", rtcl.recaptcha.on) !== -1) {
        $reportForms.each(function (index, form) {
          var $form = $(form);
          if (!$form.data("reCaptchaId")) {
            if ($form.find("#rtcl-report-abuse-g-recaptcha").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find("#rtcl-report-abuse-g-recaptcha")[0], args));
            } else if ($form.find(".rtcl-g-recaptcha-wrap").length) {
              $form.data("reCaptchaId", grecaptcha.render($form.find(".rtcl-g-recaptcha-wrap")[0], args));
            }
          }
        });
      }
      $(document).trigger("rtcl_recaptcha_loaded");
    }
  };
  function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
      sURLVariables = sPageURL.split("&"),
      sParameterName,
      i;
    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split("=");
      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  }
  function my_account_listings_ajax() {
    var page = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
    var $wrapper = $(".rtcl-my-listings-content"),
      $form = $(".rtcl-my-listings-search-form form"),
      q = $form.find('input[name="u"]').val(),
      directory = $("#rtcl-my-listings-directory").val(),
      status = $("#rtcl-my-listings-status").val();
    $.ajax({
      url: rtcl.ajaxurl,
      type: "POST",
      data: {
        action: "rtcl_my_listings_search",
        search: q,
        directory: directory,
        status: status,
        rtcl_my_listing_page: page,
        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
      },
      beforeSend: function beforeSend() {
        $wrapper.rtclBlock();
      },
      success: function success(response) {
        if (response.success) {
          $wrapper.html(response.data.html);
        }
      },
      complete: function complete() {
        $wrapper.rtclUnblock();
      }
    });
  }
  function equalHeight() {
    $(".rtcl-equal-height").each(function () {
      var $equalItemWrap = $(this),
        equalItems = $equalItemWrap.find(".equal-item");
      equalItems.height("auto");
      if ($(window).width() > 767) {
        var maxH = 0;
        equalItems.each(function () {
          var itemH = $(this).outerHeight();
          if (itemH > maxH) {
            maxH = itemH;
          }
        });
        equalItems.height(maxH + "px");
      } else {
        equalItems.height("auto");
      }
    });
  }

  // On load function
  $(function () {
    $('#rtcl-reg-confirm-password').on("cut copy paste", function (e) {
      e.preventDefault();
    });
    $(document).on("click", ".rtcl-delete-listing", function (e) {
      e.preventDefault();
      if (confirm(rtcl.confirm_text)) {
        var _self = $(this),
          wrapper = _self.closest("tr"),
          data = {
            action: "rtcl_delete_listing",
            post_id: parseInt(_self.attr("data-id"), 10),
            __rtcl_wpnonce: rtcl.__rtcl_wpnonce
          };
        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              wrapper.rtclBlock();
            },
            success: function success(data) {
              wrapper.rtclUnblock();
              if (data.success) {
                wrapper.animate({
                  height: 0,
                  opacity: 0
                }, "slow", function () {
                  $(this).remove();
                });
              }
            },
            error: function error() {
              wrapper.rtclUnblock();
            }
          });
        }
      }
      return false;
    });
    $(document).on("click", ".rtcl-delete-favourite-listing", function (e) {
      e.preventDefault();
      if (confirm(rtcl.confirm_text)) {
        var _target = this,
          _self = $(_target),
          data = {
            action: "rtcl_public_add_remove_favorites",
            post_id: parseInt(_self.attr("data-id"), 10),
            __rtcl_wpnonce: rtcl.__rtcl_wpnonce
          };
        if (data.post_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
            },
            success: function success(res) {
              res.target = _target;
              _self.next(".rtcl-icon-spinner").remove();
              if (res.success) {
                _self.closest("tr").animate({
                  height: 0,
                  opacity: 0
                }, "slow", function () {
                  $(this).remove();
                });
                toastr.success(res.message);
              } else {
                toastr.error(res.message);
              }
              $(document).trigger("rtcl.favorite", res);
            },
            error: function error(e) {
              $(document).trigger("rtcl.favorite.error", {
                action: "remove",
                post_id: data.post_id,
                target: _target
              });
              _self.next(".rtcl-icon-spinner").remove();
            }
          });
        }
      }
      return false;
    });
    $("#rtcl-checkout-form").on("click", 'input[name="pricing_id"]', function (e) {
      if ($(this).data('price') + 0 === 0) {
        $("#rtcl-billing-fields").slideUp(250);
        $("#rtcl-payment-methods").slideUp(250);
        $("#rtcl-checkout-store-gateway").slideDown(250);
      } else {
        $("#rtcl-billing-fields").slideDown(250);
        $("#rtcl-payment-methods").slideDown(250);
        $("#rtcl-checkout-store-gateway").slideUp(250);
      }
    }).on("change", 'input[name="payment_method"]', function (e) {
      var target_payment_box = $("div.payment_box.payment_method_" + $(this).val());
      if ($(this).is(":checked") && !target_payment_box.is(":visible")) {
        $("#rtcl-checkout-form div.payment_box").filter(":visible").slideUp(250);
        if ($(this).is(":checked")) {
          target_payment_box.slideDown(250);
        }
      }
    });

    // Profile picture upload
    $(".rtcl-media-upload-pp .rtcl-media-action").on("click", "span.add", function () {
      var addBtn = $(this);
      var ppFile = $("<input type='file' style='position:absolute;left:-9999px' />");
      $("body").append(ppFile);
      if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        ppFile.trigger("change");
      } else {
        ppFile.trigger("click");
      }
      ppFile.on("change", function () {
        var fileItem = $(this);
        var pp_wrap = addBtn.parents(".rtcl-media-upload-pp");
        var pp_thumb_holder = $(".rtcl-media-item", pp_wrap);
        var form = new FormData();
        var pp = fileItem[0].files[0];
        var allowed_image_types = rtcl.image_allowed_type.map(function (type) {
          return "image/" + type;
        });
        var max_image_size = parseInt(rtcl.max_image_size);
        if ($.inArray(pp.type, allowed_image_types) !== -1) {
          if (pp.size <= max_image_size) {
            form.append("pp", pp);
            form.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
            form.append("action", "rtcl_ajax_user_profile_picture_upload");
            $.ajax({
              url: rtcl.ajaxurl,
              data: form,
              cache: false,
              contentType: false,
              processData: false,
              type: "POST",
              beforeSend: function beforeSend() {
                pp_wrap.rtclBlock();
              },
              success: function success(response) {
                pp_wrap.rtclUnblock();
                if (!response.error) {
                  pp_wrap.removeClass("no-media").addClass("has-media").parents(".rtcl-profile-picture-wrap").find(".rtcl-gravatar-wrap").hide();
                  pp_thumb_holder.html("<img class='rtcl-thumbnail' src='" + response.data.src + "'/>");
                }
              },
              error: function error(jqXhr, json, errorThrown) {
                pp_wrap.rtclUnblock();
              }
            });
          } else {
            alert(rtcl.error_image_size);
          }
        } else {
          alert(rtcl.error_image_extension);
        }
      });
    }).on("click", "span.remove", function () {
      var self = $(this);
      var pp_wrap = self.parents(".rtcl-media-upload-pp");
      var media_holder = $(".rtcl-media-item", pp_wrap);
      if (confirm(rtcl.confirm_text)) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: {
            action: "rtcl_ajax_user_profile_picture_delete",
            __rtcl_wpnonce: rtcl.__rtcl_wpnonce
          },
          type: "POST",
          beforeSend: function beforeSend() {
            pp_wrap.rtclBlock();
          },
          success: function success(response) {
            pp_wrap.rtclUnblock();
            if (!response.error) {
              pp_wrap.removeClass("has-media").addClass("no-media").parents(".rtcl-profile-picture-wrap").find(".rtcl-gravatar-wrap").show();
              media_holder.html("");
            }
          },
          error: function error(jqXhr, json, errorThrown) {
            pp_wrap.rtclUnblock();
          }
        });
      }
    });

    // Toggle password fields in user account form
    $("#rtcl-change-password").on("change", function () {
      var $checked = $(this).is(":checked");
      if ($checked) {
        $(".rtcl-password-fields").show().find('input[type="password"]').attr("disabled", false);
      } else {
        $(".rtcl-password-fields").hide().find('input[type="password"]').attr("disabled", "disabled");
      }
    }).trigger("change");

    // Alert users to login (only if applicable)
    $(".rtcl-require-login").on("click", function (e) {
      e.preventDefault();
      alert(rtcl.user_login_alert_message);
    });

    // Contact do email
    $(".rtcl-do-email").on("click", "a", function (e) {
      e.preventDefault();
      var _self = $(this),
        wrap = _self.parents(".rtcl-do-email");
      $("#rtcl-contact-form", wrap).slideToggle("slow");
      return false;
    });

    // Add or Remove from favourites
    $(document).on("click", "a.rtcl-favourites", function (e) {
      e.preventDefault();
      var _target = this,
        _self = $(_target),
        _parentEl = _self.parent(),
        data = {
          action: "rtcl_public_add_remove_favorites",
          post_id: parseInt(_self.attr("data-id"), 10),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      if (data.post_id) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          beforeSend: function beforeSend() {
            $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(_self);
            _parentEl.addClass('is-loading');
          },
          success: function success(res) {
            res.target = _target;
            _self.next(".rtcl-icon-spinner").remove();
            if (res.success) {
              _self.replaceWith(res.html);
              toastr.success(res.message);
            } else {
              toastr.error(res.message);
            }
            $(document).trigger("rtcl.favorite", res);
            _parentEl.removeClass('is-loading');
          },
          error: function error(e) {
            $(document).trigger("rtcl.favorite.error", {
              action: "remove",
              post_id: data.post_id,
              target: _target
            });
            _self.next(".rtcl-icon-spinner").remove();
            _parentEl.removeClass('is-loading');
          }
        });
      }
    });

    /**
     * Slider Class.
     */
    var RtclSlider = function RtclSlider($slider) {
      this.$slider = $slider;
      this.slider = this.$slider.get(0);
      this.swiperSlider = this.slider.swiper || null;
      this.defaultOptions = {
        breakpointsInverse: true,
        observer: true,
        navigation: {
          nextEl: this.$slider.find(".swiper-button-next").get(0),
          prevEl: this.$slider.find(".swiper-button-prev").get(0)
        }
      };
      this.slider_enabled = "function" === typeof Swiper;
      this.options = Object.assign({}, this.defaultOptions, this.$slider.data("options") || {});
      this.initSlider = function () {
        if (!this.slider_enabled) {
          return;
        }
        if (this.options.rtl) {
          this.$slider.attr("dir", "rtl");
        }
        if (this.swiperSlider) {
          this.swiperSlider.parents = this.options;
          this.swiperSlider.update();
        } else {
          this.swiperSlider = new Swiper(this.$slider.get(0), this.options);
        }
      };
      this.imagesLoaded = function () {
        var that = this;
        if (!$.isFunction($.fn.imagesLoaded) || $.fn.imagesLoaded.done) {
          this.$slider.trigger("rtcl_slider_loading", this);
          this.$slider.trigger("rtcl_slider_loaded", this);
          return;
        }
        this.$slider.imagesLoaded().progress(function (instance, image) {
          that.$slider.trigger("rtcl_slider_loading", [that]);
        }).done(function (instance) {
          that.$slider.trigger("rtcl_slider_loaded", [that]);
        });
      };
      this.start = function () {
        var that = this;
        this.$slider.on("rtcl_slider_loaded", this.init.bind(this));
        setTimeout(function () {
          that.imagesLoaded();
        }, 1);
      };
      this.init = function () {
        this.initSlider();
      };
      this.start();
    };
    $.fn.rtcl_slider = function () {
      new RtclSlider(this);
      return this;
    };
    $(".rtcl-carousel-slider").each(function () {
      $(this).rtcl_slider();
    });

    // Populate child terms dropdown
    $(".rtcl-terms").on("change", "select", function (e) {
      e.preventDefault();
      var $this = $(this),
        taxonomy = $this.data("taxonomy"),
        parent = $this.data("parent"),
        value = $this.val(),
        slug = $this.find(":selected").attr("data-slug") || "",
        classes = $this.attr("class"),
        termHolder = $this.closest(".rtcl-terms").find("input.rtcl-term-hidden"),
        termValueHolder = $this.closest(".rtcl-terms").find("input.rtcl-term-hidden-value");
      termHolder.val(value).attr("data-slug", slug);
      termValueHolder.val(slug);
      $this.parent().find("div:first").remove();
      if (parent != value) {
        $this.parent().append('<div class="rtcl-spinner"><span class="rtcl-icon-spinner animate-spin"></span></div>');
        var data = {
          action: "rtcl_child_dropdown_terms",
          taxonomy: taxonomy,
          parent: value,
          "class": classes,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
        $.post(rtcl.ajaxurl, data, function (response) {
          $this.parent().find("div:first").remove();
          if (response.success) {
            $this.parent().append(response.data);
          }
        });
      }
    });
    var listObj = {
      active: null,
      target: null,
      loc: {
        items: [],
        selected: null,
        parents: [],
        text: rtcl.location_text
      },
      cat: {
        items: [],
        selected: null,
        parents: [],
        text: rtcl.category_text
      }
    };
    $(".rtcl-widget-search-form .rtcl-search-input-category").on("click", function () {
      listObj.active = "cat";
      listObj.target = $(this);
      var modal = new RtclModal({
        footer: false,
        wrapClass: "no-heading"
      });
      if (!listObj.cat.items.length) {
        $.ajax({
          url: rtcl.ajaxurl,
          type: "POST",
          data: {
            action: "rtcl_get_all_cat_list_for_modal"
          },
          beforeSend: function beforeSend() {
            modal.addModal().addLoading();
          },
          success: function success(response) {
            modal.removeLoading();
            if (response.success) {
              listObj.cat.items = response.categories;
              listObj.cat.selected = null;
              listObj.cat.parent = null;
              modal.content(generate_list());
            }
          },
          error: function error(e) {
            modal.removeLoading();
            modal.content(rtcl_validator.server_error);
          }
        });
      } else {
        modal.addModal();
        modal.content(generate_list());
      }
    });
    $(".rtcl-widget-search-form .rtcl-search-input-location").on("click", function () {
      listObj.active = "loc";
      listObj.target = $(this);
      var modal = new RtclModal({
        footer: false,
        wrapClass: "no-heading"
      });
      if (!listObj.loc.items.length) {
        $.ajax({
          url: rtcl.ajaxurl,
          type: "POST",
          data: {
            action: "rtcl_get_all_location_list_for_modal"
          },
          beforeSend: function beforeSend() {
            modal.addModal().addLoading();
          },
          success: function success(response) {
            modal.removeLoading();
            if (response.success) {
              listObj.loc.items = response.locations;
              listObj.loc.selected = null;
              listObj.loc.parent = null;
              modal.content(generate_list());
            } else {
              modal.content(rtcl_validator.server_error);
            }
          },
          error: function error(e) {
            modal.removeLoading();
            modal.content(rtcl_validator.server_error);
          }
        });
      } else {
        modal.addModal();
        modal.content(generate_list());
      }
    });
    var autocomplete_item = $(".rtcl-widget-search-form .rtcl-autocomplete");
    if ($.fn.autocomplete && autocomplete_item.length) {
      autocomplete_item.autocomplete({
        minChars: 2,
        search: function search(event, ui) {
          if (!$(event.target).parent().find(".rtcl-icon-spinner").length) {
            $("<span class='rtcl-icon-spinner animate-spin'></span>").insertAfter(event.target);
            var aiResult = $(event.target).closest('.rtcl-ai-search-field');
            if (aiResult.length) {
              $("<div class='rtcl-ai-searching-data loading'><h4>" + rtcl.i18n.ai_quick_search_loading + "</h4></div>").insertAfter(event.target);
            }
          }
        },
        response: function response(event, ui) {
          $(event.target).parent().find(".rtcl-icon-spinner").remove();
          $(event.target).parent().find(".rtcl-ai-searching-data").remove();
        },
        source: function source(req, response) {
          req.location_slug = rtcl.rtcl_location || "";
          req.category_slug = rtcl.rtcl_category || "";
          req.type = $(this.element).data("type") || "listing";
          req.action = "rtcl_inline_search_autocomplete";
          $.ajax({
            dataType: "json",
            type: "POST",
            url: rtcl.ajaxurl,
            data: req,
            success: response
          });
        },
        select: function select(event, ui) {
          var _self = $(event.target);
          _self.next("input").val(ui.item.target).change();
        }
      }).data("ui-autocomplete")._renderItem = function (ul, item) {
        return $("<li />").data("item.autocomplete", item).append(item.label).appendTo(ul);
      };
    }
    $(".rtcl-ajax-load").each(function () {
      var _self = $(this),
        settings = _self.data("settings") || {};
      settings.action = "rtcl_ajax_taxonomy_filter_get_sub_level_html";
      settings.__rtcl_wpnonce = rtcl.__rtcl_wpnonce;
      if (_self.hasClass('have-query-var')) {
        settings.query_var_location = _self.attr('data-query-var-location');
        settings.query_var_category = _self.attr('data-query-var-category');
        settings.query_var_tag = _self.attr('data-query-var-tag');
      } else {
        settings.query_var_location = '';
        settings.query_var_category = '';
        settings.query_var_tag = '';
      }
      function setValue(object, path, value, limit) {
        var keys = path.slice(0, limit),
          last = keys.pop();
        keys.reduce(function (o, k) {
          return o[k] = o[k] || {};
        }, object)[last] = value;
        return object;
      }
      var searchParams = new URLSearchParams(decodeURIComponent(window.location.search));
      if (searchParams.size) {
        var filters = {};
        searchParams.forEach(function (value, key) {
          if (value && key.startsWith('filters[')) {
            var _key = key.replace("filters", '');
            _key = _key.replace(/^\[+/, '');
            _key = _key.replace(/]$/, '');
            if (_key.includes("][")) {
              var arrayKeys = _key.split("][");
              filters = setValue(filters, arrayKeys, value);
            } else {
              filters[_key] = value;
            }
          }
        });
        if (Object.keys(filters).length) {
          settings.filters = filters;
        }
      }
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: "json",
        data: settings,
        beforeSend: function beforeSend() {
          _self.rtclBlock();
        },
        success: function success(response) {
          _self.html(response.data).rtclUnblock();
        },
        complete: function complete() {
          _self.rtclUnblock();
        },
        error: function error(request, status, _error) {
          _self.rtclUnblock();
          if (status === 500) {
            console.error("Error while adding comment");
          } else if (status === "timeout") {
            console.error("Error: Server doesn't respond.");
          } else {
            // process WordPress errors
            var wpErrorHtml = request.responseText.split("<p>"),
              wpErrorStr = wpErrorHtml[1].split("</p>");
            console.error(wpErrorStr[0]);
          }
        }
      });
    });
    function findSelectedItemFromListByIds(ids, list) {
      function findSelectedItem(id) {
        if (selectedItem.sub) {
          selectedItem = selectedItem.sub;
        }
        return selectedItem.find(function (item) {
          return id === item.id;
        });
      }
      var selectedItem = list;
      if (ids.length) {
        for (var i = 0; i < ids.length; i++) {
          selectedItem = findSelectedItem(ids[i], selectedItem);
        }
      }
      return selectedItem;
    }
    function generate_list() {
      var type = listObj.active,
        items = listObj[type].items,
        ul = get_list(items);
      var container = $('<div class="rtcl-ui-select-list-wrap"><h4>' + listObj[type].text + '</h4><div class="rtcl-select-action"></div><div class="rtcl-ui-select-list"></div></div>');
      container.find(".rtcl-ui-select-list").append(ul);
      return container;
    }
    function get_list(items) {
      var ul = $("<ul />");
      items.forEach(function (item) {
        var a = $('<a href="javascript:;" />'),
          li = $("<li />");
        if (item.hasOwnProperty("sub")) {
          li.addClass("has-sub");
        }
        if (item.hasOwnProperty("icon")) {
          a.html(item.icon);
        }
        a.append(item.name);
        a.attr("data-item", JSON.stringify(get_safe_term_item(item)));
        li.append(a);
        ul.append(li);
      });
      return ul;
    }
    function get_safe_term_item(item) {
      var safe_item = Object.assign({
        icon: "",
        sub: ""
      }, item);
      delete safe_item["icon"];
      delete safe_item["sub"];
      return safe_item;
    }
    $(document).on("click", ".rtcl-ui-select-list li.has-sub a", function (e) {
      e.preventDefault();
      var type = listObj.active,
        items = listObj[type].items,
        _self = $(this),
        _item = _self.data("item"),
        list = [],
        wrap = _self.parents(".rtcl-ui-select-list-wrap"),
        list_wrap = $(".rtcl-ui-select-list", wrap),
        action = $(".rtcl-select-action", wrap),
        title = $("h4", wrap),
        ul = _self.parents("ul"),
        selectedItemId = parseInt(_item.id, 10),
        selectedItem;
      if (listObj[type].selected) {
        selectedItem = listObj[type].selected.sub.find(function (item) {
          return item.id === selectedItemId;
        });
        listObj[type].parent = listObj[type].selected.id;
      } else {
        selectedItem = items.find(function (item) {
          return item.id === selectedItemId;
        });
      }
      listObj[type].selected = selectedItem;
      if (selectedItem.parent) {
        listObj[type].parents.push(selectedItem.parent);
      }
      if (selectedItem.hasOwnProperty("sub") && selectedItem.sub.length) {
        ul.remove();
        var updatedUl = get_list(selectedItem.sub);
        var allOfText = rtcl.i18n.all_of_.replace('%s', selectedItem.name);
        var allLink = $('<a href="javascript:;" />'),
          allLi = $("<li class='rtcl-ui-sl-all-of' />");
        if (selectedItem.hasOwnProperty("icon")) {
          allLink.html(selectedItem.icon);
        }
        allLink.append(allOfText);
        var newSelectedItem = JSON.parse(JSON.stringify(selectedItem));
        delete newSelectedItem.sub;
        allLink.attr("data-item", JSON.stringify(get_safe_term_item(newSelectedItem)));
        var _allLink = allLink.clone();
        allLi.append(allLink);
        updatedUl.prepend(allLi);
        list_wrap.html(updatedUl);
        if (title.find("span").length) {
          title.find("span").html(_allLink);
        } else {
          var wrapItem = $('<span class="rtcl-icon-angle-right rtcl-selected-term-item" />').append(_allLink);
          title.append(wrapItem);
        }
        action.html("<div class='go-back'>" + rtcl.i18n.go_back + "</div>");
      }
    }).on("click", ".rtcl-select-action .go-back", function (e) {
      e.preventDefault();
      var type = listObj.active,
        _self = $(this),
        wrap = _self.parents(".rtcl-ui-select-list-wrap"),
        list_wrap = $(".rtcl-ui-select-list", wrap),
        title = $("h4", wrap),
        action = $(".rtcl-select-action", wrap),
        list,
        selectedItem,
        level = 0;
      if (listObj[type].parents.length) {
        selectedItem = findSelectedItemFromListByIds(listObj[type].parents, listObj[type].items);
        list = selectedItem.sub;
        listObj[type].parents.pop();
        listObj[type].selected = selectedItem;
        level = 1;
      } else {
        listObj[type].selected = null;
        list = listObj[type].items;
      }
      list_wrap.html("");
      list_wrap.append(get_list(list));
      if (level) {
        var a = $('<a href="javascript:;" />');
        a.append(selectedItem.name);
        a.attr("data-item", JSON.stringify(get_safe_term_item(selectedItem)));
        if (title.find("span").length) {
          title.find("span").html(a);
        } else {
          var wrapItem = $('<span class="rtcl-icon-angle-right rtcl-selected-term-item" />').append(a);
          title.append(wrapItem);
        }
      } else {
        title.find("span").remove();
        action.find(".go-back").remove();
      }
    }).on("click", ".rtcl-ui-select-list li:not(.has-sub) a, .rtcl-selected-term-item a", function (e) {
      e.preventDefault();
      var _self = $(this),
        _item = _self.data("item") || null;
      if (_item && listObj.target.length) {
        listObj.target.find(".search-input-label").text(_item.name);
        listObj.target.find("input.rtcl-term-field").val(_item.slug).change();
        $("body > .rtcl-ui-modal").remove(); // TODO need to make this dynamic
        $("body").removeClass("rtcl-modal-open");
        if (rtcl.popup_search_widget_auto_form_submission) {
          listObj.target.closest("form").submit();
        }
      }
      return false;
    }).on("click", ".ul-list-group.is-parent > ul > li > a", function (e) {
      e.preventDefault();
      var self = $(this),
        li = self.parent("li"),
        parent = li.parent("ul"),
        target = $(".col-md-6.sub-wrapper"),
        wrap = $("<li />"),
        list = li.find(".ul-list-group.is-sub").clone() || "",
        a_clone = self.clone(),
        a = wrap.append(a_clone);
      list.find("ul").prepend(a);
      target.addClass("is-active");
      target.html(list);
      parent.find("> li").removeClass("is-active");
      li.addClass("is-active");
      return false;
    }).on("click", ".rtcl-filter-form .filter-list .is-parent.has-sub .arrow", function (e) {
      e.preventDefault();
      var self = $(this),
        li = self.closest("li"),
        parent = self.closest(".ui-accordion-content"),
        is_ajax_load = parent.hasClass("rtcl-ajax-load"),
        settings = parent.data("settings") || {},
        target = li.find("> ul.sub-list");
      if (li.hasClass("is-open")) {
        target.slideUp(function () {
          li.removeClass("is-open");
        });
      } else {
        if (is_ajax_load && settings.taxonomy && li.hasClass("has-sub") && !li.hasClass("is-loaded")) {
          if (!parent.hasClass("rtcl-loading")) {
            settings.parent = li.data("id") || -1;
            settings.action = "rtcl_ajax_taxonomy_filter_get_sub_level_html";
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: "json",
              data: settings,
              beforeSend: function beforeSend() {
                parent.rtclBlock();
              },
              success: function success(response) {
                li.append(response.data);
                parent.rtclUnblock();
                target.slideDown();
                li.addClass("is-open is-loaded");
              },
              complete: function complete() {
                parent.rtclUnblock();
              },
              error: function error(request, status, _error2) {
                parent.rtclUnblock();
                if (status === 500) {
                  console.error("Error while adding comment");
                } else if (status === "timeout") {
                  console.error("Error: Server doesn't respond.");
                } else {
                  // process WordPress errors
                  var wpErrorHtml = request.responseText.split("<p>"),
                    wpErrorStr = wpErrorHtml[1].split("</p>");
                  console.error(wpErrorStr[0]);
                }
              }
            });
          }
        } else {
          target.slideDown();
          li.addClass("is-open");
        }
      }
    }).on("click", "ul.filter-list.is-collapsed li.is-opener, ul.sub-list.is-collapsed li.is-opener, ul.ui-link-tree.is-collapsed li.is-opener", function () {
      $(this).parent("ul").removeClass("is-collapsed").addClass("is-open");
    }).on('change', '.rtcl-widget-search-form', function () {
      var $form = $(this),
        location = $form.find("[name='rtcl_location']").val(),
        category = $form.find("[name='rtcl_category']").val(),
        actionLink = rtcl.rtcl_listing_base;
      if (location && category) {
        actionLink = actionLink + rtcl.rtcl_category_base + '/' + category + '/' + rtcl.rtcl_location_base + '/' + location;
        $form.attr('action', actionLink);
      } else if (location) {
        actionLink = actionLink + rtcl.rtcl_location_base + '/' + location;
        $form.attr('action', actionLink);
      } else if (category) {
        actionLink = actionLink + rtcl.rtcl_category_base + '/' + category;
        $form.attr('action', actionLink);
      } else {
        $form.attr('action', actionLink);
      }
    });
    $("#rtcl-checkout-form").on("change", "#billing_country, #billing_state, input[name='pricing_id']", function () {
      var $this = $(this),
        $form = $this.closest("#rtcl-checkout-form"),
        country = $form.find("#billing_country").val(),
        state = $form.find("#billing_state").val(),
        postcode = $form.find("#billing_postcode").val(),
        city = $form.find("#billing_city").val();
      if (rtcl.is_enable_tax) {
        checkout_tax_pricing(country, state, postcode, city);
      }
    });

    // Custom event for addons (e.g. coupon) to trigger tax recalculation
    $(document).on("rtcl_recalculate_tax", function () {
      if (rtcl.is_enable_tax) {
        var $form = $("#rtcl-checkout-form"),
          country = $form.find("#billing_country").val(),
          state = $form.find("#billing_state").val(),
          postcode = $form.find("#billing_postcode").val(),
          city = $form.find("#billing_city").val();
        checkout_tax_pricing(country, state, postcode, city);
      }
    });
    $(".rtcl-filter-form .ui-accordion-item, .rtcl-ajax-filter-form .ui-accordion-item").on("click", ".ui-accordion-title", function () {
      var self = $(this),
        holder = self.parents(".ui-accordion-item"),
        target = $(".ui-accordion-content", holder);
      if (holder.hasClass("is-open")) {
        target.slideUp(function () {
          holder.removeClass("is-open");
        });
      } else {
        target.slideDown();
        holder.addClass("is-open");
      }
    });
    $(".rtcl-filter-form").on("click", ".filter-submit-trigger", function (e) {
      var r,
        i,
        self = $(this);
      if (!self.is(":checkbox")) {
        e.preventDefault();
        r = self.siblings("input");
        i = r.prop("checked");
        r.prop("checked", !i);
      }
      if (self.is(":radio") || !self.is(":radio") && self.siblings("input").is(":radio")) {
        self.closest("form").submit();
      }
    });

    // Reveal phone
    $(document).on("click keydown", ".reveal-phone", function (e) {
      if (e.type === "keydown" && e.keyCode !== 13) {
        return;
      }
      var $this = $(this),
        isMobile = $this.hasClass("rtcl-mobile");
      if (!$this.hasClass("revealed")) {
        e.preventDefault();
        var options = $this.data("options") || {};
        var $numbers = $this.find(".numbers");
        var aPhone = "";
        var wPhone = "";
        if (options.safe_phone && options.phone_hidden) {
          var purePhone = options.safe_phone.replace(rtcl.phone_number_placeholder, options.phone_hidden);
          aPhone = $('<a class="revealed-phone-number" href="#" />').attr("href", "tel:" + purePhone).html('<i class="rtcl-icon rtcl-icon-phone"></i>').append(purePhone);
          $this.attr("data-tel", "tel:" + purePhone);
        }
        if (options.safe_whatsapp_number && options.whatsapp_hidden) {
          var pureWPhone = options.safe_whatsapp_number.replace(rtcl.phone_number_placeholder, options.whatsapp_hidden);
          wPhone = $('<a class="revealed-whatsapp-number" href="#" />').attr("href", "https://wa.me/" + pureWPhone.replace(/\D/g, "").replace(/^0+/, "") + "/?text=" + rtcl.wa_message).html('<i class="rtcl-icon rtcl-icon-whatsapp"></i>').append(pureWPhone);
        }
        $numbers.html(aPhone).append(wPhone);
        $this.addClass("revealed");
        $.ajax({
          url: rtcl.ajaxurl,
          type: "POST",
          dataType: "json",
          data: {
            listing_id: $this.attr('data-id'),
            action: 'rtcl_phone_whatsapp_revealed',
            __rtcl_wpnonce: rtcl.__rtcl_wpnonce
          },
          success: function success(res) {
            console.log(res);
          },
          error: function error(e) {
            console.log(e);
          }
        });
      } else {
        if (isMobile) {
          var tel = $this.attr("data-tel");
          if (tel) {
            window.location = tel;
          }
        }
      }
    });
    // Phone click count
    $(document).on("click", ".reveal-phone.revealed a.revealed-phone-number", function (e) {
      e.preventDefault();
      var $this = $(this),
        $wrapper = $this.closest('.reveal-phone.revealed');
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          listing_id: $wrapper.attr('data-id'),
          action: 'rtcl_phone_click',
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        },
        success: function success(res) {
          if (res.success) {
            window.location = $this.attr('href');
          }
        },
        error: function error(e) {
          console.log(e);
        }
      });
    });
    // WhatsApp click count
    $(document).on("click", ".reveal-phone.revealed a.revealed-whatsapp-number", function (e) {
      e.preventDefault();
      var $this = $(this),
        $wrapper = $this.closest('.reveal-phone.revealed');
      $.ajax({
        url: rtcl.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          listing_id: $wrapper.attr('data-id'),
          action: 'rtcl_whatsapp_click',
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        },
        success: function success(res) {
          if (res.success) {
            window.location = $this.attr('href');
          }
        },
        error: function error(e) {
          console.log(e);
        }
      });
    });
    var option = getUrlParameter("option") || "",
      gateway = getUrlParameter("gateway") || "";
    if (option) {
      $("input[name='pricing_id'][value='" + option + "']").prop("checked", true);
    } else {
      $("input[name='pricing_id'][value='0']").prop("checked", true);
    }
    if (gateway) {
      $("label[for='gateway-" + gateway + "']").trigger("click");
    }
    rtclInitDateField();
  });
  if ($.fn.validate) {
    $("#rtcl-lost-password-form, #rtcl-password-reset-form").each(function () {
      $(this).validate();
    });

    // Check out validation
    $("#rtcl-checkout-form").validate({
      submitHandler: function submitHandler(form) {
        $(document.body).trigger("rtcl_before_checkout_request", [form]);
        rtcl_make_checkout_request(form);
        return false;
      }
    });

    //Login form
    $("form#rtcl-login-form, form.rtcl-login-form").each(function () {
      $(this).validate({
        submitHandler: function submitHandler(form) {
          var $form = $(form);
          console.log($form.data("reCaptchaId"));
          // recaptcha v2
          if (rtcl.recaptcha && typeof grecaptcha !== "undefined" && rtcl.recaptcha.on && $.inArray("login", rtcl.recaptcha.on) !== -1) {
            if (rtcl.recaptcha.v === 2 && $form.data("reCaptchaId") !== undefined) {
              var response = grecaptcha.getResponse($form.data("reCaptchaId"));
              console.log(response);
              var $captcha_msg = $form.find("#rtcl-login-g-recaptcha-message");
              $captcha_msg.html("");
              if (0 === response.length) {
                $captcha_msg.addClass("text-danger").html(rtcl.recaptcha.msg.invalid);
                grecaptcha.reset($form.data("reCaptchaId"));
                return false;
              }
              if ($form.hasClass("rtcl-ajax-login")) {
                submit_form_data_ajax();
                return false;
              }
              return true;
            } else if (rtcl.recaptcha.v === 3) {
              grecaptcha.ready(function () {
                $form.rtclBlock();
                grecaptcha.execute(rtcl.recaptcha.site_key, {
                  action: "login"
                }).then(function (token) {
                  if ($form.hasClass("rtcl-ajax-login")) {
                    submit_form_data_ajax(token);
                    return false;
                  } else {
                    $form.append('<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="' + token + '" />');
                    $form.append('<input type="hidden" name="rtcl-login" value="login" />');
                    $form.off("submit").trigger("submit");
                    return true;
                  }
                });
              });
              return false;
            }
          }
          if ($form.hasClass("rtcl-ajax-login")) {
            submit_form_data_ajax();
            return false;
          } else {
            return true;
          }
          function submit_form_data_ajax(token) {
            var fromData = new FormData(form);
            var temp_user = fromData.get("username").trim();
            var temp_pass = fromData.get("password");
            fromData["delete"]("username");
            fromData["delete"]("password");
            fromData.set("username", rtclCipher(rtcl.__rtcl_wpnonce)(temp_user));
            fromData.set("password", rtclCipher(rtcl.__rtcl_wpnonce)(temp_pass));
            if (token) {
              fromData.set("g-recaptcha-response", token);
            }
            fromData.append("action", "rtcl_login_request");
            fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: "json",
              cache: false,
              processData: false,
              contentType: false,
              data: fromData,
              beforeSend: function beforeSend() {
                $form.find(".rtcl-error").remove();
                $form.rtclBlock();
              },
              success: function success(res) {
                if (res.success) {
                  toastr.success(res.data.message);
                  $form.append('<div class="rtcl-error alert alert-success" role="alert"><p>' + res.data.message + "</p></div>");
                  $form[0].reset();
                  window.location.reload(true);
                } else {
                  $form.rtclUnblock();
                  toastr.error(res.data);
                  $form.append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + res.data + "</p></div>");
                }
              },
              error: function error() {
                $form.rtclUnblock().append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + rtcl_validator.messages.server_error + "</p></div>");
                toastr.error(rtcl_validator.messages.server_error);
              }
            });
          }
        }
      });
    });

    // Validate registration form
    $("form#rtcl-register-form, form.rtcl-register-form").each(function () {
      $(this).validate({
        submitHandler: function submitHandler(form) {
          var $form = $(form);
          if (rtcl.recaptcha && typeof grecaptcha !== "undefined" && rtcl.recaptcha.on && $.inArray("registration", rtcl.recaptcha.on) !== -1) {
            if (rtcl.recaptcha.v === 2 && $form.data("reCaptchaId") !== undefined) {
              var response = grecaptcha.getResponse($form.data("reCaptchaId"));
              var $captcha_msg = $("#rtcl-registration-g-recaptcha-message");
              $captcha_msg.html("");
              if (0 === response.length) {
                $captcha_msg.addClass("text-danger").html(rtcl.recaptcha.msg.invalid);
                grecaptcha.reset($form.data("reCaptchaId"));
                return false;
              }
              if ($form.hasClass("rtcl-ajax-registration")) {
                submit_form_data_ajax();
                return false;
              }
              return true;
            } else if (rtcl.recaptcha.v === 3) {
              grecaptcha.ready(function () {
                $form.rtclBlock();
                grecaptcha.execute(rtcl.recaptcha.site_key, {
                  action: "registration"
                }).then(function (token) {
                  if ($form.hasClass("rtcl-ajax-registration")) {
                    submit_form_data_ajax(token);
                    return false;
                  } else {
                    $form.append('<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="' + token + '" />');
                    $form.append('<input type="hidden" name="rtcl-register" value="register" />');
                    $form.off("submit").trigger("submit");
                    return true;
                  }
                });
              });
              return false;
            }
          }
          if ($form.hasClass("rtcl-ajax-registration")) {
            submit_form_data_ajax();
            return false;
          } else {
            return true;
          }
          function submit_form_data_ajax(recaptcha_token) {
            var fromData = new FormData(form);
            if (recaptcha_token) {
              fromData.append("g-recaptcha-response", recaptcha_token);
            }
            fromData.append("action", "rtcl_registration_request");
            fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: "json",
              cache: false,
              processData: false,
              contentType: false,
              data: fromData,
              beforeSend: function beforeSend() {
                $form.find(".rtcl-error").remove();
                $form.rtclBlock();
              },
              success: function success(res) {
                $form.rtclUnblock();
                if (res.success) {
                  $form.append('<div class="rtcl-error alert alert-success" role="alert"><p>' + res.data.message + "</p></div>");
                  $form[0].reset();
                  if (res.data.redirect_url && res.data.redirect_utl !== window.location.href) {
                    window.location = res.data.redirect_url + "?t=" + new Date().getTime();
                  }
                } else {
                  $form.append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + res.data + "</p></div>");
                }
              },
              error: function error() {
                $form.rtclUnblock().append('<div class="rtcl-error alert alert-danger" role="alert"><p>' + rtcl_validator.messages.server_error + "</p></div>");
              }
            });
          }
        }
        /*messages: {
        	pass2: {
        		equalTo: 'ggjggjj'
        	}
        }*/
      });
    });

    // Validate report abuse form
    $("form.rtcl-report-abuse-form, form#rtcl-report-abuse-form").each(function () {
      $(this).validate({
        submitHandler: function submitHandler(form) {
          var $form = $(form);
          if (rtcl.recaptcha && typeof grecaptcha !== "undefined" && rtcl.recaptcha.on && $.inArray("report_abuse", rtcl.recaptcha.on) !== -1) {
            if (rtcl.recaptcha.v === 2 && $form.data("reCaptchaId") !== undefined) {
              var response = grecaptcha.getResponse($form.data("reCaptchaId"));
              var $captcha_msg = $form.find("#rtcl-report-abuse-message-display");
              $captcha_msg.html("");
              if (0 === response.length) {
                $captcha_msg.removeClass("text-success").addClass("text-danger").html(rtcl.recaptcha.msg.invalid);
                grecaptcha.reset(rtcl.recaptcha.response["report_abuse"]);
                return false;
              }
              submit_form_data_ajax(response);
              return false;
            } else if (rtcl.recaptcha.v === 3) {
              grecaptcha.ready(function () {
                grecaptcha.execute(rtcl.recaptcha.site_key, {
                  action: "reportAbuse"
                }).then(function (token) {
                  submit_form_data_ajax(token);
                });
              });
              return false;
            }
          }
          submit_form_data_ajax();
          return false;
          function submit_form_data_ajax(reCaptchaToken) {
            //Post via AJAX
            var fromData = new FormData(form);
            fromData.append("action", "rtcl_public_report_abuse");
            fromData.append("post_id", rtcl.post_id || 0);
            fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
            if (reCaptchaToken) {
              fromData.append("g-recaptcha-response", reCaptchaToken);
            }
            var targetBtn = $form.find(".rtcl-btn.rtcl-btn-primary");
            $.ajax({
              url: rtcl.ajaxurl,
              data: fromData,
              dataType: "json",
              cache: false,
              processData: false,
              contentType: false,
              type: "POST",
              beforeSend: function beforeSend() {
                $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
              },
              success: function success(response) {
                targetBtn.next(".rtcl-icon-spinner").remove();
                if (response.success) {
                  form.reset();
                  $form.find("#rtcl-report-abuse-message-display").removeClass("text-danger").addClass("text-success").html(response.data.message);
                  setTimeout(function () {
                    $form.parents("#rtcl-report-abuse-modal").removeClass('show');
                  }, 1500);
                } else {
                  $form.find("#rtcl-report-abuse-message-display").removeClass("text-success").addClass("text-danger").html(response.data.error);
                }
                if (rtcl.recaptcha && rtcl.recaptcha.v === 2 && $form.data("reCaptchaId") !== undefined) {
                  grecaptcha.reset($form.data("reCaptchaId"));
                }
              },
              error: function error(e) {
                $("#rtcl-report-abuse-message-display").removeClass("text-success").addClass("text-danger").html(e);
                targetBtn.next(".rtcl-icon-spinner").remove();
              }
            });
          }
        }
      });
    });

    // Validate Listing Contact form
    $("form.rtcl-contact-form, form#rtcl-contact-form").each(function () {
      $(this).validate({
        submitHandler: function submitHandler(form) {
          var $form = $(form);
          var $captcha_msg = $form.find("#rtcl-contact-message-display");
          var recaptchaId = $form.data("reCaptchaId");
          if (rtcl.recaptcha && typeof grecaptcha !== "undefined" && rtcl.recaptcha.on && $.inArray("contact", rtcl.recaptcha.on) !== -1) {
            if (rtcl.recaptcha.v === 2 && recaptchaId !== undefined) {
              var response = grecaptcha.getResponse(recaptchaId);
              $captcha_msg.html("");
              if (0 === response.length) {
                $captcha_msg.removeClass("text-success").addClass("text-danger").html(rtcl.recaptcha.msg.invalid);
                grecaptcha.reset(recaptchaId);
                return false;
              }
              submit_form_data_ajax(response);
              return false;
            } else if (rtcl.recaptcha.v === 3) {
              grecaptcha.ready(function () {
                $form.rtclBlock();
                grecaptcha.execute(rtcl.recaptcha.site_key, {
                  action: "contact"
                }).then(function (token) {
                  $form.rtclUnblock();
                  submit_form_data_ajax(token);
                });
              });
              return false;
            }
          }
          submit_form_data_ajax();
          return false;
          function submit_form_data_ajax(reCaptchaToken) {
            // Post via AJAX
            var fromData = new FormData(form);
            if (reCaptchaToken) {
              fromData.append("g-recaptcha-response", reCaptchaToken);
            }
            fromData.append("action", "rtcl_public_send_contact_email");
            fromData.append("post_id", rtcl.post_id || 0);
            fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: "json",
              cache: false,
              processData: false,
              contentType: false,
              data: fromData,
              beforeSend: function beforeSend() {
                $form.rtclBlock();
                $captcha_msg.removeClass("d-block").html("");
                $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter($form.find(".btn"));
              },
              success: function success(response) {
                $form.rtclUnblock();
                $form.find(".btn").next(".rtcl-icon-spinner").remove();
                $captcha_msg.addClass("d-block");
                if (response.success) {
                  form.reset();
                  $captcha_msg.removeClass("text-danger").addClass("d-block text-success").html(response.data.message);
                  if ($form.parent().data("hide") !== 0) {
                    setTimeout(function () {
                      $form.slideUp();
                    }, 800);
                  }
                } else {
                  $captcha_msg.removeClass("text-success").addClass("d-block text-danger").html(response.data.error);
                }
                if (rtcl.recaptcha && rtcl.recaptcha.v === 2 && recaptchaId !== undefined) {
                  grecaptcha.reset(recaptchaId);
                }
              },
              error: function error(e) {
                $form.rtclUnblock();
                $captcha_msg.removeClass("text-success").addClass("d-block text-danger").html(e);
                $form.find(".btn").next(".rtcl-icon-spinner").remove();
              }
            });
          }
        }
      });
    });

    // User account form
    $("#rtcl-user-account").validate({
      submitHandler: function submitHandler(form) {
        var $form = $(form),
          targetBtn = $form.find("input[type=submit]"),
          responseHolder = $form.find(".rtcl-response"),
          msgHolder = $("<div class='alert'></div>"),
          fromData = new FormData(form);
        fromData.append("action", "rtcl_update_user_account");
        fromData.append("__rtcl_wpnonce", rtcl.__rtcl_wpnonce);
        $.ajax({
          url: rtcl.ajaxurl,
          data: fromData,
          dataType: "json",
          cache: false,
          processData: false,
          contentType: false,
          type: "POST",
          beforeSend: function beforeSend() {
            $form.addClass("rtcl-loading");
            targetBtn.prop("disabled", true);
            responseHolder.html("");
            $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(targetBtn);
          },
          success: function success(response) {
            targetBtn.prop("disabled", false).next(".rtcl-icon-spinner").remove();
            $form.removeClass("rtcl-loading");
            if (response.success) {
              $form.find("input[name=pass1]").val("");
              $form.find("input[name=pass2]").val("");
              msgHolder.removeClass("alert-danger").addClass("alert-success").html(response.data.message).appendTo(responseHolder);
              setTimeout(function () {
                responseHolder.html("");
              }, 1000);
            } else {
              msgHolder.removeClass("alert-success").addClass("alert-danger").html(response.data.error).appendTo(responseHolder);
            }
          },
          error: function error(e) {
            msgHolder.removeClass("alert-success").addClass("alert-danger").html(e.responseText).appendTo(responseHolder);
            targetBtn.prop("disabled", false).next(".rtcl-icon-spinner").remove();
            $form.removeClass("rtcl-loading");
          }
        });
      }
    });
  }
  window.rtclInitDateField = function () {
    if ($.fn.daterangepicker) {
      $(".rtcl-date").each(function () {
        var input = $(this);
        var options = input.data("options") || {};
        options = rtclFilter.apply('dateRangePickerOptions', options);
        if (Array.isArray(options.invalidDateList) && options.invalidDateList.length) {
          var formattedDates = options.invalidDateList.map(function (dateStr) {
            return moment(dateStr).format(options.locale.format);
          });
          options.isInvalidDate = function (param) {
            return formattedDates.includes(param.format(options.locale.format));
          };
        }
        $(this).daterangepicker(options);
        if (options.autoUpdateInput === false) {
          input.on("apply.daterangepicker", function (ev, picker) {
            if (picker.singleDatePicker) {
              $(this).val(picker.startDate.format(picker.locale.format));
            } else {
              $(this).val(picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format));
            }
          });
          input.on("cancel.daterangepicker", function (ev, picker) {
            $(this).val("");
          });
        }
      });
    }
  };

  /* Listing - Reveal Phone */
  // On load function
  $(function () {
    $(".rtcl-phone-reveal").on("click", function () {
      if ($(this).hasClass("revealed")) {
        var $link;
        $link = $(this).attr('href');
        if ($link) {
          window.location.href = $link;
        }
      }
      if ($(this).hasClass("not-revealed")) {
        $(this).removeClass("not-revealed").addClass("revealed");
        var phone = $(this).data("phone");
        $(this).find("span").text(phone);
      }
      return false;
    });

    // User page ad listing infinity scroll
    var user_ads_wrapper = $(".rtcl-user-ad-listing-wrapper"),
      pagination;
    if (user_ads_wrapper.length) {
      var wrapper = $(".rtcl-listing-wrapper", user_ads_wrapper);
      pagination = wrapper.data("pagination") || {};
      pagination.disable = false;
      pagination.loading = false;
      $(window).on("scroll load", function () {
        infinite_scroll(wrapper);
      });
    }
    function infinite_scroll(wrapper) {
      var ajaxVisible = user_ads_wrapper.offset().top + user_ads_wrapper.outerHeight(true),
        ajaxScrollTop = $(window).scrollTop() + $(window).height();
      if (ajaxVisible <= ajaxScrollTop && ajaxVisible + $(window).height() > ajaxScrollTop) {
        if (pagination.max_num_pages > pagination.current_page && !pagination.loading && !pagination.disable) {
          var data = {
            action: "rtcl_user_ad_load_more",
            current_page: pagination.current_page,
            max_num_pages: pagination.max_num_pages,
            found_posts: pagination.found_posts,
            posts_per_page: pagination.posts_per_page,
            user_id: rtcl.user_id
          };
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            beforeSend: function beforeSend() {
              pagination.loading = true;
              $('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(wrapper);
            },
            success: function success(response) {
              wrapper.next(".rtcl-icon-spinner").remove();
              pagination.loading = false;
              pagination.current_page = response.current_page;
              if (pagination.max_num_pages === response.current_page) {
                pagination.disable = true;
              }
              if (response.complete && response.html) {
                wrapper.append(response.html);
              }
            },
            error: function error(e) {
              pagination.loading = false;
              wrapper.next(".rtcl-icon-spinner").remove();
            }
          });
        }
      }
    }
  });
  var rtclAjaxFilter = new _classes_RtclAjaxFilter__WEBPACK_IMPORTED_MODULE_0__["default"]();
  $(document).ready(function () {
    rtclAjaxFilter.init();
  });

  // Window load and resize function
  $(window).on("resize load", equalHeight).on("load", function () {
    $(".rtcl-range-slider-input").on("input", function () {
      var field_wrap = $(this).parent();
      field_wrap.find("span.rtcl-range-value").text(this.value);
    });
  });

  //Favourite Icon Update
  //=========================
  $(document).on("rtcl.favorite", function (e, data) {
    var $favCount = $(".rt-el-header-favourite-count").first();
    var $favCountAll = $(".rt-el-header-favourite-count");
    var favCountVal = parseInt($favCount.text(), 10);
    favCountVal = isNaN(favCountVal) ? 0 : favCountVal;
    if ("added" === data.action) {
      favCountVal++;
      $favCountAll.text(favCountVal);
    } else if ("removed" === data.action) {
      favCountVal--;
      $favCountAll.text(favCountVal);
    }
  });
  //End Favourite Icon Update
  //Compare icon update
  //====================
  $(document).on("rtcl.compare.added", function (e, data) {
    $(".rtcl-el-compare-count").text(data.current_listings);
  });
  $(document).on("rtcl.compare.removed", function (e, data) {
    $(".rtcl-el-compare-count").text(data.current_listings);
  });
  $(document).on("click", ".rtcl-compare-btn-clear", function () {
    $(".rtcl-el-compare-count").text("0");
  });

  // Builder Content visible. Elementor Builder Jumping issue fixed
  $(window).on('load', function () {
    $('.builder-content').removeClass('content-invisible');
  });
  function checkout_tax_pricing(country, state, postcode, city) {
    var $wrapper = $(".rtcl-checkout-content"),
      $form = $wrapper.find("#rtcl-checkout-form"),
      $overview = $form.find('#rtcl-payment-overview'),
      type = $form.find("input[name='type']").val(),
      pricing_id = $form.find("input[name='pricing_id']:checked").val(),
      $content = '';
    $.ajax({
      type: "POST",
      url: rtcl.ajaxurl,
      data: {
        action: 'rtcl_calculate_checkout_tax',
        country_code: country,
        state_code: state,
        postcode: postcode,
        city: city,
        type: type,
        pricing_id: pricing_id,
        __rtcl_wpnonce: rtcl.__rtcl_wpnonce
      },
      beforeSend: function beforeSend() {
        $wrapper.rtclBlock();
      },
      success: function success(response) {
        $wrapper.rtclUnblock();
        if (!response.error) {
          var taxData = response.hasOwnProperty('available_tax') ? response.available_tax : [];
          $overview.find(".cart-subtotal .checkout-price").text(response.pricing_price);
          $overview.find(".order-total .checkout-price").text(response.total_amount);
          if (Array.isArray(taxData)) {
            $overview.find('tr.tax-rate td').html('');
            $.each(taxData, function (index, singleTax) {
              $content += '<span class="price-amount">';
              $content += '<span class="checkout-price-currency-symbol">' + rtcl.payment_currency_symbol + '</span>';
              $content += '<span class="checkout-price"> ' + singleTax.amount + '</span>';
              $content += '<span class="checkout-tax-label">(' + singleTax.label + ')</span>';
              $content += '</span>';
              if (!response.enable_multiple_tax) {
                return false;
              }
            });
            $overview.find('tr.tax-rate td').append($content);
          }
        }
      },
      error: function error(jqXHR, exception) {
        $wrapper.rtclUnblock();
      }
    });
  }
  jQuery(document).ready(function ($) {
    var $repeater = $('.rtcl-is-collapsable');
    if (!$repeater.length) return;
    $repeater.each(function () {
      $(this).find('.rtcl-cfp-repeater-item').each(function (index) {
        console.log($(this));
        var $item = $(this);
        var $fields = $item.find('> .rtcl-cfp-repeater-field'); // direct children only
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

  //End Compare icon update
})(jQuery);
})();

/******/ })()
;