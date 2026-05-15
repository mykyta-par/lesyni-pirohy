<?php
/**
 * WooCommerce: Order Received / Thank You page
 * Overrides: woocommerce/templates/checkout/thankyou.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_thankyou', $order->get_id() );

if ( ! $order ) {
    wc_print_notices();
    echo '<p class="ty-no-order">Замовлення не знайдено.</p>';
    return;
}

/* ── Order data ───────────────────────────────────────────── */
$order_id      = $order->get_id();
$order_number  = $order->get_order_number();
$date_obj      = $order->get_date_created();
$date_str      = $date_obj ? $date_obj->date_i18n( 'd.m.Y' ) : '';
$time_str      = $date_obj ? $date_obj->date_i18n( 'H:i' ) : '';
$total         = (float) $order->get_total();
$subtotal      = (float) $order->get_subtotal();
$shipping_cost = (float) $order->get_shipping_total();
$discount      = (float) $order->get_discount_total();
$items         = $order->get_items();

$delivery_type = $order->get_meta( '_delivery_type' ) ?: 'courier';
$delivery_time = $order->get_meta( '_delivery_time' ) ?: '';

$first_name = $order->get_billing_first_name();
$last_name  = $order->get_billing_last_name();
$full_name  = trim( $first_name . ' ' . $last_name ) ?: 'Не вказано';
$phone      = $order->get_billing_phone() ?: '';
$email      = $order->get_billing_email() ?: '';
$address    = trim( $order->get_billing_address_1() );
$city       = $order->get_billing_city() ?: 'Дніпро';

switch ( $delivery_type ) {
    case 'pickup': $delivery_label = 'Самовивіз'; $delivery_sub = 'вул. Воскресенська, 41 · Дніпро'; break;
    case 'np':     $delivery_label = 'Нова Пошта'; $delivery_sub = $address ?: ''; break;
    default:       $delivery_label = 'Кур\'єр'; $delivery_sub = $address ? $address . ', ' . $city : $city; break;
}

switch ( $order->get_payment_method() ) {
    case 'cod':    $payment_label = 'Оплата при отриманні'; $payment_sub = 'Готівкою або карткою'; break;
    default:       $payment_label = $order->get_payment_method_title(); $payment_sub = ''; break;
}

$item_count = (int) array_sum( array_map( fn( $i ) => $i->get_quantity(), $items ) );
$n = $item_count;
$count_str = ( $n % 10 === 1 && $n % 100 !== 11 ) ? "$n позиція"
           : ( ( $n % 10 >= 2 && $n % 10 <= 4 && ( $n % 100 < 10 || $n % 100 >= 20 ) ) ? "$n позиції"
           : "$n позицій" );

$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
?>

<div class="ty-wrap">

    <!-- ── HERO ──────────────────────────────────────────────── -->
    <div class="ty-hero">
        <div class="ty-hero-icon">✓</div>
        <h1 class="ty-hero-title">Дякуємо, ваше замовлення прийнято!</h1>
        <p class="ty-hero-desc">Ми вже починаємо пекти. Очікуйте дзвінок від нашого оператора для підтвердження деталей.</p>
        <div class="ty-order-tag">
            <span>Замовлення</span>
            <strong><?php echo esc_html( '№ ' . $order_number ); ?></strong>
            <span class="ty-tag-sep">·</span>
            <span><?php echo esc_html( $date_str ); ?></span>
        </div>
    </div>

    <!-- ── INFO STRIP ────────────────────────────────────────── -->
    <div class="ty-strip">
        <div class="ty-strip-cell">
            <span class="ty-strip-label">Номер</span>
            <span class="ty-strip-val"><?php echo esc_html( '№ ' . $order_number ); ?></span>
        </div>
        <div class="ty-strip-cell">
            <span class="ty-strip-label">Дата</span>
            <span class="ty-strip-val"><?php echo esc_html( $date_str ); ?></span>
            <?php if ( $time_str ) : ?>
                <span class="ty-strip-sub"><?php echo esc_html( $time_str ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ty-strip-cell">
            <span class="ty-strip-label">Спосіб оплати</span>
            <span class="ty-strip-val" style="font-size:14px;"><?php echo esc_html( $payment_label ); ?></span>
            <?php if ( $payment_sub ) : ?>
                <span class="ty-strip-sub"><?php echo esc_html( $payment_sub ); ?></span>
            <?php endif; ?>
        </div>
        <div class="ty-strip-cell">
            <span class="ty-strip-label">Всього</span>
            <span class="ty-strip-val ty-strip-val--amount"><?php echo esc_html( number_format( $total, 0, '.', ' ' ) ); ?> грн</span>
        </div>
    </div>

    <!-- ── ORDER DETAILS ─────────────────────────────────────── -->
    <div class="ty-card">
        <div class="ty-card-head">
            <h2 class="ty-card-title">Деталі замовлення</h2>
            <span class="ty-card-hint"><?php echo esc_html( $count_str ); ?></span>
        </div>

        <div class="ty-items">
            <?php foreach ( $items as $item_id => $item ) :
                $product    = $item->get_product();
                $image_id   = $product ? $product->get_image_id() : 0;
                $image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
                $name       = $item->get_name();
                $qty        = $item->get_quantity();
                $line_total = (float) $item->get_total();
                $meta_data  = $item->get_all_formatted_meta_data( '' );
            ?>
            <div class="ty-item">
                <div class="ty-item-img">
                    <?php if ( $image_url ) : ?>
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                    <?php else : ?>
                        🥧
                    <?php endif; ?>
                </div>
                <div class="ty-item-info">
                    <span class="ty-item-name"><?php echo esc_html( $name ); ?></span>
                    <span class="ty-item-meta">
                        <?php foreach ( $meta_data as $meta ) : ?>
                            <?php echo esc_html( $meta->display_key . ': ' . $meta->display_value ); ?> ·
                        <?php endforeach; ?>
                        <strong>× <?php echo esc_html( $qty ); ?></strong>
                    </span>
                </div>
                <div class="ty-item-price"><?php echo esc_html( number_format( $line_total, 0, '.', ' ' ) ); ?> грн</div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="ty-totals">
            <div class="ty-total-row">
                <span>Разом за товари</span>
                <strong><?php echo esc_html( number_format( $subtotal, 0, '.', ' ' ) ); ?> грн</strong>
            </div>
            <?php if ( $discount > 0 ) : ?>
            <div class="ty-total-row">
                <span>Знижка</span>
                <strong style="color:#7a9b6e;">−<?php echo esc_html( number_format( $discount, 0, '.', ' ' ) ); ?> грн</strong>
            </div>
            <?php endif; ?>
            <div class="ty-total-row">
                <span>Доставка</span>
                <strong <?php echo $shipping_cost === 0.0 ? 'style="color:#7a9b6e;"' : ''; ?>>
                    <?php echo $shipping_cost > 0 ? esc_html( number_format( $shipping_cost, 0 ) . ' грн' ) : 'Безкоштовно'; ?>
                </strong>
            </div>
            <div class="ty-total-row">
                <span>Спосіб оплати</span>
                <strong><?php echo esc_html( $payment_label ); ?></strong>
            </div>
            <div class="ty-total-row ty-total-grand">
                <span class="ty-grand-label">Всього</span>
                <span>
                    <span class="ty-grand-val"><?php echo esc_html( number_format( $total, 0, '.', ' ' ) ); ?></span>
                    <span class="ty-grand-unit">грн</span>
                </span>
            </div>
        </div>
    </div>

    <!-- ── DELIVERY INFO ─────────────────────────────────────── -->
    <div class="ty-card">
        <div class="ty-card-head">
            <h2 class="ty-card-title">Інформація про доставку</h2>
        </div>
        <div class="ty-info-grid">
            <div class="ty-info-block">
                <div class="ty-info-label ty-info-label--delivery">Спосіб доставки</div>
                <div class="ty-info-val">
                    <strong><?php echo esc_html( $delivery_label ); ?></strong>
                    <?php if ( $delivery_sub ) : ?><br>
                    <span class="ty-muted"><?php echo esc_html( $delivery_sub ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ty-info-block">
                <div class="ty-info-label ty-info-label--payment">Спосіб оплати</div>
                <div class="ty-info-val">
                    <strong><?php echo esc_html( $payment_label ); ?></strong>
                    <?php if ( $payment_sub ) : ?><br>
                    <span class="ty-muted"><?php echo esc_html( $payment_sub ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ty-info-block">
                <div class="ty-info-label ty-info-label--contact">Отримувач</div>
                <div class="ty-info-val">
                    <strong><?php echo esc_html( $full_name ); ?></strong><br>
                    <span class="ty-muted">
                        <?php echo esc_html( $phone ); ?>
                        <?php if ( $email ) : ?><br><?php echo esc_html( $email ); ?><?php endif; ?>
                    </span>
                </div>
            </div>
            <div class="ty-info-block">
                <div class="ty-info-label ty-info-label--time">Час доставки</div>
                <div class="ty-info-val">
                    <?php if ( $delivery_time ) : ?>
                        <strong><?php echo esc_html( $delivery_time ); ?></strong><br>
                        <span class="ty-muted">Зателефонуємо за 15 хв до готовності.</span>
                    <?php else : ?>
                        <span class="ty-muted">Уточнить оператор</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── ACTION ────────────────────────────────────────────── -->
    <div class="ty-actions">
        <a href="<?php echo esc_url( $shop_url ); ?>" class="ty-btn">
            Продовжити покупки →
        </a>
    </div>

    <!-- ── CONTACT ───────────────────────────────────────────── -->
    <div class="ty-contact">
        <div class="ty-contact-icon">☎</div>
        <div class="ty-contact-info">
            <div class="ty-contact-title">Виникли питання щодо замовлення?</div>
            <div class="ty-contact-desc">Наш оператор з радістю допоможе — щодня з 9:00 до 18:30.</div>
        </div>
        <a href="tel:+380632532696" class="ty-contact-phone">+38 063 253 26 96</a>
    </div>

</div><!-- .ty-wrap -->

<script>
(function () {
    var colors = ['#c4845a', '#d4a574', '#7a9b6e', '#f5e5d3', '#e8d5c4'];
    function makeConfetti() {
        for (var i = 0; i < 60; i++) {
            (function () {
                var c = document.createElement('div');
                c.className = 'ty-confetti';
                c.style.left = (Math.random() * 100) + 'vw';
                c.style.background = colors[Math.floor(Math.random() * colors.length)];
                c.style.animationDelay = (Math.random() * 1.5) + 's';
                c.style.animationDuration = (2.5 + Math.random() * 2) + 's';
                c.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
                if (Math.random() > 0.5) {
                    c.style.borderRadius = '50%';
                    c.style.width = '10px';
                    c.style.height = '10px';
                }
                document.body.appendChild(c);
                setTimeout(function () { c.remove(); }, 5000);
            }());
        }
    }
    window.addEventListener('load', function () { setTimeout(makeConfetti, 300); });
}());
</script>
