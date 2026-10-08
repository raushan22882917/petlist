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
      var noticeType = (type === 'error' || type === 'danger') ? 'error' : (type === 'warning' ? 'warning' : (type === 'info' ? 'info' : 'success'));
      var icon = (noticeType === 'error') ? '⚠️' : ((noticeType === 'success') ? '✅' : ((noticeType === 'warning') ? '⚠️' : 'ℹ️'));
      $el.removeClass('success error danger alert alert-danger alert-success dd-notice dd-notice--error dd-notice--success dd-notice--warning dd-notice--info')
         .addClass('dd-notice dd-notice--' + noticeType)
         .html('<span style="font-size:16px; line-height:1;">' + icon + '</span> <span>' + text + '</span>')
         .show();
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
          var fulltimeBreeder = $('input[name="fulltime_breeder"]:checked').val() || 'no';
          var regState = $('#dd-reg-state').val() || '';
          var regCity  = $('#dd-reg-city').val() || '';
          var regLoc   = $('#dd-reg-location').length ? $('#dd-reg-location').val() : [regCity, regState].filter(Boolean).join(', ');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_register', {
            nonce:            ddVars.nonces.auth,
            name:             $('#dd-reg-name').val(),
            state:            regState,
            city:             regCity,
            location:         regLoc,
            phone:            $('#dd-reg-phone').val(),
            email:            $('#dd-reg-email').val(),
            fulltime_breeder: fulltimeBreeder,
            password:         $('#dd-reg-pass').val(),
            redirect_to:      new URLSearchParams(window.location.search).get('redirect_to') || '',
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
        this.unpublishDog();
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

          // Auto-assign thumbnail_id from front_photo if empty
          if (!data['dog_data[thumbnail_id]'] && data['dog_data[front_photo]']) {
            data['dog_data[thumbnail_id]'] = data['dog_data[front_photo]'];
          }

          // Validate required photos (2 photos: Front View and Side View)
          if (!data['dog_data[front_photo]'] || !data['dog_data[side_photo]']) {
            DD.msg('dd-dog-form-message', 'Please upload both required photos (Front View and Side View).', 'error');
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
        var totalSteps = 3;

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
            DD.msg('dd-dog-form-message', 'Please fill out all required fields to proceed.', 'error');
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
        $(document).on('input change', '#dd-dog-form input, #dd-dog-form select, #dd-dog-form textarea', function () {
          $(this).removeClass('dd-input-error');
        });

        function updateWizard() {
          // Clear any message
          $('#dd-dog-form-message').hide().text('');

          // Show/Hide Sections
          $('.dd-dog-form__section').hide();
          var $targetSection = $('.dd-dog-form__section[data-step="' + currentStep + '"]');
          $targetSection.fadeIn(300, function () {
            if (currentStep === 2 && typeof ddInitCountrySelects === 'function') {
              ddInitCountrySelects();
            }
          });

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

      unpublishDog: function () {
        $(document).on('click', '.dd-unpublish-dog', function () {
          if (!confirm('Are you sure you want to unpublish and remove this listing from public view?')) return;
          var $btn = $(this);
          var id   = $btn.data('id');
          $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
          DD.ajax('dd_unpublish_dog', { nonce: ddVars.nonces.dog, post_id: id }, function (res) {
            if (res.success) {
              location.reload();
            } else {
              $btn.prop('disabled', false).html('<i class="fa-solid fa-ban"></i>');
              DD.msg('dd-dog-list-message', res.data.message, 'error');
            }
          });
        });
      },

      mediaUpload: function () {
        $(document).on('click', '.dd-upload-photo, .dd-photo-upload-area', function (e) {
          e.preventDefault();
          var $el       = $(this);
          var targetId  = $el.data('target');
          var previewId = $el.data('preview');

          if (!targetId || !previewId) {
            var $box = $el.closest('.dd-photo-upload-box');
            targetId  = $box.find('.dd-upload-photo').data('target');
            previewId = $box.find('.dd-upload-photo').data('preview');
          }

          if (typeof wp === 'undefined' || !wp.media) {
            return;
          }

          var frame = wp.media({
            title:    'Select Photo',
            button:   { text: 'Use This Photo' },
            multiple: false,
            library:  { type: 'image' },
          });

          frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var imgUrl = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : (att.sizes && att.sizes.large ? att.sizes.large.url : (att.sizes && att.sizes.full ? att.sizes.full.url : att.url));

            $('#' + targetId).val(att.id);
            var $preview = $('#' + previewId);
            $preview.html('<img src="' + imgUrl + '" alt="Selected Dog Photo" style="width:100%;height:100%;object-fit:cover;border-radius:8px;display:block;">');

            if (targetId === 'dd-front-id') {
              $('#dd-thumb-id').val(att.id);
            }
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
          var profState = $('#dd-profile-state').val() || '';
          var profCity  = $('#dd-profile-city').val() || '';
          var profLoc   = $('#dd-profile-location').length ? $('#dd-profile-location').val() : [profCity, profState].filter(Boolean).join(', ');
          DD.Auth._setLoading($btn, true);
          DD.ajax('dd_update_profile', {
            nonce:            ddVars.nonces.dashboard,
            display_name:     $('#dd-profile-name').val(),
            bio:              $('#dd-profile-bio').val(),
            phone:            $('#dd-profile-phone').val(),
            state:            profState,
            city:             profCity,
            location:         profLoc,
            fulltime_breeder: $('#dd-profile-fulltime-breeder').val(),
            website:          $('#dd-profile-website').val(),
            avatar_id:        $('#dd-profile-avatar-id').val(),
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
        this.searchableSelects();
      },

      searchableSelects: function () {
        if ($.fn.select2) {
          $('select.dd-searchable-select, #dd-reg-state, #dd-profile-state, #dd-country').each(function () {
            var $sel = $(this);
            if ($sel.is(':hidden') && $sel.closest('.dd-dog-form__section').length) return;
            if (!$sel.hasClass('select2-hidden-accessible')) {
              var placeholderText = $sel.find('option:first').text() || 'Select State';
              $sel.select2({
                theme: 'classic',
                width: '100%',
                dropdownAutoWidth: true,
                placeholder: placeholderText,
                allowClear: false
              });
            }
          });
        }
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

    $(document).on('submit', '#dd-stripe-hosted-form', function (e) {
      e.preventDefault();
      var $form = $(this);
      var $btn  = $('#dd-stripe-checkout-btn');
      $btn.prop('disabled', true);
      $btn.find('span').first().hide();
      $btn.find('.dd-btn__loader').show();

      $.post(ddVars.ajaxUrl, {
        action: 'dd_create_stripe_session',
        nonce:  $form.find('[name=nonce]').val() || ddVars.nonces.auth,
        plan:   $form.find('[name=plan]').val()
      }, function (res) {
        if (res.success && res.data.checkout_url) {
          window.location.href = res.data.checkout_url;
        } else {
          $btn.prop('disabled', false);
          $btn.find('span').first().show();
          $btn.find('.dd-btn__loader').hide();
          DD.msg('dd-checkout-message', (res.data && res.data.message) ? res.data.message : 'Stripe checkout error.', 'error');
        }
      }).fail(function () {
        $btn.prop('disabled', false);
        $btn.find('span').first().show();
        $btn.find('.dd-btn__loader').hide();
        DD.msg('dd-checkout-message', 'Server connection error. Please try again.', 'error');
      });
    });
  });

})(jQuery);

/* ═══════════════════════════════════════════════════════════
   WORLDWIDE COUNTRY + STATE / PROVINCE SELECTOR
   Runs on ALL DD pages: Register, Profile, Add Stud
═══════════════════════════════════════════════════════════ */
(function ($) {
    'use strict';

    var DD_COUNTRIES = [
        { name: 'United States', states: ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming','District of Columbia','Puerto Rico','Guam','U.S. Virgin Islands','American Samoa','Northern Mariana Islands'] },
        { name: 'United Kingdom', states: ['England','Scotland','Wales','Northern Ireland','Greater London','West Midlands','Greater Manchester','West Yorkshire','South Yorkshire','Merseyside','Tyne and Wear','South East','South West','East Midlands','East of England','North West','North East','Yorkshire and the Humber'] },
        { name: 'Afghanistan', states: ['Kabul','Kandahar','Herat','Mazar-i-Sharif','Kunduz','Jalalabad','Ghazni','Balkh','Baghlan','Badakhshan'] },
        { name: 'Albania', states: ['Berat','Diber','Durres','Elbasan','Fier','Gjirokaster','Korce','Kukes','Lezhe','Shkoder','Tirana','Vlore'] },
        { name: 'Algeria', states: ['Adrar','Ain Defla','Ain Temouchent','Algiers','Annaba','Batna','Bechar','Bejaia','Biskra','Blida','Bordj Bou Arreridj','Bouira','Boumerdes','Chlef','Constantine','Djelfa','El Bayadh','El Oued','El Tarf','Ghardaia','Guelma','Illizi','Jijel','Khenchela','Laghouat','Msila','Mascara','Medea','Mila','Mostaganem','Naama','Oran','Ouargla','Oum El Bouaghi','Relizane','Saida','Setif','Sidi Bel Abbes','Skikda','Souk Ahras','Tamanrasset','Tebessa','Tiaret','Tindouf','Tipaza','Tissemsilt','Tizi Ouzou','Tlemcen'] },
        { name: 'Andorra', states: ['Andorra la Vella','Canillo','Encamp','Escaldes-Engordany','La Massana','Ordino','Sant Julia de Loria'] },
        { name: 'Angola', states: ['Bengo','Benguela','Bie','Cabinda','Cuando Cubango','Cuanza Norte','Cuanza Sul','Cunene','Huambo','Huila','Luanda','Lunda Norte','Lunda Sul','Malanje','Moxico','Namibe','Uige','Zaire'] },
        { name: 'Antigua and Barbuda', states: ['Saint George','Saint John','Saint Mary','Saint Paul','Saint Peter','Saint Philip'] },
        { name: 'Argentina', states: ['Buenos Aires','Buenos Aires City','Catamarca','Chaco','Chubut','Cordoba','Corrientes','Entre Rios','Formosa','Jujuy','La Pampa','La Rioja','Mendoza','Misiones','Neuquen','Rio Negro','Salta','San Juan','San Luis','Santa Cruz','Santa Fe','Santiago del Estero','Tierra del Fuego','Tucuman'] },
        { name: 'Armenia', states: ['Aragatsotn','Ararat','Armavir','Gegharkunik','Kotayk','Lori','Shirak','Syunik','Tavush','Vayots Dzor','Yerevan'] },
        { name: 'Australia', states: ['Australian Capital Territory','New South Wales','Northern Territory','Queensland','South Australia','Tasmania','Victoria','Western Australia'] },
        { name: 'Austria', states: ['Burgenland','Carinthia','Lower Austria','Salzburg','Styria','Tyrol','Upper Austria','Vienna','Vorarlberg'] },
        { name: 'Azerbaijan', states: ['Absheron','Agdam','Agdash','Aghjabadi','Agstafa','Agsu','Astara','Balakan','Barda','Beylagan','Bilasuvar','Dashkasan','Fizuli','Gadabay','Ganja','Goranboy','Goychay','Goygol','Hajigabul','Imishli','Ismayilli','Jabrayil','Jalilabad','Kalbajar','Kurdamir','Lachin','Lankaran','Lerik','Masally','Mingachevir','Nakhchivan','Neftchala','Oghuz','Qakh','Qazakh','Quba','Qubadli','Qusar','Saatly','Sabirabad','Salyan','Shamakhi','Shamkir','Shirvan','Shusha','Siyazan','Sumgayit','Tartar','Tovuz','Ujar','Yardymli','Yevlakh','Zangilan','Zaqatala','Zardab'] },
        { name: 'Bahamas', states: ['Acklins','Berry Islands','Bimini','Black Point','Cat Island','Central Abaco','Central Andros','Central Eleuthera','City of Freeport','Crooked Island','East Grand Bahama','Exuma','Grand Cay','Harbour Island','Hope Town','Inagua','Long Island','Mangrove Cay','Mayaguana','North Abaco','North Andros','North Eleuthera','Ragged Island','Rum Cay','San Salvador','South Abaco','South Andros','South Eleuthera','Spanish Wells','West Grand Bahama'] },
        { name: 'Bahrain', states: ['Capital','Central','Muharraq','Northern','Southern'] },
        { name: 'Bangladesh', states: ['Barisal','Chittagong','Dhaka','Khulna','Mymensingh','Rajshahi','Rangpur','Sylhet'] },
        { name: 'Barbados', states: ['Christ Church','Saint Andrew','Saint George','Saint James','Saint John','Saint Joseph','Saint Lucy','Saint Michael','Saint Peter','Saint Philip','Saint Thomas'] },
        { name: 'Belarus', states: ['Brest','Gomel','Grodno','Minsk','Minsk City','Mogilev','Vitebsk'] },
        { name: 'Belgium', states: ['Antwerp','Brussels','East Flanders','Flemish Brabant','Hainaut','Liege','Limburg','Luxembourg','Namur','Walloon Brabant','West Flanders'] },
        { name: 'Belize', states: ['Belize','Cayo','Corozal','Orange Walk','Stann Creek','Toledo'] },
        { name: 'Benin', states: ['Alibori','Atakora','Atlantique','Borgou','Collines','Couffo','Donga','Littoral','Mono','Oueme','Plateau','Zou'] },
        { name: 'Bolivia', states: ['Beni','Chuquisaca','Cochabamba','La Paz','Oruro','Pando','Potosi','Santa Cruz','Tarija'] },
        { name: 'Bosnia and Herzegovina', states: ['Brcko District','Federation of Bosnia and Herzegovina','Republika Srpska'] },
        { name: 'Botswana', states: ['Central','Chobe','Francistown','Gaborone','Ghanzi','Jwaneng','Kgalagadi','Kgatleng','Kweneng','Lobatse','North East','North West','South East','Southern','Sowa Town'] },
        { name: 'Brazil', states: ['Acre','Alagoas','Amapa','Amazonas','Bahia','Ceara','Distrito Federal','Espirito Santo','Goias','Maranhao','Mato Grosso','Mato Grosso do Sul','Minas Gerais','Para','Paraiba','Parana','Pernambuco','Piaui','Rio de Janeiro','Rio Grande do Norte','Rio Grande do Sul','Rondonia','Roraima','Santa Catarina','Sao Paulo','Sergipe','Tocantins'] },
        { name: 'Brunei', states: ['Belait','Brunei-Muara','Temburong','Tutong'] },
        { name: 'Bulgaria', states: ['Blagoevgrad','Burgas','Dobrich','Gabrovo','Haskovo','Kardzhali','Kyustendil','Lovech','Montana','Pazardzhik','Pernik','Pleven','Plovdiv','Razgrad','Ruse','Shumen','Silistra','Sliven','Smolyan','Sofia','Sofia City','Stara Zagora','Targovishte','Varna','Veliko Tarnovo','Vidin','Vratsa','Yambol'] },
        { name: 'Cambodia', states: ['Banteay Meanchey','Battambang','Kampong Cham','Kampong Chhnang','Kampong Speu','Kampong Thom','Kampot','Kandal','Kep','Koh Kong','Kratie','Mondulkiri','Oddar Meanchey','Pailin','Phnom Penh','Preah Sihanouk','Preah Vihear','Prey Veng','Pursat','Ratanakiri','Siem Reap','Stung Treng','Svay Rieng','Takeo','Tboung Khmum'] },
        { name: 'Cameroon', states: ['Adamawa','Centre','East','Far North','Littoral','North','North West','South','South West','West'] },
        { name: 'Canada', states: ['Alberta','British Columbia','Manitoba','New Brunswick','Newfoundland and Labrador','Northwest Territories','Nova Scotia','Nunavut','Ontario','Prince Edward Island','Quebec','Saskatchewan','Yukon'] },
        { name: 'Chile', states: ['Antofagasta','Arica and Parinacota','Atacama','Aysen','Biobio','Coquimbo','La Araucania','Los Lagos','Los Rios','Magallanes','Maule','Metropolitan Region','Nuble','O Higgins','Tarapaca','Valparaiso'] },
        { name: 'China', states: ['Anhui','Beijing','Chongqing','Fujian','Gansu','Guangdong','Guangxi','Guizhou','Hainan','Hebei','Heilongjiang','Henan','Hong Kong','Hubei','Hunan','Inner Mongolia','Jiangsu','Jiangxi','Jilin','Liaoning','Macau','Ningxia','Qinghai','Shaanxi','Shandong','Shanghai','Shanxi','Sichuan','Tianjin','Tibet','Xinjiang','Yunnan','Zhejiang'] },
        { name: 'Colombia', states: ['Amazonas','Antioquia','Arauca','Atlantico','Bogota','Bolivar','Boyaca','Caldas','Caqueta','Casanare','Cauca','Cesar','Choco','Cordoba','Cundinamarca','Guainia','Guaviare','Huila','La Guajira','Magdalena','Meta','Narino','Norte de Santander','Putumayo','Quindio','Risaralda','San Andres y Providencia','Santander','Sucre','Tolima','Valle del Cauca','Vaupes','Vichada'] },
        { name: 'Costa Rica', states: ['Alajuela','Cartago','Guanacaste','Heredia','Limon','Puntarenas','San Jose'] },
        { name: 'Croatia', states: ['Bjelovar-Bilogora','Brod-Posavina','Dubrovnik-Neretva','Istria','Karlovac','Koprivnica-Krizevci','Krapina-Zagorje','Lika-Senj','Medimurje','Osijek-Baranja','Pozega-Slavonia','Primorje-Gorski Kotar','Sibenik-Knin','Sisak-Moslavina','Split-Dalmatia','Varazdin','Virovitica-Podravina','Vukovar-Syrmia','Zadar','Zagreb','Zagreb City'] },
        { name: 'Cuba', states: ['Artemisa','Camaguey','Ciego de Avila','Cienfuegos','Granma','Guantanamo','Havana','Holguin','Isla de la Juventud','Las Tunas','Matanzas','Mayabeque','Pinar del Rio','Sancti Spiritus','Santiago de Cuba','Villa Clara'] },
        { name: 'Cyprus', states: ['Famagusta','Kyrenia','Larnaca','Limassol','Nicosia','Paphos'] },
        { name: 'Czech Republic', states: ['Central Bohemian','Hradec Kralove','Karlovy Vary','Liberec','Moravian-Silesian','Olomouc','Pardubice','Plzen','Prague','South Bohemian','South Moravian','Usti nad Labem','Vysocina','Zlin'] },
        { name: 'Denmark', states: ['Capital','Central Denmark','North Denmark','Region Zealand','Southern Denmark'] },
        { name: 'Dominican Republic', states: ['Azua','Bahoruco','Barahona','Dajabon','Distrito Nacional','Duarte','El Seibo','Espaillat','Hato Mayor','Hermanas Mirabal','Independencia','La Altagracia','La Romana','La Vega','Maria Trinidad Sanchez','Monsenor Nouel','Monte Cristi','Monte Plata','Pedernales','Peravia','Puerto Plata','Samana','San Cristobal','San Jose de Ocoa','San Juan','San Pedro de Macoris','Sanchez Ramirez','Santiago','Santiago Rodriguez','Santo Domingo','Valverde'] },
        { name: 'Ecuador', states: ['Azuay','Bolivar','Canar','Carchi','Chimborazo','Cotopaxi','El Oro','Esmeraldas','Galapagos','Guayas','Imbabura','Loja','Los Rios','Manabi','Morona Santiago','Napo','Orellana','Pastaza','Pichincha','Santa Elena','Santo Domingo','Sucumbios','Tungurahua','Zamora Chinchipe'] },
        { name: 'Egypt', states: ['Alexandria','Aswan','Asyut','Beheira','Beni Suef','Cairo','Dakahlia','Damietta','Faiyum','Gharbia','Giza','Ismailia','Kafr el-Sheikh','Luxor','Matruh','Minya','Monufia','New Valley','North Sinai','Port Said','Qalyubia','Qena','Red Sea','Sharqia','Sohag','South Sinai','Suez'] },
        { name: 'El Salvador', states: ['Ahuachapan','Cabanas','Chalatenango','Cuscatlan','La Libertad','La Paz','La Union','Morazan','San Miguel','San Salvador','San Vicente','Santa Ana','Sonsonate','Usulutan'] },
        { name: 'Estonia', states: ['Harju','Hiiu','Ida-Viru','Jarva','Jogeva','Laane','Laane-Viru','Polva','Parnu','Rapla','Saare','Tartu','Valga','Viljandi','Voru'] },
        { name: 'Ethiopia', states: ['Addis Ababa','Afar','Amhara','Benishangul-Gumuz','Dire Dawa','Gambela','Harari','Oromia','Sidama','SNNPR','Somali','Tigray'] },
        { name: 'Fiji', states: ['Central','Eastern','Northern','Western'] },
        { name: 'Finland', states: ['Central Finland','Central Ostrobothnia','Kainuu','Kymenlaakso','Lapland','North Karelia','North Ostrobothnia','North Savo','Ostrobothnia','Paijat-Hame','Pirkanmaa','Satakunta','South Karelia','South Ostrobothnia','South Savo','Southwest Finland','Tavastia Proper','Uusimaa'] },
        { name: 'France', states: ['Auvergne-Rhone-Alpes','Bourgogne-Franche-Comte','Brittany','Centre-Val de Loire','Corsica','Grand Est','Guadeloupe','Guyane','Hauts-de-France','Ile-de-France','La Reunion','Martinique','Mayotte','Normandy','Nouvelle-Aquitaine','Occitanie','Pays de la Loire','Provence-Alpes-Cote Azur'] },
        { name: 'Germany', states: ['Baden-Wurttemberg','Bavaria','Berlin','Brandenburg','Bremen','Hamburg','Hesse','Lower Saxony','Mecklenburg-Vorpommern','North Rhine-Westphalia','Rhineland-Palatinate','Saarland','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia'] },
        { name: 'Ghana', states: ['Ahafo','Ashanti','Bono','Bono East','Central','Eastern','Greater Accra','North East','Northern','Oti','Savannah','Upper East','Upper West','Volta','Western','Western North'] },
        { name: 'Greece', states: ['Attica','Central Greece','Central Macedonia','Crete','Eastern Macedonia and Thrace','Epirus','Ionian Islands','North Aegean','Peloponnese','South Aegean','Thessaly','Western Greece','Western Macedonia'] },
        { name: 'Guatemala', states: ['Alta Verapaz','Baja Verapaz','Chimaltenango','Chiquimula','El Progreso','Escuintla','Guatemala','Huehuetenango','Izabal','Jalapa','Jutiapa','Peten','Quetzaltenango','Quiche','Retalhuleu','Sacatepequez','San Marcos','Santa Rosa','Solola','Suchitepequez','Totonicapan','Zacapa'] },
        { name: 'Honduras', states: ['Atlantida','Choluteca','Colon','Comayagua','Copan','Cortes','El Paraiso','Francisco Morazan','Gracias a Dios','Intibuca','Islas de la Bahia','La Paz','Lempira','Ocotepeque','Olancho','Santa Barbara','Valle','Yoro'] },
        { name: 'Hungary', states: ['Baranya','Bacs-Kiskun','Bekes','Borsod-Abauj-Zemplen','Budapest','Csongrad-Csanad','Fejer','Gyor-Moson-Sopron','Hajdu-Bihar','Heves','Jasz-Nagykun-Szolnok','Komarom-Esztergom','Nograd','Pest','Somogy','Szabolcs-Szatmar-Bereg','Tolna','Vas','Veszprem','Zala'] },
        { name: 'Iceland', states: ['Capital Region','Eastern','Northwestern','Northern','Southern','Southern Peninsula','Western','Westfjords'] },
        { name: 'India', states: ['Andaman and Nicobar Islands','Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chandigarh','Chhattisgarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jammu and Kashmir','Jharkhand','Karnataka','Kerala','Ladakh','Lakshadweep','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Puducherry','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'] },
        { name: 'Indonesia', states: ['Aceh','Bali','Bangka Belitung Islands','Banten','Bengkulu','Central Java','Central Kalimantan','Central Sulawesi','East Java','East Kalimantan','East Nusa Tenggara','Gorontalo','Jakarta','Jambi','Lampung','Maluku','North Kalimantan','North Maluku','North Sulawesi','North Sumatra','Riau','Riau Islands','South Kalimantan','South Sulawesi','South Sumatra','Southeast Sulawesi','West Java','West Kalimantan','West Nusa Tenggara','West Papua','West Sulawesi','West Sumatra','Yogyakarta'] },
        { name: 'Iran', states: ['Alborz','Ardabil','Bushehr','Chaharmahal and Bakhtiari','East Azerbaijan','Fars','Gilan','Golestan','Hamadan','Hormozgan','Ilam','Isfahan','Kerman','Kermanshah','Khuzestan','Kohgiluyeh and Boyer-Ahmad','Kurdistan','Lorestan','Markazi','Mazandaran','North Khorasan','Qom','Razavi Khorasan','Semnan','Sistan and Baluchestan','South Khorasan','Tehran','West Azerbaijan','Yazd','Zanjan'] },
        { name: 'Iraq', states: ['Al Anbar','Al Muthanna','Al Qadisiyyah','An Najaf','Babylon','Baghdad','Basra','Dhi Qar','Diyala','Dohuk','Erbil','Halabja','Karbala','Kirkuk','Maysan','Ninewa','Saladin','Sulaymaniyah','Wasit'] },
        { name: 'Ireland', states: ['Carlow','Cavan','Clare','Cork','Donegal','Dublin','Galway','Kerry','Kildare','Kilkenny','Laois','Leitrim','Limerick','Longford','Louth','Mayo','Meath','Monaghan','Offaly','Roscommon','Sligo','Tipperary','Waterford','Westmeath','Wexford','Wicklow'] },
        { name: 'Israel', states: ['Central','Haifa','Jerusalem','Northern','Southern','Tel Aviv'] },
        { name: 'Italy', states: ['Abruzzo','Aosta Valley','Apulia','Basilicata','Calabria','Campania','Emilia-Romagna','Friuli-Venezia Giulia','Lazio','Liguria','Lombardy','Marche','Molise','Piedmont','Sardinia','Sicily','Trentino-Alto Adige','Tuscany','Umbria','Veneto'] },
        { name: 'Jamaica', states: ['Clarendon','Hanover','Kingston','Manchester','Portland','Saint Andrew','Saint Ann','Saint Catherine','Saint Elizabeth','Saint James','Saint Mary','Saint Thomas','Trelawny','Westmoreland'] },
        { name: 'Japan', states: ['Aichi','Akita','Aomori','Chiba','Ehime','Fukui','Fukuoka','Fukushima','Gifu','Gunma','Hiroshima','Hokkaido','Hyogo','Ibaraki','Ishikawa','Iwate','Kagawa','Kagoshima','Kanagawa','Kochi','Kumamoto','Kyoto','Mie','Miyagi','Miyazaki','Nagano','Nagasaki','Nara','Niigata','Oita','Okayama','Okinawa','Osaka','Saga','Saitama','Shiga','Shimane','Shizuoka','Tochigi','Tokushima','Tokyo','Tottori','Toyama','Wakayama','Yamagata','Yamaguchi','Yamanashi'] },
        { name: 'Jordan', states: ['Ajloun','Aqaba','Balqa','Irbid','Jarash','Karak','Maan','Madaba','Mafraq','Tafilah','Zarqa'] },
        { name: 'Kazakhstan', states: ['Akmola','Aktobe','Almaty','Almaty City','Atyrau','East Kazakhstan','Jambyl','Karaganda','Kostanay','Kyzylorda','Mangystau','North Kazakhstan','Nur-Sultan','Pavlodar','Shymkent','West Kazakhstan'] },
        { name: 'Kenya', states: ['Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit','Meru','Migori','Mombasa','Muranga','Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu','Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia','Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot'] },
        { name: 'Kuwait', states: ['Ahmadi','Al Asimah','Farwaniya','Hawalli','Jahra','Mubarak Al-Kabeer'] },
        { name: 'Laos', states: ['Attapeu','Bokeo','Bolikhamsai','Champasak','Houaphanh','Khammouane','Luang Namtha','Luang Prabang','Oudomxay','Phongsali','Salavan','Savannakhet','Vientiane','Vientiane Prefecture','Xekong','Xiengkhouang'] },
        { name: 'Latvia', states: ['Aizkraukle','Aluksne','Balvi','Bauska','Cesis','Daugavpils','Dobele','Gulbene','Jekabpils','Jelgava','Jurmala','Kraslava','Kuldiga','Liepaja','Limbazi','Ludza','Madona','Ogre','Rezekne','Riga','Saldus','Talsi','Tukums','Valmiera','Ventspils'] },
        { name: 'Lebanon', states: ['Akkar','Baalbek-Hermel','Beirut','Beqaa','Mount Lebanon','Nabatieh','North Lebanon','South Lebanon'] },
        { name: 'Libya', states: ['Al Butnan','Al Jabal al Akhdar','Al Jabal al Gharbi','Al Jafara','Al Jufra','Al Kufra','Al Marj','Al Marqab','Al Murzuq','Benghazi','Derna','Misrata','Sabha','Sirte','Tripoli','Wadi al Hayaa','Zuwarah'] },
        { name: 'Lithuania', states: ['Alytus','Kaunas','Klaipeda','Marijampole','Panevezys','Siauliai','Taurage','Telsiai','Utena','Vilnius'] },
        { name: 'Luxembourg', states: ['Capellen','Clervaux','Diekirch','Echternach','Esch-sur-Alzette','Grevenmacher','Luxembourg','Mersch','Redange','Remich','Vianden','Wiltz'] },
        { name: 'Madagascar', states: ['Alaotra-Mangoro','Amoron i Mania','Analamanga','Analanjirofo','Androy','Anosy','Atsimo-Andrefana','Atsimo-Atsinanana','Atsinanana','Betsiboka','Boeny','Bongolava','Diana','Haute Matsiatra','Ihorombe','Itasy','Melaky','Menabe','Sava','Sofia','Vakinankaratra','Vatovavy-Fitovinany'] },
        { name: 'Malawi', states: ['Balaka','Blantyre','Chikwawa','Chiradzulu','Chitipa','Dedza','Dowa','Karonga','Kasungu','Lilongwe','Machinga','Mangochi','Mchinji','Mulanje','Mwanza','Mzimba','Nkhata Bay','Nkhotakota','Nsanje','Ntcheu','Ntchisi','Phalombe','Rumphi','Salima','Thyolo','Zomba'] },
        { name: 'Malaysia', states: ['Johor','Kedah','Kelantan','Kuala Lumpur','Labuan','Malacca','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Putrajaya','Sabah','Sarawak','Selangor','Terengganu'] },
        { name: 'Mali', states: ['Bamako','Gao','Kayes','Kidal','Koulikoro','Mopti','Menaka','Segou','Sikasso','Taoudenit','Tombouctou'] },
        { name: 'Malta', states: ['Gozo','Malta'] },
        { name: 'Mauritius', states: ['Black River','Flacq','Grand Port','Moka','Pamplemousses','Plaines Wilhems','Port Louis','Riviere du Rempart','Rodrigues','Savanne'] },
        { name: 'Mexico', states: ['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Coahuila','Colima','Durango','Guanajuato','Guerrero','Hidalgo','Jalisco','Mexico City','Mexico State','Michoacan','Morelos','Nayarit','Nuevo Leon','Oaxaca','Puebla','Queretaro','Quintana Roo','San Luis Potosi','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatan','Zacatecas'] },
        { name: 'Moldova', states: ['Anenii Noi','Basarabeasca','Briceni','Cahul','Calarasi','Cantemir','Causeni','Cimislia','Criuleni','Donduseni','Drochia','Dubasari','Edinet','Falesti','Floresti','Gagauzia','Glodeni','Hincesti','Ialoveni','Leova','Nisporeni','Ocnita','Orhei','Rezina','Riscani','Singerei','Soldanesti','Soroca','Straseni','Stefan Voda','Taraclia','Telenesti','Transnistria','Ungheni'] },
        { name: 'Mongolia', states: ['Arkhangai','Bayan-Olgii','Bayankhongor','Bulgan','Darkhan-Uul','Dornod','Dornogovi','Dundgovi','Govi-Altai','Govisumber','Khentii','Khovd','Khovsgol','Orkhon','Ovorkhangi','Selenge','Sukhbaatar','Tuv','Ulaanbaatar','Uvs','Zavkhan'] },
        { name: 'Montenegro', states: ['Andrijevica','Bar','Berane','Bijelo Polje','Budva','Cetinje','Danilovgrad','Gusinje','Herceg Novi','Kolashin','Kotor','Mojkovac','Niksic','Petnjica','Plav','Pljevlja','Pluzine','Podgorica','Rozaje','Savnik','Tivat','Tuzi','Ulcinj','Zablak','Zeta'] },
        { name: 'Morocco', states: ['Beni Mellal-Khenifra','Casablanca-Settat','Dakhla-Oued Ed-Dahab','Draa-Tafilalet','Fes-Meknes','Guelmim-Oued Noun','Laayoune-Sakia El Hamra','Marrakesh-Safi','Oriental','Rabat-Sale-Kenitra','Souss-Massa','Tanger-Tetouan-Al Hoceima'] },
        { name: 'Mozambique', states: ['Cabo Delgado','Gaza','Inhambane','Manica','Maputo','Maputo City','Nampula','Niassa','Sofala','Tete','Zambezia'] },
        { name: 'Myanmar', states: ['Ayeyarwady','Bago','Chin','Kachin','Kayah','Kayin','Magway','Mandalay','Mon','Naypyidaw','Rakhine','Sagaing','Shan','Tanintharyi','Yangon'] },
        { name: 'Namibia', states: ['Erongo','Hardap','Karas','Kavango East','Kavango West','Khomas','Kunene','Ohangwena','Omaheke','Omusati','Oshana','Oshikoto','Otjozondjupa','Zambezi'] },
        { name: 'Nepal', states: ['Bagmati','Gandaki','Karnali','Koshi','Lumbini','Madhesh','Sudurpashchim'] },
        { name: 'Netherlands', states: ['Drenthe','Flevoland','Friesland','Gelderland','Groningen','Limburg','North Brabant','North Holland','Overijssel','South Holland','Utrecht','Zeeland'] },
        { name: 'New Zealand', states: ['Auckland','Bay of Plenty','Canterbury','Gisborne','Hawkes Bay','Manawatu-Wanganui','Marlborough','Nelson','Northland','Otago','Southland','Taranaki','Tasman','Waikato','Wellington','West Coast'] },
        { name: 'Nicaragua', states: ['Boaco','Carazo','Chinandega','Chontales','Esteli','Granada','Jinotega','Leon','Madriz','Managua','Masaya','Matagalpa','Nueva Segovia','RACCN','RACCS','Rio San Juan','Rivas'] },
        { name: 'Niger', states: ['Agadez','Diffa','Dosso','Maradi','Niamey','Tahoua','Tillaberi','Zinder'] },
        { name: 'Nigeria', states: ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT - Abuja','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'] },
        { name: 'Norway', states: ['Agder','Innlandet','More og Romsdal','Nordland','Oslo','Rogaland','Troms og Finnmark','Trondelag','Vestfold og Telemark','Vestland','Viken'] },
        { name: 'Oman', states: ['Ad Dakhiliyah','Ad Dhahirah','Al Batinah North','Al Batinah South','Al Buraymi','Al Wusta','Ash Sharqiyah North','Ash Sharqiyah South','Dhofar','Musandam','Muscat'] },
        { name: 'Pakistan', states: ['Azad Kashmir','Balochistan','Gilgit-Baltistan','Islamabad Capital Territory','Khyber Pakhtunkhwa','Punjab','Sindh'] },
        { name: 'Palestine', states: ['Gaza Strip','West Bank'] },
        { name: 'Panama', states: ['Bocas del Toro','Chiriqui','Cocle','Colon','Darien','Embera','Guna Yala','Herrera','Los Santos','Ngabe-Bugle','Panama','Panama Oeste','Veraguas'] },
        { name: 'Papua New Guinea', states: ['Bougainville','Central','Chimbu','East New Britain','East Sepik','Eastern Highlands','Enga','Gulf','Hela','Jiwaka','Madang','Manus','Milne Bay','Morobe','National Capital','New Ireland','Northern','Sandaun','Southern Highlands','West New Britain','West Sepik','Western','Western Highlands'] },
        { name: 'Paraguay', states: ['Alto Paraguay','Alto Parana','Amambay','Asuncion','Boqueron','Caaguazu','Caazapa','Canindeyu','Central','Concepcion','Cordillera','Guaira','Itapua','Misiones','Neembucu','Paraguari','Presidente Hayes','San Pedro'] },
        { name: 'Peru', states: ['Amazonas','Ancash','Apurimac','Arequipa','Ayacucho','Cajamarca','Callao','Cusco','Huancavelica','Huanuco','Ica','Junin','La Libertad','Lambayeque','Lima','Loreto','Madre de Dios','Moquegua','Pasco','Piura','Puno','San Martin','Tacna','Tumbes','Ucayali'] },
        { name: 'Philippines', states: ['Abra','Agusan del Norte','Agusan del Sur','Aklan','Albay','Antique','Apayao','Aurora','Basilan','Bataan','Batanes','Batangas','Benguet','Biliran','Bohol','Bukidnon','Bulacan','Cagayan','Camarines Norte','Camarines Sur','Camiguin','Capiz','Catanduanes','Cavite','Cebu','Cotabato','Davao de Oro','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental','Dinagat Islands','Eastern Samar','Guimaras','Ifugao','Ilocos Norte','Ilocos Sur','Iloilo','Isabela','Kalinga','La Union','Laguna','Lanao del Norte','Lanao del Sur','Leyte','Metro Manila','Misamis Occidental','Misamis Oriental','Mountain Province','Negros Occidental','Negros Oriental','Northern Samar','Nueva Ecija','Nueva Vizcaya','Occidental Mindoro','Oriental Mindoro','Palawan','Pampanga','Pangasinan','Quezon','Quirino','Rizal','Romblon','Samar','Sarangani','Siquijor','Sorsogon','South Cotabato','Southern Leyte','Sultan Kudarat','Sulu','Surigao del Norte','Surigao del Sur','Tarlac','Tawi-Tawi','Zambales','Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay'] },
        { name: 'Poland', states: ['Greater Poland','Holy Cross','Kuyavian-Pomeranian','Lesser Poland','Lodz','Lower Silesian','Lublin','Lubusz','Masovian','Opole','Podlaskie','Pomeranian','Silesian','Subcarpathian','Warmian-Masurian','West Pomeranian'] },
        { name: 'Portugal', states: ['Aveiro','Azores','Beja','Braga','Braganca','Castelo Branco','Coimbra','Evora','Faro','Guarda','Leiria','Lisbon','Madeira','Portalegre','Porto','Santarem','Setubal','Viana do Castelo','Vila Real','Viseu'] },
        { name: 'Qatar', states: ['Ad Dawhah','Al Daayen','Al Khor','Al Rayyan','Al Shamal','Al Wakrah','Umm Slal'] },
        { name: 'Romania', states: ['Alba','Arad','Arges','Bacau','Bihor','Bistrita-Nasaud','Botosani','Braila','Brasov','Bucharest','Buzau','Calaras','Caras-Severin','Cluj','Constanta','Covasna','Dambovita','Dolj','Galati','Giurgiu','Gorj','Harghita','Hunedoara','Ialomita','Iasi','Ilfov','Maramures','Mehedinti','Mures','Neamt','Olt','Prahova','Salaj','Satu Mare','Sibiu','Suceava','Teleorman','Timis','Tulcea','Valcea','Vaslui','Vrancea'] },
        { name: 'Russia', states: ['Adygea','Altai Krai','Altai Republic','Amur Oblast','Arkhangelsk Oblast','Astrakhan Oblast','Bashkortostan','Belgorod Oblast','Bryansk Oblast','Buryatia','Chechen Republic','Chelyabinsk Oblast','Chukotka Autonomous Okrug','Chuvashia','Dagestan','Ingushetia','Irkutsk Oblast','Ivanovo Oblast','Kabardino-Balkaria','Kaliningrad Oblast','Kalmykia','Kaluga Oblast','Kamchatka Krai','Karachay-Cherkessia','Karelia','Kemerovo Oblast','Khabarovsk Krai','Khakassia','Kirov Oblast','Komi Republic','Kostroma Oblast','Krasnodar Krai','Krasnoyarsk Krai','Kurgan Oblast','Kursk Oblast','Leningrad Oblast','Lipetsk Oblast','Magadan Oblast','Mari El','Mordovia','Moscow','Moscow Oblast','Murmansk Oblast','Nizhny Novgorod Oblast','North Ossetia','Novgorod Oblast','Novosibirsk Oblast','Omsk Oblast','Orenburg Oblast','Oryol Oblast','Penza Oblast','Perm Krai','Primorsky Krai','Pskov Oblast','Rostov Oblast','Ryazan Oblast','Saint Petersburg','Sakha Republic','Sakhalin Oblast','Samara Oblast','Saratov Oblast','Smolensk Oblast','Stavropol Krai','Sverdlovsk Oblast','Tambov Oblast','Tatarstan','Tomsk Oblast','Tula Oblast','Tuva','Tver Oblast','Tyumen Oblast','Udmurtia','Ulyanovsk Oblast','Vladimir Oblast','Volgograd Oblast','Vologda Oblast','Voronezh Oblast','Yamalo-Nenets Autonomous Okrug','Yaroslavl Oblast','Zabaykalsky Krai'] },
        { name: 'Rwanda', states: ['Eastern','Kigali','Northern','Southern','Western'] },
        { name: 'Saudi Arabia', states: ['Al Bahah','Al Jawf','Al Madinah','Al Qassim','Asir','Eastern Province','Hail','Jizan','Makkah','Najran','Northern Borders','Riyadh','Tabuk'] },
        { name: 'Senegal', states: ['Dakar','Diourbel','Fatick','Kaffrine','Kaolack','Kedougou','Kolda','Louga','Matam','Saint-Louis','Sedhiou','Tambacounda','Thies','Ziguinchor'] },
        { name: 'Serbia', states: ['Belgrade','Bor','Branicevo','Central Banat','Jablanica','Kolubara','Macva','Moravica','Nisava','North Backa','North Banat','Pceinja','Pirot','Podunavlje','Pomoravlje','Rasina','Raska','South Backa','South Banat','Srem','Sumadija','Toplica','West Backa','Zajecar','Zlatibor'] },
        { name: 'Singapore', states: ['Central Region','East Region','North Region','North-East Region','West Region'] },
        { name: 'Slovakia', states: ['Banska Bystrica','Bratislava','Kosice','Nitra','Presov','Trencin','Trnava','Zilina'] },
        { name: 'Somalia', states: ['Awdal','Bakool','Banaadir','Bari','Bay','Galguduud','Gedo','Hiiraan','Jubbada Dhexe','Jubbada Hoose','Mudug','Nugaal','Sanaag','Togdheer','Woqooyi Galbeed'] },
        { name: 'South Africa', states: ['Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga','North West','Northern Cape','Western Cape'] },
        { name: 'South Korea', states: ['Busan','Chungbuk','Chungnam','Daegu','Daejeon','Gangwon','Gwangju','Gyeongbuk','Gyeonggi','Gyeongnam','Incheon','Jeju','Jeonbuk','Jeonnam','Sejong','Seoul','Ulsan'] },
        { name: 'South Sudan', states: ['Central Equatoria','Eastern Equatoria','Jonglei','Lakes','Northern Bahr el Ghazal','Unity','Upper Nile','Warrap','Western Bahr el Ghazal','Western Equatoria'] },
        { name: 'Spain', states: ['Andalusia','Aragon','Asturias','Balearic Islands','Basque Country','Canary Islands','Cantabria','Castile and Leon','Castilla-La Mancha','Catalonia','Ceuta','Extremadura','Galicia','La Rioja','Madrid','Melilla','Murcia','Navarre','Valencia'] },
        { name: 'Sri Lanka', states: ['Central','Eastern','North Central','Northern','North Western','Sabaragamuwa','Southern','Uva','Western'] },
        { name: 'Sudan', states: ['Al Gedaref','Al Jazirah','Blue Nile','Central Darfur','East Darfur','Kassala','Khartoum','North Darfur','North Kordofan','Northern','Red Sea','River Nile','Sennar','South Darfur','South Kordofan','West Darfur','West Kordofan','White Nile'] },
        { name: 'Sweden', states: ['Blekinge','Dalarna','Gavleborg','Gotland','Halland','Jamtland','Jonkoping','Kalmar','Kronoberg','Norrbotten','Orebro','Ostergotland','Skane','Sodermanland','Stockholm','Uppsala','Varmland','Vasterbotten','Vasternorrland','Vastmanland','Vastra Gotaland'] },
        { name: 'Switzerland', states: ['Aargau','Appenzell Ausserrhoden','Appenzell Innerrhoden','Basel-Landschaft','Basel-Stadt','Bern','Fribourg','Geneva','Glarus','Graubunden','Jura','Lucerne','Neuchatel','Nidwalden','Obwalden','Schaffhausen','Schwyz','Solothurn','St. Gallen','Thurgau','Ticino','Uri','Valais','Vaud','Zug','Zurich'] },
        { name: 'Syria', states: ['Al-Hasakah','Al-Raqqah','Aleppo','As-Suwayda','Damascus','Daraa','Deir ez-Zor','Hama','Homs','Idlib','Latakia','Quneitra','Rif Dimashq','Tartus'] },
        { name: 'Taiwan', states: ['Changhua','Chiayi City','Chiayi County','Hsinchu City','Hsinchu County','Hualien','Kaohsiung','Keelung','Kinmen','Lienchiang','Miaoli','Nantou','New Taipei','Penghu','Pingtung','Taichung','Tainan','Taipei','Taitung','Taoyuan','Yilan','Yunlin'] },
        { name: 'Tajikistan', states: ['Districts of Republican Subordination','Gorno-Badakhshan','Khatlon','Sughd'] },
        { name: 'Tanzania', states: ['Arusha','Dar es Salaam','Dodoma','Geita','Iringa','Kagera','Katavi','Kigoma','Kilimanjaro','Lindi','Manyara','Mara','Mbeya','Morogoro','Mtwara','Mwanza','Njombe','Pwani','Rukwa','Ruvuma','Shinyanga','Simiyu','Singida','Songwe','Tabora','Tanga','Zanzibar'] },
        { name: 'Thailand', states: ['Amnat Charoen','Ang Thong','Bangkok','Bueng Kan','Buriram','Chachoengsao','Chai Nat','Chaiyaphum','Chanthaburi','Chiang Mai','Chiang Rai','Chon Buri','Chumphon','Kalasin','Kamphaeng Phet','Kanchanaburi','Khon Kaen','Krabi','Lampang','Lamphun','Loei','Lop Buri','Mae Hong Son','Maha Sarakham','Mukdahan','Nakhon Nayok','Nakhon Pathom','Nakhon Phanom','Nakhon Ratchasima','Nakhon Sawan','Nakhon Si Thammarat','Nan','Narathiwat','Nong Bua Lam Phu','Nong Khai','Nonthaburi','Pathum Thani','Pattani','Phang Nga','Phatthalung','Phayao','Phetchabun','Phetchaburi','Phichit','Phitsanulok','Phra Nakhon Si Ayutthaya','Phrae','Phuket','Prachin Buri','Prachuap Khiri Khan','Ranong','Ratchaburi','Rayong','Roi Et','Sa Kaeo','Sakon Nakhon','Samut Prakan','Samut Sakhon','Samut Songkhram','Saraburi','Satun','Sing Buri','Sisaket','Songkhla','Sukhothai','Suphan Buri','Surat Thani','Surin','Tak','Trang','Trat','Ubon Ratchathani','Udon Thani','Uthai Thani','Uttaradit','Yala','Yasothon'] },
        { name: 'Timor-Leste', states: ['Aileu','Ainaro','Baucau','Bobonaro','Covalima','Dili','Ermera','Lautem','Liquica','Manatuto','Manufahi','Oecusse','Viqueque'] },
        { name: 'Trinidad and Tobago', states: ['Arima','Chaguanas','Couva-Tabaquite-Talparo','Diego Martin','Eastern Tobago','Penal-Debe','Point Fortin','Port of Spain','Princes Town','Rio Claro-Mayaro','San Fernando','San Juan-Laventille','Sangre Grande','Siparia','Tunapuna-Piarco','Western Tobago'] },
        { name: 'Tunisia', states: ['Ariana','Beja','Ben Arous','Bizerte','Gabes','Gafsa','Jendouba','Kairouan','Kasserine','Kebili','Kef','Mahdia','Manouba','Medenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'] },
        { name: 'Turkey', states: ['Adana','Adiyaman','Afyonkarahisar','Agri','Aksaray','Amasya','Ankara','Antalya','Ardahan','Artvin','Aydin','Balikesir','Bartin','Batman','Bayburt','Bilecik','Bingol','Bitlis','Bolu','Burdur','Bursa','Canakkale','Cankiri','Corum','Denizli','Diyarbakir','Duzce','Edirne','Elazig','Erzincan','Erzurum','Eskisehir','Gaziantep','Giresun','Gumushane','Hakkari','Hatay','Igdir','Isparta','Istanbul','Izmir','Kahramanmaras','Karabuk','Karaman','Kars','Kastamonu','Kayseri','Kilis','Kirikkale','Kirklareli','Kirsehir','Kocaeli','Konya','Kutahya','Malatya','Manisa','Mardin','Mersin','Mugla','Mus','Nevsehir','Nigde','Ordu','Osmaniye','Rize','Sakarya','Samsun','Sanliurfa','Siirt','Sinop','Sirnak','Sivas','Tekirdag','Tokat','Trabzon','Tunceli','Usak','Van','Yalova','Yozgat','Zonguldak'] },
        { name: 'Turkmenistan', states: ['Ahal','Ashgabat','Balkan','Dashoguz','Lebap','Mary'] },
        { name: 'Uganda', states: ['Kampala','Central','Eastern','Northern','Western'] },
        { name: 'Ukraine', states: ['Cherkasy','Chernihiv','Chernivtsi','Dnipropetrovsk','Donetsk','Ivano-Frankivsk','Kharkiv','Kherson','Khmelnytskyi','Kirovohrad','Kyiv','Kyiv City','Luhansk','Lviv','Mykolaiv','Odessa','Poltava','Rivne','Sumy','Ternopil','Vinnytsia','Volyn','Zakarpattia','Zaporizhia','Zhytomyr'] },
        { name: 'United Arab Emirates', states: ['Abu Dhabi','Ajman','Dubai','Fujairah','Ras al-Khaimah','Sharjah','Umm al-Quwain'] },
        { name: 'Uruguay', states: ['Artigas','Canelones','Cerro Largo','Colonia','Durazno','Flores','Florida','Lavalleja','Maldonado','Montevideo','Paysandu','Rio Negro','Rivera','Rocha','Salto','San Jose','Soriano','Tacuarembo','Treinta y Tres'] },
        { name: 'Uzbekistan', states: ['Andijan','Bukhara','Fergana','Jizzakh','Karakalpakstan','Kashkadarya','Khorezm','Namangan','Navoiy','Samarkand','Sirdaryo','Surkhandarya','Tashkent'] },
        { name: 'Venezuela', states: ['Amazonas','Anzoategui','Apure','Aragua','Barinas','Bolivar','Carabobo','Capital District','Cojedes','Delta Amacuro','Falcon','Guarico','Lara','Merida','Miranda','Monagas','Nueva Esparta','Portuguesa','Sucre','Tachira','Trujillo','Vargas','Yaracuy','Zulia'] },
        { name: 'Vietnam', states: ['An Giang','Ba Ria-Vung Tau','Bac Giang','Bac Kan','Bac Lieu','Bac Ninh','Ben Tre','Binh Dinh','Binh Duong','Binh Phuoc','Binh Thuan','Ca Mau','Can Tho','Cao Bang','Da Nang','Dak Lak','Dak Nong','Dien Bien','Dong Nai','Dong Thap','Gia Lai','Ha Giang','Ha Nam','Ha Tinh','Hai Duong','Hai Phong','Hanoi','Hau Giang','Ho Chi Minh City','Hoa Binh','Hung Yen','Khanh Hoa','Kien Giang','Kon Tum','Lai Chau','Lam Dong','Lang Son','Lao Cai','Long An','Nam Dinh','Nghe An','Ninh Binh','Ninh Thuan','Phu Tho','Phu Yen','Quang Binh','Quang Nam','Quang Ngai','Quang Ninh','Quang Tri','Soc Trang','Son La','Tay Ninh','Thai Binh','Thai Nguyen','Thanh Hoa','Thua Thien Hue','Tien Giang','Tra Vinh','Tuyen Quang','Vinh Long','Vinh Phuc','Yen Bai'] },
        { name: 'Yemen', states: ['Abyan','Aden','Al Bayda','Al Dhale e','Al Hudaydah','Al Jawf','Al Mahrah','Al Mahwit','Amran','Dhamar','Hadramawt','Hajjah','Ibb','Lahij','Marib','Raymah','Saada','Sanaa','Shabwah','Socotra','Taiz'] },
        { name: 'Zambia', states: ['Central','Copperbelt','Eastern','Luapula','Lusaka','Muchinga','Northern','North-Western','Southern','Western'] },
        { name: 'Zimbabwe', states: ['Bulawayo','Harare','Manicaland','Mashonaland Central','Mashonaland East','Mashonaland West','Masvingo','Matabeleland North','Matabeleland South','Midlands'] }
    ];

    function ddPopulateCountries($sel, savedValue) {
        if ($sel.find('option').length > 1) {
            if (savedValue) {
                $sel.val(savedValue);
            }
            return;
        }
        $sel.find('option:not(:first)').remove();
        for (var i = 0; i < DD_COUNTRIES.length; i++) {
            var opt = document.createElement('option');
            opt.value = DD_COUNTRIES[i].name;
            opt.textContent = DD_COUNTRIES[i].name;
            if (DD_COUNTRIES[i].name === savedValue) { opt.selected = true; }
            $sel[0].appendChild(opt);
        }
    }

    function ddPopulateStates($stateSel, countryName, savedState) {
        var firstOpt = $stateSel.find('option:first');
        var placeholderText = firstOpt.length ? firstOpt.text() : 'All States';
        $stateSel.empty();
        $stateSel.append($('<option>', { value: '', text: placeholderText }));

        var country = null;
        if (countryName) {
            for (var i = 0; i < DD_COUNTRIES.length; i++) {
                if (DD_COUNTRIES[i].name === countryName) { country = DD_COUNTRIES[i]; break; }
            }
        } else {
            // Default to United States states when no country is picked
            for (var i = 0; i < DD_COUNTRIES.length; i++) {
                if (DD_COUNTRIES[i].name === 'United States') { country = DD_COUNTRIES[i]; break; }
            }
        }

        if (country && country.states && country.states.length) {
            for (var j = 0; j < country.states.length; j++) {
                var opt = document.createElement('option');
                opt.value = country.states[j];
                opt.textContent = country.states[j];
                if (country.states[j] === savedState) { opt.selected = true; }
                $stateSel[0].appendChild(opt);
            }
            $stateSel.prop('disabled', false);
        } else {
            // If country has no states, keep enabled with only placeholder
            $stateSel.prop('disabled', false);
        }
        if ($.fn.select2 && $stateSel.hasClass('select2-hidden-accessible')) {
            $stateSel.trigger('change.select2');
        }
    }

    function ddInitCountrySelects() {
        $('.dd-country-select').each(function () {
            var $countrySel  = $(this);
            var targetId     = $countrySel.data('state-target');
            var $stateSel    = targetId
                ? $('#' + targetId)
                : $countrySel.closest('form, .dd-dog-form__grid, .dd-form').find('.dd-state-select').first();

            var savedCountry = ($countrySel.data('saved-country') || $countrySel.val() || '').toString().trim();
            var savedState   = ($stateSel.data('saved') || $stateSel.val() || '').toString().trim();

            /* Populate countries */
            ddPopulateCountries($countrySel, savedCountry);

            /* Init Select2 on country ONLY if specified by class */
            var useSelect2 = $countrySel.hasClass('select2') || $countrySel.hasClass('dd-searchable-select');
            if ($.fn.select2 && useSelect2) {
                $countrySel.select2({ placeholder: 'Select Country', allowClear: true, width: '100%' });
            }

            /* Pre-fill states if we have a saved country or if state options are missing */
            if (savedCountry) {
                ddPopulateStates($stateSel, savedCountry, savedState);
            } else if ($stateSel.find('option').length <= 1) {
                ddPopulateStates($stateSel, '', savedState);
            }

            /* Init Select2 on state ONLY if specified by class */
            var useStateSelect2 = $stateSel.hasClass('select2') || $stateSel.hasClass('dd-searchable-select');
            if ($.fn.select2 && useStateSelect2) {
                $stateSel.select2({ placeholder: 'Select State / Province', allowClear: true, width: '100%' });
            }

            /* On country change → repopulate states */
            $countrySel.off('change.dd_country').on('change.dd_country', function () {
                var selected = $(this).val() || '';
                ddPopulateStates($stateSel, selected, '');
                $countrySel.closest('form, .dd-dog-form__grid').find('#dd-dog-country-hidden, #dd-profile-country-hidden').val(selected);
                if ($.fn.select2 && useStateSelect2) {
                    $stateSel.trigger('change.select2');
                }
            });
        });
    }

    $(document).ready(function () {
        ddInitCountrySelects();

        // Auto capitalize first letter only on City fields
        $(document).on('input blur', '#dd-city, #dd-profile-city, #dd-reg-city, input[name="dog_data[city]"], input[name="city"]', function () {
            var el = this;
            var val = el.value;
            if (val && val.length > 0) {
                var formatted = val.replace(/(?:^|\s|-)([a-z])/g, function (match) {
                    return match.toUpperCase();
                });
                if (formatted !== val) {
                    var start = el.selectionStart;
                    var end = el.selectionEnd;
                    el.value = formatted;
                    if (el.setSelectionRange && start !== null) {
                        try { el.setSelectionRange(start, end); } catch (e) {}
                    }
                }
            }
        });

        // Delegate clicks on country/state wrappers to focus/open the select
        $(document).on('click', '.rtin-country-space .form-group, .rtin-state-space .form-group', function (e) {
            // If Select2 container was clicked, let Select2 handle it naturally
            if ($(e.target).closest('.select2-container').length) {
                return;
            }
            var $sel = $(this).find('select');
            if ($sel.length) {
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('open');
                    return;
                }
                if (e.target.tagName !== 'SELECT') {
                    $sel.focus();
                    if (typeof $sel[0].showPicker === 'function') {
                        try { $sel[0].showPicker(); } catch (err) {}
                    }
                }
            }
        });
    });

    // Expose globally so dashboard.js can call it on tab changes
    window.ddInitCountrySelects = ddInitCountrySelects;

})(jQuery);
