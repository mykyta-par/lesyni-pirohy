<?php
/**
 * Email styles — overrides woocommerce/templates/emails/email-styles.php
 *
 * @version 10.8.0
 */
defined( 'ABSPATH' ) || exit;

$bg        = '#fffaf5';
$body_bg   = '#ffffff';
$primary   = '#e07a3f';
$primary_dk= '#d4672d';
$text      = '#2d2d2d';
$muted     = '#7a6a5a';
$border    = '#f0e5d9';
?>
body {
    padding: 0;
    margin: 0;
    background-color: <?php echo $bg; ?>;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: <?php echo $text; ?>;
}

#wrapper {
    background-color: <?php echo $bg; ?>;
    margin: 0 auto;
    padding: 28px 0 48px;
    width: 100%;
    max-width: 600px;
    -webkit-text-size-adjust: none;
}

#template_container {
    box-shadow: 0 4px 24px rgba(224,122,63,.12);
    border-radius: 16px;
    overflow: hidden;
}

#template_header {
    background-color: <?php echo $primary; ?>;
    background-image: linear-gradient(135deg, <?php echo $primary; ?> 0%, <?php echo $primary_dk; ?> 100%);
    color: #ffffff;
    border-radius: 16px 16px 0 0;
    padding: 0;
}

#template_header h1,
#template_header h1 a {
    color: #ffffff;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0;
    line-height: 1.3;
    margin: 0;
    text-decoration: none;
}

#template_header_image {
    padding: 28px 40px 0;
}

#template_header_image img {
    height: 44px;
    width: auto;
    display: block;
}

.lesyni-header-title {
    padding: 18px 40px 28px;
    border-top: 1px solid rgba(255,255,255,.2);
    margin-top: 18px;
}

#template_body {
    background-color: <?php echo $body_bg; ?>;
}

#body_content {
    background-color: <?php echo $body_bg; ?>;
    padding: 36px 40px 32px;
}

#body_content table td {
    padding: 12px;
}

#body_content p,
#body_content ul,
#body_content ol {
    font-size: 15px;
    line-height: 1.7;
    color: <?php echo $text; ?>;
    margin: 0 0 16px;
}

#body_content p.email-intro {
    font-size: 16px;
    color: <?php echo $muted; ?>;
    margin-bottom: 28px;
}

h2 {
    color: <?php echo $text; ?>;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
    margin: 0 0 18px;
    text-align: left;
}

a {
    color: <?php echo $primary; ?>;
    text-decoration: none;
}

/* ── Order table ─────────────────────────────────────────── */
.td {
    color: <?php echo $text; ?>;
    border: 1px solid <?php echo $border; ?>;
    padding: 14px 16px !important;
    vertical-align: middle;
    font-size: 14px;
}

.td.product-quantity { text-align: center; }
.td.product-name a   { color: <?php echo $text; ?>; font-weight: 600; }

#body_content table.td th {
    background: #fdf5ee;
    color: <?php echo $muted; ?>;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    border: 1px solid <?php echo $border; ?>;
    padding: 10px 16px !important;
}

/* Totals */
#body_content .woocommerce-Price-amount {
    font-weight: 700;
    color: <?php echo $text; ?>;
}

tr.order-total td,
tr.order-total th {
    border-top: 2px solid <?php echo $primary; ?> !important;
}

tr.order-total .woocommerce-Price-amount {
    color: <?php echo $primary; ?>;
    font-size: 18px;
}

/* ── Address block ───────────────────────────────────────── */
.address {
    background: #fdf5ee;
    border-radius: 10px;
    padding: 16px 18px;
    font-size: 14px;
    color: <?php echo $muted; ?>;
    line-height: 1.7;
}

/* ── Footer ──────────────────────────────────────────────── */
#template_footer {
    background-color: <?php echo $bg; ?>;
    border-radius: 0 0 16px 16px;
    padding: 24px 40px 8px;
}

#credit {
    color: #a09080;
    font-size: 12px;
    line-height: 1.7;
    text-align: center;
}

#credit a {
    color: <?php echo $primary; ?>;
    text-decoration: none;
}

#credit p { margin: 0 0 6px; }
