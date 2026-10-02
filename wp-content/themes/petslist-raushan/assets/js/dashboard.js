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

    // ── Worldwide Country + State/Province Selector ──────────
    var DD_COUNTRIES = [
        { name: 'Afghanistan', states: ['Kabul','Kandahar','Herat','Mazar-i-Sharif'] },
        { name: 'Albania', states: ['Tirana','Durrës','Vlorë','Shkodër'] },
        { name: 'Algeria', states: ['Algiers','Oran','Constantine','Annaba'] },
        { name: 'Argentina', states: ['Buenos Aires','Córdoba','Rosario','Mendoza','Tucumán','La Plata','Mar del Plata','Salta'] },
        { name: 'Armenia', states: ['Yerevan','Gyumri','Vanadzor'] },
        { name: 'Australia', states: ['New South Wales','Victoria','Queensland','Western Australia','South Australia','Tasmania','Australian Capital Territory','Northern Territory'] },
        { name: 'Austria', states: ['Vienna','Lower Austria','Upper Austria','Styria','Tyrol','Carinthia','Salzburg','Vorarlberg','Burgenland'] },
        { name: 'Azerbaijan', states: ['Baku','Ganja','Sumqayit'] },
        { name: 'Bahrain', states: ['Capital','Central','Muharraq','Northern','Southern'] },
        { name: 'Bangladesh', states: ['Dhaka','Chittagong','Rajshahi','Khulna','Sylhet','Barisal','Rangpur','Mymensingh'] },
        { name: 'Belarus', states: ['Minsk','Brest','Vitebsk','Gomel','Grodno','Mogilev'] },
        { name: 'Belgium', states: ['Brussels','Flanders','Wallonia'] },
        { name: 'Bolivia', states: ['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Tarija','Beni','Pando','Chuquisaca'] },
        { name: 'Bosnia and Herzegovina', states: ['Federation of B&H','Republika Srpska','Brčko District'] },
        { name: 'Brazil', states: ['São Paulo','Rio de Janeiro','Minas Gerais','Bahia','Paraná','Rio Grande do Sul','Pernambuco','Ceará','Pará','Maranhão','Amazonas','Goiás','Espírito Santo','Paraíba','Mato Grosso','Rio Grande do Norte','Alagoas','Piauí','Mato Grosso do Sul','Distrito Federal','Sergipe','Rondônia','Tocantins','Acre','Amapá','Roraima'] },
        { name: 'Bulgaria', states: ['Sofia','Plovdiv','Varna','Burgas','Ruse'] },
        { name: 'Cambodia', states: ['Phnom Penh','Siem Reap','Battambang','Sihanoukville'] },
        { name: 'Cameroon', states: ['Centre','Littoral','West','North West','South West','East','North','Adamawa','Far North','South'] },
        { name: 'Canada', states: ['Alberta','British Columbia','Manitoba','New Brunswick','Newfoundland and Labrador','Nova Scotia','Ontario','Prince Edward Island','Quebec','Saskatchewan','Northwest Territories','Nunavut','Yukon'] },
        { name: 'Chile', states: ['Santiago Metropolitan','Valparaíso','Biobío','La Araucanía','Maule','Los Lagos','O\'Higgins','Los Ríos','Antofagasta','Atacama','Coquimbo','Aysén','Tarapacá','Arica and Parinacota','Magallanes'] },
        { name: 'China', states: ['Beijing','Shanghai','Guangdong','Sichuan','Henan','Hebei','Hunan','Hubei','Zhejiang','Jiangsu','Liaoning','Shandong','Shanxi','Anhui','Guangxi','Fujian','Jiangxi','Guizhou','Shaanxi','Jilin','Yunnan','Heilongjiang','Inner Mongolia','Xinjiang','Tibet','Hainan','Chongqing','Tianjin','Ningxia','Gansu','Qinghai'] },
        { name: 'Colombia', states: ['Bogotá','Antioquia','Valle del Cauca','Cundinamarca','Atlántico','Bolívar','Santander','Nariño','Córdoba','Tolima'] },
        { name: 'Croatia', states: ['Zagreb','Split-Dalmatia','Rijeka','Osijek-Baranja','Slavonski Brod-Posavina'] },
        { name: 'Cuba', states: ['Havana','Santiago de Cuba','Holguín','Camagüey','Matanzas'] },
        { name: 'Cyprus', states: ['Nicosia','Limassol','Larnaca','Paphos','Famagusta','Kyrenia'] },
        { name: 'Czech Republic', states: ['Prague','South Bohemian','South Moravian','Hradec Králové','Liberec','Moravian-Silesian','Olomouc','Pardubice','Plzeň','Central Bohemian','Ústí nad Labem','Vysočina','Zlín','Karlovy Vary'] },
        { name: 'Denmark', states: ['Capital','Central Jutland','North Jutland','Zealand','Southern Denmark'] },
        { name: 'Ecuador', states: ['Pichincha','Guayas','Azuay','Manabí','Los Ríos','Tungurahua','El Oro','Loja','Esmeraldas','Chimborazo'] },
        { name: 'Egypt', states: ['Cairo','Giza','Alexandria','Dakahlia','Sharqia','Qalyubia','Kafr el-Sheikh','Gharbia','Monufia','Beheira','Ismailia','Damietta','Faiyum','Beni Suef','Minya','Asyut','Sohag','Qena','Aswan','Luxor','Red Sea','New Valley','Matruh','North Sinai','South Sinai','Port Said','Suez'] },
        { name: 'Ethiopia', states: ['Addis Ababa','Oromia','Amhara','SNNPR','Tigray','Afar','Somali','Gambela','Harari','Dire Dawa','Benishangul-Gumuz'] },
        { name: 'Finland', states: ['Uusimaa','Pirkanmaa','Varsinais-Suomi','Pohjois-Pohjanmaa','Keski-Suomi','Pohjois-Savo','Pohjanmaa','Satakunta','Lappi','Etelä-Savo'] },
        { name: 'France', states: ['Île-de-France','Auvergne-Rhône-Alpes','Hauts-de-France','Nouvelle-Aquitaine','Occitanie','Grand Est','Normandie','Provence-Alpes-Côte d\'Azur','Pays de la Loire','Bretagne','Bourgogne-Franche-Comté','Centre-Val de Loire','Corse'] },
        { name: 'Georgia', states: ['Tbilisi','Adjara','Guria','Imereti','Kakheti','Kvemo Kartli','Mtskheta-Mtianeti','Racha-Lechkhumi','Samegrelo-Zemo Svaneti','Samtskhe-Javakheti','Shida Kartli','Abkhazia','South Ossetia'] },
        { name: 'Germany', states: ['Baden-Württemberg','Bavaria','Berlin','Brandenburg','Bremen','Hamburg','Hesse','Lower Saxony','Mecklenburg-Vorpommern','North Rhine-Westphalia','Rhineland-Palatinate','Saarland','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia'] },
        { name: 'Ghana', states: ['Greater Accra','Ashanti','Eastern','Central','Western','Northern','Upper East','Upper West','Brong-Ahafo','Volta'] },
        { name: 'Greece', states: ['Attica','Central Greece','Central Macedonia','Crete','Eastern Macedonia and Thrace','Epirus','Ionian Islands','North Aegean','Peloponnese','South Aegean','Thessaly','Western Greece','Western Macedonia'] },
        { name: 'Guatemala', states: ['Guatemala City','Huehuetenango','Quiché','Alta Verapaz','San Marcos','Quetzaltenango','Petén','Escuintla','Jutiapa','Jalapa'] },
        { name: 'Honduras', states: ['Francisco Morazán','Cortés','Yoro','Olancho','Choluteca','El Paraíso','Atlántida','Santa Bárbara','Comayagua','La Paz'] },
        { name: 'Hungary', states: ['Budapest','Pest','Győr-Moson-Sopron','Borsod-Abaúj-Zemplén','Hajdú-Bihar','Bács-Kiskun','Szabolcs-Szatmár-Bereg','Jász-Nagykun-Szolnok','Fejér','Baranya','Komárom-Esztergom','Veszprém','Somogy','Csongrád-Csanád','Tolna','Heves','Nógrád','Vas','Zala','Békés'] },
        { name: 'India', states: ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] },
        { name: 'Indonesia', states: ['Aceh','Bali','Banten','Bengkulu','Central Java','Central Kalimantan','Central Sulawesi','East Java','East Kalimantan','East Nusa Tenggara','Gorontalo','Jakarta','Jambi','Lampung','Maluku','North Kalimantan','North Maluku','North Sulawesi','North Sumatra','Riau','Riau Islands','South Kalimantan','South Sulawesi','South Sumatra','Southeast Sulawesi','West Java','West Kalimantan','West Nusa Tenggara','West Papua','West Sulawesi','West Sumatra','Yogyakarta'] },
        { name: 'Iran', states: ['Tehran','Isfahan','Fars','Khorasan Razavi','Khuzestan','Alborz','Azerbaijan East','Azerbaijan West','Gilan','Kerman','Lorestan','Mazandaran','Markazi','Qom','Semnan','Sistan and Baluchestan','Zanjan'] },
        { name: 'Iraq', states: ['Baghdad','Basra','Ninawa','Erbil','Kirkuk','Duhok','Sulaymaniyah','Anbar','Wasit','Babylon','Karbala','Najaf','Diyala','Saladin','Muthanna','Maysan','Dhi Qar','Qadisiyyah'] },
        { name: 'Ireland', states: ['Carlow','Cavan','Clare','Cork','Donegal','Dublin','Galway','Kerry','Kildare','Kilkenny','Laois','Leitrim','Limerick','Longford','Louth','Mayo','Meath','Monaghan','Offaly','Roscommon','Sligo','Tipperary','Waterford','Westmeath','Wexford','Wicklow'] },
        { name: 'Israel', states: ['Central','Haifa','Jerusalem','Northern','Southern','Tel Aviv'] },
        { name: 'Italy', states: ['Abruzzo','Aosta Valley','Apulia','Basilicata','Calabria','Campania','Emilia-Romagna','Friuli-Venezia Giulia','Lazio','Liguria','Lombardy','Marche','Molise','Piedmont','Sardinia','Sicily','Trentino-Alto Adige','Tuscany','Umbria','Veneto'] },
        { name: 'Japan', states: ['Hokkaido','Aomori','Iwate','Miyagi','Akita','Yamagata','Fukushima','Ibaraki','Tochigi','Gunma','Saitama','Chiba','Tokyo','Kanagawa','Niigata','Toyama','Ishikawa','Fukui','Yamanashi','Nagano','Shizuoka','Aichi','Mie','Shiga','Kyoto','Osaka','Hyogo','Nara','Wakayama','Tottori','Shimane','Okayama','Hiroshima','Yamaguchi','Tokushima','Kagawa','Ehime','Kochi','Fukuoka','Saga','Nagasaki','Kumamoto','Oita','Miyazaki','Kagoshima','Okinawa','Gifu'] },
        { name: 'Jordan', states: ['Amman','Zarqa','Irbid','Balqa','Mafraq','Karak','Ajloun','Jerash','Madaba','Aqaba','Tafilah','Ma\'an'] },
        { name: 'Kazakhstan', states: ['Almaty','Nur-Sultan','Shymkent','Aktobe','Karaganda','Pavlodar','East Kazakhstan','West Kazakhstan','North Kazakhstan','South Kazakhstan'] },
        { name: 'Kenya', states: ['Nairobi','Mombasa','Nakuru','Eldoret','Kisumu','Thika','Malindi','Kitale','Garissa','Kakamega'] },
        { name: 'Kuwait', states: ['Al Asimah','Hawalli','Farwaniya','Ahmadi','Jahra','Mubarak Al-Kabeer'] },
        { name: 'Lebanon', states: ['Beirut','Mount Lebanon','North Lebanon','South Lebanon','Beqaa','Nabatieh','Akkar','Baalbek-Hermel'] },
        { name: 'Malaysia', states: ['Johor','Kedah','Kelantan','Kuala Lumpur','Labuan','Malacca','Negeri Sembilan','Pahang','Penang','Perak','Perlis','Putrajaya','Sabah','Sarawak','Selangor','Terengganu'] },
        { name: 'Mexico', states: ['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','Coahuila','Colima','Durango','Guanajuato','Guerrero','Hidalgo','Jalisco','México','México City','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'] },
        { name: 'Morocco', states: ['Casablanca-Settat','Rabat-Salé-Kénitra','Fès-Meknès','Marrakesh-Safi','Oriental','Drâa-Tafilalet','Souss-Massa','Béni Mellal-Khénifra','Laâyoune-Sakia El Hamra','Dakhla-Oued Ed-Dahab','Tanger-Tétouan-Al Hoceïma','Guelmim-Oued Noun'] },
        { name: 'Mozambique', states: ['Maputo','Gaza','Inhambane','Sofala','Manica','Tete','Zambézia','Nampula','Cabo Delgado','Niassa','Maputo City'] },
        { name: 'Myanmar', states: ['Ayeyarwady','Bago','Chin','Kachin','Kayah','Kayin','Magway','Mandalay','Mon','Rakhine','Sagaing','Shan','Tanintharyi','Yangon','Naypyidaw'] },
        { name: 'Nepal', states: ['Koshi','Madhesh','Bagmati','Gandaki','Lumbini','Karnali','Sudurpashchim'] },
        { name: 'Netherlands', states: ['Drenthe','Flevoland','Friesland','Gelderland','Groningen','Limburg','North Brabant','North Holland','Overijssel','South Holland','Utrecht','Zeeland'] },
        { name: 'New Zealand', states: ['Auckland','Bay of Plenty','Canterbury','Gisborne','Hawke\'s Bay','Manawatu-Wanganui','Marlborough','Nelson','Northland','Otago','Southland','Taranaki','Tasman','Waikato','Wellington','West Coast'] },
        { name: 'Nicaragua', states: ['Boaco','Carazo','Chinandega','Chontales','Estelí','Granada','Jinotega','León','Madriz','Managua','Masaya','Matagalpa','Nueva Segovia','Río San Juan','Rivas','RACCN','RACCS'] },
        { name: 'Nigeria', states: ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'] },
        { name: 'Norway', states: ['Oslo','Viken','Innlandet','Vestfold og Telemark','Agder','Rogaland','Vestland','Møre og Romsdal','Trøndelag','Nordland','Troms og Finnmark'] },
        { name: 'Oman', states: ['Muscat','Dhofar','Musandam','Al Buraymi','Al Dakhiliyah','Al Batinah North','Al Batinah South','Al Sharqiyah North','Al Sharqiyah South','Al Wusta','Al Dhahirah'] },
        { name: 'Pakistan', states: ['Punjab','Sindh','Khyber Pakhtunkhwa','Balochistan','Islamabad Capital Territory','Gilgit-Baltistan','Azad Kashmir'] },
        { name: 'Panama', states: ['Bocas del Toro','Chiriquí','Coclé','Colón','Darién','Herrera','Los Santos','Panamá','Veraguas','Ngäbe-Buglé','Guna Yala','Emberá','Kuna de Madugandí','Kuna de Wargandí'] },
        { name: 'Paraguay', states: ['Alto Paraguay','Alto Paraná','Amambay','Boquerón','Caaguazú','Caazapá','Canindeyú','Central','Concepción','Cordillera','Guairá','Itapúa','Misiones','Ñeembucú','Paraguarí','Presidente Hayes','San Pedro','Asunción'] },
        { name: 'Peru', states: ['Amazonas','Áncash','Apurímac','Arequipa','Ayacucho','Cajamarca','Callao','Cusco','Huancavelica','Huánuco','Ica','Junín','La Libertad','Lambayeque','Lima','Loreto','Madre de Dios','Moquegua','Pasco','Piura','Puno','San Martín','Tacna','Tumbes','Ucayali'] },
        { name: 'Philippines', states: ['Abra','Agusan del Norte','Agusan del Sur','Aklan','Albay','Antique','Apayao','Aurora','Basilan','Bataan','Batanes','Batangas','Benguet','Biliran','Bohol','Bukidnon','Bulacan','Cagayan','Camarines Norte','Camarines Sur','Camiguin','Capiz','Catanduanes','Cavite','Cebu','Cotabato','Davao de Oro','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental','Dinagat Islands','Eastern Samar','Guimaras','Ifugao','Ilocos Norte','Ilocos Sur','Iloilo','Isabela','Kalinga','La Union','Laguna','Lanao del Norte','Lanao del Sur','Leyte','Maguindanao del Norte','Maguindanao del Sur','Marinduque','Masbate','Metro Manila','Misamis Occidental','Misamis Oriental','Mountain Province','Negros Occidental','Negros Oriental','Northern Samar','Nueva Ecija','Nueva Vizcaya','Occidental Mindoro','Oriental Mindoro','Palawan','Pampanga','Pangasinan','Quezon','Quirino','Rizal','Romblon','Samar','Sarangani','Siquijor','Sorsogon','South Cotabato','Southern Leyte','Sultan Kudarat','Sulu','Surigao del Norte','Surigao del Sur','Tarlac','Tawi-Tawi','Zambales','Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay'] },
        { name: 'Poland', states: ['Greater Poland','Holy Cross','Kuyavian-Pomeranian','Lesser Poland','Łódź','Lower Silesian','Lublin','Lubusz','Masovian','Opole','Podlaskie','Pomeranian','Silesian','Subcarpathian','Warmian-Masurian','West Pomeranian'] },
        { name: 'Portugal', states: ['Aveiro','Beja','Braga','Bragança','Castelo Branco','Coimbra','Évora','Faro','Guarda','Leiria','Lisbon','Portalegre','Porto','Santarém','Setúbal','Viana do Castelo','Vila Real','Viseu','Azores','Madeira'] },
        { name: 'Qatar', states: ['Ad Dawhah','Al Khor','Al Shamal','Al Wakrah','Ar Rayyan','Ash Shahaniyah','Az Za\'ayin','Madinat ash Shamal','Umm Salal'] },
        { name: 'Romania', states: ['Alba','Arad','Argeș','Bacău','Bihor','Bistrița-Năsăud','Botoșani','Brăila','Brașov','București','Buzău','Călărași','Caraș-Severin','Cluj','Constanța','Covasna','Dâmbovița','Dolj','Galați','Giurgiu','Gorj','Harghita','Hunedoara','Ialomița','Iași','Ilfov','Maramureș','Mehedinți','Mureș','Neamț','Olt','Prahova','Sălaj','Satu Mare','Sibiu','Suceava','Teleorman','Timiș','Tulcea','Vâlcea','Vaslui','Vrancea'] },
        { name: 'Russia', states: ['Moscow','St. Petersburg','Novosibirsk','Yekaterinburg','Kazan','Krasnoyarsk','Chelyabinsk','Samara','Ufa','Rostov-on-Don','Krasnodar','Vladivostok','Nizhny Novgorod','Volgograd','Perm','Voronezh','Saratov','Khabarovsk','Irkutsk','Omsk'] },
        { name: 'Saudi Arabia', states: ['Riyadh','Mecca','Medina','Eastern Province','Asir','Tabuk','Qassim','Ha\'il','Jizan','Najran','Al Bahah','Northern Borders','Jouf','Al-Quawa'] },
        { name: 'Senegal', states: ['Dakar','Thiès','Saint-Louis','Diourbel','Louga','Tambacounda','Kaolack','Ziguinchor','Matam','Fatick','Kolda','Kaffrine','Kédougou','Sédhiou'] },
        { name: 'Serbia', states: ['Belgrade','Vojvodina','Šumadija','Western Serbia','Eastern and Southern Serbia','Kosovo and Metohija'] },
        { name: 'Singapore', states: ['Central Region','East Region','North Region','North-East Region','West Region'] },
        { name: 'South Africa', states: ['Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga','North West','Northern Cape','Western Cape'] },
        { name: 'South Korea', states: ['Seoul','Busan','Daegu','Incheon','Gwangju','Daejeon','Ulsan','Sejong','Gyeonggi','Gangwon','North Chungcheong','South Chungcheong','North Jeolla','South Jeolla','North Gyeongsang','South Gyeongsang','Jeju'] },
        { name: 'Spain', states: ['Andalusia','Aragon','Asturias','Balearic Islands','Basque Country','Canary Islands','Cantabria','Castile and León','Castilla-La Mancha','Catalonia','Ceuta','Extremadura','Galicia','La Rioja','Madrid','Melilla','Murcia','Navarre','Valencia'] },
        { name: 'Sri Lanka', states: ['Central','Eastern','North Central','Northern','North Western','Sabaragamuwa','Southern','Uva','Western'] },
        { name: 'Sudan', states: ['Khartoum','Northern Kordofan','Blue Nile','Gedaref','River Nile','Northern','Kassala','Red Sea','White Nile','Sinnar','South Kordofan','West Kordofan','North Darfur','South Darfur','East Darfur','Central Darfur','West Darfur'] },
        { name: 'Sweden', states: ['Blekinge','Dalarna','Gävleborg','Gotland','Halland','Jämtland','Jönköping','Kalmar','Kronoberg','Norrbotten','Örebro','Östergötland','Skåne','Södermanland','Stockholm','Uppsala','Värmland','Västerbotten','Västernorrland','Västmanland','Västra Götaland'] },
        { name: 'Switzerland', states: ['Aargau','Appenzell Ausserrhoden','Appenzell Innerrhoden','Basel-Landschaft','Basel-Stadt','Bern','Fribourg','Geneva','Glarus','Graubünden','Jura','Lucerne','Neuchâtel','Nidwalden','Obwalden','Schaffhausen','Schwyz','Solothurn','St. Gallen','Thurgau','Ticino','Uri','Valais','Vaud','Zug','Zürich'] },
        { name: 'Syria', states: ['Damascus','Aleppo','Homs','Latakia','Hama','Deir ez-Zor','Tartus','Idlib','Raqqa','Daraa','Hasakah','Quneitra','As-Suwayda','Rif Dimashq'] },
        { name: 'Taiwan', states: ['Taipei','New Taipei','Taichung','Kaohsiung','Taoyuan','Tainan','Changhua','Hsinchu','Miaoli','Nantou','Pingtung','Yilan','Yunlin','Chiayi','Hualien','Taitung','Penghu','Kinmen','Matsu'] },
        { name: 'Tanzania', states: ['Arusha','Dar es Salaam','Dodoma','Geita','Iringa','Kagera','Katavi','Kigoma','Kilimanjaro','Lindi','Manyara','Mara','Mbeya','Morogoro','Mtwara','Mwanza','Njombe','Pemba North','Pemba South','Pwani','Rukwa','Ruvuma','Shinyanga','Simiyu','Singida','Tabora','Tanga','Zanzibar Central','Zanzibar North','Zanzibar Urban West'] },
        { name: 'Thailand', states: ['Amnat Charoen','Ang Thong','Bangkok','Bueng Kan','Buri Ram','Chachoengsao','Chai Nat','Chaiyaphum','Chanthaburi','Chiang Mai','Chiang Rai','Chon Buri','Chumphon','Kalasin','Kamphaeng Phet','Kanchanaburi','Khon Kaen','Krabi','Lampang','Lamphun','Loei','Lop Buri','Mae Hong Son','Maha Sarakham','Mukdahan','Nakhon Nayok','Nakhon Pathom','Nakhon Phanom','Nakhon Ratchasima','Nakhon Sawan','Nakhon Si Thammarat','Nan','Narathiwat','Nong Bua Lam Phu','Nong Khai','Nonthaburi','Pathum Thani','Pattani','Phang Nga','Phatthalung','Phayao','Phetchabun','Phetchaburi','Phichit','Phitsanulok','Phra Nakhon Si Ayutthaya','Phrae','Phuket','Prachin Buri','Prachuap Khiri Khan','Ranong','Ratchaburi','Rayong','Roi Et','Sa Kaeo','Sakon Nakhon','Samut Prakan','Samut Sakhon','Samut Songkhram','Saraburi','Satun','Sing Buri','Sisaket','Songkhla','Sukhothai','Suphan Buri','Surat Thani','Surin','Tak','Trang','Trat','Ubon Ratchathani','Udon Thani','Uthai Thani','Uttaradit','Yala','Yasothon'] },
        { name: 'Tunisia', states: ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Kef','Mahdia','Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'] },
        { name: 'Turkey', states: ['Adana','Adıyaman','Afyonkarahisar','Ağrı','Amasya','Ankara','Antalya','Ardahan','Artvin','Aydın','Balıkesir','Bartın','Batman','Bayburt','Bilecik','Bingöl','Bitlis','Bolu','Burdur','Bursa','Çanakkale','Çankırı','Çorum','Denizli','Diyarbakır','Düzce','Edirne','Elazığ','Erzincan','Erzurum','Eskişehir','Gaziantep','Giresun','Gümüşhane','Hakkari','Hatay','Iğdır','Isparta','İstanbul','İzmir','Kahramanmaraş','Karabük','Karaman','Kars','Kastamonu','Kayseri','Kilis','Kırıkkale','Kırklareli','Kırşehir','Kocaeli','Konya','Kütahya','Malatya','Manisa','Mardin','Mersin','Muğla','Muş','Nevşehir','Niğde','Ordu','Osmaniye','Rize','Sakarya','Samsun','Şanlıurfa','Siirt','Sinop','Şırnak','Sivas','Tekirdağ','Tokat','Trabzon','Tunceli','Uşak','Van','Yalova','Yozgat','Zonguldak'] },
        { name: 'Uganda', states: ['Kampala','Gulu','Lira','Mbarara','Jinja','Mbale','Masaka','Arua','Entebbe','Soroti'] },
        { name: 'Ukraine', states: ['Cherkasy','Chernihiv','Chernivtsi','Dnipropetrovsk','Donetsk','Ivano-Frankivsk','Kharkiv','Kherson','Khmelnytskyi','Kyiv','Kirovohrad','Luhansk','Lviv','Mykolaiv','Odessa','Poltava','Rivne','Sumy','Ternopil','Vinnytsia','Volyn','Zakarpattia','Zaporizhia','Zhytomyr','Sevastopol','Crimea'] },
        { name: 'United Arab Emirates', states: ['Abu Dhabi','Dubai','Sharjah','Ajman','Umm al-Quwain','Ras al-Khaimah','Fujairah'] },
        { name: 'United Kingdom', states: ['England','Scotland','Wales','Northern Ireland','Greater London','West Midlands','Greater Manchester','West Yorkshire','South Yorkshire','Merseyside','Tyne and Wear','South East','South West','East Midlands','East of England','North West','North East','Yorkshire and the Humber'] },
        { name: 'United States', states: ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming','District of Columbia','Puerto Rico','Guam','U.S. Virgin Islands','American Samoa','Northern Mariana Islands'] },
        { name: 'Uruguay', states: ['Artigas','Canelones','Cerro Largo','Colonia','Durazno','Flores','Florida','Lavalleja','Maldonado','Montevideo','Paysandú','Río Negro','Rivera','Rocha','Salto','San José','Soriano','Tacuarembó','Treinta y Tres'] },
        { name: 'Uzbekistan', states: ['Tashkent','Andijan','Bukhara','Fergana','Jizzakh','Karakalpakstan','Kashkadarya','Khorezm','Namangan','Navoiy','Samarkand','Sirdaryo','Surkhandarya'] },
        { name: 'Venezuela', states: ['Amazonas','Anzoátegui','Apure','Aragua','Barinas','Bolívar','Carabobo','Cojedes','Delta Amacuro','Falcón','Guárico','Lara','Mérida','Miranda','Monagas','Nueva Esparta','Portuguesa','Sucre','Táchira','Trujillo','Vargas','Yaracuy','Zulia','Capital District','Federal Dependencies'] },
        { name: 'Vietnam', states: ['An Giang','Ba Ria–Vung Tau','Bac Giang','Bac Kan','Bac Lieu','Bac Ninh','Ben Tre','Binh Dinh','Binh Duong','Binh Phuoc','Binh Thuan','Ca Mau','Can Tho','Cao Bang','Da Nang','Dak Lak','Dak Nong','Dien Bien','Dong Nai','Dong Thap','Gia Lai','Ha Giang','Ha Nam','Ha Noi','Ha Tinh','Hai Duong','Hai Phong','Hau Giang','Hoa Binh','Ho Chi Minh City','Hung Yen','Khanh Hoa','Kien Giang','Kon Tum','Lai Chau','Lam Dong','Lang Son','Lao Cai','Long An','Nam Dinh','Nghe An','Ninh Binh','Ninh Thuan','Phu Tho','Phu Yen','Quang Binh','Quang Nam','Quang Ngai','Quang Ninh','Quang Tri','Soc Trang','Son La','Tay Ninh','Thai Binh','Thai Nguyen','Thanh Hoa','Thua Thien Hue','Tien Giang','Tra Vinh','Tuyen Quang','Vinh Long','Vinh Phuc','Yen Bai'] },
        { name: 'Yemen', states: ['Abyan','Aden','Al Bayda','Al Dhale\'e','Al Hudaydah','Al Jawf','Al Mahrah','Al Mahwit','Amran','Dhamar','Hadramawt','Hajjah','Ibb','Lahij','Marib','Raymah','Saada','Sanaa','Shabwah','Socotra','Taiz'] },
        { name: 'Zambia', states: ['Central','Copperbelt','Eastern','Luapula','Lusaka','Muchinga','Northern','North-Western','Southern','Western'] },
        { name: 'Zimbabwe', states: ['Bulawayo','Harare','Manicaland','Mashonaland Central','Mashonaland East','Mashonaland West','Masvingo','Matabeleland North','Matabeleland South','Midlands'] }
    ];

    function ddPopulateCountries($sel, savedValue) {
        // Sort: United States first, then alphabetical
        var sorted = DD_COUNTRIES.slice().sort(function(a, b) {
            if (a.name === 'United States') return -1;
            if (b.name === 'United States') return 1;
            if (a.name === 'United Kingdom') return -1;
            if (b.name === 'United Kingdom') return 1;
            return a.name.localeCompare(b.name);
        });
        sorted.forEach(function(c) {
            var sel = (c.name === savedValue) ? ' selected' : '';
            $sel.append('<option value="' + c.name + '"' + sel + '>' + c.name + '</option>');
        });
    }

    function ddPopulateStates($stateSel, countryName, savedState) {
        $stateSel.find('option:not(:first)').remove();
        var country = DD_COUNTRIES.find(function(c) { return c.name === countryName; });
        if (country && country.states.length) {
            country.states.forEach(function(s) {
                var sel = (s === savedState) ? ' selected' : '';
                $stateSel.append('<option value="' + s + '"' + sel + '>' + s + '</option>');
            });
            $stateSel.prop('disabled', false);
        } else {
            $stateSel.prop('disabled', true);
        }
        // Re-init Select2 if present
        if ($.fn.select2 && $stateSel.hasClass('select2-hidden-accessible')) {
            $stateSel.trigger('change.select2');
        }
    }

    // Init all country selects on page
    function ddInitCountrySelects() {
        $('.dd-country-select').each(function() {
            var $countrySel = $(this);
            var stateTargetId = $countrySel.data('state-target');
            var $stateSel = stateTargetId ? $('#' + stateTargetId) : $countrySel.closest('.dd-dog-form__grid, .dd-profile-form, form').find('.dd-state-select').first();

            // Determine saved values
            var savedCountry = $countrySel.val() || $('#' + $countrySel.attr('id') + '-hidden').val() || $countrySel.find('option[selected]').val() || '';
            var savedState = $stateSel.data('saved') || '';

            // Populate countries
            ddPopulateCountries($countrySel, savedCountry);

            // Init Select2 on country if available
            if ($.fn.select2) {
                $countrySel.select2({ placeholder: 'Select Country', allowClear: true });
            }

            // Populate states from saved country
            if (savedCountry) {
                ddPopulateStates($stateSel, savedCountry, savedState);
            } else {
                $stateSel.prop('disabled', true);
            }

            // Init Select2 on state if available
            if ($.fn.select2) {
                $stateSel.select2({ placeholder: 'Select State / Province', allowClear: true });
            }

            // On country change → repopulate states
            $countrySel.on('change', function() {
                var country = $(this).val();
                ddPopulateStates($stateSel, country, '');
                if ($.fn.select2) {
                    $stateSel.trigger('change.select2');
                }
            });
        });
    }

    // Run on DOM ready
    ddInitCountrySelects();

})(jQuery);

