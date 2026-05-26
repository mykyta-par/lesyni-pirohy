<?php
/**
 * Customer processing order email — custom branded template
 * Overrides: woocommerce/templates/emails/customer-processing-order.php
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var string   $additional_content
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var WC_Email $email
 */
defined( 'ABSPATH' ) || exit;

// ── Order data ────────────────────────────────────────────────────────
$first_name        = $order->get_billing_first_name();
$order_number      = $order->get_order_number();
$order_date        = $order->get_date_created()
    ? $order->get_date_created()->date_i18n( 'j F Y' )
    : date_i18n( 'j F Y' );

$total_raw         = (float) $order->get_total();
$subtotal_raw      = (float) $order->get_subtotal();
$shipping_raw      = (float) $order->get_shipping_total();
$discount_raw      = (float) $order->get_discount_total();
$payment_method    = $order->get_payment_method_title();
$customer_note     = $order->get_customer_note();
$additional_content = isset( $additional_content ) ? $additional_content : '';

$p = fn( float $n ) => number_format( $n, 0, '.', ' ' ) . ' грн';

// ── Billing address ───────────────────────────────────────────────────
$billing_name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
$billing_addr1 = $order->get_billing_address_1();
$billing_addr2 = $order->get_billing_address_2();
$billing_city  = $order->get_billing_city();
$billing_email = $order->get_billing_email();
$billing_phone = $order->get_billing_phone();
$billing_phone_href = $billing_phone
    ? 'tel:+' . preg_replace( '/[^0-9]/', '', $billing_phone )
    : '';

// ── Status ────────────────────────────────────────────────────────────
$status_labels = [
    'processing' => '● Обробляється',
    'pending'    => '⏳ Очікує оплати',
    'on-hold'    => '⏸ На утриманні',
    'completed'  => '✅ Виконано',
    'cancelled'  => '✕ Скасовано',
    'refunded'   => '↩ Повернено',
];
$status_text = $status_labels[ $order->get_status() ] ?? '● ' . ucfirst( $order->get_status() );

// ── Store info ────────────────────────────────────────────────────────
$store_phone      = get_theme_mod( 'footer_phone',    '+38 063 253 26 96' );
$store_email_addr = get_theme_mod( 'footer_email',    'info@lesynpie.com.ua' );
$store_address    = get_theme_mod( 'footer_address',  'вул. Воскресенська, 41, Дніпро' );
$store_hours      = get_theme_mod( 'footer_hours',    'Щодня 9:00 — 18:30' );
$fb_url           = get_theme_mod( 'footer_facebook', 'https://www.facebook.com/lesyni.pyrogy' );
$ig_url           = get_theme_mod( 'footer_instagram', 'https://www.instagram.com/lesyni_pyrogy/' );
$store_phone_href = 'tel:+' . preg_replace( '/[^0-9]/', '', $store_phone );

$privacy_url = get_privacy_policy_url();
$terms_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'terms' ) : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="uk">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet" />
<style type="text/css">
body,table,td,p,a,li{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}
table,td{mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;}
img{-ms-interpolation-mode:bicubic;border:0;outline:none;text-decoration:none;display:block;}
body{margin:0;padding:0;width:100%!important;font-family:'Montserrat',Helvetica,Arial,sans-serif;background-color:#faf6f1;color:#2c2c2c;line-height:1.6;}
a{color:#e07a3f;text-decoration:none;}
a:hover{text-decoration:underline;}

.email-bg{background-color:#faf6f1;padding:32px 16px;}
.email-shell{width:100%;max-width:620px;margin:0 auto;background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 28px rgba(44,44,44,.06);border:1px solid rgba(44,44,44,.06);}

/* Header */
.header{background-color:#e07a3f;background-image:linear-gradient(135deg,#e07a3f 0%,#d4672d 100%);padding:36px 40px 30px;}
.header-brand{font-family:'Nunito',Helvetica,Arial,sans-serif;font-weight:800;font-size:20px;color:#ffffff;letter-spacing:-0.2px;line-height:1;}
.header-brand small{display:block;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-weight:500;font-size:11px;color:rgba(255,255,255,.78);text-transform:uppercase;letter-spacing:1.4px;margin-top:6px;}

/* Hero */
.hero{padding:32px 40px 8px;background-color:#ffffff;}
.eyebrow{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;color:#e07a3f;text-transform:uppercase;letter-spacing:1.6px;margin:0 0 12px;}
h1.hero-title{font-family:'Nunito',Helvetica,Arial,sans-serif;font-weight:800;font-size:32px;line-height:1.15;letter-spacing:-0.5px;color:#2c2c2c;margin:0 0 14px;}
.hero-lead{font-size:15px;color:#555;margin:0;line-height:1.6;}

/* Order card */
.body-pad{padding:28px 40px 8px;}
.order-card{background-color:#fff5ec;border-radius:16px;padding:22px 26px;}
.order-meta-label{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;color:#b8866a;text-transform:uppercase;letter-spacing:1.4px;margin:0 0 6px;}
.order-number{font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:22px;font-weight:800;color:#2c2c2c;letter-spacing:-0.3px;margin:0;line-height:1.1;}
.order-date{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:13px;color:#888;margin:4px 0 0;}
.status-pill{display:inline-block;background-color:#ffffff;color:#e07a3f;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;padding:8px 14px;border-radius:999px;text-transform:uppercase;letter-spacing:1px;border:1.5px solid #e07a3f;white-space:nowrap;}

/* Items section */
.section-pad{padding:28px 40px 8px;}
.section-label{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:1.6px;margin:0 0 14px;}
.items{width:100%;border-collapse:separate;border-spacing:0;}
.items th{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:#999;padding:0 0 12px;border-bottom:1.5px solid rgba(224,122,63,.18);text-align:left;}
.items th.right{text-align:right;}
.items th.center{text-align:center;}
.items td.item{padding:18px 0;border-bottom:1px solid rgba(44,44,44,.06);vertical-align:middle;}
.item-name{font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#2c2c2c;margin:0;line-height:1.3;}
.item-variant{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:12px;color:#999;margin:4px 0 0;}
.item-qty{font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#2c2c2c;text-align:center;}
.item-price{font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#2c2c2c;text-align:right;white-space:nowrap;}

/* Totals */
.totals{width:100%;border-collapse:separate;border-spacing:0;margin-top:6px;}
.totals td{padding:10px 0;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:14px;color:#555;vertical-align:top;}
.totals td.label{color:#888;}
.totals td.value{text-align:right;color:#2c2c2c;font-weight:600;white-space:nowrap;}
.totals tr.grand td{padding-top:16px;padding-bottom:4px;font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:20px;font-weight:800;color:#2c2c2c;letter-spacing:-0.2px;}
.totals tr.grand td.value{color:#e07a3f;}

/* Note */
.note{margin-top:18px;background-color:#faf6f1;border-radius:12px;padding:14px 18px;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:13px;color:#666;line-height:1.55;}
.note strong{display:block;font-size:11px;font-weight:700;color:#b8866a;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:4px;}

/* Address cards */
.addr-grid{width:100%;border-collapse:separate;border-spacing:0;}
.addr-cell{vertical-align:top;padding:0;}
.addr-cell.left{padding-right:10px;}
.addr-cell.right{padding-left:10px;}
.addr-card{background-color:#ffffff;border:1.5px solid rgba(224,122,63,.18);border-radius:14px;padding:18px 20px;}
.addr-card .addr-name{font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#2c2c2c;margin:0 0 8px;}
.addr-card p{font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:13px;color:#666;margin:0 0 4px;line-height:1.5;}
.addr-card a{color:#e07a3f;font-weight:500;}

/* Help strip */
.help-strip{background-color:#fff5ec;padding:24px 40px;text-align:center;}
.help-strip p{margin:0;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:13px;color:#777;line-height:1.6;}
.help-strip a{color:#e07a3f;font-weight:600;}

/* Footer */
.footer{text-align:center;padding:28px 24px 8px;font-family:'Montserrat',Helvetica,Arial,sans-serif;font-size:12px;color:#aaa;line-height:1.7;}
.footer-brand{font-family:'Nunito',Helvetica,Arial,sans-serif;font-weight:800;font-size:14px;color:#888;letter-spacing:-0.1px;margin-bottom:6px;}
.footer a{color:#888;text-decoration:underline;}
.socials{margin:14px 0 8px;font-size:0;line-height:0;}
.socials a{display:inline-block;width:34px;height:34px;line-height:34px;background-color:#ffffff;border:1.5px solid rgba(224,122,63,.25);border-radius:50%;color:#e07a3f!important;font-family:'Nunito',Helvetica,Arial,sans-serif;font-weight:800;font-size:13px;text-decoration:none;margin:0 4px;text-align:center;}

@media only screen and (max-width:600px){
    .email-bg{padding:16px 8px!important;}
    .header{padding:28px 22px 24px!important;}
    .hero{padding:24px 22px 4px!important;}
    .body-pad{padding:20px 22px 4px!important;}
    .section-pad{padding:22px 22px 4px!important;}
    .help-strip{padding:20px 22px!important;}
    h1.hero-title{font-size:26px!important;}
    .order-number{font-size:18px!important;}
    .totals tr.grand td{font-size:17px!important;}
    .addr-cell.left,.addr-cell.right{display:block!important;width:100%!important;padding:0 0 14px!important;}
    .status-pill{margin-top:12px!important;display:block!important;text-align:center!important;}
    .items th.center,.items th.right{font-size:10px!important;}
}
</style>
</head>
<body>
<div class="email-bg">

    <!-- Preheader (hidden preview text) -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#faf6f1;">
        Дякуємо! Замовлення #<?php echo esc_html( $order_number ); ?> прийнято і вже готується. Загальна сума <?php echo esc_html( $p( $total_raw ) ); ?>.
    </div>

    <table role="presentation" class="email-shell" cellpadding="0" cellspacing="0" border="0" align="center">

        <!-- ── HEADER ────────────────────────────────────────── -->
        <tr>
            <td class="header">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="left">
                            <div class="header-brand">
                                <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
                                <small>з любов'ю до вашого столу</small>
                            </div>
                        </td>
                        <td align="right" style="font-family:'Nunito',Helvetica,Arial,sans-serif;font-size:36px;line-height:1;color:#ffffff;">
                            🥧
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ── HERO ──────────────────────────────────────────── -->
        <tr>
            <td class="hero">
                <p class="eyebrow">ЗАМОВЛЕННЯ ОТРИМАНО</p>
                <h1 class="hero-title">Щиро дякуємо, <?php echo esc_html( $first_name ); ?>!</h1>
                <p class="hero-lead">Ваше замовлення вже отримано і передано в обробку.<br>Ми зв'яжемося з вами для підтвердження.</p>
            </td>
        </tr>

        <!-- ── ORDER META CARD ────────────────────────────────── -->
        <tr>
            <td class="body-pad">
                <table role="presentation" class="order-card" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="left">
                            <p class="order-meta-label">НОМЕР ЗАМОВЛЕННЯ</p>
                            <p class="order-number">#<?php echo esc_html( $order_number ); ?></p>
                            <p class="order-date"><?php echo esc_html( $order_date ); ?></p>
                        </td>
                        <td align="right" style="padding:0 4px 0 0;vertical-align:middle;">
                            <span class="status-pill"><?php echo esc_html( $status_text ); ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ── ITEMS ─────────────────────────────────────────── -->
        <tr>
            <td class="section-pad">
                <p class="section-label">Ваше замовлення</p>
                <table role="presentation" class="items" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <thead>
                        <tr>
                            <th colspan="2">Товар</th>
                            <th class="center" width="60">К-сть</th>
                            <th class="right" width="100">Ціна</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $order->get_items() as $item_id => $item ) :
                            $product    = $item->get_product();
                            $item_name  = $item->get_name();
                            $item_qty   = $item->get_quantity();
                            $item_total = $p( (float) $item->get_total() );

                            // Variation meta (size, flavour, etc.)
                            $variant_parts = [];
                            foreach ( $item->get_formatted_meta_data( '', true ) as $meta ) {
                                $variant_parts[] = wp_strip_all_tags( $meta->display_value );
                            }
                            $variant_text = implode( ' · ', $variant_parts );

                            // Thumbnail
                            $thumb_html = '';
                            if ( $product && $product->get_image_id() ) {
                                $thumb_url = wp_get_attachment_image_url( $product->get_image_id(), [ 56, 56 ] );
                                if ( $thumb_url ) {
                                    $thumb_html = '<img src="' . esc_url( $thumb_url ) . '" width="56" height="56" '
                                        . 'style="border-radius:12px;display:block;width:56px;height:56px;object-fit:cover;" '
                                        . 'alt="' . esc_attr( $item_name ) . '">';
                                }
                            }
                            if ( ! $thumb_html ) {
                                $thumb_html = '<div style="width:56px;height:56px;border-radius:12px;background:#fff5ec;text-align:center;font-size:28px;line-height:56px;">🥧</div>';
                            }
                        ?>
                        <tr>
                            <td class="item" width="72" style="padding-right:14px;">
                                <?php echo $thumb_html; // phpcs:ignore ?>
                            </td>
                            <td class="item">
                                <p class="item-name"><?php echo esc_html( $item_name ); ?></p>
                                <?php if ( $variant_text ) : ?>
                                    <p class="item-variant"><?php echo esc_html( $variant_text ); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="item item-qty"><?php echo esc_html( $item_qty ); ?></td>
                            <td class="item item-price"><?php echo esc_html( $item_total ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <table role="presentation" class="totals" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="label">Сума товарів</td>
                        <td class="value"><?php echo esc_html( $p( $subtotal_raw ) ); ?></td>
                    </tr>
                    <?php if ( $discount_raw > 0 ) : ?>
                    <tr>
                        <td class="label">Знижка</td>
                        <td class="value" style="color:#7a9b6e;">−<?php echo esc_html( $p( $discount_raw ) ); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">
                            Доставка
                            <?php if ( $billing_city ) : ?>
                                <span style="display:block;font-size:11px;color:#aaa;margin-top:3px;"><?php echo esc_html( $billing_city ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="value">
                            <?php echo $shipping_raw > 0 ? esc_html( $p( $shipping_raw ) ) : 'Безкоштовно'; ?>
                        </td>
                    </tr>
                    <?php if ( $payment_method ) : ?>
                    <tr>
                        <td class="label">Спосіб оплати</td>
                        <td class="value"><?php echo esc_html( $payment_method ); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" style="padding:0;border-top:1px dashed rgba(44,44,44,.12);height:1px;font-size:0;line-height:0;"></td>
                    </tr>
                    <tr class="grand">
                        <td>До сплати</td>
                        <td class="value"><?php echo esc_html( $p( $total_raw ) ); ?></td>
                    </tr>
                </table>

                <?php if ( $customer_note ) : ?>
                <div class="note">
                    <strong>Примітка до замовлення</strong>
                    <?php echo wp_kses_post( $customer_note ); ?>
                </div>
                <?php endif; ?>

                <?php if ( $additional_content ) : ?>
                <div style="margin-top:18px;font-size:14px;color:#555;line-height:1.6;">
                    <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>

        <!-- ── DELIVERY DETAILS ───────────────────────────────── -->
        <tr>
            <td class="section-pad" style="padding-top:32px;">
                <p class="section-label">Деталі доставки</p>
                <table role="presentation" class="addr-grid" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="addr-cell left" width="50%">
                            <div class="addr-card">
                                <p class="addr-name">📍 Адреса доставки</p>
                                <?php if ( $billing_name ) : ?><p><?php echo esc_html( $billing_name ); ?></p><?php endif; ?>
                                <?php if ( $billing_addr1 ) : ?><p><?php echo esc_html( $billing_addr1 ); ?></p><?php endif; ?>
                                <?php if ( $billing_addr2 ) : ?><p><?php echo esc_html( $billing_addr2 ); ?></p><?php endif; ?>
                                <?php if ( $billing_city ) : ?><p><?php echo esc_html( $billing_city ); ?></p><?php endif; ?>
                            </div>
                        </td>
                        <td class="addr-cell right" width="50%">
                            <div class="addr-card">
                                <p class="addr-name">✉️ Контакт</p>
                                <?php if ( $billing_email ) : ?>
                                    <p><a href="mailto:<?php echo esc_attr( $billing_email ); ?>"><?php echo esc_html( $billing_email ); ?></a></p>
                                <?php endif; ?>
                                <?php if ( $billing_phone ) : ?>
                                    <p><a href="<?php echo esc_attr( $billing_phone_href ); ?>"><?php echo esc_html( $billing_phone ); ?></a></p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- spacer -->
        <tr><td style="height:24px;font-size:0;line-height:0;">&nbsp;</td></tr>

        <!-- ── HELP STRIP ─────────────────────────────────────── -->
        <tr>
            <td class="help-strip">
                <p>
                    Маєте питання щодо замовлення?<br>
                    Телефонуйте&nbsp;<a href="<?php echo esc_attr( $store_phone_href ); ?>"><?php echo esc_html( $store_phone ); ?></a>
                    &nbsp;або пишіть&nbsp;<a href="mailto:<?php echo esc_attr( $store_email_addr ); ?>"><?php echo esc_html( $store_email_addr ); ?></a>
                </p>
            </td>
        </tr>

    </table><!-- /email-shell -->

    <!-- ── FOOTER ────────────────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="footer" align="center">
                <div class="footer-brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
                <div><?php echo esc_html( $store_address ); ?> · <?php echo esc_html( $store_hours ); ?></div>
                <?php if ( $fb_url || $ig_url ) : ?>
                <div class="socials">
                    <?php if ( $fb_url ) : ?><a href="<?php echo esc_url( $fb_url ); ?>" aria-label="Facebook">f</a><?php endif; ?>
                    <?php if ( $ig_url ) : ?><a href="<?php echo esc_url( $ig_url ); ?>" aria-label="Instagram">ig</a><?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ( $privacy_url || $terms_url ) : ?>
                <div style="margin:10px 0 6px;">
                    <?php if ( $privacy_url ) : ?><a href="<?php echo esc_url( $privacy_url ); ?>">Конфіденційність</a><?php endif; ?>
                    <?php if ( $privacy_url && $terms_url ) : ?>&nbsp;&nbsp;<?php endif; ?>
                    <?php if ( $terms_url ) : ?><a href="<?php echo esc_url( $terms_url ); ?>">Умови</a><?php endif; ?>
                </div>
                <?php endif; ?>
                <div style="margin-top:10px;">© <?php echo date( 'Y' ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Всі права захищено.</div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
