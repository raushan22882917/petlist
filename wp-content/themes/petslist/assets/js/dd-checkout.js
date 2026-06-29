/**
 * Dog Directory — Stripe Checkout
 * @package Petslist Dog Directory
 */
(function ($) {
  'use strict';

  if (typeof Stripe === 'undefined' || !ddCheckout.publishableKey) {
    // Stripe not configured — hide form gracefully
    if ($('#dd-payment-form').length) {
      $('#dd-payment-form').html(
        '<div class="dd-notice dd-notice--warning"><i class="fa-solid fa-triangle-exclamation"></i>' +
        ' Payment gateway is not yet configured. Please contact support.</div>'
      );
    }
    return;
  }

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
              $msg.removeClass('error').addClass('success dd-auth-message')
                  .html('<i class="fa-solid fa-circle-check"></i> ' + confirmRes.data.message)
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
      $msg.removeClass('success').addClass('error dd-auth-message')
          .html('<i class="fa-solid fa-circle-xmark"></i> ' + message)
          .show();
      $('html,body').animate({ scrollTop: $msg.offset().top - 80 }, 300);
    }
  });

})(jQuery);
