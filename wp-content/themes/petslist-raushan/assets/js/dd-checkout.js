/**
 * Dog Directory — Stripe Checkout
 * @package Petslist Dog Directory
 */
(function ($) {
  'use strict';

  // ── Promo / Coupon Code Handlers ────────────────────────────────
  $(document).on('click', '#dd-apply-coupon-btn', function (e) {
    e.preventDefault();
    var code = $.trim($('#dd-coupon-input').val());
    var plan = $('input[name=plan]').val() || '';
    var $btn = $(this);
    var $msg = $('#dd-coupon-msg');

    if (!code) {
      $msg.css('color', '#dc2626').text('Please enter a coupon code.').show();
      return;
    }

    $btn.prop('disabled', true);
    $btn.find('span').first().hide();
    $btn.find('.dd-btn__loader').show();
    $msg.hide();

    var nonce = (typeof ddCheckout !== 'undefined' && ddCheckout.nonce) ? ddCheckout.nonce : '';
    var ajaxUrl = (typeof ddCheckout !== 'undefined' && ddCheckout.ajaxUrl) ? ddCheckout.ajaxUrl : '/wp-admin/admin-ajax.php';

    $.post(ajaxUrl, {
      action: 'dd_apply_coupon',
      code: code,
      plan: plan,
      nonce: nonce
    }, function (res) {
      $btn.prop('disabled', false);
      $btn.find('span').first().show();
      $btn.find('.dd-btn__loader').hide();

      if (res.success && res.data) {
        $msg.css('color', '#16a34a').text('✅ ' + (res.data.message || 'Coupon applied successfully!')).show();
        $('#dd-discount-row').show();
        $('#dd-discount-code-badge').text(res.data.code);
        $('#dd-discount-val').text('-$' + parseFloat(res.data.discount_amount || 0).toFixed(2));
        $('#dd-checkout-total-val').text('$' + parseFloat(res.data.new_total || 0).toFixed(2));

        if (res.data.is_free) {
          $('#dd-paid-gateways-wrap').slideUp(200);
          $('#dd-free-activation-card').slideDown(250);
          $('#dd-claim-free-btn').data('code', res.data.code);
        }
      } else {
        $msg.css('color', '#dc2626').text('⚠️ ' + (res.data && res.data.message ? res.data.message : 'Invalid promo code.')).show();
      }
    }).fail(function () {
      $btn.prop('disabled', false);
      $btn.find('span').first().show();
      $btn.find('.dd-btn__loader').hide();
      $msg.css('color', '#dc2626').text('⚠️ Server error validating promo code.').show();
    });
  });

  // Allow Enter key to trigger apply coupon
  $(document).on('keypress', '#dd-coupon-input', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#dd-apply-coupon-btn').trigger('click');
    }
  });

  // Remove applied coupon
  $(document).on('click', '#dd-remove-coupon-link', function (e) {
    e.preventDefault();
    $('#dd-coupon-input').val('');
    $('#dd-coupon-msg').hide().empty();
    $('#dd-discount-row').hide();
    $('#dd-discount-code-badge').text('');
    $('#dd-free-activation-card').slideUp(200);
    $('#dd-paid-gateways-wrap').slideDown(250);

    var originalPrice = $('#dd-checkout-subtotal').text();
    $('#dd-checkout-total-val').text(originalPrice);
  });

  // Activate 100% Free Subscription (No credit card needed)
  $(document).on('click', '#dd-claim-free-btn', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var code = $btn.data('code') || $.trim($('#dd-coupon-input').val());
    var plan = $('input[name=plan]').val() || '';
    var $msg = $('#dd-checkout-message');

    $btn.prop('disabled', true);
    $btn.find('span').first().hide();
    $btn.find('.dd-btn__loader').show();
    $msg.hide();

    var nonce = (typeof ddCheckout !== 'undefined' && ddCheckout.nonce) ? ddCheckout.nonce : '';
    var ajaxUrl = (typeof ddCheckout !== 'undefined' && ddCheckout.ajaxUrl) ? ddCheckout.ajaxUrl : '/wp-admin/admin-ajax.php';
    var returnUrl = (typeof ddCheckout !== 'undefined' && ddCheckout.returnUrl) ? ddCheckout.returnUrl : '/my-account/dashboard/?tab=subscription';

    $.post(ajaxUrl, {
      action: 'dd_redeem_free_subscription',
      code: code,
      plan: plan,
      nonce: nonce
    }, function (res) {
      if (res.success) {
        $msg.removeClass('error dd-notice--error').addClass('dd-notice dd-notice--success')
            .html('<span>🎉</span> <span>' + (res.data.message || 'Free subscription activated! Redirecting...') + '</span>')
            .show();
        $('html,body').animate({ scrollTop: $msg.offset().top - 80 }, 300);
        setTimeout(function () {
          window.location.href = res.data.redirect || returnUrl;
        }, 1200);
      } else {
        $btn.prop('disabled', false);
        $btn.find('span').first().show();
        $btn.find('.dd-btn__loader').hide();
        $msg.removeClass('success dd-notice--success').addClass('dd-notice dd-notice--error')
            .html('<span>⚠️</span> <span>' + (res.data && res.data.message ? res.data.message : 'Could not activate subscription.') + '</span>')
            .show();
        $('html,body').animate({ scrollTop: $msg.offset().top - 80 }, 300);
      }
    }).fail(function () {
      $btn.prop('disabled', false);
      $btn.find('span').first().show();
      $btn.find('.dd-btn__loader').hide();
      $msg.removeClass('success dd-notice--success').addClass('dd-notice dd-notice--error')
          .html('<span>⚠️</span> <span>Server connection error. Please try again.</span>')
          .show();
    });
  });

  // ── Stripe Integration (Only if configured) ───────────────────────
  if (typeof Stripe !== 'undefined' && typeof ddCheckout !== 'undefined' && ddCheckout.publishableKey) {
    // Mount Stripe elements ONLY if inline card inputs exist on DOM
    if ($('#dd-card-number').length) {
      var stripe   = Stripe(ddCheckout.publishableKey);
      var elements = stripe.elements();

      var style = {
        base: {
          fontSize:       '15px',
          color:          '#070C3E',
          fontFamily:     '"Plus Jakarta Sans", system-ui, sans-serif',
          '::placeholder': { color: '#adb5bd' },
        },
        invalid: { color: '#ef4444' },
      };

      var cardNumber  = elements.create('cardNumber',  { style: style });
      var cardExpiry  = elements.create('cardExpiry',  { style: style });
      var cardCvc     = elements.create('cardCvc',     { style: style });

      cardNumber.mount('#dd-card-number');
      cardExpiry.mount('#dd-card-expiry');
      cardCvc.mount('#dd-card-cvc');

      // Real-time error display
      [cardNumber, cardExpiry, cardCvc].forEach(function (el) {
        el.on('change', function (e) {
          var $err = $('#dd-card-errors');
          if (e.error) {
            $err.text(e.error.message);
          } else {
            $err.text('');
          }
        });
      });
    }
  }

  // Handle Hosted Stripe Checkout redirect
  $(document).on('submit', '#dd-stripe-hosted-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn  = $('#dd-stripe-checkout-btn');
    $btn.prop('disabled', true);
    $btn.find('span').first().hide();
    $btn.find('.dd-btn__loader').show();

    $.post(ddCheckout.ajaxUrl, {
      action: 'dd_create_stripe_session',
      nonce:  $form.find('[name=nonce]').val() || ddCheckout.nonce,
      plan:   $form.find('[name=plan]').val()
    }, function (res) {
      if (res.success && res.data.checkout_url) {
        window.location.href = res.data.checkout_url;
      } else {
        $btn.prop('disabled', false);
        $btn.find('span').first().show();
        $btn.find('.dd-btn__loader').hide();
        if (typeof DD !== 'undefined' && DD.msg) {
          DD.msg('dd-checkout-message', (res.data && res.data.message) ? res.data.message : 'Stripe checkout error.', 'error');
        } else {
          $('#dd-checkout-message').text(res.data.message || 'Stripe error.').show();
        }
      }
    }).fail(function () {
      $btn.prop('disabled', false);
      $btn.find('span').first().show();
      $btn.find('.dd-btn__loader').hide();
      if (typeof DD !== 'undefined' && DD.msg) {
        DD.msg('dd-checkout-message', 'Server connection error. Please try again.', 'error');
      } else {
        $('#dd-checkout-message').text('Server connection error.').show();
      }
    });
  });

  // Submit handler
  $('#dd-payment-form').on('submit', function (e) {
    e.preventDefault();

    var $btn  = $('#dd-pay-btn');
    var plan  = $('input[name=plan]').val();
    var $msg  = $('#dd-checkout-message');

    $btn.find('.dd-btn__text').hide();
    $btn.find('.dd-btn__loader').show();
    $btn.prop('disabled', true);
    $msg.hide();

    // Step 1: Get PaymentIntent from server
    $.post(ddCheckout.ajaxUrl, {
      action: 'dd_create_payment_intent',
      nonce:  ddCheckout.nonce,
      plan:   plan,
    }, function (res) {

      if (!res.success || !res.data.clientSecret) {
        showError(res.data.message || 'Could not initiate payment.');
        return;
      }

      var clientSecret = res.data.clientSecret;

      // Step 2: Confirm card payment on Stripe
      stripe.confirmCardPayment(clientSecret, {
        payment_method: {
          card: cardNumber,
          billing_details: { name: $('input[name=cardholder_name]').val() || 'Dog Directory User' },
        },
      }).then(function (result) {
        if (result.error) {
          showError(result.error.message);
          return;
        }

        if (result.paymentIntent.status === 'succeeded') {
          // Step 3: Confirm with our server and activate subscription
          $.post(ddCheckout.ajaxUrl, {
            action:            'dd_confirm_payment',
            nonce:             ddCheckout.nonce,
            plan:              plan,
            payment_intent_id: result.paymentIntent.id,
          }, function (confirmRes) {
            if (confirmRes.success) {
              $msg.removeClass('error dd-auth-message dd-notice--error').addClass('dd-notice dd-notice--success')
                  .html('<span>✅</span> <span>' + confirmRes.data.message + '</span>')
                  .show();
              setTimeout(function () {
                window.location.href = confirmRes.data.redirect || ddCheckout.returnUrl;
              }, 1500);
            } else {
              showError(confirmRes.data.message || 'Activation failed. Please contact support.');
            }
          }).fail(function () {
            showError('Server error. Please contact support with your payment reference: ' + result.paymentIntent.id);
          });
        } else {
          showError('Payment status: ' + result.paymentIntent.status + '. Please try again.');
        }
      });

    }).fail(function () {
      showError('Could not connect to payment server. Please try again.');
    });

    function showError(message) {
      $btn.find('.dd-btn__text').show();
      $btn.find('.dd-btn__loader').hide();
      $btn.prop('disabled', false);
      $msg.removeClass('success dd-auth-message dd-notice--success').addClass('dd-notice dd-notice--error')
          .html('<span>⚠️</span> <span>' + message + '</span>')
          .show();
      $('html,body').animate({ scrollTop: $msg.offset().top - 80 }, 300);
    }
  });

})(jQuery);
