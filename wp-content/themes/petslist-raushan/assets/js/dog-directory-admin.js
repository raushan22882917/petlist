/**
 * Dog Directory — Admin JS
 */
(function ($) {
    'use strict';

    // Approve dog
    $(document).on('click', '.dd-approve-dog', function () {
        var id = $(this).data('id');
        var $btn = $(this);
        $btn.text('Approving...').prop('disabled', true);
        $.post(ddAdminVars.ajaxUrl, {
            action:  'dd_admin_approve_dog',
            nonce:   ddAdminVars.nonce,
            post_id: id,
        }, function (res) {
            if (res.success) {
                $btn.closest('tr').find('.dd-status').text('Live').css({ background: '#dcfce7', color: '#166534' });
                $btn.text('Approved ✓').addClass('button-primary');
            }
        });
    });

    // Reject dog
    $(document).on('click', '.dd-reject-dog', function () {
        var id = $(this).data('id');
        $.post(ddAdminVars.ajaxUrl, {
            action:  'dd_admin_reject_dog',
            nonce:   ddAdminVars.nonce,
            post_id: id,
        }, function (res) {
            if (res.success) location.reload();
        });
    });

})(jQuery);
