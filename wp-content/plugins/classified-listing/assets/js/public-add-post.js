(function ($) {
  'use restrict';

  $.fn.getType = function () {
    return this[0].tagName === "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase();
  };
  var spinner = '<div class="rtcl-spinner block"><span class="rtcl-icon-spinner animate-spin"></span></div>';
  $(document.body).on('rtcl_add_error_tip', function (e, element, error_type) {
    var offset = element.position();
    if (element.parent().find('.rtcl_error_tip').length === 0) {
      element.after('<div class="rtcl_error_tip ' + error_type + '">' + rtcl[error_type] + '</div>');
      element.parent().find('.rtcl_error_tip').css('left', offset.left + element.width() - element.width() / 2 - $('.rtcl_error_tip').width() / 2).css('top', offset.top + element.height()).fadeIn('100');
    }
  }).on('rtcl_remove_error_tip', function (e, element, error_type) {
    element.parent().find('.rtcl_error_tip.' + error_type).fadeOut('100', function () {
      $(this).remove();
    });
  }).on('click', function () {
    $('.rtcl_error_tip').fadeOut('100', function () {
      $(this).remove();
    });
  }).on('blur', '#rtcl-price[type=text], input.rtcl-price', function () {
    $('.rtcl_error_tip').fadeOut('100', function () {
      $(this).remove();
    });
  }).on('keyup', '#rtcl-price[type=text], input.rtcl-price', function () {
    var regex = new RegExp('[^\-0-9\%\\' + rtcl.decimal_point + ']+', 'gi'),
      error = 'i18n_mon_decimal_error';
    var value = $(this).val();
    var newvalue = value.replace(regex, '');
    if (value !== newvalue) {
      $(document.body).triggerHandler('rtcl_add_error_tip', [$(this), error]);
    } else {
      $(document.body).triggerHandler('rtcl_remove_error_tip', [$(this), error]);
    }
  }).on('change', '#rtcl-price[type=text], input.rtcl-price', function () {
    var regex = new RegExp('[^\-0-9\%\\' + rtcl.decimal_point + ']+', 'gi'),
      value = $(this).val(),
      newvalue = value.replace(regex, '');
    if (value !== newvalue) {
      $(this).val(newvalue);
    }
  }).on('rtcl_price_type_changed', function (e, element) {
    if (element.value === "on_call" || element.value === "free" || element.value === "no_price") {
      $('#rtcl-price').attr("required", "false").val('');
    } else {
      $('#rtcl-price').attr("required", "true");
    }
    $("#rtcl-price-items").removeClass(function (index, className) {
      return (className.match(/(^|\s)rtcl-price-type\S+/g) || []).join(' ');
    }).addClass('rtcl-price-type-' + element.value);
  }).on('rtcl_listing_pricing_type_changed', function (e, element) {
    $("#rtcl-pricing-items").removeClass().addClass('rtcl-pricing-' + element.value);
  }).on('change', '#rtcl-price-type', function () {
    $(document.body).trigger('rtcl_price_type_changed', [this]);
  }).on('change', 'input[name=_rtcl_listing_pricing]', function () {
    $(document.body).trigger('rtcl_listing_pricing_type_changed', [this]);
  }).on('keyup', '#new-tag-rtcl_tag', function (e) {
    var $this = $(this),
      $wrapper = $this.closest('.rtcl-tags-input-wrap'),
      searchKey = $this.val();
    var data = {
      action: 'rtcl_tag_search',
      __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
      q: searchKey,
      number: 20
    };
    switch (e.keyCode) {
      case 188:
        var word = searchKey.trim().replace(',', '');
        rtcl_tag_add_word(word);
        return;
      case 40:
        if ($wrapper.find("ul li[active]").length === 0) {
          $wrapper.find('ul li').first().attr('active', '1').css('background-color', '#f6f6f6');
        } else {
          $wrapper.find('ul li[active]').removeAttr('active').css('background-color', '#ffffff').next().attr('active', '1').css('background-color', '#f6f6f6');
        }
        return;
      case 38:
        if ($wrapper.find("ul li[active]").length === 0) {
          $wrapper.find('ul li').last().attr('active', '1').css('background-color', '#f6f6f6');
        } else {
          $wrapper.find('ul li[active]').removeAttr('active').css('background-color', '#ffffff').prev().attr('active', '1').css('background-color', '#f6f6f6');
        }
        return;
      case 13:
        var $activeList = $wrapper.find("ul li[active]");
        if ($activeList.length) {
          var _word = $activeList.text();
          rtcl_tag_add_word(_word);
          $activeList.removeAttr('active').css('background-color', '#ffffff');
        }
        return;
    }
    if (searchKey.length > 1) {
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: "GET",
        dataType: 'json',
        beforeSend: function beforeSend() {
          $wrapper.find('ul').remove();
        },
        success: function success(response) {
          $wrapper.append(response.list);
        },
        error: function error(e) {
          $wrapper.find('ul').remove();
          console.log(e.responseText);
        }
      });
    } else {
      $wrapper.find('ul').remove();
    }
  }).on('click', '.rtcl-tags-input-wrap ul li', function () {
    rtcl_tag_add_word($(this).text());
  }).on('click', '.rtcl-tags-input div span.remove', function () {
    var $this = $(this),
      $wrapper = $this.closest('div'),
      termText = $wrapper.find('.rtcl-tag-term').text();
    $this.closest('div').remove();
    rtcl_tag_remove_word(termText);
  });
  function rtcl_tag_remove_word(word) {
    var $wrapper = $('.rtcl-tags-input-wrap'),
      $input = $wrapper.find('#rtcl_listing_tag'),
      currentValues = $input.val();
    currentValues = currentValues ? currentValues.split(',').filter(function (_item) {
      return _item.trim() !== word;
    }) : [];
    $input.val(currentValues.join(', '));
  }
  function rtcl_tag_add_word(word) {
    var $wrapper = $('.rtcl-tags-input-wrap'),
      $input = $wrapper.find('#rtcl_listing_tag'),
      oldValues = $input.val();
    oldValues = oldValues ? oldValues.split(',').map(function (_item) {
      return _item.trim();
    }) : [];
    oldValues.push(word);
    $input.val(oldValues.join(', '));
    var $content = $('<div><span class="rtcl-tag-term">' + word + '</span><span class="remove">x</span></div>');
    if ($wrapper.find('.rtcl-tags-input').find('div').length) {
      $content.insertAfter($wrapper.find('.rtcl-tags-input').find('div').last());
    } else {
      $wrapper.find('.rtcl-tags-input').prepend($content);
    }
    $wrapper.find('ul').remove();
    $wrapper.find('#new-tag-rtcl_tag').val('').focus();
  }
  if ($.fn.validate) {
    var submitForm = $("form#rtcl-post-form");
    if ($('.rtcl-tags-input-wrap').length) {
      submitForm.on('keypress', function (e) {
        if ('TEXTAREA' !== e.target.nodeName && e.keyCode === 13) {
          e.preventDefault();
        }
      });
    }
    if (submitForm.length) {
      submitForm.validate({
        rules: {
          _rtcl_max_price: {
            greaterThan: '#rtcl-price'
          }
        },
        messages: {
          _rtcl_max_price: {
            greaterThan: rtcl_validator.messages.maxPrice
          }
        },
        submitHandler: function submitHandler(form) {
          var $form = $(form);
          var reCaptchaId = $form.data('reCaptchaId');
          try {
            tinymce.triggerSave();
            var editor = tinymce.get("description");
            editor.save();
          } catch (e) {}
          if (rtcl.recaptcha && rtcl.recaptcha.on && $.inArray("listing", rtcl.recaptcha.on) !== -1) {
            if (rtcl.recaptcha.v === 2 && reCaptchaId !== undefined) {
              var response = grecaptcha.getResponse(reCaptchaId);
              var $captcha_msg = $form.find('#rtcl-listing-g-recaptcha-message');
              if (0 === response.length) {
                $captcha_msg.addClass('text-danger').html(rtcl.recaptcha.msg.invalid);
                grecaptcha.reset(reCaptchaId);
                return false;
              }
              submit_form_data();
              return false;
            } else if (rtcl.recaptcha.v === 3) {
              grecaptcha.ready(function () {
                $form.rtclBlock();
                grecaptcha.execute(rtcl.recaptcha.site_key, {
                  action: 'listing'
                }).then(function (token) {
                  submit_form_data(token);
                });
              });
              return false;
            }
          }
          submit_form_data();
          return false;
          function submit_form_data(reCaptcha_token) {
            var fromData = new FormData(form),
              msgHolder = $("<div class='alert rtcl-response'></div>");
            if (reCaptcha_token) {
              fromData.set('g-recaptcha-response', reCaptcha_token);
            }
            fromData.set('action', 'rtcl_post_new_listing');
            $.ajax({
              url: rtcl.ajaxurl,
              type: "POST",
              dataType: 'json',
              cache: false,
              contentType: false,
              processData: false,
              data: fromData,
              beforeSend: function beforeSend() {
                $form.find('.alert.rtcl-response').remove();
                $form.find('button[type=submit]').prop("disabled", true);
                $form.rtclBlock();
              },
              success: function success(response) {
                $form.find('button[type=submit]').prop("disabled", false);
                $form.rtclUnblock();
                var msg = '';
                if (response.message.length) {
                  response.message.map(function (message) {
                    msg += "<p>" + message + "</p>";
                  });
                }
                if (response.success) {
                  submitForm[0].reset();
                  if (msg) {
                    msgHolder.removeClass('alert-danger').addClass('alert-success').html(msg).appendTo(submitForm);
                  }
                  if (response.redirect_url) {
                    setTimeout(function () {
                      window.location.href = response.redirect_url;
                    }, 500);
                  }
                } else {
                  if (msg) {
                    msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo(submitForm);
                  }
                }
              },
              error: function error(e) {
                msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(submitForm);
                $form.find('button[type=submit]').prop("disabled", false);
                $form.rtclUnblock();
              }
            });
            return false;
          }
        }
      });
    }
  }
  function rtcl_delete_on_unload() {
    var pId = parseInt($("#_post_id").val(), 10);
    if (!pId || pId === 0 || isNaN(pId)) {
      return;
    }
    var data = {
      action: 'rtcl_delete_temp_listing',
      __rtcl_wpnonce: rtcl.__rtcl_wpnonce,
      id: pId
    };
    $.ajax(rtcl.ajaxurl, {
      data: data,
      dataType: 'json',
      type: 'post',
      success: function success(response) {}
    });
  }

  /* Ready */
  $(document).ready(function () {
    if (rtcl_add_post.character_limit.title) {
      $('#rtcl-title').on('keyup', function () {
        var _self = $(this);
        var value = $(this).val();
        var maxLength = rtcl_add_post.character_limit.title;
        if (maxLength) {
          var limitWrap = _self.parents('.form-group').find('.target-limit');
          if (limitWrap.length) {
            limitWrap.text(value ? maxLength - value.length : maxLength);
          }
        }
      });
    }
    if (rtcl_add_post.character_limit.description) {
      var maxLength = rtcl_add_post.character_limit.description;
      $('#description').on('keyup', function (event) {
        var _self = $(this);
        var value = $(this).val();
        var limitWrap = _self.parents('.form-group').find('.target-limit');
        if (limitWrap.length) {
          var remain = value ? maxLength - value.length : maxLength;
          if (remain <= 0) {
            limitWrap.text(0);
            _self.val(value.substring(0, maxLength));
          } else {
            limitWrap.text(remain);
          }
        }
      });
      if (typeof tinyMCE !== "undefined") {
        var _tinyEditor = tinyMCE.get('description');
        if (_tinyEditor) {
          var calculateContent = function calculateContent(event) {
            var _content = _tinyEditor.getContent();
            _content = _content.replace(/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])|(\s+)/ig, '');
            var remain = _content ? maxLength - _content.length : maxLength;
            if (_content.length >= maxLength) {
              limitWrap.text(0);
              event.preventDefault();
            } else {
              limitWrap.text(remain);
            }
          };
          var _self = $('#description');
          var limitWrap = _self.parents('.form-group').find('.target-limit');
          _tinyEditor.on('keypress', calculateContent);
          _tinyEditor.on('keyup', calculateContent);
          _tinyEditor.on("change", calculateContent);
        }
      }
    }
    $('#rtcl-ad-type').on('change', function () {
      var _self = $(this),
        type = _self.val(),
        msgHolder = $("<div class='alert rtcl-response'></div>"),
        selfWrapper = _self.parents('#rtcl-ad-type-selection'),
        target = $('#rtcl-ad-category-selection'),
        sub_category_wrap = $('#rtcl-sub-category-wrap'),
        sub_cat_row = $('#sub-cat-row'),
        data = {
          'action': 'rtcl_get_one_level_category_select_list_by_type',
          'type': type,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      if (type) {
        $.ajax({
          url: rtcl.ajaxurl,
          data: data,
          type: "POST",
          dataType: 'json',
          beforeSend: function beforeSend() {
            $(spinner).insertAfter(_self);
            selfWrapper.find('.alert.rtcl-response').remove();
            target.find('.alert.rtcl-response').remove();
            sub_cat_row.addClass("rtcl-hide");
            sub_category_wrap.html('');
          },
          success: function success(response) {
            _self.next('.rtcl-spinner').remove();
            sub_cat_row.addClass("rtcl-hide");
            sub_category_wrap.html('');
            var msg = '';
            if (response.message.length) {
              response.message.map(function (message) {
                msg += "<p>" + message + "</p>";
              });
            }
            if (response.success) {
              $('#rtcl-category').html(response.cats);
              target.slideDown();
            } else {
              $('#rtcl-category').html('');
              target.slideUp();
              if (msg) {
                msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo(selfWrapper);
              }
            }
          },
          error: function error(e) {
            _self.next('.rtcl-spinner').remove();
            msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(selfWrapper);
          }
        });
      } else {
        target.slideUp();
        sub_cat_row.addClass("rtcl-hide");
        sub_category_wrap.html('');
      }
    });
    $('#rtcl-category').on('change', function () {
      var self = $(this),
        target = self.parents('.rtcl-post-category'),
        sub_category_wrap = $('#rtcl-sub-category-wrap'),
        sub_cat_row = $('#sub-cat-row'),
        term_id = $(this).val(),
        type_field = $('#rtcl-ad-type'),
        type = type_field.val() || '',
        msgHolder = $("<div class='alert rtcl-response'></div>"),
        data = {
          'action': 'rtcl_get_one_level_category_select_list',
          'term_id': term_id,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      if (!type && rtcl_add_post.hide_ad_type || type && !rtcl_add_post.hide_ad_type) {
        if (term_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            dataType: 'json',
            beforeSend: function beforeSend() {
              $(spinner).insertAfter(self);
              target.find('.alert.rtcl-response').remove();
            },
            success: function success(response) {
              self.next('.rtcl-spinner').remove();
              if (response.success) {
                if (response.child_cats) {
                  sub_category_wrap.html($('<select class="form-control" required />').append(response.child_cats));
                  sub_cat_row.removeClass("rtcl-hide");
                } else {
                  sub_category_wrap.html('');
                  sub_cat_row.addClass("rtcl-hide");
                  var uri = rtcl_add_post.form_uri.toString(),
                    params = {
                      "category": term_id
                    },
                    glue = uri.indexOf('?') !== -1 ? "&" : "?";
                  if (!rtcl_add_post.hide_ad_type && type) {
                    params['type'] = type;
                  }
                  uri = uri + glue + $.param(params);
                  window.location = uri;
                }
              } else {
                sub_cat_row.addClass("rtcl-hide");
                if (response.message.length) {
                  var msg = '';
                  response.message.map(function (message) {
                    msg += "<p>" + message + "</p>";
                  });
                  if (msg) {
                    msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo(target);
                  }
                }
              }
            },
            error: function error(e) {
              self.next('.rtcl-spinner').remove();
              msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(target);
            }
          });
        } else {
          sub_category_wrap.html('');
          sub_cat_row.addClass("rtcl-hide");
        }
      } else {
        alert(rtcl_add_post.message.ad_type);
        type_field.focus();
      }
    });
    $(document).on('change', '#rtcl-sub-category-wrap select', function () {
      var self = $(this),
        target = self.parents('#rtcl-sub-category-wrap'),
        term_id = $(this).val(),
        type_field = $('#rtcl-ad-type'),
        type = type_field.val() || '',
        msgHolder = $("<div class='alert rtcl-response'></div>"),
        data = {
          'action': 'rtcl_get_one_level_category_select_list',
          'term_id': term_id,
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      if (!type && rtcl_add_post.hide_ad_type || type && !rtcl_add_post.hide_ad_type) {
        if (term_id) {
          $.ajax({
            url: rtcl.ajaxurl,
            data: data,
            type: "POST",
            dataType: 'json',
            beforeSend: function beforeSend() {
              $(spinner).insertAfter(self);
              target.find('.alert.rtcl-response').remove();
              self.nextAll('select').remove();
            },
            success: function success(response) {
              target.find('.rtcl-spinner').remove();
              if (response.success) {
                if (response.child_cats) {
                  target.append($('<select class="form-control" required />').append(response.child_cats));
                } else {
                  var uri = rtcl_add_post.form_uri.toString(),
                    params = {
                      "category": term_id
                    },
                    glue = uri.indexOf('?') !== -1 ? "&" : "?";
                  if (!rtcl_add_post.hide_ad_type && type) {
                    params['type'] = type;
                  }
                  uri = uri + glue + $.param(params);
                  window.location = uri;
                }
              } else {
                self.nextAll('select').remove();
                if (response.message.length) {
                  var msg = '';
                  response.message.map(function (message) {
                    msg += "<p>" + message + "</p>";
                  });
                  if (msg) {
                    msgHolder.removeClass('alert-success').addClass('alert-danger').html(msg).appendTo(target);
                  }
                }
              }
            },
            error: function error(e) {
              target.find('.rtcl-spinner').remove();
              msgHolder.removeClass('alert-success').addClass('alert-danger').html(e.responseText).appendTo(target);
            }
          });
        } else {
          self.nextAll('select').remove();
        }
      } else {
        alert(rtcl_add_post.message.ad_type);
        type_field.focus();
      }
    });

    // First level
    $('#rtcl-location').on('change', function () {
      var self = $(this),
        subLocation = $('#rtcl-sub-location'),
        subLocationRow = subLocation.parents('#sub-location-row'),
        data = {
          'action': 'rtcl_get_sub_location_options',
          'term_id': $(this).val(),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: 'POST',
        dataType: 'json',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          subLocation.find('option').each(function () {
            if ($(this).val().trim()) {
              $(this).remove();
            }
          });
          subLocation.append(data.locations);
          if (data.locations) {
            subLocationRow.removeClass('rtcl-hide');
          } else {
            subLocationRow.addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    });

    // Second level
    $('#rtcl-sub-location').on('change', function () {
      var self = $(this),
        subSubLocation = $('#rtcl-sub-sub-location'),
        subSubLocationRow = subSubLocation.parents('#sub-sub-location-row'),
        data = {
          'action': 'rtcl_get_sub_location_options',
          'term_id': $(this).val(),
          __rtcl_wpnonce: rtcl.__rtcl_wpnonce
        };
      $.ajax({
        url: rtcl.ajaxurl,
        data: data,
        type: 'POST',
        dataType: 'json',
        beforeSend: function beforeSend() {
          $(spinner).insertAfter(self);
        },
        success: function success(data) {
          self.next('.rtcl-spinner').remove();
          subSubLocation.find('option').each(function () {
            if ($(this).val().trim()) {
              $(this).remove();
            }
          });
          subSubLocation.append(data.locations);
          if (data.locations) {
            subSubLocationRow.removeClass('rtcl-hide');
          } else {
            subSubLocationRow.addClass('rtcl-hide');
          }
        },
        error: function error() {
          self.next('.rtcl-spinner').remove();
        }
      });
    });
  });
  $(window).bind("beforeunload", rtcl_delete_on_unload);
})(jQuery);
