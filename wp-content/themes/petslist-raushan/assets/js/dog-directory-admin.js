/**
 * Dog Directory — Admin JS
 */
(function ($) {
    'use strict';

    function getAdminNonce() {
        if (typeof ddAdminVars !== 'undefined' && ddAdminVars.nonce) return ddAdminVars.nonce;
        if (typeof ddVars !== 'undefined' && ddVars.nonces) {
            return ddVars.nonces.admin || ddVars.nonces.dog || '';
        }
        return '';
    }

    function getAjaxUrl() {
        if (typeof ddAdminVars !== 'undefined' && ddAdminVars.ajaxUrl) return ddAdminVars.ajaxUrl;
        if (typeof ddVars !== 'undefined' && ddVars.ajaxUrl) return ddVars.ajaxUrl;
        return '/wp-admin/admin-ajax.php';
    }

    // Approve dog
    $(document).on('click', '.dd-approve-dog', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        var $tr = $(this).closest('tr');
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.post(getAjaxUrl(), {
            action:  'dd_admin_approve_dog',
            nonce:   getAdminNonce(),
            post_id: id,
        }, function (res) {
            if (res.success) {
                $tr.fadeOut(300, function () {
                    $(this).remove();
                });
            } else {
                $btn.prop('disabled', false);
            }
        });
    });

    // Reject / Unpublish dog (Simple remove from ads list without drawer)
    $(document).on('click', '.dd-reject-dog', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        var $tr = $(this).closest('tr');
        $tr.css('opacity', '0.5');
        $.post(getAjaxUrl(), {
            action:  'dd_admin_reject_dog',
            nonce:   getAdminNonce(),
            post_id: id,
        }, function (res) {
            if (res.success) {
                $tr.fadeOut(300, function () {
                    $(this).remove();
                });
            } else {
                $tr.css('opacity', '1');
            }
        });
    });

    // Expire dog
    $(document).on('click', '.dd-expire-dog', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        var $tr = $(this).closest('tr');
        if (!confirm('Are you sure you want to mark this listing as expired?')) return;
        $.post(getAjaxUrl(), {
            action:  'dd_admin_expire_dog',
            nonce:   getAdminNonce(),
            post_id: id,
        }, function (res) {
            if (res.success) {
                $tr.fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });
    });

    // Send SMTP Test Email
    $(document).on('click', '#dd-send-test-email', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var email = $('#dd-test-email-address').val();
        var $result = $('#dd-smtp-test-result');

        if (!email) {
            alert('Please enter a target email address for testing.');
            return;
        }

        $btn.prop('disabled', true).text('Sending...');
        $result.hide().removeClass('notice notice-success notice-error');

        $.post(getAjaxUrl(), {
            action: 'dd_send_test_email',
            nonce:  getAdminNonce(),
            email:  email
        }, function (res) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt" style="vertical-align: middle; margin-top: -2px;"></span> Send Test Email');
            $result.show();
            if (res.success) {
                $result.css({ 'padding': '10px 15px', 'background': '#d1fae5', 'border-left': '4px solid #10b981', 'color': '#065f46', 'border-radius': '4px' })
                       .html('<strong>✅ Success:</strong> ' + res.data.message);
            } else {
                $result.css({ 'padding': '10px 15px', 'background': '#fee2e2', 'border-left': '4px solid #ef4444', 'color': '#991b1b', 'border-radius': '4px' })
                       .html('<strong>❌ Failed:</strong> ' + res.data.message);
            }
        }).fail(function () {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt" style="vertical-align: middle; margin-top: -2px;"></span> Send Test Email');
            $result.show().css({ 'padding': '10px 15px', 'background': '#fee2e2', 'border-left': '4px solid #ef4444', 'color': '#991b1b', 'border-radius': '4px' })
                   .html('<strong>❌ Error:</strong> Server connection failed.');
        });
    });

    // Dynamic Country and State dropdown sync in WP Admin Dog Profile
    function syncAdminStateDropdown($countrySelect) {
        var country = $countrySelect.val();
        var targetId = $countrySelect.data('state-target');
        var $state = targetId ? $('#' + targetId) : $('#dd_state_province');
        if (!$state.length) {
            $state = $countrySelect.closest('.dd-meta-grid, form').find('#dd_state_province, .dd-state-select').first();
        }
        if (!$state.length) return;

        var countriesData = (typeof ddAdminVars !== 'undefined' && ddAdminVars.countries) ? ddAdminVars.countries : {};
        var states = countriesData[country] || [];
        var currentState = $state.val() || $state.data('saved') || '';

        $state.empty();
        $state.append($('<option>', { value: '', text: 'Select State / Province' }));
        if (states && states.length) {
            for (var i = 0; i < states.length; i++) {
                var opt = document.createElement('option');
                opt.value = states[i];
                opt.textContent = states[i];
                if (states[i] === currentState) opt.selected = true;
                $state[0].appendChild(opt);
            }
        }
    }

    $(document).on('change', '#dd_country, .dd-country-select', function () {
        syncAdminStateDropdown($(this));
    });

    $(function () {
        var $country = $('#dd_country, .dd-country-select').first();
        var $state = $('#dd_state_province, .dd-state-select').first();
        if ($country.length && $state.length && $state.find('option').length <= 1) {
            syncAdminStateDropdown($country);
        }
    });

})(jQuery);
