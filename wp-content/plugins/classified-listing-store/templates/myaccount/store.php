<?php
/**
 * @author     RadiusTheme
 * @package    classified-listing-store/templates
 * @version    1.0.0
 *
 * @var Store $store
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use RtclStore\Helpers\Functions as StoreFunctions;
use RtclStore\Models\Store;
use RtclStore\Resources\Options;
use Rtcl\Helpers\Text;

$max_image_size     = Functions::formatBytes( Functions::get_max_upload(), 0 );
$allowed_image_type = implode( ', ', (array) Functions::get_option_item( 'rtcl_misc_settings', 'image_allowed_type', [
	'png',
	'jpeg',
	'jpg'
] ) );

?>
<?php if ( StoreFunctions::is_enable_store_manager() ): ?>
    <div id="rtcl-store-sub-menu" class="rtcl-account-sub-menu">
        <ul>
            <li class="active"><a class="" data-target="settings"
                                  href="#"><?php esc_attr_e( "Settings", "classified-listing-store" ); ?></a>
            </li>
            <li><a data-target="managers" href="#"><?php esc_attr_e( "Managers", "classified-listing-store" ); ?></a>
            </li>
        </ul>
    </div>
<?php endif; ?>
<div id="rtcl-store-content-wrap">
    <div class="rtcl-store-settings rtcl-store-content" id="rtcl-store-settings-content">
        <div id="rtcl-store-media">
            <div class="rtcl-form-group">
                <label class="rtcl-field-label"><?php esc_html_e( "Store Banner", 'classified-listing-store' ); ?></label>
                <div class="rtcl-store-media-item rtcl-store-banner-wrap">
					<?php $bannerClass = $store && $store->get_banner_id() ? '' : ' no-banner'; ?>
                    <div class="rtcl-store-banner<?php echo esc_attr( $bannerClass ); ?>">
                        <div class="rtcl-media-action">
                            <span class="add">
                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_159_2487)">
                                        <path d="M3.50629 19.4726C1.60288 19.4726 0 17.9699 0 15.9663V13.562C0 13.2615 0.200366 12.9609 0.601085 12.9609C0.901624 12.9609 1.20215 13.1613 1.20215 13.562V15.9663C1.20215 17.2687 2.20395 18.1703 3.40611 18.1703H15.6281C16.9304 18.1703 17.832 17.1685 17.832 15.9663V13.562C17.832 13.2615 18.0324 12.9609 18.4331 12.9609C18.7336 12.9609 19.0342 13.1613 19.0342 13.562V15.9663C19.0342 17.8697 17.5315 19.4726 15.5279 19.4726H3.50629Z"
                                              fill="#FF3C48"/>
                                        <path d="M9.51648 15.0647C9.21594 15.0647 8.9154 14.8644 8.9154 14.4636V3.64422L5.91 6.7498C5.80982 6.84998 5.60947 6.95016 5.60947 6.95016C5.50929 6.95016 5.40911 6.95015 5.30893 6.84997C5.30893 6.84997 5.20875 6.7498 5.10857 6.7498C5.0084 6.64962 4.9082 6.54943 4.9082 6.34907C4.9082 6.14871 4.90821 6.04854 5.00839 5.94836L9.01558 1.94117C9.21594 1.74081 9.31612 1.64062 9.51648 1.64062C9.71684 1.64062 9.81701 1.7408 9.91719 1.84098L13.9244 5.84817C14.0246 5.94835 14.1248 6.04854 14.1248 6.2489C14.1248 6.44926 14.0246 6.54944 13.9244 6.64962C13.8242 6.74979 13.724 6.84997 13.5237 6.84997C13.3233 6.84997 13.2231 6.74979 13.123 6.64962L10.1176 3.64422L10.2177 14.2633C10.2177 14.664 9.9172 15.0647 9.51648 15.0647Z"
                                              fill="#FF3C48"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_159_2487">
                                            <rect width="20.25" height="20.25" fill="white" transform="translate(0 0.375)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <?php esc_html_e( "Upload Image", "classified-listing-store" ) ?>
                            </span>
                            <span class="remove">
                                <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.15 17C2.625 17 2.1875 16.8152 1.8375 16.4457C1.4875 16.0761 1.3125 15.6141 1.3125 15.0598V6.19022C0.9625 6.09783 0.7 6.00543 0.4375 5.72826C0.175 5.3587 0 4.98913 0 4.52717V3.88043C0 3.51087 0.175 3.04891 0.4375 2.77174C0.7 2.49457 1.1375 2.30978 1.575 2.30978H3.325V1.94022C3.325 1.38587 3.5 0.923913 3.85 0.554348C4.2 0.184783 4.6375 0 5.1625 0H8.8375C9.275 0 9.8 0.184783 10.15 0.554348C10.5 0.923913 10.675 1.38587 10.675 1.84783V2.21739H12.425C12.8625 2.21739 13.2125 2.40217 13.5625 2.67935C13.825 2.95652 14 3.41848 14 3.88043V4.52717C14 4.98913 13.825 5.3587 13.5625 5.72826C13.3 6.00543 13.0375 6.09783 12.6875 6.19022V15.0598C12.6875 15.6141 12.5125 16.0761 12.1625 16.4457C11.8125 16.8152 11.375 17 10.85 17H3.15ZM2.625 15.0598C2.625 15.2446 2.7125 15.337 2.8 15.4293C2.8875 15.5217 3.0625 15.6141 3.15 15.6141H10.7625C10.9375 15.6141 11.025 15.5217 11.1125 15.4293C11.2 15.337 11.2875 15.1522 11.2875 15.0598V6.19022H2.625V15.0598ZM1.575 3.69565C1.4875 3.69565 1.4 3.69565 1.4 3.69565C1.3125 3.78804 1.3125 3.88043 1.3125 3.88043V4.52717C1.3125 4.61957 1.3125 4.61957 1.4 4.71196C1.4875 4.80435 1.4875 4.80435 1.575 4.80435H12.425C12.5125 4.80435 12.5125 4.80435 12.6 4.71196C12.6 4.61957 12.6 4.61957 12.6 4.52717V3.88043C12.6 3.78804 12.6 3.78804 12.5125 3.69565H1.575ZM9.275 2.30978V1.94022C9.275 1.84783 9.1875 1.66304 9.1 1.57065C9.0125 1.47826 8.925 1.38587 8.75 1.38587H5.1625C5.075 1.38587 4.9 1.47826 4.8125 1.57065C4.725 1.66304 4.6375 1.75543 4.6375 1.94022V2.30978H9.275Z"
                                          fill="#FF3C48"/>
                                    <path d="M4.37502 14.2283C4.11252 14.2283 3.85002 13.9511 3.85002 13.6739V8.13043C3.85002 7.85326 4.11252 7.57609 4.37502 7.57609C4.63752 7.57609 4.90002 7.85326 4.90002 8.13043V13.6739C4.90002 13.9511 4.63752 14.2283 4.37502 14.2283Z"
                                          fill="#FF3C48"/>
                                    <path d="M7.00002 14.2283C6.73752 14.2283 6.47502 13.9511 6.47502 13.6739V8.13043C6.47502 7.85326 6.73752 7.57609 7.00002 7.57609C7.26252 7.57609 7.52502 7.85326 7.52502 8.13043V13.6739C7.52502 13.9511 7.26252 14.2283 7.00002 14.2283Z"
                                          fill="#FF3C48"/>
                                    <path d="M9.62502 14.2283C9.36252 14.2283 9.10002 13.9511 9.10002 13.6739V8.13043C9.10002 7.85326 9.36252 7.57609 9.62502 7.57609C9.88752 7.57609 10.15 7.85326 10.15 8.13043V13.6739C10.15 13.9511 9.88752 14.2283 9.62502 14.2283Z"
                                          fill="#FF3C48"/>
                                </svg>
                                <?php esc_html_e( "Delete", "classified-listing-store" ) ?>
                            </span>
                        </div>
                        <div class="banner"><?php $store ? $store->the_banner() : null; ?></div>
                    </div>
                    <div class="rtcl-form-notice">
						<?php
						$banner_size = (array) Functions::get_option_item( 'rtcl_misc_settings', 'store_banner_size', [
							'width'  => 992,
							'height' => 300,
							'crop'   => 'yes'
						] );
						printf(
							esc_html__( "Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image type (%s)", "classified-listing-store" ),
							absint( $banner_size['width'] ),
							absint( $banner_size['height'] ),
							esc_html( $max_image_size ),
							esc_html( $allowed_image_type )
						) ?>
                    </div>
                </div>
            </div>
            <div class="rtcl-form-group">
                <label class="rtcl-field-label"><?php esc_html_e( "Store Logo", 'classified-listing-store' ); ?></label>
                <div class="rtcl-store-media-item rtcl-store-logo-wrap">
					<?php $logoClass = $store && $store->has_logo() ? '' : ' no-logo'; ?>
                    <div class="rtcl-store-logo<?php echo esc_attr( $logoClass ); ?>">
                        <div class="logo"><?php $store ? $store->the_logo() : ''; ?></div>
                    </div>
                    <div class="rtcl-store-info">
                        <div class="rtcl-media-action">
                            <span class="add">
                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_159_2487)">
                                        <path d="M3.50629 19.4726C1.60288 19.4726 0 17.9699 0 15.9663V13.562C0 13.2615 0.200366 12.9609 0.601085 12.9609C0.901624 12.9609 1.20215 13.1613 1.20215 13.562V15.9663C1.20215 17.2687 2.20395 18.1703 3.40611 18.1703H15.6281C16.9304 18.1703 17.832 17.1685 17.832 15.9663V13.562C17.832 13.2615 18.0324 12.9609 18.4331 12.9609C18.7336 12.9609 19.0342 13.1613 19.0342 13.562V15.9663C19.0342 17.8697 17.5315 19.4726 15.5279 19.4726H3.50629Z"
                                              fill="#FF3C48"/>
                                        <path d="M9.51648 15.0647C9.21594 15.0647 8.9154 14.8644 8.9154 14.4636V3.64422L5.91 6.7498C5.80982 6.84998 5.60947 6.95016 5.60947 6.95016C5.50929 6.95016 5.40911 6.95015 5.30893 6.84997C5.30893 6.84997 5.20875 6.7498 5.10857 6.7498C5.0084 6.64962 4.9082 6.54943 4.9082 6.34907C4.9082 6.14871 4.90821 6.04854 5.00839 5.94836L9.01558 1.94117C9.21594 1.74081 9.31612 1.64062 9.51648 1.64062C9.71684 1.64062 9.81701 1.7408 9.91719 1.84098L13.9244 5.84817C14.0246 5.94835 14.1248 6.04854 14.1248 6.2489C14.1248 6.44926 14.0246 6.54944 13.9244 6.64962C13.8242 6.74979 13.724 6.84997 13.5237 6.84997C13.3233 6.84997 13.2231 6.74979 13.123 6.64962L10.1176 3.64422L10.2177 14.2633C10.2177 14.664 9.9172 15.0647 9.51648 15.0647Z"
                                              fill="#FF3C48"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_159_2487">
                                            <rect width="20.25" height="20.25" fill="white" transform="translate(0 0.375)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <?php esc_html_e( "Upload Image", "classified-listing-store" ) ?>
                            </span>
                            <span class="remove">
                                <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.15 17C2.625 17 2.1875 16.8152 1.8375 16.4457C1.4875 16.0761 1.3125 15.6141 1.3125 15.0598V6.19022C0.9625 6.09783 0.7 6.00543 0.4375 5.72826C0.175 5.3587 0 4.98913 0 4.52717V3.88043C0 3.51087 0.175 3.04891 0.4375 2.77174C0.7 2.49457 1.1375 2.30978 1.575 2.30978H3.325V1.94022C3.325 1.38587 3.5 0.923913 3.85 0.554348C4.2 0.184783 4.6375 0 5.1625 0H8.8375C9.275 0 9.8 0.184783 10.15 0.554348C10.5 0.923913 10.675 1.38587 10.675 1.84783V2.21739H12.425C12.8625 2.21739 13.2125 2.40217 13.5625 2.67935C13.825 2.95652 14 3.41848 14 3.88043V4.52717C14 4.98913 13.825 5.3587 13.5625 5.72826C13.3 6.00543 13.0375 6.09783 12.6875 6.19022V15.0598C12.6875 15.6141 12.5125 16.0761 12.1625 16.4457C11.8125 16.8152 11.375 17 10.85 17H3.15ZM2.625 15.0598C2.625 15.2446 2.7125 15.337 2.8 15.4293C2.8875 15.5217 3.0625 15.6141 3.15 15.6141H10.7625C10.9375 15.6141 11.025 15.5217 11.1125 15.4293C11.2 15.337 11.2875 15.1522 11.2875 15.0598V6.19022H2.625V15.0598ZM1.575 3.69565C1.4875 3.69565 1.4 3.69565 1.4 3.69565C1.3125 3.78804 1.3125 3.88043 1.3125 3.88043V4.52717C1.3125 4.61957 1.3125 4.61957 1.4 4.71196C1.4875 4.80435 1.4875 4.80435 1.575 4.80435H12.425C12.5125 4.80435 12.5125 4.80435 12.6 4.71196C12.6 4.61957 12.6 4.61957 12.6 4.52717V3.88043C12.6 3.78804 12.6 3.78804 12.5125 3.69565H1.575ZM9.275 2.30978V1.94022C9.275 1.84783 9.1875 1.66304 9.1 1.57065C9.0125 1.47826 8.925 1.38587 8.75 1.38587H5.1625C5.075 1.38587 4.9 1.47826 4.8125 1.57065C4.725 1.66304 4.6375 1.75543 4.6375 1.94022V2.30978H9.275Z"
                                          fill="#FF3C48"/>
                                    <path d="M4.37502 14.2283C4.11252 14.2283 3.85002 13.9511 3.85002 13.6739V8.13043C3.85002 7.85326 4.11252 7.57609 4.37502 7.57609C4.63752 7.57609 4.90002 7.85326 4.90002 8.13043V13.6739C4.90002 13.9511 4.63752 14.2283 4.37502 14.2283Z"
                                          fill="#FF3C48"/>
                                    <path d="M7.00002 14.2283C6.73752 14.2283 6.47502 13.9511 6.47502 13.6739V8.13043C6.47502 7.85326 6.73752 7.57609 7.00002 7.57609C7.26252 7.57609 7.52502 7.85326 7.52502 8.13043V13.6739C7.52502 13.9511 7.26252 14.2283 7.00002 14.2283Z"
                                          fill="#FF3C48"/>
                                    <path d="M9.62502 14.2283C9.36252 14.2283 9.10002 13.9511 9.10002 13.6739V8.13043C9.10002 7.85326 9.36252 7.57609 9.62502 7.57609C9.88752 7.57609 10.15 7.85326 10.15 8.13043V13.6739C10.15 13.9511 9.88752 14.2283 9.62502 14.2283Z"
                                          fill="#FF3C48"/>
                                </svg>
                                <?php esc_html_e( "Delete", "classified-listing-store" ) ?>
                            </span>
                        </div>
                        <div class="rtcl-form-notice">
							<?php
							$logo_size = Functions::get_option_item( 'rtcl_misc_settings', 'store_logo_size', [
								'width'  => 200,
								'height' => 150,
								'crop'   => 'yes'
							] );
							printf(
								esc_html__( "Recommended image size to (%dx%d)px, Maximum file size %s, Allowed image types %s", "classified-listing-store" ),
								absint( $logo_size['width'] ),
								absint( $logo_size['height'] ),
								esc_html( $max_image_size ),
								esc_html( $allowed_image_type )
							) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form id="rtcl-store-settings" method="post" role="form">
			<?php do_action( 'rtcl_store_my_account_form_start', $store ); ?>
            <div id="rtcl-store-hours">
                <div class="rtcl-form-group">
                    <label class="rtcl-field-label"><?php esc_html_e( "Opening hours", "classified-listing-store" ) ?></label>
                    <div class="oh-list-wrap">
                        <div class="form-group">
                            <div id="oh-type-wrap">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="meta[oh_type]"
                                           id="oh-type-open-on-selected"
                                           value="selected" <?php checked( "selected", $store ? $store->get_open_hour_type() : '' ) ?>>
                                    <label class="form-check-label" for="oh-type-open-on-selected">
										<?php esc_html_e( "Open on selected hours", "classified-listing-store" ) ?>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="meta[oh_type]"
                                           id="oh-type-always-open"
                                           value="always" <?php checked( "always", $store ? $store->get_open_hour_type() : '' ) ?>>
                                    <label class="form-check-label" for="oh-type-always-open">
										<?php esc_html_e( "Always open", "classified-listing-store" ) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group<?php echo esc_attr( $store && $store->get_open_hour_type() !== 'selected' ? ' rtcl-hide' : '' ); ?>"
                             id="oh-list">
							<?php
							$oh_hours = $store ? $store->get_open_hours() : [];
							$days     = Options::store_open_hour_days();
							foreach ( $days as $dayKey => $day ) {
								$idDay = "oh-" . $dayKey . "-active";
								?>
                                <div class="oh-item">
                                    <table>
                                        <tr>
                                            <td class="oh-time-active">
                                                <input id="<?php echo esc_attr( $idDay ); ?>" name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][active]"
                                                       value="1" <?php checked( 1, isset( $oh_hours[ $dayKey ]['active'] ) ? 1 : 0 ) ?>
                                                       type="checkbox">
	                                            <label for="<?php echo esc_attr( $idDay ); ?>"><?php echo esc_html( $day ) ?></label>
                                            </td>
                                            <td class="oh-time-hour">
                                                <div class="oh-time"><input type="text"
                                                                            value="<?php echo isset( $oh_hours[ $dayKey ]['open'] )
													                            ? esc_attr( $oh_hours[ $dayKey ]['open'] ) : null; ?>"
                                                                            name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][open]"
                                                                            autocomplete="off"
                                                                            class="rtcl-form-control open-hour"> - <input
                                                            value="<?php echo isset( $oh_hours[ $dayKey ]['open'] ) ? esc_attr( $oh_hours[ $dayKey ]['close'] )
																: null; ?>"
                                                            type="text"
                                                            name="meta[oh_hours][<?php echo esc_attr( $dayKey ); ?>][close]"
                                                            autocomplete="off"
                                                            class="rtcl-form-control close-hour"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
							<?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rtcl-form-group-wrap">
                <div class="rtcl-form-group">
                    <label for="rtcl-store-name" class="rtcl-field-label">
						<?php esc_html_e( 'Store Name', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
                        <input type="text" name="name" id="rtcl-store-name"
                               value="<?php echo esc_attr( $store ? $store->get_the_title() : '' ); ?>" class="rtcl-form-control"
                               required/>
                    </div>
                </div>

                <div class="rtcl-form-group">
                    <label for="rtcl-store-id" class="rtcl-field-label">
						<?php esc_html_e( 'Store Slug / URL', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
						<?php
						$id          = $store ? $store->get_slug() : '';
						$storeIdAttr = ( $id ) ? " disabled readonly" : null; ?>
                        <input type="text" name="id" id="rtcl-store-id"
                               value="<?php echo esc_attr( $id ); ?>" class="rtcl-form-control"
                               required<?php echo esc_attr( $storeIdAttr ); ?>/>
                        <small class="form-text"><?php esc_html_e( 'This should be unique and you can\'t able to change in future. This will be your store url.',
								'classified-listing-store' ); ?></small>
                    </div>
                </div>

                <div class="rtcl-form-group">
                    <label for="rtcl-slogan" class="rtcl-field-label">
						<?php esc_html_e( 'Slogan', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
                        <input type="text" name="meta[slogan]" id="rtcl-slogan"
                               value="<?php echo esc_attr( $store ? $store->get_the_slogan() : '' ); ?>"
                               class="rtcl-form-control"/>
                    </div>
                </div>
            </div>
			<?php
			$selectedTerm   = StoreFunctions::get_store_selected_term_id( $store ? $store->get_id() : 0 );
			$selectedTermId = isset( $selectedTerm['termId'] ) ? $selectedTerm['termId'] : [];
			$parent         = isset( $selectedTerm['parent'] ) ? $selectedTerm['parent'] : [];

			$childTerm = ! empty( $selectedTermId ) ? end( $selectedTermId ) : 0;

			$storeTerms = StoreFunctions::get_store_category();
			?>
            <div class="rtcl-store-category-wrap">
                <div class="rtcl-form-group" id="rtcl-store-cat-row">
                    <label for="rtcl-store-category"
                           class="rtcl-field-label"><?php esc_html_e( 'Store Category', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col" id="rtcl-store-category-holder">
                        <select class="rtcl-form-control" id="rtcl-store-category">
                            <option value=""><?php echo esc_html( Text::get_select_category_text() ) ?></option>
							<?php
							if ( ! empty( $storeTerms ) ) {
								foreach ( $storeTerms as $cat ) {
									$selected = ( ! empty( $parent ) && $parent->term_id == $cat->term_id ) ? 'selected' : '';
									echo "<option {$selected} value='{$cat->term_id}'>{$cat->name}</option>";
								}
							}
							?>
                        </select>
                    </div>
                    <input type="hidden" id="rtcl-store-cat-id" value="<?php echo esc_attr( $childTerm ); ?>"
                           name="store-category"/>
                </div>
				<?php
				$subCategory = ! empty( $selectedTermId ) ? StoreFunctions::get_store_category( $parent->term_id ) : [];
				$hideRow     = empty( $subCategory ) ? ' rtcl-hide' : '';
				?>
                <div class="rtcl-form-group<?php echo esc_attr( $hideRow ); ?>" id="rtcl-store-sub-cat-row">
                    <label for="rtcl-store-sub-category"
                           class="rtcl-field-label"><?php esc_html_e( 'Store Sub Category', 'classified-listing-store' ); ?>
                    </label>
                    <div class="col-sm-9" id="rtcl-store-sub-category-holder">
						<?php
						if ( ! empty( $selectedTermId ) ) {
							foreach ( $selectedTermId as $parentTerm ) {
								$childTerm = StoreFunctions::get_store_category( $parentTerm );
								if ( ! empty( $childTerm ) ) {
									?>
                                    <select class="rtcl-form-control" required>
                                        <option value=""><?php echo esc_html( Text::get_select_category_text() ) ?></option>
										<?php
										foreach ( $childTerm as $term ) {
											$selected = ( in_array( $term->term_id, $selectedTermId ) ) ? 'selected' : '';
											echo "<option {$selected} value='{$term->term_id}'>{$term->name}</option>";
										}
										?>
                                    </select>
									<?php
								}
							}
						}
						?>
                    </div>
                </div>
            </div>
            <div class="rtcl-form-group-wrap">
                <div class="rtcl-form-group">
                    <label for="rtcl-email" class="rtcl-field-label">
						<?php esc_html_e( 'Store E-mail Address', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
                        <input type="text" name="meta[email]" id="rtcl-email" class="rtcl-form-control"
                               value="<?php echo esc_attr( $store ? $store->get_email() : '' ); ?>"/>
                    </div>
                </div>
                <div class="rtcl-form-group">
                    <label for="rtcl-phone" class="rtcl-field-label">
						<?php esc_html_e( 'Store Phone', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
                        <input type="text" name="meta[phone]" id="rtcl-phone"
                               value="<?php echo esc_attr( $store ? $store->get_phone() : '' ) ?>"
                               class="rtcl-form-control"/>
                    </div>
                </div>
                <div class="rtcl-form-group">
                    <label for="rtcl-website" class="rtcl-field-label">
						<?php esc_html_e( 'Store Website', 'classified-listing-store' ); ?>
                    </label>
                    <div class="rtcl-field-col">
                        <input type="url" name="meta[website]" id="rtcl-website"
                               value="<?php echo esc_url( $store ? $store->get_website() : '' ); ?>"
                               class="rtcl-form-control"/>
                    </div>
                </div>
            </div>
            <div class="rtcl-form-group">
                <label for="rtcl-store-address" class="rtcl-field-label">
					<?php esc_html_e( 'Store Address', 'classified-listing-store' ); ?>
                </label>
                <div class="rtcl-field-col">
                    <textarea class="rtcl-form-control" id="rtcl-store-address" name="meta[address]"><?php echo esc_textarea( $store ? $store->get_address()
		                    : '' ) ?></textarea>
                </div>
            </div>
            <div class="rtcl-form-group">
                <label for="rtcl-store-details" class="rtcl-field-label">
					<?php esc_html_e( 'Store Details', 'classified-listing-store' ); ?>
                </label>
                <div class="rtcl-field-col">
                <textarea rows="6" class="rtcl-form-control"
                          name="details"
                          id="rtcl-store-details"><?php echo esc_textarea( $store ? $store->get_the_description() : "" ) ?></textarea>
                </div>
            </div>
            <div class="rtcl-form-group rtcl-social-wrap-row">
                <label for="rtcl-social" class="rtcl-field-label">
					<?php esc_html_e( 'Store Media', 'classified-listing-store' ); ?>
                </label>
                <div class="rtcl-field-col rtcl-social-wrap">
					<?php
					$social_options = Options::store_social_media_options();
					$social_media   = $store ? $store->get_social_media() : [];
					foreach ( $social_options as $key => $social_option ) {
						echo sprintf( '<input type="url" name="meta[social_media][%1$s]" id="rtcl-store-social-%1$s" value="%2$s" placeholder="%3$s" class="rtcl-form-control"/>',
							$key,
							esc_url( isset( $social_media[ $key ] ) ? $social_media[ $key ] : '' ),
							$social_option
						);
					}
					?>

                </div>
            </div>
			<?php do_action( 'rtcl_store_my_account_form_end', $store ); ?>
            <div class="rtcl-form-group">
                <div class="rtcl-field-col">
                    <input type="submit" name="submit" class="btn btn-primary"
                           value="<?php esc_html_e( 'Update Store', 'classified-listing-store' ); ?>"/>
                </div>
            </div>
        </form>
        <div class="rtcl-response"></div>
    </div>
	<?php if ( $store = StoreFunctions::get_current_user_store() ):
		$manager_ids = $store->get_manager_ids();
		$invitations = $store->get_manager_invitation_list();
		?>
        <div id="rtcl-store-managers-content" class="rtcl-store-content">
            <div class="rtcl-store-manager-action">
                <span class="rtcl-store-invite-manager btn btn-primary">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.525 0.975C5.525 0.436522 5.96152 0 6.5 0C7.03848 0 7.475 0.436522 7.475 0.975V12.025C7.475 12.5635 7.03848 13 6.5 13C5.96152 13 5.525 12.5635 5.525 12.025V0.975Z" fill="white"/>
                        <path d="M12.025 5.525C12.5635 5.525 13 5.96152 13 6.5C13 7.03848 12.5635 7.475 12.025 7.475L0.975 7.475C0.436522 7.475 -2.35376e-08 7.03848 0 6.5C2.35376e-08 5.96152 0.436522 5.525 0.975 5.525L12.025 5.525Z" fill="white"/>
                    </svg>
                    <?php esc_html_e( "Invite Manager", "classified-listing-store" ); ?>
                </span>
            </div>
            <div id="rtcl-store-managers">
				<?php
				$invitations_ids = ! empty( $invitations ) ? array_keys( $invitations ) : [];
				$manager_ids     = array_merge( $manager_ids, $invitations_ids );
				if ( ! empty( $manager_ids ) ) {
					foreach ( $manager_ids as $manager_id ) {
						$user = get_user_by( 'id', $manager_id );
						if ( ! $user ) {
							continue;
						}
						$isPending = ! empty( $invitations_ids ) && in_array( $user->ID, $invitations_ids );
						$name      = trim( implode( ' ', [ $user->first_name, $user->last_name ] ) );
						$name      = $name ? $name : $user->display_name;
						$pp_id     = absint( get_user_meta( $manager_id, '_rtcl_pp_id', true ) );
						?>
                        <div class="rtcl-store-manager">
                            <div class="rtcl-store-m-avatar"><?php echo $pp_id ? wp_get_attachment_image( $pp_id, [
									100,
									100
								] ) : get_avatar( $manager_id ) ?></div>
                            <div class="rtcl-store-m-info">
                                <div class="rtcl-m-info-name"><?php echo esc_html( $name ) ?></div>
                                <div class="rtcl-m-info-email"><?php echo esc_html( $user->user_email ); ?></div>
								<?php if ( $isPending ) { ?>
                                    <div class="rtcl-m-info-status pending"><?php esc_html_e( "Pending", "classified-listing-store" ); ?></div>
								<?php } else { ?>
                                    <div class="rtcl-m-info-status">
                                        <a href="<?php echo esc_url( add_query_arg( [ 'manager' => $user->user_login ],
											Link::get_account_endpoint_url( 'listings' ) ) ) ?>">
											<?php printf( esc_html__( 'Listings (%s)', 'classified-listing-store' ),
												count( $store->get_manager_listing_ids( $manager_id ) ) ); ?>
                                        </a>
                                    </div>
								<?php } ?>
                            </div>
                            <span class="rtcl-store-manager-remove rtcl-icon rtcl-icon-trash"
                                  data-manager_user_id="<?php echo absint( $manager_id ) ?>"></span>
                        </div>
						<?php
					}
				}
				?>
            </div>
        </div>
	<?php endif; ?>
</div>