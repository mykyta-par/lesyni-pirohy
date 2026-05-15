<?php
/**
 * WooCommerce: Combined Cart + Checkout page
 * Overrides: woocommerce/templates/cart/cart.php
 */

defined( 'ABSPATH' ) || exit;

$cart         = WC()->cart;
$checkout     = WC()->checkout();
$cart_items   = $cart->get_cart();
$shop_url     = get_permalink( wc_get_page_id( 'shop' ) );
$cart_count   = $cart->get_cart_contents_count();

// Prices
$subtotal   = (float) $cart->get_subtotal();
$discount   = (float) $cart->get_discount_total();
$total      = (float) $cart->get_total( 'edit' );
$ship_total = (float) $cart->get_shipping_total();

if ( $ship_total > 0 ) {
    $ship_label = number_format( $ship_total, 0, '', '' ) . ' грн';
    $ship_free  = false;
} else {
    $ship_label = 'Безкоштовно';
    $ship_free  = true;
}

do_action( 'woocommerce_before_cart' );

// Billing field helper (reuse / define if not already defined)
if ( ! function_exists( 'lesyni_co_field' ) ) {
    function lesyni_co_field( $name, $label, $args = [] ) {
        $checkout = WC()->checkout();
        $defaults = [
            'type'         => 'text',
            'placeholder'  => '',
            'required'     => false,
            'class'        => [],
            'autocomplete' => '',
            'hint'         => '',
        ];
        $args  = wp_parse_args( $args, $defaults );
        $value = $checkout->get_value( $name );
        $req   = $args['required'] ? ' <span class="oco-req">*</span>' : '';
        $reqattr = $args['required'] ? ' required' : '';
        $auto  = $args['autocomplete'] ? ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"' : '';
        ?>
        <div class="oco-field<?php echo ! empty( $args['class'] ) ? ' ' . esc_attr( implode( ' ', $args['class'] ) ) : ''; ?>">
            <label class="oco-label" for="<?php echo esc_attr( $name ); ?>"><?php echo $label . $req; ?></label>
            <?php if ( $args['type'] === 'select' ) : ?>
                <select id="<?php echo esc_attr( $name ); ?>"
                        name="<?php echo esc_attr( $name ); ?>"
                        class="oco-select"<?php echo $reqattr; ?>>
                    <?php foreach ( $args['options'] as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $k ); ?>"<?php selected( $value, $k ); ?>><?php echo esc_html( $v ); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <input id="<?php echo esc_attr( $name ); ?>"
                       name="<?php echo esc_attr( $name ); ?>"
                       type="<?php echo esc_attr( $args['type'] ); ?>"
                       class="oco-input"
                       value="<?php echo esc_attr( $value ); ?>"
                       placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"<?php echo $reqattr . $auto; ?>>
            <?php endif; ?>
            <?php if ( ! empty( $args['hint'] ) ) : ?>
                <span class="oco-hint"><?php echo esc_html( $args['hint'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Item count string
$n = (int) $cart_count;
if     ( $n % 10 === 1 && $n % 100 !== 11 ) $count_str = $n . ' позиція';
elseif ( $n % 10 >= 2 && $n % 10 <= 4 && ( $n % 100 < 10 || $n % 100 >= 20 ) ) $count_str = $n . ' позиції';
else   $count_str = $n . ' позицій';
?>

<div class="oco-wrap">

    <!-- Breadcrumbs -->
    <nav class="oco-breadcrumbs" aria-label="Breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( $shop_url ); ?>">Пироги</a>
        <span aria-hidden="true">›</span>
        <span>Кошик і оформлення</span>
    </nav>

    <!-- Page head -->
    <div class="oco-page-head">
        <div>
            <h1 class="oco-page-title">Кошик і оформлення</h1>
            <p class="oco-page-subtitle">
                <span id="oco-total-items"><?php echo esc_html( $count_str ); ?></span>
                · усе на одній сторінці — швидко й зручно
            </p>
        </div>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="oco-continue-link">← Продовжити покупки</a>
    </div>

    <?php woocommerce_output_all_notices(); ?>

    <?php if ( ! $cart->is_empty() ) : ?>

    <!-- Hidden WC checkout form (processes the actual order) -->
    <form id="oco-wc-form" name="checkout"
          method="post"
          action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
          style="display:none;">
        <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
        <input type="hidden" name="delivery_type"  id="oco-delivery-type-val" value="courier">
        <input type="hidden" name="delivery_time"  id="oco-delivery-time-val" value="">
        <input type="hidden" name="payment_method" id="oco-payment-method-val" value="cod">
        <input type="hidden" name="billing_first_name"  id="wc-billing_first_name"  value="">
        <input type="hidden" name="billing_last_name"   id="wc-billing_last_name"   value="">
        <input type="hidden" name="billing_phone"       id="wc-billing_phone"       value="">
        <input type="hidden" name="billing_email"       id="wc-billing_email"       value="">
        <input type="hidden" name="billing_address_1"   id="wc-billing_address_1"   value="">
        <input type="hidden" name="billing_city"        id="wc-billing_city"        value="Київ">
        <input type="hidden" name="billing_country"     id="wc-billing_country"     value="UA">
        <input type="hidden" name="billing_postcode"    id="wc-billing_postcode"    value="01001">
        <input type="hidden" name="order_comments"      id="wc-order_comments"      value="">
        <input type="hidden" name="lesyni_gift"         id="wc-lesyni_gift"         value="">
        <input type="hidden" name="terms"               value="1">
        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
    </form>

    <!-- Cart update form (WC requires POST to update quantities) -->
    <form id="oco-cart-update-form"
          action="<?php echo esc_url( wc_get_cart_url() ); ?>"
          method="post" style="display:none;">
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        <div id="oco-cart-hidden-inputs"></div>
        <button type="submit" name="update_cart" value="Update Cart" id="oco-cart-update-btn">Update</button>
    </form>

    <!-- ===== MAIN GRID ===== -->
    <div class="oco-grid">

        <!-- ─── LEFT COLUMN ────────────────────────────────── -->
        <div class="oco-left">

            <!-- SECTION 1: CART ITEMS -->
            <div class="oco-card oco-card--flush">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">1</span>Ваш кошик</h2>
                </div>

                <div id="oco-cart-items">
                    <div class="oco-thead">
                        <div></div>
                        <div>Товар</div>
                        <div>Кількість</div>
                        <div class="oco-thead-r">Сума</div>
                        <div></div>
                    </div>

                    <?php foreach ( $cart_items as $cart_item_key => $cart_item ) :
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] ) continue;
                        if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;

                        $permalink  = apply_filters( 'woocommerce_cart_item_permalink',
                            $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
                            $cart_item, $cart_item_key );

                        // Category
                        $cat_name = '';
                        $cat_ids  = wc_get_product_term_ids( $_product->get_id(), 'product_cat' );
                        if ( $cat_ids ) {
                            $term = get_term( $cat_ids[0], 'product_cat' );
                            if ( $term && ! is_wp_error( $term ) ) $cat_name = $term->name;
                        }

                        $thumbnail  = apply_filters( 'woocommerce_cart_item_thumbnail',
                            $_product->get_image( 'woocommerce_thumbnail', [ 'alt' => esc_attr( $_product->get_name() ) ] ),
                            $cart_item, $cart_item_key );
                        $has_img    = $_product->get_image_id() > 0;
                        $pie_class  = lesyni_pie_visual_class( $_product );
                        $unit_price = (float) $_product->get_price();
                        $line_total = (float) $cart_item['line_subtotal'];
                        $qty        = (int) $cart_item['quantity'];
                        $var_meta   = wc_get_formatted_cart_item_data( $cart_item );
                        $remove_url = esc_url( wc_get_cart_remove_url( $cart_item_key ) );
                    ?>

                    <div class="oco-row"
                         data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                         data-unit="<?php echo esc_attr( $unit_price ); ?>">

                        <div class="oco-ci-img">
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

                        <div class="oco-ci-info">
                            <?php if ( $cat_name ) : ?>
                                <span class="oco-ci-cat"><?php echo esc_html( $cat_name ); ?></span>
                            <?php endif; ?>
                            <span class="oco-ci-name">
                                <?php $name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ); ?>
                                <?php if ( $permalink ) : ?>
                                    <a href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $name ); ?></a>
                                <?php else : ?>
                                    <?php echo wp_kses_post( $name ); ?>
                                <?php endif; ?>
                            </span>
                            <?php if ( $var_meta ) : ?>
                                <span class="oco-ci-meta"><?php echo $var_meta; ?></span>
                            <?php endif; ?>
                            <?php if ( $permalink ) : ?>
                                <a href="<?php echo esc_url( $permalink ); ?>" class="oco-ci-edit">Змінити параметри</a>
                            <?php endif; ?>
                        </div>

                        <div class="oco-ci-qty">
                            <button type="button" class="oco-qty-btn oco-qty-btn--minus" aria-label="Зменшити">−</button>
                            <span class="oco-qty-val"><?php echo esc_html( $qty ); ?></span>
                            <button type="button" class="oco-qty-btn oco-qty-btn--plus" aria-label="Збільшити">+</button>
                        </div>

                        <div class="oco-ci-price">
                            <span class="oco-ci-price-now">
                                <span class="oco-row-sum"><?php echo number_format( $line_total, 0, '', '' ); ?></span> грн
                            </span>
                            <span class="oco-ci-price-unit"><?php echo number_format( $unit_price, 0, '', '' ); ?> грн / шт</span>
                        </div>

                        <a href="<?php echo $remove_url; ?>"
                           class="oco-ci-remove"
                           aria-label="Видалити"
                           data-key="<?php echo esc_attr( $cart_item_key ); ?>">×</a>

                    </div><!-- .oco-row -->

                    <?php endforeach; ?>

                    <!-- Cart subtotal strip -->
                    <div class="oco-cart-subtotal">
                        <div class="oco-cart-subtotal-label">
                            Разом за <strong><span id="oco-cart-count"><?php echo esc_html( $cart_count ); ?></span> шт</strong>
                            <span class="oco-cart-free-hint" id="oco-cart-free-hint"
                                  style="color:<?php echo $ship_free ? '#7a9b6e' : '#c4845a'; ?>">
                                <?php echo $ship_free ? '✓ Доставка безкоштовна' : ''; ?>
                            </span>
                        </div>
                        <div class="oco-cart-subtotal-amount">
                            <span id="oco-cart-subtotal"><?php echo number_format( $subtotal, 0, '', '' ); ?></span><span class="oco-cart-subtotal-unit">грн</span>
                        </div>
                    </div>

                    <!-- Cart foot: coupon -->
                    <?php if ( wc_coupons_enabled() ) : ?>
                    <div class="oco-cart-foot">
                        <div class="oco-promo">
                            <input type="text" id="oco-coupon-input" class="oco-promo-input"
                                   placeholder="Промокод"
                                   autocomplete="off"
                                   value="<?php echo ! empty( $cart->get_applied_coupons() ) ? esc_attr( strtoupper( current( $cart->get_applied_coupons() ) ) ) : ''; ?>">
                            <button type="button" class="oco-promo-btn" id="oco-coupon-btn">Застосувати</button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php foreach ( $cart->get_applied_coupons() as $code ) : ?>
                    <div class="oco-coupon-tag">
                        Промокод <strong><?php echo esc_html( strtoupper( $code ) ); ?></strong> застосовано
                        <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', urlencode( $code ), wc_get_cart_url() ) ); ?>"
                           class="oco-coupon-tag-remove">× Скасувати</a>
                    </div>
                    <?php endforeach; ?>

                </div><!-- #oco-cart-items -->
            </div><!-- .oco-card -->

            <!-- SECTION 2: CONTACT -->
            <div class="oco-card">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">2</span>Контактні дані</h2>
                    <span class="oco-section-hint">Поля з <span style="color:#c4845a;">*</span> обов'язкові</span>
                </div>
                <div class="oco-form-row">
                    <div class="oco-field">
                        <label class="oco-label" for="oco-first-name">Ім'я <span class="oco-req">*</span></label>
                        <input type="text" id="oco-first-name" class="oco-input"
                               placeholder="Олена Куценко"
                               value="<?php echo esc_attr( $checkout->get_value( 'billing_first_name' ) . ( $checkout->get_value( 'billing_last_name' ) ? ' ' . $checkout->get_value( 'billing_last_name' ) : '' ) ); ?>"
                               autocomplete="given-name" required>
                    </div>
                    <div class="oco-field">
                        <label class="oco-label" for="oco-phone">Телефон <span class="oco-req">*</span></label>
                        <input type="tel" id="oco-phone" class="oco-input"
                               placeholder="+38 (___) ___-__-__"
                               value="<?php echo esc_attr( $checkout->get_value( 'billing_phone' ) ); ?>"
                               autocomplete="tel" required>
                    </div>
                </div>
                <div class="oco-form-row oco-form-row--full">
                    <div class="oco-field">
                        <label class="oco-label" for="oco-email">E-mail</label>
                        <input type="email" id="oco-email" class="oco-input"
                               placeholder="olena@gmail.com"
                               value="<?php echo esc_attr( $checkout->get_value( 'billing_email' ) ); ?>"
                               autocomplete="email">
                        <span class="oco-hint">Надішлемо чек і статус замовлення</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: ADDRESS -->
            <div class="oco-card" id="oco-address-section">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">3</span><span id="oco-address-title">Адреса доставки</span></h2>
                    <span class="oco-section-hint" id="oco-address-hint">Доставка по Києву · безкоштовно</span>
                </div>
                <div class="oco-form-row">
                    <div class="oco-field">
                        <label class="oco-label">Місто</label>
                        <div class="oco-city-locked">
                            <span><span class="oco-city-icon">📍</span>Київ</span>
                            <span class="oco-city-tag">Поки що лише</span>
                        </div>
                    </div>
                    <div class="oco-field">
                        <label class="oco-label" for="oco-street">Вулиця <span class="oco-req">*</span></label>
                        <input type="text" id="oco-street" class="oco-input"
                               placeholder="напр. Хрещатик"
                               value="<?php echo esc_attr( $checkout->get_value( 'billing_address_1' ) ); ?>"
                               autocomplete="address-line1">
                    </div>
                </div>
                <div class="oco-form-row oco-form-row--addr">
                    <div class="oco-field">
                        <label class="oco-label" for="oco-house">Будинок <span class="oco-req">*</span></label>
                        <input type="text" id="oco-house" class="oco-input" placeholder="12">
                    </div>
                    <div class="oco-field">
                        <label class="oco-label" for="oco-apt">Кв./Офіс</label>
                        <input type="text" id="oco-apt" class="oco-input" placeholder="45">
                    </div>
                    <div class="oco-field">
                        <label class="oco-label" for="oco-entrance">Під'їзд</label>
                        <input type="text" id="oco-entrance" class="oco-input" placeholder="2">
                    </div>
                    <div class="oco-field">
                        <label class="oco-label" for="oco-floor">Поверх</label>
                        <input type="text" id="oco-floor" class="oco-input" placeholder="4">
                    </div>
                </div>
            </div>

            <!-- SECTION 4: DELIVERY METHOD -->
            <div class="oco-card">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">4</span>Спосіб доставки</h2>
                </div>
                <div class="oco-options" id="oco-delivery-options">
                    <?php
                    // Get WC shipping methods if available
                    $packages = WC()->shipping()->get_packages();
                    $has_wc_shipping = false;
                    if ( ! empty( $packages ) ) {
                        foreach ( $packages as $pkg ) {
                            if ( ! empty( $pkg['rates'] ) ) { $has_wc_shipping = true; break; }
                        }
                    }
                    if ( $has_wc_shipping ) :
                        foreach ( $packages as $pkg_idx => $pkg ) :
                            foreach ( $pkg['rates'] as $rate_id => $rate ) :
                                $active_class = ( $pkg['chosen_method'] === $rate_id ) ? ' oco-opt--active' : '';
                                $cost = (float) $rate->get_cost();
                                $cost_label = $cost > 0 ? number_format($cost,0,'','') . ' грн' : 'Безкоштовно';
                    ?>
                    <div class="oco-opt-card<?php echo $active_class; ?>"
                         data-delivery="wc_rate"
                         data-rate-id="<?php echo esc_attr( $rate_id ); ?>"
                         data-cost="<?php echo esc_attr( $cost ); ?>"
                         data-has-address="1">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">🚚</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name"><?php echo esc_html( $rate->get_label() ); ?></div>
                            <div class="oco-opt-desc"><?php echo esc_html( $cost_label ); ?></div>
                        </div>
                    </div>
                    <?php endforeach; endforeach;
                    else : ?>
                    <div class="oco-opt-card oco-opt--active"
                         data-delivery="courier"
                         data-cost="0"
                         data-has-address="1">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">🚚</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name">Кур'єром по Києву</div>
                            <div class="oco-opt-desc">Сьогодні протягом 2 годин або на обраний час · безкоштовно</div>
                        </div>
                    </div>
                    <div class="oco-opt-card"
                         data-delivery="pickup"
                         data-cost="0"
                         data-has-address="0">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">🏠</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name">Самовивіз</div>
                            <div class="oco-opt-desc">вул. Прорізна, 15 · щодня 9:00–18:00 · безкоштовно</div>
                        </div>
                    </div>
                    <div class="oco-opt-card"
                         data-delivery="np"
                         data-cost="80"
                         data-has-address="1">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">📦</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name">Нова Пошта</div>
                            <div class="oco-opt-desc">По всій Україні · 1–2 робочі дні · у спеціальній упаковці · 80 грн</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION 5: DATE & TIME -->
            <div class="oco-card" id="oco-when-section">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">5</span>Дата та час доставки</h2>
                </div>
                <p class="oco-subhead">Оберіть дату</p>
                <div class="oco-date-picker" id="oco-date-picker"></div>

                <p class="oco-subhead" style="margin-top:24px;">Оберіть час</p>
                <div class="oco-time-grid">
                    <button type="button" class="oco-time-slot" data-time="Якнайшвидше">Якнайшвидше</button>
                    <button type="button" class="oco-time-slot" data-time="09:00–10:00">09:00–10:00</button>
                    <button type="button" class="oco-time-slot" data-time="10:00–11:00">10:00–11:00</button>
                    <button type="button" class="oco-time-slot" data-time="11:00–12:00">11:00–12:00</button>
                    <button type="button" class="oco-time-slot" data-time="12:00–13:00">12:00–13:00</button>
                    <button type="button" class="oco-time-slot" data-time="13:00–14:00">13:00–14:00</button>
                    <button type="button" class="oco-time-slot oco-time-slot--active" data-time="14:00–15:00">14:00–15:00</button>
                    <button type="button" class="oco-time-slot" data-time="15:00–16:00">15:00–16:00</button>
                    <button type="button" class="oco-time-slot" data-time="16:00–17:00">16:00–17:00</button>
                    <button type="button" class="oco-time-slot" data-time="17:00–18:00">17:00–18:00</button>
                    <button type="button" class="oco-time-slot" data-time="18:00–19:00">18:00–19:00</button>
                </div>
            </div>

            <!-- SECTION 6: PAYMENT -->
            <div class="oco-card">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">6</span>Спосіб оплати</h2>
                </div>
                <div class="oco-options" id="oco-payment-options">
                    <?php
                    $gateways = WC()->payment_gateways()->get_available_payment_gateways();
                    $first = true;
                    if ( ! empty( $gateways ) ) :
                        foreach ( $gateways as $gw_id => $gateway ) :
                            $icons = [
                                'cod'       => '💵',
                                'bacs'      => '🏦',
                                'cheque'    => '📄',
                                'liqpay'    => '💳',
                                'wayforpay' => '💳',
                            ];
                            $icon = $icons[ $gw_id ] ?? '💳';
                    ?>
                    <div class="oco-opt-card<?php echo $first ? ' oco-opt--active' : ''; ?>"
                         data-payment="<?php echo esc_attr( $gw_id ); ?>">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon"><?php echo $icon; ?></div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name"><?php echo esc_html( $gateway->get_title() ); ?></div>
                            <?php if ( $gateway->get_description() ) : ?>
                                <div class="oco-opt-desc"><?php echo wp_kses_post( $gateway->get_description() ); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $first = false; endforeach;
                    else : ?>
                    <div class="oco-opt-card oco-opt--active" data-payment="cod">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">💵</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name">Готівкою кур'єру</div>
                            <div class="oco-opt-desc">Оплата при отриманні · готівка або карта</div>
                        </div>
                    </div>
                    <div class="oco-opt-card" data-payment="bacs">
                        <div class="oco-opt-radio"></div>
                        <div class="oco-opt-icon">🏦</div>
                        <div class="oco-opt-info">
                            <div class="oco-opt-name">Банківський переказ</div>
                            <div class="oco-opt-desc">Виставимо рахунок на e-mail</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION 7: COMMENT -->
            <div class="oco-card">
                <div class="oco-section-head">
                    <h2 class="oco-section-title"><span class="oco-num">7</span>Коментар до замовлення</h2>
                    <span class="oco-section-hint">за бажанням</span>
                </div>
                <div class="oco-field oco-field--full">
                    <textarea id="oco-note" class="oco-textarea"
                              placeholder="Особливі побажання: подзвонити перед доставкою, передати без контакту, додати листівку…"></textarea>
                </div>

                <label class="oco-check" for="oco-gift-check">
                    <input type="checkbox" id="oco-gift-check">
                    <span class="oco-check-box"></span>
                    <span><strong style="color:#3d3d3d;">Це подарунок</strong> — додамо рукописну листівку та святкову стрічку (безкоштовно)</span>
                </label>

                <label class="oco-check" for="oco-terms-check">
                    <input type="checkbox" id="oco-terms-check" required>
                    <span class="oco-check-box"></span>
                    <span>Я погоджуюся з <a href="<?php echo esc_url( wc_get_page_permalink( 'terms' ) ); ?>">умовами обслуговування</a> та <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">політикою конфіденційності</a> <span style="color:#c4845a;">*</span></span>
                </label>
            </div>

        </div><!-- .oco-left -->

        <!-- ─── RIGHT: STICKY SUMMARY ──────────────────────── -->
        <div class="oco-right">
            <div class="oco-summary"
                 data-subtotal="<?php echo esc_attr( $subtotal ); ?>"
                 data-shipping="<?php echo esc_attr( $ship_total ); ?>"
                 data-discount="<?php echo esc_attr( $discount ); ?>">

                <h3 class="oco-summary-title">Підсумок замовлення</h3>

                <div class="oco-summary-when">
                    <div>Доставимо <strong id="oco-when-label">сьогодні, 14:00–15:00</strong> · теплим, у фірмовій упаковці</div>
                </div>

                <div class="oco-summary-row">
                    <span>Сума товарів (<span id="oco-items-count"><?php echo esc_html( $cart_count ); ?></span> шт)</span>
                    <strong><span id="oco-sum-subtotal"><?php echo number_format( $subtotal, 0, '', '' ); ?></span> грн</strong>
                </div>

                <?php if ( $discount > 0 ) : ?>
                <div class="oco-summary-row oco-summary-row--discount" id="oco-discount-row">
                    <span>Знижка
                        <?php foreach ( $cart->get_applied_coupons() as $code ) : ?>
                            <span class="oco-coupon-badge"><?php echo esc_html( strtoupper( $code ) ); ?></span>
                        <?php endforeach; ?>
                    </span>
                    <strong>− <span id="oco-sum-discount"><?php echo number_format( $discount, 0, '', '' ); ?></span> грн</strong>
                </div>
                <?php else : ?>
                <div class="oco-summary-row oco-summary-row--discount" id="oco-discount-row" style="display:none;">
                    <span>Знижка <span id="oco-discount-label"></span></span>
                    <strong>− <span id="oco-sum-discount">0</span> грн</strong>
                </div>
                <?php endif; ?>

                <div class="oco-summary-row">
                    <span id="oco-shipping-label">Доставка по Києву</span>
                    <strong id="oco-sum-shipping" style="color:<?php echo $ship_free ? '#7a9b6e' : '#3d3d3d'; ?>">
                        <?php echo esc_html( $ship_label ); ?>
                    </strong>
                </div>

                <div class="oco-summary-total">
                    <span class="oco-summary-total-label">До оплати</span>
                    <div class="oco-summary-total-price">
                        <span id="oco-sum-total"><?php echo number_format( $total, 0, '', '' ); ?></span><span class="oco-summary-total-unit">грн</span>
                    </div>
                </div>

                <button type="button" class="oco-place-btn" id="oco-place-btn">
                    Підтвердити замовлення →
                </button>

                <div class="oco-secure-note">Захищене з'єднання · ваші дані під захистом</div>

                <div class="oco-summary-trust">
                    <div class="oco-trust-item">Випікаємо під замовлення</div>
                    <div class="oco-trust-item">Доставка за 2 години</div>
                    <div class="oco-trust-item">Оплата при отриманні</div>
                    <div class="oco-trust-item">Скасування безкоштовно</div>
                </div>
            </div>
        </div><!-- .oco-right -->

    </div><!-- .oco-grid -->

    <?php else : ?>

    <!-- Empty cart -->
    <div class="oco-empty">
        <div class="oco-empty-icon">🛒</div>
        <h2 class="oco-empty-title">Кошик поки порожній</h2>
        <p class="oco-empty-text">Оберіть улюблений пиріг у каталозі — приготуємо й привеземо теплим.</p>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="oco-place-btn" style="display:inline-block;text-align:center;text-decoration:none;">До каталогу →</a>
        <?php do_action( 'woocommerce_cart_is_empty' ); ?>
    </div>

    <?php endif; ?>

</div><!-- .oco-wrap -->

<?php do_action( 'woocommerce_after_cart' ); ?>
