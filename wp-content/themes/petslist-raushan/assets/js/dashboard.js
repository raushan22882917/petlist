/**
 * Dog Directory — Dashboard JS (User + Admin)
 */
(function ($) {
    'use strict';

    // ── Sidebar collapse (user dashboard) ──────────────────
    var $sidebar = $('#ddu-sidebar, .ddu-sidebar');

    // Restore collapse state
    if (localStorage.getItem('ddu_collapsed') === '1') {
        $sidebar.addClass('collapsed');
    }

    $(document).on('click', '#ddu-sidebar-toggle', function () {
        if ($(window).width() <= 900) {
            $('#ddu-sidebar, .ddu-sidebar, .dda-sidebar').removeClass('open');
            $('#dds-overlay').removeClass('active');
        } else {
            $sidebar.toggleClass('collapsed');
            var col = $sidebar.hasClass('collapsed') ? '1' : '0';
            localStorage.setItem('ddu_collapsed', col);
            $(this).find('svg').css('transform', col === '1' ? 'rotate(180deg)' : '');
        }
    });

    // Mobile sidebar toggle (user + admin)
    if (!$('#dds-overlay').length) {
        $('body').append('<div class="dds-overlay" id="dds-overlay"></div>');
    }

    $(document).on('click', '#ddu-mobile-menu, #dda-mobile-menu, .ddu-topbar__menu-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ddu-sidebar, .ddu-sidebar, .dda-sidebar').toggleClass('open');
        $('#dds-overlay').toggleClass('active');
    });

    $(document).on('click', '#dds-overlay, .ddu-sidebar__collapse', function () {
        $('#ddu-sidebar, .ddu-sidebar, .dda-sidebar').removeClass('open');
        $('#dds-overlay').removeClass('active');
    });

    $(document).on('click', '.ddu-sidebar__nav-item, .dda-sidebar__nav-item', function () {
        if ($(window).width() <= 900) {
            $('#ddu-sidebar, .ddu-sidebar, .dda-sidebar').removeClass('open');
            $('#dds-overlay').removeClass('active');
        }
    });

    // ── Header Profile Dropdown ────────────────────────────
    $(document).on('click', '#ddu-header-avatar-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $dropdown = $(this).closest('.ddu-topbar__profile-menu').find('.ddu-profile-dropdown');
        var isOpen = $dropdown.hasClass('active');
        $('.ddu-profile-dropdown').removeClass('active');
        $('.ddu-topbar__avatar-btn').attr('aria-expanded', 'false');
        if (!isOpen) {
            $dropdown.addClass('active');
            $(this).attr('aria-expanded', 'true');
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.ddu-topbar__profile-menu').length) {
            $('.ddu-profile-dropdown').removeClass('active');
            $('.ddu-topbar__avatar-btn').attr('aria-expanded', 'false');
        }
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

    // ── Drawer Panel Actions (Only triggered by Inspect button or non-action elements) ─────────────────────────────────
    $(document).on('click', '.dd-get-dog-drawer', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var postId = $(this).data('id') || $(this).data('post-id');
        if (!postId) return;

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
    });

    $(document).on('click', '.dd-dogs-table tbody tr, .dda-table tbody tr', function (e) {
        if ($(e.target).closest('.dd-dogs-table__actions, .dda-action-btn, .dd-action-btn, a, button, i, svg').length) {
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

    // ── Promo Code Redemption (User Dashboard) ───────────────
    $(document).on('submit', '#dd-dashboard-redeem-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#dd-dash-redeem-btn');
        var $msg = $('#dd-dash-coupon-msg');
        var code = $.trim($('#dd-dash-coupon-code').val());

        if (!code) {
            $msg.css('color', '#dc2626').text('Please enter a promo code.').show();
            return;
        }

        $btn.prop('disabled', true);
        $btn.find('span').first().hide();
        $btn.find('.dd-btn__loader').show();
        $msg.hide();

        var ajaxUrl = (typeof ddVars !== 'undefined' && ddVars.ajaxUrl) ? ddVars.ajaxUrl : '/wp-admin/admin-ajax.php';
        var nonce = (typeof ddVars !== 'undefined' && ddVars.nonces) ? (ddVars.nonces.dashboard || ddVars.nonces.checkout || ddVars.nonces.auth || '') : '';

        $.post(ajaxUrl, {
            action: 'dd_redeem_free_subscription',
            code: code,
            nonce: nonce
        }, function (res) {
            $btn.prop('disabled', false);
            $btn.find('span').first().show();
            $btn.find('.dd-btn__loader').hide();

            if (res.success) {
                $msg.css('color', '#16a34a').html('🎉 ' + (res.data.message || 'Free subscription activated! Reloading...')).show();
                setTimeout(function () {
                    window.location.reload();
                }, 1500);
            } else {
                $msg.css('color', '#dc2626').html('⚠️ ' + (res.data && res.data.message ? res.data.message : 'Invalid promo code.')).show();
            }
        }).fail(function () {
            $btn.prop('disabled', false);
            $btn.find('span').first().show();
            $btn.find('.dd-btn__loader').hide();
            $msg.css('color', '#dc2626').text('⚠️ Server error redeeming promo code.').show();
        });
    });

    // ── Helpers ──────────────────────────────────────────────
    function _flashMsg(selector, text, type) {
        $(selector).each(function () {
            $(this).removeClass('success error').addClass(type + ' dd-auth-message')
                   .html(text).show();
            $('html,body').animate({ scrollTop: $(this).offset().top - 100 }, 300);
        });
    }

    // ── Country + State Selector ──────────────────────────────
    // The full country/state list and ddInitCountrySelects() are defined in
    // dog-directory.js which loads before this file on all DD pages.
    // We re-call it here so dynamic tab content also gets initialized.
    $(document).on('dd:tab:shown', function () {
        if (typeof ddInitCountrySelects === 'function') {
            ddInitCountrySelects();
        }
    });

    // ── Admin Delete Handlers (Fail-safe) ──────────────────────
    $(document).on('click', '.dd-admin-delete-user', function (e) {
        // If already handled by dd-admin, avoid double firing
        if ($(this).data('handled-del-user')) return;
        $(this).data('handled-del-user', true);

        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var id = $btn.data('id') || $btn.attr('data-id');
        var name = $btn.data('name') || $btn.attr('data-name') || 'this user';
        if (!id) return;
        if (!confirm('Are you sure you want to permanently delete user "' + name + '"? This will remove their account and all associated studs/data.')) {
            $btn.data('handled-del-user', false);
            return;
        }
        $btn.prop('disabled', true).css('opacity', '0.6');
        var ajaxUrl = (typeof ddAdminVars !== 'undefined' && ddAdminVars.ajaxUrl) ? ddAdminVars.ajaxUrl : ((typeof ddVars !== 'undefined' && ddVars.ajaxUrl) ? ddVars.ajaxUrl : '/wp-admin/admin-ajax.php');
        var nonce = (typeof ddAdminVars !== 'undefined' && ddAdminVars.nonce) ? ddAdminVars.nonce : ((typeof ddVars !== 'undefined' && ddVars.nonces) ? (ddVars.nonces.admin || ddVars.nonces.dog || ddVars.nonces.dashboard || '') : '');
        $.post(ajaxUrl, {
            action:  'dd_admin_delete_user',
            nonce:   nonce,
            user_id: id,
        }, function (res) {
            if (res.success) {
                var $row = $btn.closest('tr');
                if ($row.length) {
                    $row.fadeOut(300, function () { $(this).remove(); });
                }
                if ($('#dd-dog-drawer').hasClass('dd-drawer--open')) {
                    $('#dd-dog-drawer').removeClass('dd-drawer--open');
                    $('body').removeClass('dd-drawer-active');
                }
                setTimeout(function () { location.reload(); }, 400);
            } else {
                $btn.prop('disabled', false).css('opacity', '1').data('handled-del-user', false);
                alert(res.data && res.data.message ? res.data.message : 'Error deleting user.');
            }
        }).fail(function () {
            $btn.prop('disabled', false).css('opacity', '1').data('handled-del-user', false);
            alert('Server error. Please try again.');
        });
    });

    $(document).on('click', '.dd-admin-delete-dog', function (e) {
        if ($(this).data('handled-del-dog')) return;
        $(this).data('handled-del-dog', true);

        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var id = $btn.data('id') || $btn.attr('data-id');
        var name = $btn.data('name') || $btn.attr('data-name') || 'this stud listing';
        if (!id) return;
        if (!confirm('Are you sure you want to permanently delete stud listing "' + name + '"? This action cannot be undone.')) {
            $btn.data('handled-del-dog', false);
            return;
        }
        $btn.prop('disabled', true).css('opacity', '0.6');
        var ajaxUrl = (typeof ddAdminVars !== 'undefined' && ddAdminVars.ajaxUrl) ? ddAdminVars.ajaxUrl : ((typeof ddVars !== 'undefined' && ddVars.ajaxUrl) ? ddVars.ajaxUrl : '/wp-admin/admin-ajax.php');
        var nonce = (typeof ddAdminVars !== 'undefined' && ddAdminVars.nonce) ? ddAdminVars.nonce : ((typeof ddVars !== 'undefined' && ddVars.nonces) ? (ddVars.nonces.dog || ddVars.nonces.admin || ddVars.nonces.dashboard || '') : '');
        $.post(ajaxUrl, {
            action:  'dd_delete_dog',
            nonce:   nonce,
            post_id: id,
        }, function (res) {
            if (res.success) {
                var $row = $btn.closest('tr');
                if ($row.length) {
                    $row.fadeOut(300, function () { $(this).remove(); });
                }
                if ($('#dd-dog-drawer').hasClass('dd-drawer--open')) {
                    $('#dd-dog-drawer').removeClass('dd-drawer--open');
                    $('body').removeClass('dd-drawer-active');
                }
                setTimeout(function () { location.reload(); }, 400);
            } else {
                $btn.prop('disabled', false).css('opacity', '1').data('handled-del-dog', false);
                alert(res.data && res.data.message ? res.data.message : 'Error deleting stud listing.');
            }
        }).fail(function () {
            $btn.prop('disabled', false).css('opacity', '1').data('handled-del-dog', false);
            alert('Server error. Please try again.');
        });
    });

})(jQuery);
