/**
 * Dog Directory — Dashboard JS (User + Admin)
 */
(function ($) {
    'use strict';

    // ── Sidebar collapse (user dashboard) ──────────────────
    var $shell   = $('#ddu-shell');
    var $sidebar = $('#ddu-sidebar');
    var $toggle  = $('#ddu-sidebar-toggle');
    var $mobileBtn = $('#ddu-mobile-menu');

    // Restore collapse state
    if (localStorage.getItem('ddu_collapsed') === '1') {
        $sidebar.addClass('collapsed');
    }

    $toggle.on('click', function () {
        $sidebar.toggleClass('collapsed');
        var col = $sidebar.hasClass('collapsed') ? '1' : '0';
        localStorage.setItem('ddu_collapsed', col);
        // Rotate icon
        $(this).find('svg').css('transform', col === '1' ? 'rotate(180deg)' : '');
    });

    // Mobile sidebar toggle (user)
    var $overlay = $('<div class="dds-overlay" id="dds-overlay"></div>');
    $('body').append($overlay);

    $mobileBtn.on('click', function () {
        $sidebar.toggleClass('open');
        $overlay.toggleClass('active');
    });

    // Admin mobile toggle
    var $adminSidebar = $('.dda-sidebar');
    var $adminMenuBtn = $('<button class="ddu-topbar__menu-btn" id="dda-mobile-menu" aria-label="Menu" style="display:none">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 12h18M3 6h18M3 18h18"/></svg>' +
        '</button>');
    $('.dda-topbar__title').before($adminMenuBtn);

    if ($(window).width() <= 900) {
        $adminMenuBtn.show();
    }
    $adminMenuBtn.on('click', function () {
        $adminSidebar.toggleClass('open');
        $overlay.toggleClass('active');
    });

    $overlay.on('click', function () {
        $sidebar.removeClass('open');
        $adminSidebar.removeClass('open');
        $overlay.removeClass('active');
    });

    // ── Admin: Approve dog ──────────────────────────────────
    $(document).on('click', '.dd-approve-dog', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.text('…').prop('disabled', true);
        $.post(ddVars.ajaxUrl, {
            action:  'dd_admin_approve_dog',
            nonce:   ddVars.nonces.dog,
            post_id: id,
        }, function (res) {
            if (res.success) {
                var $row = $btn.closest('tr, .ddu-dog-row');
                $row.find('.ddu-pill, .dd-status-pill').attr('class','ddu-pill ddu-pill--active').text('Live');
                $btn.closest('td, .ddu-dog-row__btns').html(
                    '<a href="' + (ddVars.siteUrl || '/') + '" class="dda-action-btn" target="_blank">View</a>'
                );
                _flashMsg('#dd-admin-message', res.data.message, 'success');
            }
        });
    });

    // ── Admin: Reject dog ───────────────────────────────────
    $(document).on('click', '.dd-reject-dog', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.text('…').prop('disabled', true);
        $.post(ddVars.ajaxUrl, {
            action:  'dd_admin_reject_dog',
            nonce:   ddVars.nonces.dog,
            post_id: id,
        }, function (res) {
            if (res.success) {
                $btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
                _flashMsg('#dd-admin-message', res.data.message, 'success');
            }
        });
    });

    // ── Admin: Toggle sponsored status ───────────────────────
    $(document).on('click', '.dd-toggle-sponsored', function (e) {
        e.stopPropagation();
        var $btn = $(this);
        var id   = $btn.data('id');
        var $icon = $btn.find('i');
        $btn.css('pointer-events', 'none').css('opacity', '0.5');
        $.post(ddVars.ajaxUrl, {
            action:  'dd_admin_toggle_sponsored',
            nonce:   ddVars.nonces.dog,
            post_id: id,
        }, function (res) {
            $btn.css('pointer-events', '').css('opacity', '');
            if (res.success) {
                if (res.data.is_sponsored) {
                    $icon.attr('class', 'fa-solid fa-star').css('color', '#eab308');
                    $btn.attr('title', 'Unmark Sponsored Ad');
                } else {
                    $icon.attr('class', 'fa-regular fa-star').css('color', '#9ca3af');
                    $btn.attr('title', 'Mark Sponsored Ad');
                }
                _flashMsg('#dd-admin-message', res.data.message, 'success');
            } else {
                _flashMsg('#dd-admin-message', res.data.message || 'Error updating status', 'error');
            }
        });
    });

    // ── Table inline filters ─────────────────────────────────
    $(document).on('input change', '.dd-table-filter-input, .dd-table-filter-select', function () {
        var filters = {};
        $('.dd-table-filter-input, .dd-table-filter-select').each(function () {
            var col = $(this).data('column');
            var val = $(this).val().toLowerCase().trim();
            if (val) {
                filters[col] = val;
            }
        });

        $('.dd-dogs-table tbody tr').each(function () {
            var $row = $(this);
            var show = true;

            $.each(filters, function (col, val) {
                if (col === 'name') {
                    var nameText = $row.find('.dd-dogs-table__name strong').text().toLowerCase();
                    if (nameText.indexOf(val) === -1) {
                        show = false;
                    }
                } else if (col === 'breed') {
                    var breedText = $row.find('.dd-dogs-table__breed').text().toLowerCase().trim();
                    if (breedText !== val) {
                        show = false;
                    }
                } else if (col === 'gender') {
                    var genderText = $row.find('.dd-dogs-table__gender').text().toLowerCase().trim();
                    if (genderText.indexOf(val) === -1) {
                        show = false;
                    }
                } else if (col === 'status') {
                    var statusText = $row.find('.dd-dogs-table__status').text().toLowerCase().trim();
                    if (statusText.indexOf(val) === -1) {
                        show = false;
                    }
                }
            });

            if (show) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    });

    // ── FAQ accordion ───────────────────────────────────────
    $(document).on('click', '.dd-faq-item__question', function () {
        $(this).closest('.dd-faq-item').siblings('.open').removeClass('open');
        $(this).closest('.dd-faq-item').toggleClass('open');
    });

    // ── Drawer Panel Actions ─────────────────────────────────
    $(document).on('click', '.dd-dogs-table tbody tr, .dda-table tbody tr', function (e) {
        if ($(e.target).closest('.dd-dogs-table__actions, .dda-action-btn, a, button').length) {
            return;
        }

        var postId = $(this).data('post-id');
        var userId = $(this).data('user-id');
        if (!postId && !userId) return;

        if (postId) {
            $('#dd-dog-drawer-body').html(
                '<div class="dd-drawer-loading"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading profile details...</p></div>'
            );
            $('#dd-dog-drawer').addClass('dd-drawer--open');
            $('body').addClass('dd-drawer-active');

            $.ajax({
                url: ddVars.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dd_get_dog_drawer',
                    post_id: postId,
                    nonce: ddVars.nonces.dog
                },
                success: function (response) {
                    if (response.success) {
                        $('#dd-dog-drawer-body').html(response.data.html);
                    } else {
                        $('#dd-dog-drawer-body').html(
                            '<div class="dd-drawer-error"><p>' + (response.data.message || 'Error loading profile details.') + '</p></div>'
                        );
                    }
                },
                error: function () {
                    $('#dd-dog-drawer-body').html(
                        '<div class="dd-drawer-error"><p>Failed to connect. Please try again.</p></div>'
                    );
                }
            });
        } else if (userId) {
            $('#dd-dog-drawer-body').html(
                '<div class="dd-drawer-loading"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading user details...</p></div>'
            );
            $('#dd-dog-drawer').addClass('dd-drawer--open');
            $('body').addClass('dd-drawer-active');

            $.ajax({
                url: ddVars.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dd_get_user_drawer',
                    user_id: userId,
                    nonce: ddVars.nonces.dog
                },
                success: function (response) {
                    if (response.success) {
                        $('#dd-dog-drawer-body').html(response.data.html);
                    } else {
                        $('#dd-dog-drawer-body').html(
                            '<div class="dd-drawer-error"><p>' + (response.data.message || 'Error loading user details.') + '</p></div>'
                        );
                    }
                },
                error: function () {
                    $('#dd-dog-drawer-body').html(
                        '<div class="dd-drawer-error"><p>Failed to connect. Please try again.</p></div>'
                    );
                }
            });
        }
    });

    $(document).on('click', '#dd-drawer-close-btn, .dd-drawer__overlay', function () {
        $('#dd-dog-drawer').removeClass('dd-drawer--open');
        $('body').removeClass('dd-drawer-active');
    });

    $(document).on('click', '.dd-drawer-gallery-btn', function (e) {
        e.preventDefault();
        var src = $(this).data('src');
        $('#dd-drawer-main-photo').attr('src', src);
        $('.dd-drawer-gallery-btn').removeClass('active');
        $(this).addClass('active');
    });

    // ── Helpers ──────────────────────────────────────────────
    function _flashMsg(selector, text, type) {
        $(selector).each(function () {
            $(this).removeClass('success error').addClass(type + ' dd-auth-message')
                   .html(text).show();
            $('html,body').animate({ scrollTop: $(this).offset().top - 100 }, 300);
        });
    }

})(jQuery);
