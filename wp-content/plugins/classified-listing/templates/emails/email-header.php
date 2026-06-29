<?php
/**
 * Email Header
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/email-header.php.
 *
 * @see
 * @package ClassifiedListing/Templates/Emails
 * @version 2.3.0
 *
 * @var RtclEmail $email
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>"/>
	<title><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></title>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="
0">
<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="<?php echo esc_attr( Functions::email_wrapper_style( $email ) ); ?>">
	<table border="0" cellpadding="0" cellspacing="0"
		   style="width: 100%; height: 100%; max-width: 100%; table-layout: fixed">
		<tr>
			<td align="center" valign="top" style="max-width: 600px">
				<div id="template_header_image">
					<?php
					$img = $email->get_header_image_url();
					if ( $img ) {
						echo '<p style="margin-top:0;' . esc_url( Functions::email_image_style( $email ) ) . '"><img src="' . esc_url( $img ) . '" alt="'
							 . esc_html( get_bloginfo( 'name', 'display' ) ) . '" /></p>';
					}
					?>
				</div>
				<table border="0" cellpadding="0" cellspacing="0" id="template_container"
					   style="<?php echo esc_attr( Functions::email_template_container_style( $email ) ) ?>">
					<tr>
						<td align="center" valign="top">
							<!-- Header -->
							<table border="0" cellpadding="0" cellspacing="0" id="template_header"
								   style="<?php echo esc_attr( Functions::email_template_header_style( $email ) ); ?>">
								<tr>
									<td id="header_wrapper" style="<?php echo esc_attr( Functions::email_header_wrapper_style( $email ) ); ?>">
										<h1 style="<?php echo esc_attr( Functions::email_h1_style( $email ) ) ?><?php echo esc_attr( Functions::email_template_header_h1_style( $email ) ) ?>"><?php echo esc_html( $email->get_heading() ); ?></h1>
									</td>
								</tr>
							</table>
							<!-- End Header -->
						</td>
					</tr>
					<tr>
						<td align="center" valign="top">
							<!-- Body -->
							<table border="0" cellpadding="0" cellspacing="0" id="template_body"
								   style="width: 100%; max-width: 600px; table-layout: fixed">
								<tr>
									<td valign="top" id="body_content" style="<?php echo esc_attr( Functions::email_body_content_style( $email ) ); ?>">
										<!-- Content -->
										<table border="0" cellpadding="20" cellspacing="0" width="100%"
											   style="max-width: 100%; table-layout: fixed">
											<tr>
												<td valign="top" style="<?php echo esc_attr( Functions::email_body_content_table_td_style( $email ) ); ?>">
													<div id="body_content_inner"
														 style="<?php echo esc_attr( Functions::email_body_content_inner_style( $email ) ); ?>">
