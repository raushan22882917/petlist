/**
 * Dog Directory — Main Frontend JavaScript
 * @package Petslist Dog Directory
 */
(function ($) {
  'use strict';

  var DD = {

    init: function () {
      this.Auth.init();
      this.Dogs.init();
      this.Dashboard.init();
      this.Directory.init();
      this.UI.init();
    },

    /* ── Helper: show message ── */
    msg: function (id, text, type) {
      var $el = $('#' + id);
      if (!$el.length) return;
      $el.removeClass('success error').addClass(type).html(text).show();
      $('html,body').animate({ scrollTop: $el.offset().top - 80 }, 300);
    },

    /* ── Helper: AJAX wrapper ── */
    ajax: function (action, data, cb) {
      data = $.extend({ action: action }, data);
      $.post(ddVars.ajaxUrl, data, function (res) {
        cb(res);
      }).fail(function () {
        cb({ success: false, data: { message: ddVars.strings.error } });
      });
    },

    /* ─────────────────────────────────────────
       AUTH
    ───────────────────────────────────────── */
    Auth: {
      init: function () {
        this.loginForm();
        this.registerForm();
        this.forgotForm();
        this.passwordToggle();
        this.passwordStrength();
      },

      loginForm: function () {
        $(document).on('submit', '#dd-login-form', function (e) {
          e.preventDefault();
          var $btn = $('#dd-login-submit');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_login', {
            nonce:       ddVars.nonces.auth,
            email:       $('#dd-login-email').val(),
            password:    $('#dd-login-pass').val(),
            remember:    $('#dd-remember').is(':checked') ? 1 : 0,
            redirect_to: $('input[name=redirect_to]').val() || '',
          }, function (res) {
            DD.Auth._setLoading($btn, false);
            if (res.success) {
              DD.msg('dd-login-message', res.data.message, 'success');
              setTimeout(function () {
                window.location.href = res.data.redirect || ddVars.dashboardUrl;
              }, 800);
            } else {
              DD.msg('dd-login-message', res.data.message, 'error');
            }
          });
        });
      },

      registerForm: function () {
        $(document).on('submit', '#dd-register-form', function (e) {
          e.preventDefault();
          var $btn = $('#dd-register-submit');
          if (!$('#dd-terms').is(':checked')) {
            DD.msg('dd-register-message', 'Please accept the Terms of Service.', 'error');
            return;
          }
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_register', {
            nonce:       ddVars.nonces.auth,
            name:        $('#dd-reg-name').val(),
            email:       $('#dd-reg-email').val(),
            password:    $('#dd-reg-pass').val(),
            redirect_to: new URLSearchParams(window.location.search).get('redirect_to') || '',
          }, function (res) {
            DD.Auth._setLoading($btn, false);
            if (res.success) {
              DD.msg('dd-register-message', res.data.message, 'success');
              setTimeout(function () {
                window.location.href = res.data.redirect || ddVars.pricingUrl;
              }, 1000);
            } else {
              DD.msg('dd-register-message', res.data.message, 'error');
            }
          });
        });
      },

      forgotForm: function () {
        $(document).on('submit', '#dd-forgot-form', function (e) {
          e.preventDefault();
          var $btn = $(this).find('[type=submit]');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_forgot_password', {
            nonce: ddVars.nonces.auth,
            email: $(this).find('[name=email]').val(),
          }, function (res) {
            DD.Auth._setLoading($btn, false);
            DD.msg('dd-forgot-message', res.data.message, res.success ? 'success' : 'error');
          });
        });
      },

      passwordToggle: function () {
        $(document).on('click', '.dd-toggle-pass', function () {
          var $inp = $(this).closest('.dd-form-input-wrap').find('input');
          var type = $inp.attr('type') === 'password' ? 'text' : 'password';
          $inp.attr('type', type);
          $(this).find('i').toggleClass('icon-pl-eye icon-pl-eye-slash');
        });
      },

      passwordStrength: function () {
        $(document).on('input', '#dd-reg-pass, #dd-new-pass', function () {
          var val      = $(this).val();
          var strength = DD.Auth._calcStrength(val);
          var $bar     = $(this).closest('.dd-form-group').find('.dd-password-strength__bar');
          var $label   = $(this).closest('.dd-form-group').find('.dd-password-strength__label');
          var levels   = [
            { w: 0,   color: '#e2e8f0', label: '' },
            { w: '25%', color: '#ef4444', label: 'Weak' },
            { w: '50%', color: '#f59e0b', label: 'Fair' },
            { w: '75%', color: '#3b82f6', label: 'Good' },
            { w: '100%', color: '#22c55e', label: 'Strong' },
          ];
          var l = levels[strength];
          $bar.css({ width: l.w, background: l.color });
          $label.text(l.label).css('color', l.color);

          // Requirements
          DD.Auth._checkReq('#dd-req-length', val.length >= 8);
          DD.Auth._checkReq('#dd-req-upper',  /[A-Z]/.test(val));
          DD.Auth._checkReq('#dd-req-number', /[0-9]/.test(val));
        });
      },

      _checkReq: function (id, pass) {
        var $el = $(id);
        if (!$el.length) return;
        $el.toggleClass('valid', pass);
        $el.find('i').attr('class', pass
          ? 'fa-solid fa-circle-check'
          : 'fa-solid fa-circle-xmark');
      },

      _calcStrength: function (v) {
        if (!v) return 0;
        var s = 0;
        if (v.length >= 8) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        return s;
      },

      _setLoading: function ($btn, on) {
        $btn.find('.dd-btn__text').toggle(!on);
        $btn.find('.dd-btn__loader').toggle(on);
        $btn.prop('disabled', on);
      },
    },

    /* ─────────────────────────────────────────
       DOGS
    ───────────────────────────────────────── */
    Dogs: {
      init: function () {
        this.dogForm();
        this.deleteDog();
        this.mediaUpload();
        this.dogFormWizard();
      },

      dogForm: function () {
        $(document).on('submit', '#dd-dog-form', function (e) {
          e.preventDefault();
          var $btn  = $('#dd-dog-submit');
          var $form = $(this);
          var data  = { nonce: ddVars.nonces.dog };

          // Collect all fields
          $form.find('[name]').each(function () {
            var name = $(this).attr('name');
            var val  = $(this).val();
            if (name) {
              // Build nested object for dog_data
              if (name.startsWith('dog_data[') || name === 'post_id') {
                data[name] = val;
              }
            }
          });

          // Validate required photos
          if (!data['dog_data[thumbnail_id]'] || !data['dog_data[front_photo]'] || !data['dog_data[side_photo]']) {
            DD.msg('dd-dog-form-message', 'Please upload all 3 required photos (Profile, Front, and Side).', 'error');
            return;
          }

          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_save_dog', data, function (res) {
            DD.Auth._setLoading($btn, false);
            if (res.success) {
              DD.msg('dd-dog-form-message', res.data.message, 'success');
              setTimeout(function () {
                window.location.href = res.data.edit_url || ddVars.dashboardUrl;
              }, 1200);
            } else {
              DD.msg('dd-dog-form-message', res.data.message, 'error');
              if (res.data.subscribe) {
                setTimeout(function () {
                  window.location.href = ddVars.pricingUrl;
                }, 1500);
              }
            }
          });
        });
      },

      dogFormWizard: function () {
        var currentStep = 1;
        var totalSteps = 4;

        // Next button click
        $(document).on('click', '#dd-wizard-next', function () {
          // Validate current step
          var isValid = true;
          var $currentSection = $('.dd-dog-form__section[data-step="' + currentStep + '"]');
          
          // Basic validation for required fields in current step
          $currentSection.find('input[required], select[required], textarea[required]').each(function() {
            if (!this.value.trim()) {
              isValid = false;
              $(this).addClass('dd-input-error');
              if ($currentSection.find('.dd-input-error').length === 1) {
                this.focus();
              }
            } else {
              $(this).removeClass('dd-input-error');
            }
          });

          if (!isValid) {
            DD.msg('dd-dog-form-message', 'Please fill out all required fields.', 'error');
            return;
          }

          // Advance
          if (currentStep < totalSteps) {
            currentStep++;
            updateWizard();
          }
        });

        // Prev button click
        $(document).on('click', '#dd-wizard-prev', function () {
          if (currentStep > 1) {
            currentStep--;
            updateWizard();
          }
        });

        // Remove error class on input
        $(document).on('input change', '#dd-dog-form input, #dd-dog-form select', function () {
          $(this).removeClass('dd-input-error');
        });

        function updateWizard() {
          // Clear any message
          $('#dd-dog-form-message').hide().text('');

          // Show/Hide Sections
          $('.dd-dog-form__section').hide();
          $('.dd-dog-form__section[data-step="' + currentStep + '"]').fadeIn(300);

          // Update Steps Indicator
          $('.dd-wizard-step').removeClass('dd-wizard-step--active dd-wizard-step--completed');
          $('.dd-wizard-step').each(function() {
            var step = $(this).data('step');
            if (step < currentStep) {
              $(this).addClass('dd-wizard-step--completed');
            } else if (step === currentStep) {
              $(this).addClass('dd-wizard-step--active');
            }
          });

          // Show/Hide Buttons
          if (currentStep === 1) {
            $('#dd-wizard-prev').addClass('dd-hide');
            $('#dd-wizard-next').removeClass('dd-hide');
            $('#dd-dog-submit').addClass('dd-hide');
          } else if (currentStep === totalSteps) {
            $('#dd-wizard-prev').removeClass('dd-hide');
            $('#dd-wizard-next').addClass('dd-hide');
            $('#dd-dog-submit').removeClass('dd-hide');
          } else {
            $('#dd-wizard-prev').removeClass('dd-hide');
            $('#dd-wizard-next').removeClass('dd-hide');
            $('#dd-dog-submit').addClass('dd-hide');
          }

          // Scroll to top of form on step change
          var $formTop = $('.dd-tab-add-dog__header');
          if ($formTop.length) {
            $('html, body').animate({ scrollTop: $formTop.offset().top - 24 }, 200);
          }
        }

        // Set initial button state
        updateWizard();
      },

      deleteDog: function () {
        $(document).on('click', '.dd-delete-dog', function () {
          if (!confirm(ddVars.strings.confirmDelete)) return;
          var $btn = $(this);
          var id   = $btn.data('id');
          $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
          DD.ajax('dd_delete_dog', { nonce: ddVars.nonces.dog, post_id: id }, function (res) {
            if (res.success) {
              $btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
              DD.msg('dd-dog-list-message', res.data.message, 'success');
            } else {
              $btn.prop('disabled', false).html('<i class="fa-solid fa-trash"></i>');
              DD.msg('dd-dog-list-message', res.data.message, 'error');
            }
          });
        });
      },

      mediaUpload: function () {
        $(document).on('click', '.dd-upload-photo', function (e) {
          e.preventDefault();
          var $btn     = $(this);
          var targetId = $btn.data('target');
          var previewId = $btn.data('preview');

          var frame = wp.media({
            title:    'Select Photo',
            button:   { text: 'Use This Photo' },
            multiple: false,
            library:  { type: 'image' },
          });

          frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $('#' + targetId).val(att.id);
            var $preview = $('#' + previewId);
            $preview.html('<img src="' + att.url + '" style="width:100%;height:100%;object-fit:cover">');
          });

          frame.open();
        });
      },
    },

    /* ─────────────────────────────────────────
       DASHBOARD
    ───────────────────────────────────────── */
    Dashboard: {
      init: function () {
        this.profileForm();
        this.passwordForm();
        this.cancelSubscription();
      },

      profileForm: function () {
        // Avatar upload trigger
        $(document).on('click', '#dd-profile-avatar-upload-trigger', function (e) {
          e.preventDefault();
          var frame = wp.media({
            title:    'Select Profile Photo',
            button:   { text: 'Use As Profile Photo' },
            multiple: false,
            library:  { type: 'image' },
          });

          frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $('#dd-profile-avatar-id').val(att.id);
            $('#dd-profile-avatar-preview').attr('src', att.url);
          });

          frame.open();
        });

        $(document).on('submit', '#dd-profile-form', function (e) {
          e.preventDefault();
          var $btn = $('#dd-profile-submit');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_update_profile', {
            nonce:        ddVars.nonces.dashboard,
            display_name: $('#dd-profile-name').val(),
            bio:          $('#dd-profile-bio').val(),
            phone:        $('#dd-profile-phone').val(),
            website:      $('#dd-profile-website').val(),
            avatar_id:    $('#dd-profile-avatar-id').val(),
          }, function (res) {
            DD.Auth._setLoading($btn, false);
            DD.msg('dd-profile-message', res.data.message, res.success ? 'success' : 'error');
            if (res.success) {
              $('.ddu-sidebar__user-avatar, .ddu-topbar__avatar').attr('src', $('#dd-profile-avatar-preview').attr('src'));
            }
          });
        });
      },

      passwordForm: function () {
        $(document).on('submit', '#dd-password-form', function (e) {
          e.preventDefault();
          var newP    = $('#dd-new-pass').val();
          var confirm = $('#dd-confirm-pass').val();
          if (newP !== confirm) {
            DD.msg('dd-password-message', 'Passwords do not match.', 'error');
            return;
          }
          var $btn = $('#dd-password-submit');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_change_password', {
            nonce:            ddVars.nonces.dashboard,
            current_password: $('#dd-current-pass').val(),
            new_password:     newP,
            confirm_password: confirm,
          }, function (res) {
            DD.Auth._setLoading($btn, false);
            DD.msg('dd-password-message', res.data.message, res.success ? 'success' : 'error');
            if (res.success && res.data.logout) {
              setTimeout(function () {
                window.location.href = ddVars.loginUrl;
              }, 1500);
            }
          });
        });
      },

      cancelSubscription: function () {
        $(document).on('click', '#dd-cancel-sub', function () {
          if (!confirm(ddVars.strings.confirmCancel)) return;
          var $btn = $(this);
          $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Cancelling...');
          DD.ajax('dd_cancel_subscription', { nonce: ddVars.nonces.cancel }, function (res) {
            DD.msg('dd-sub-message', res.data.message, res.success ? 'success' : 'error');
            if (res.success) setTimeout(function () { location.reload(); }, 1500);
            else $btn.prop('disabled', false).html('<i class="fa-solid fa-xmark"></i> Cancel Subscription');
          });
        });
      },
    },

    /* ─────────────────────────────────────────
       DIRECTORY
    ───────────────────────────────────────── */
    Directory: {
      init: function () {
        this.viewToggle();
      },

      viewToggle: function () {
        $(document).on('click', '.dd-view-btn', function () {
          var view = $(this).data('view');
          $('.dd-view-btn').removeClass('active');
          $(this).addClass('active');
          var $grid = $('#dd-dogs-grid-view');
          var $table = $('#dd-dogs-table-view');
          if (view === 'list') {
            $grid.hide();
            $table.show();
          } else {
            $grid.show();
            $table.hide();
          }
          localStorage.setItem('dd_view', view);
        });

        // Restore from localStorage
        var savedView = localStorage.getItem('dd_view') || 'list';
        if (savedView === 'list') {
          $('.dd-view-btn--list').addClass('active');
          $('.dd-view-btn--grid').removeClass('active');
          $('#dd-dogs-grid-view').hide();
          $('#dd-dogs-table-view').show();
        } else {
          $('.dd-view-btn--grid').addClass('active');
          $('.dd-view-btn--list').removeClass('active');
          $('#dd-dogs-grid-view').show();
          $('#dd-dogs-table-view').hide();
        }
      },
    },

    /* ─────────────────────────────────────────
       UI (misc)
    ───────────────────────────────────────── */
    UI: {
      init: function () {
        this.faqAccordion();
        this.mobileNav();
        this.galleryNav();
      },

      faqAccordion: function () {
        $(document).on('click', '.dd-faq-item__question', function () {
          var $item = $(this).closest('.dd-faq-item');
          $item.siblings('.open').removeClass('open');
          $item.toggleClass('open');
        });
      },

      mobileNav: function () {
        // Mobile dashboard nav collapse
        if ($(window).width() < 900) {
          var currentLabel = $('.dd-dashboard__nav-item.is-active .dd-dashboard__nav-link span').text();
          $('.dd-dashboard__nav').prepend(
            '<button class="dd-mobile-nav-toggle" style="width:100%;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;background:none;border:none;font-weight:700;font-size:14px;cursor:pointer;color:#515167;">' +
            '<span>' + (currentLabel || 'Menu') + '</span><i class="icon-pl-angle-down-fat"></i></button>'
          );
          $('.dd-dashboard__nav-list').hide();
          $(document).on('click', '.dd-mobile-nav-toggle', function () {
            $('.dd-dashboard__nav-list').slideToggle(200);
            $(this).find('i').toggleClass('icon-pl-angle-down-fat icon-pl-angle-up-fat');
          });
        }
      },

      galleryNav: function () {
        // Already handled inline in single-dog template
      },
    },
  };

  $(document).ready(function () {
    DD.init();
  });

})(jQuery);
