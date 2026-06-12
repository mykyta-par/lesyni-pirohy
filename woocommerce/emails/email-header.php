<?php
/**
 * Email header — overrides woocommerce/templates/emails/email-header.php
 *
 * @version 10.7.0
 * @var string $email_heading
 */
defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></title>
    <?php do_action( 'woocommerce_email_styles' ); ?>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">

<div id="wrapper">
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="outer_wrapper">
        <tr>
            <td>
                <div id="template_container">

                    <!-- Header -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header">
                        <tr>
                            <td>
                                <!-- Logo -->
                                <div id="template_header_image">
                                    <?php
                                    $logo_url = get_template_directory_uri() . '/assets/images/logo.png';
                                    ?>
                                    <img src="<?php echo esc_url( $logo_url ); ?>"
                                         alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                                         style="height:44px;width:auto;display:block;filter:brightness(0) invert(1);">
                                </div>
                                <!-- Heading -->
                                <div class="lesyni-header-title">
                                    <h1><?php echo esc_html( $email_heading ); ?></h1>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Body -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body">
                        <tr>
                            <td id="body_content">
                                <div id="body_content_inner">
