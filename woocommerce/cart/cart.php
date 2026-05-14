<?php
/**
 * WooCommerce: Cart page
 * Overrides: woocommerce/templates/cart/cart.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$cart         = WC()->cart;
$cart_items   = $cart->get_cart();
$shop_url     = get_permalink( wc_get_page_id( 'shop' ) );
$checkout_url = wc_get_checkout_url();
$cart_count   = $cart->get_cart_contents_count();
?>

<div class="cart-wrap">

    <!-- Breadcrumbs -->
    <nav class="cart-breadcrumbs" aria-label="Breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( $shop_url ); ?>">Пироги</a>
        <span aria-hidden="true">›</span>
        <span>Кошик</span>
    </nav>

    <!-- Page head -->
    <div class="cart-page-head">
        <div>
            <h1 class="cart-page-title">Ваш кошик</h1>
            <p class="cart-page-subtitle">
                <?php
                $n = (int) $cart_count;
                echo esc_html( $n ) . ' ';
                if     ( $n % 10 === 1 && $n % 100 !== 11 ) echo 'позиція';
                elseif ( $n % 10 >= 2 && $n % 10 <= 4 && ( $n % 100 < 10 || $n % 100 >= 20 ) ) echo 'позиції';
                else   echo 'позицій';
                ?>
                · готові до оформлення
            </p>
        </div>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="cart-continue-link">← Продовжити покупки</a>
    </div>

    <!-- Steps -->
    <div class="cart-steps">
        <div class="cart-step cart-step--active">
            <span class="cart-step__num">1</span>
            <span class="cart-step__label">Кошик</span>
        </div>
        <span class="cart-step__arrow" aria-hidden="true">›</span>
        <div class="cart-step">
            <span class="cart-step__num">2</span>
            <span class="cart-step__label">Оформлення</span>
        </div>
        <span class="cart-step__arrow" aria-hidden="true">›</span>
        <div class="cart-step">
            <span class="cart-step__num">3</span>
            <span class="cart-step__label">Підтвердження</span>
        </div>
    </div>

    <?php if ( ! $cart->is_empty() ) : ?>

    <div class="cart-layout">

        <!-- ── LEFT: items ───────────────────────────────────────── -->
        <div class="cart-main">

            <?php woocommerce_output_all_notices(); ?>

            <form class="woocommerce-cart-form cart-items-card"
                  action="<?php echo esc_url( wc_get_cart_url() ); ?>"
                  method="post" id="cart-form">

                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <!-- Desktop column headers -->
                <div class="cart-thead">
                    <div></div>
                    <div>Товар</div>
                    <div>Кількість</div>
                    <div class="cart-thead__r">Сума</div>
                    <div></div>
                </div>

                <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                <?php foreach ( $cart_items as $cart_item_key => $cart_item ) :
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] ) continue;
                    if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;

                    $permalink = apply_filters( 'woocommerce_cart_item_permalink',
                        $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
                        $cart_item, $cart_item_key
                    );

                    // Category
                    $cat_name = '';
                    $cat_ids  = wc_get_product_term_ids( $_product->get_id(), 'product_cat' );
                    if ( $cat_ids ) {
                        $term = get_term( $cat_ids[0], 'product_cat' );
                        if ( $term && ! is_wp_error( $term ) ) $cat_name = $term->name;
                    }

                    // Thumbnail
                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail',
                        $_product->get_image( 'woocommerce_thumbnail', [ 'alt' => esc_attr( $_product->get_name() ) ] ),
                        $cart_item, $cart_item_key
                    );
                    $has_img   = $_product->get_image_id() > 0;
                    $pie_class = lesyni_pie_visual_class( $_product );

                    // Prices
                    $unit_price = (float) $_product->get_price();
                    $line_total = (float) $cart_item['line_subtotal'];
                    $qty        = (int) $cart_item['quantity'];

                    // Variation meta
                    $var_meta = wc_get_formatted_cart_item_data( $cart_item );
                ?>

                <div class="cart-row" data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                     data-unit="<?php echo esc_attr( $unit_price ); ?>">

                    <!-- Image -->
                    <div class="ci-img">
                        <?php if ( $has_img ) : ?>
                            <?php if ( $permalink ) : ?><a href="<?php echo esc_url( $permalink ); ?>"><?php endif; ?>
                            <?php echo $thumbnail; ?>
                            <?php if ( $permalink ) : ?></a><?php endif; ?>
                        <?php else : ?>
                            <?php if ( $permalink ) : ?><a href="<?php echo esc_url( $permalink ); ?>"><?php endif; ?>
                            <span class="pie-visual <?php echo esc_attr( $pie_class ); ?>"></span>
                            <?php if ( $permalink ) : ?></a><?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="ci-info">
                        <?php if ( $cat_name ) : ?>
                            <span class="ci-cat"><?php echo esc_html( $cat_name ); ?></span>
                        <?php endif; ?>
                        <span class="ci-name">
                            <?php $name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ); ?>
                            <?php if ( $permalink ) : ?>
                                <a href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $name ); ?></a>
                            <?php else : ?>
                                <?php echo wp_kses_post( $name ); ?>
                            <?php endif; ?>
                        </span>
                        <?php if ( $var_meta ) : ?>
                            <span class="ci-meta"><?php echo $var_meta; ?></span>
                        <?php endif; ?>
                        <?php if ( $permalink ) : ?>
                            <a href="<?php echo esc_url( $permalink ); ?>" class="ci-edit-link">Змінити параметри</a>
                        <?php endif; ?>
                    </div>

                    <!-- Qty stepper -->
                    <div class="ci-qty">
                        <button type="button" class="ci-qty-btn ci-qty-btn--minus" aria-label="Зменшити">−</button>
                        <span class="ci-qty-val"><?php echo esc_html( $qty ); ?></span>
                        <input type="hidden" class="ci-qty-input"
                               name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
                               value="<?php echo esc_attr( $qty ); ?>">
                        <button type="button" class="ci-qty-btn ci-qty-btn--plus" aria-label="Збільшити">+</button>
                    </div>

                    <!-- Price -->
                    <div class="ci-price">
                        <span class="ci-price__total">
                            <span class="ci-row-sum"><?php echo number_format( $line_total, 0, '', '' ); ?></span> грн
                        </span>
                        <span class="ci-price__unit"><?php echo number_format( $unit_price, 0, '', '' ); ?> грн / шт</span>
                    </div>

                    <!-- Remove -->
                    <?php echo apply_filters( 'woocommerce_cart_item_remove_link',
                        sprintf(
                            '<a href="%s" class="ci-remove" aria-label="%s" data-product-id="%s">×</a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            esc_attr__( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id )
                        ),
                        $cart_item_key
                    ); ?>

                </div><!-- .cart-row -->

                <?php endforeach; ?>

                <?php do_action( 'woocommerce_cart_contents' ); ?>

                <!-- Cart footer: coupon + actions -->
                <div class="cart-foot">
                    <?php if ( wc_coupons_enabled() ) : ?>
                        <div class="cart-coupon">
                            <input type="text" name="coupon_code" class="cart-coupon__input"
                                   id="coupon_code" value=""
                                   placeholder="Промокод"
                                   autocomplete="off">
                            <button type="submit" class="cart-coupon__btn"
                                    name="apply_coupon"
                                    value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>">
                                Застосувати
                            </button>
                            <?php do_action( 'woocommerce_cart_coupon' ); ?>
                        </div>
                    <?php endif; ?>

                    <?php do_action( 'woocommerce_cart_actions' ); ?>
                    <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

                    <button type="submit" name="update_cart"
                            value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"
                            class="cart-update-btn"
                            id="cart-update-btn">
                        Оновити кошик
                    </button>
                </div>

                <?php do_action( 'woocommerce_after_cart_contents' ); ?>

            </form>

            <?php do_action( 'woocommerce_after_cart_table' ); ?>

            <!-- Applied coupons -->
            <?php foreach ( $cart->get_applied_coupons() as $code ) : ?>
                <div class="cart-coupon-tag">
                    Промокод <strong><?php echo esc_html( strtoupper( $code ) ); ?></strong> застосовано
                    <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', urlencode( $code ), wc_get_cart_url() ) ); ?>"
                       class="cart-coupon-tag__remove">× Скасувати</a>
                </div>
            <?php endforeach; ?>

            <!-- Cross-sells -->
            <?php do_action( 'woocommerce_cart_collaterals' ); ?>

        </div><!-- .cart-main -->

        <!-- ── RIGHT: Summary ─────────────────────────────────────── -->
        <div class="cart-summary">

            <?php
            $subtotal   = (float) $cart->get_subtotal();
            $discount   = (float) $cart->get_discount_total();
            $total      = (float) $cart->get_total( 'edit' );
            $ship_total = (float) $cart->get_shipping_total();

            if ( $ship_total > 0 ) {
                $ship_label = number_format( $ship_total, 0, '', '' ) . ' грн';
            } elseif ( $cart->needs_shipping() && ! $cart->show_shipping() ) {
                $ship_label = 'Розраховується';
            } else {
                $ship_label = 'Безкоштовно';
            }
            ?>

            <h3 class="cart-summary__title">Підсумок замовлення</h3>

            <div class="cart-summary__row">
                <span>Сума товарів</span>
                <strong><?php echo number_format( $subtotal, 0, '', '' ); ?> грн</strong>
            </div>

            <?php if ( $discount > 0 ) : ?>
                <div class="cart-summary__row cart-summary__row--discount">
                    <span>Знижка
                        <?php foreach ( $cart->get_applied_coupons() as $code ) : ?>
                            <span class="cart-coupon-badge"><?php echo esc_html( strtoupper( $code ) ); ?></span>
                        <?php endforeach; ?>
                    </span>
                    <strong>− <?php echo number_format( $discount, 0, '', '' ); ?> грн</strong>
                </div>
            <?php endif; ?>

            <div class="cart-summary__row">
                <span>Доставка по Києву</span>
                <strong><?php echo esc_html( $ship_label ); ?></strong>
            </div>

            <div class="cart-delivery-note">
                Доставимо <strong>сьогодні з 14:00</strong> · теплим, у фірмовій упаковці
            </div>

            <div class="cart-summary__total">
                <span class="cart-summary__total-label">До оплати</span>
                <span class="cart-summary__total-price">
                    <?php echo number_format( $total, 0, '', '' ); ?>
                    <span class="cart-summary__total-unit">грн</span>
                </span>
            </div>

            <a href="<?php echo esc_url( $checkout_url ); ?>" class="cart-summary__cta">
                Оформити замовлення →
            </a>

            <div class="cart-summary__trust">
                <div class="cart-summary__trust-item">Випікаємо під замовлення</div>
                <div class="cart-summary__trust-item">Доставка за 2 години</div>
                <div class="cart-summary__trust-item">Оплата при отриманні</div>
                <div class="cart-summary__trust-item">Скасування безкоштовно</div>
            </div>

            <div class="cart-summary__pay">
                <div class="cart-summary__pay-label">Способи оплати</div>
                <div class="cart-summary__pay-icons">
                    <span class="cart-pay-icon">VISA</span>
                    <span class="cart-pay-icon">MC</span>
                    <span class="cart-pay-icon">Apple Pay</span>
                    <span class="cart-pay-icon">Google Pay</span>
                </div>
            </div>

        </div><!-- .cart-summary -->

    </div><!-- .cart-layout -->

    <?php else : ?>

    <!-- Empty state -->
    <div class="cart-empty">
        <div class="cart-empty__icon">🛒</div>
        <h2 class="cart-empty__title">Кошик поки порожній</h2>
        <p class="cart-empty__text">Оберіть улюблений пиріг у каталозі — приготуємо й привеземо теплим.</p>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="cart-summary__cta cart-summary__cta--inline">До каталогу →</a>
        <?php do_action( 'woocommerce_cart_is_empty' ); ?>
    </div>

    <?php endif; ?>

</div><!-- .cart-wrap -->

<?php do_action( 'woocommerce_after_cart' ); ?>
