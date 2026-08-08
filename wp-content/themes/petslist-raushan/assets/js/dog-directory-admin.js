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

})(jQuery);
