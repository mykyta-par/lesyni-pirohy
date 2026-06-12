<?php
/**
 * WooCommerce: Checkout form
 * Overrides: woocommerce/templates/checkout/form-checkout.php
 *
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

$checkout = WC()->checkout();
$cart     = WC()->cart;
$cart_url = wc_get_cart_url();
$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

if ( $cart->is_empty() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
    wp_safe_redirect( $cart_url );
    exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// Billing field helper
function lesyni_co_field( $name, $label, $args = [] ) {
    $checkout = WC()->checkout();
    $defaults = [
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => [],
        'autocomplete'=> '',
    ];
    $args = wp_parse_args( $args, $defaults );
    $value = $checkout->get_value( $name );
    $req   = $args['required'] ? ' <span class="co-req">*</span>' : '';
    $reqattr = $args['required'] ? ' required' : '';
    $auto = $args['autocomplete'] ? ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"' : '';
    ?>
    <div class="co-field<?php echo ! empty( $args['class'] ) ? ' ' . esc_attr( implode( ' ', $args['class'] ) ) : ''; ?>">
        <label class="co-label" for="<?php echo esc_attr( $name ); ?>"><?php echo $label . $req; ?></label>
        <?php if ( $args['type'] === 'select' ) : ?>
            <select id="<?php echo esc_attr( $name ); ?>"
                    name="<?php echo esc_attr( $name ); ?>"
                    class="co-select"<?php echo $reqattr; ?>>
                <?php foreach ( $args['options'] as $k => $v ) : ?>
                    <option value="<?php echo esc_attr( $k ); ?>"<?php selected( $value, $k ); ?>><?php echo esc_html( $v ); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <input id="<?php echo esc_attr( $name ); ?>"
                   name="<?php echo esc_attr( $name ); ?>"
                   type="<?php echo esc_attr( $args['type'] ); ?>"
                   class="co-input"
                   value="<?php echo esc_attr( $value ); ?>"
                   placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"<?php echo $reqattr . $auto; ?>>
        <?php endif; ?>
        <?php if ( ! empty( $args['hint'] ) ) : ?>
            <span class="co-hint"><?php echo esc_html( $args['hint'] ); ?></span>
        <?php endif; ?>
    </div>
    <?php
}

// Shipping packages for delivery section
$ship_packages = WC()->shipping()->get_packages();
$has_shipping  = ! empty( $ship_packages );
$chosen_methods = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', [] ) : [];
?>

<div class="checkout-wrap">

    <!-- Breadcrumbs -->
    <nav class="cart-breadcrumbs" aria-label="Breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( $shop_url ); ?>">Пироги</a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( $cart_url ); ?>">Кошик</a>
        <span aria-hidden="true">›</span>
        <span>Оформлення</span>
    </nav>

    <!-- Page head -->
    <div class="cart-page-head">
        <div>
            <h1 class="cart-page-title">Оформлення замовлення</h1>
            <p class="cart-page-subtitle">Залишилось кілька деталей — і ми починаємо пекти ваш пиріг.</p>
        </div>
    </div>

    <!-- Steps -->
    <div class="cart-steps">
        <div class="cart-step cart-step--done">
            <span class="cart-step__num">✓</span>
            <span class="cart-step__label">Кошик</span>
        </div>
        <span class="cart-step__arrow" aria-hidden="true">›</span>
        <div class="cart-step cart-step--active">
            <span class="cart-step__num">2</span>
            <span class="cart-step__label">Оформлення</span>
        </div>
        <span class="cart-step__arrow" aria-hidden="true">›</span>
        <div class="cart-step">
            <span class="cart-step__num">3</span>
            <span class="cart-step__label">Підтвердження</span>
        </div>
    </div>

    <?php woocommerce_output_all_notices(); ?>

    <form name="checkout"
          id="checkout-form"
          method="post"
          class="checkout woocommerce-checkout checkout-layout"
          action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
          enctype="multipart/form-data"
          novalidate="novalidate">

        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

        <!-- ══ LEFT COLUMN ══════════════════════════════════════════ -->
        <div class="checkout-main">

            <?php if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) : ?>
                <div class="co-login-callout">
                    <span>👤</span>
                    <span>Уже є акаунт? <a href="#" class="co-login-link">Увійти</a> — заповнимо дані автоматично.</span>
                </div>
            <?php endif; ?>

            <?php do_action( 'woocommerce_checkout_before_billing_form' ); ?>

            <!-- ── Section 1: Contact ── -->
            <div class="co-section">
                <div class="co-section-head">
                    <h2 class="co-section-title">
                        <span class="co-section-num">1</span>Контактні дані
                    </h2>
                    <span class="co-section-hint">Поля з <span class="co-req">*</span> обов'язкові</span>
                </div>

                <div class="co-row">
                    <?php lesyni_co_field( 'billing_first_name', 'Ім\'я', [ 'required' => true, 'placeholder' => 'Олена', 'autocomplete' => 'given-name' ] ); ?>
                    <?php lesyni_co_field( 'billing_last_name',  'Прізвище', [ 'required' => true, 'placeholder' => 'Куценко', 'autocomplete' => 'family-name' ] ); ?>
                </div>
                <div class="co-row">
                    <?php lesyni_co_field( 'billing_phone', 'Телефон', [ 'required' => true, 'type' => 'tel', 'placeholder' => '+38 (___) ___-__-__', 'autocomplete' => 'tel' ] ); ?>
                    <?php lesyni_co_field( 'billing_email', 'E-mail', [ 'type' => 'email', 'placeholder' => 'lesya@gmail.com', 'hint' => 'Надішлемо чек і статус замовлення', 'autocomplete' => 'email' ] ); ?>
                </div>
            </div>

            <?php do_action( 'woocommerce_checkout_after_billing_form', $checkout ); ?>

            <!-- ── Section 2: Delivery ── -->
            <div class="co-section">
                <div class="co-section-head">
                    <h2 class="co-section-title">
                        <span class="co-section-num">2</span>Доставка
                    </h2>
                </div>

                <?php if ( $has_shipping ) : ?>
                    <!-- WooCommerce shipping methods as option cards -->
                    <div class="co-options" id="co-delivery-options">
                        <?php foreach ( $ship_packages as $i => $package ) :
                            $chosen = isset( $chosen_methods[ $i ] ) ? $chosen_methods[ $i ] : '';
                            foreach ( $package['rates'] as $rate_id => $rate ) :
                                $is_active = ( $chosen === $rate_id ) || ( ! $chosen && $i === 0 && $rate === reset( $package['rates'] ) );
                                $is_free   = ( (float) $rate->get_cost() == 0 );
                        ?>
                            <label class="co-opt-card <?php echo $is_active ? 'co-opt-card--active' : ''; ?>"
                                   data-has-address="1">
                                <input type="radio"
                                       name="shipping_method[<?php echo esc_attr( $i ); ?>]"
                                       id="shipping_method_<?php echo esc_attr( $i ); ?>_<?php echo esc_attr( sanitize_title( $rate_id ) ); ?>"
                                       value="<?php echo esc_attr( $rate_id ); ?>"
                                       class="shipping_method co-opt-radio-input"
                                       <?php checked( $is_active ); ?>>
                                <span class="co-opt-radio"></span>
                                <span class="co-opt-icon">🚚</span>
                                <span class="co-opt-info">
                                    <span class="co-opt-name"><?php echo esc_html( $rate->get_label() ); ?></span>
                                </span>
                                <span class="co-opt-price <?php echo $is_free ? 'co-opt-price--free' : ''; ?>">
                                    <?php echo $is_free ? 'Безкоштовно' : wc_price( $rate->get_cost() ); ?>
                                </span>
                            </label>
                        <?php endforeach; endforeach; ?>
                    </div>

                <?php else : ?>
                    <!-- Custom visual delivery cards (no WC shipping methods configured) -->
                    <div class="co-options" id="co-delivery-options">
                        <label class="co-opt-card co-opt-card--active" data-has-address="1">
                            <input type="radio" name="delivery_type" value="kyiv" class="co-opt-radio-input" checked>
                            <span class="co-opt-radio"></span>
                            <span class="co-opt-icon">🚚</span>
                            <span class="co-opt-info">
                                <span class="co-opt-name">Доставка по Києву</span>
                                <span class="co-opt-desc">Сьогодні протягом 2 годин · теплим у термобоксі</span>
                            </span>
                            <span class="co-opt-price co-opt-price--free">Безкоштовно</span>
                        </label>
                        <label class="co-opt-card" data-has-address="1">
                            <input type="radio" name="delivery_type" value="suburb" class="co-opt-radio-input">
                            <span class="co-opt-radio"></span>
                            <span class="co-opt-icon">🛵</span>
                            <span class="co-opt-info">
                                <span class="co-opt-name">Передмістя</span>
                                <span class="co-opt-desc">Бровари, Бориспіль, Ірпінь, Буча · 90–120 хвилин</span>
                            </span>
                            <span class="co-opt-price">80 грн</span>
                        </label>
                        <label class="co-opt-card" data-has-address="1">
                            <input type="radio" name="delivery_type" value="np" class="co-opt-radio-input">
                            <span class="co-opt-radio"></span>
                            <span class="co-opt-icon">📦</span>
                            <span class="co-opt-info">
                                <span class="co-opt-name">Нова Пошта</span>
                                <span class="co-opt-desc">По всій Україні · 1–2 робочі дні</span>
                            </span>
                            <span class="co-opt-price">65 грн</span>
                        </label>
                        <label class="co-opt-card" data-has-address="0">
                            <input type="radio" name="delivery_type" value="pickup" class="co-opt-radio-input">
                            <span class="co-opt-radio"></span>
                            <span class="co-opt-icon">🏠</span>
                            <span class="co-opt-info">
                                <span class="co-opt-name">Самовивіз з пекарні</span>
                                <span class="co-opt-desc">вул. Михайлівська, 12 · щодня 9:00–20:00</span>
                            </span>
                            <span class="co-opt-price co-opt-price--free">Безкоштовно</span>
                        </label>
                    </div>
                <?php endif; ?>

                <!-- Address fields -->
                <div class="co-address-block" id="co-address-block">
                    <div class="co-row">
                        <?php lesyni_co_field( 'billing_city', 'Місто', [ 'required' => true, 'placeholder' => 'Київ', 'autocomplete' => 'address-level2' ] ); ?>
                        <?php lesyni_co_field( 'billing_state', 'Район / Область', [ 'placeholder' => 'Шевченківський', 'autocomplete' => 'address-level1' ] ); ?>
                    </div>
                    <div class="co-row">
                        <?php lesyni_co_field( 'billing_address_1', 'Вулиця', [ 'required' => true, 'placeholder' => 'вул. Яворницького', 'autocomplete' => 'address-line1' ] ); ?>
                        <?php lesyni_co_field( 'billing_address_2', 'Квартира / Офіс', [ 'placeholder' => '45', 'autocomplete' => 'address-line2' ] ); ?>
                    </div>
                    <input type="hidden" name="billing_country" value="UA">
                    <input type="hidden" name="billing_postcode" value="">
                </div>

                <!-- Time slots -->
                <div class="co-time-block">
                    <span class="co-label">Бажаний час доставки</span>
                    <div class="co-time-grid">
                        <?php
                        $slots = [ 'Якнайшвидше', '14:00–15:00', '15:00–16:00', '16:00–17:00', '17:00–18:00', '18:00–19:00', '19:00–20:00', 'Завтра' ];
                        foreach ( $slots as $i => $slot ) :
                        ?>
                            <button type="button"
                                    class="co-time-slot<?php echo $i === 1 ? ' co-time-slot--active' : ''; ?>"
                                    data-time="<?php echo esc_attr( $slot ); ?>">
                                <?php echo esc_html( $slot ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="delivery_time" id="delivery_time_input"
                           value="14:00–15:00">
                </div>
            </div>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

            <!-- ── Section 3: Payment ── -->
            <div class="co-section co-section--payment">
                <div class="co-section-head">
                    <h2 class="co-section-title">
                        <span class="co-section-num">3</span>Спосіб оплати
                    </h2>
                </div>
                <?php do_action( 'woocommerce_checkout_payment' ); ?>
            </div>

            <!-- ── Section 4: Notes & Checkboxes ── -->
            <div class="co-section">
                <div class="co-section-head">
                    <h2 class="co-section-title">
                        <span class="co-section-num">4</span>Коментар до замовлення
                    </h2>
                    <span class="co-section-hint">за бажанням</span>
                </div>

                <div class="co-field">
                    <label class="co-label" for="order_comments">Особливі побажання</label>
                    <textarea id="order_comments"
                              name="order_comments"
                              class="co-textarea"
                              placeholder="Подзвонити перед доставкою, подарункове пакування, рукописна листівка…"><?php echo esc_textarea( $checkout->get_value( 'order_comments' ) ); ?></textarea>
                </div>

                <label class="co-check">
                    <input type="checkbox" name="lesyni_gift" id="lesyni_gift" value="1">
                    <span class="co-check-box"></span>
                    <span><strong>Це подарунок</strong> — додамо рукописну листівку від Лесі та святкову стрічку (безкоштовно)</span>
                </label>

                <label class="co-check">
                    <input type="checkbox" name="lesyni_subscribe" id="lesyni_subscribe" value="1" checked>
                    <span class="co-check-box"></span>
                    <span>Підписатися на новини про сезонні смаки та акції</span>
                </label>

                <?php if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>
                    <label class="co-check">
                        <input type="checkbox" name="terms" id="terms" value="1" required>
                        <span class="co-check-box"></span>
                        <span>
                            Я погоджуюся з
                            <?php wc_terms_and_conditions_page_content(); ?>
                            <span class="co-req">*</span>
                        </span>
                    </label>
                <?php endif; ?>

                <?php do_action( 'woocommerce_checkout_after_terms_and_conditions' ); ?>

            </div>

        </div><!-- .checkout-main -->

        <!-- ══ RIGHT COLUMN ══════════════════════════════════════════ -->
        <div class="checkout-side">
            <div class="co-summary">

                <h3 class="co-summary__title">
                    Ваше замовлення
                    <a href="<?php echo esc_url( $cart_url ); ?>" class="co-summary__edit">Змінити</a>
                </h3>

                <!-- Cart items -->
                <div class="co-sum-items">
                    <?php foreach ( $cart->get_cart() as $item_key => $item ) :
                        $_product = $item['data'];
                        if ( ! $_product || ! $_product->exists() ) continue;

                        $has_img  = $_product->get_image_id() > 0;
                        $var_data = wc_get_formatted_cart_item_data( $item, true );
                    ?>
                        <div class="co-sum-item">
                            <div class="co-sum-item__img">
                                <?php if ( $has_img ) : ?>
                                    <?php echo $_product->get_image( 'thumbnail', [ 'alt' => '' ] ); ?>
                                <?php else : ?>
                                    <span class="pie-visual pie-visual--sm <?php echo esc_attr( lesyni_pie_visual_class( $_product ) ); ?>"></span>
                                <?php endif; ?>
                                <span class="co-sum-item__qty"><?php echo esc_html( $item['quantity'] ); ?></span>
                            </div>
                            <div class="co-sum-item__info">
                                <span class="co-sum-item__name"><?php echo esc_html( $_product->get_name() ); ?></span>
                                <?php if ( $var_data ) : ?>
                                    <span class="co-sum-item__meta"><?php echo wp_strip_all_tags( $var_data ); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="co-sum-item__price">
                                <?php echo number_format( (float) $item['line_subtotal'], 0, '', '' ); ?> грн
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Totals -->
                <div class="co-summary__row">
                    <span>Сума товарів</span>
                    <strong><?php echo number_format( (float) $cart->get_subtotal(), 0, '', '' ); ?> грн</strong>
                </div>

                <?php if ( (float) $cart->get_discount_total() > 0 ) : ?>
                    <div class="co-summary__row co-summary__row--discount">
                        <span>Знижка</span>
                        <strong>− <?php echo number_format( (float) $cart->get_discount_total(), 0, '', '' ); ?> грн</strong>
                    </div>
                <?php endif; ?>

                <div class="co-summary__row">
                    <span>Доставка</span>
                    <strong id="co-shipping-label" style="color:#7a9b6e;">
                        <?php
                        $ship = (float) $cart->get_shipping_total();
                        echo $ship > 0 ? number_format( $ship, 0, '', '' ) . ' грн' : 'Безкоштовно';
                        ?>
                    </strong>
                </div>

                <div class="co-summary__total">
                    <span class="co-summary__total-label">До оплати</span>
                    <span class="co-summary__total-price">
                        <?php echo number_format( (float) $cart->get_total( 'edit' ), 0, '', '' ); ?>
                        <span class="co-summary__total-unit">грн</span>
                    </span>
                </div>

                <!-- CTA — triggers the real WC #place_order button -->
                <button type="button" id="co-place-order-cta" class="co-summary__cta">
                    Підтвердити замовлення
                </button>

                <div class="co-secure-note">🔒 Захищене з'єднання · ваші дані під захистом</div>

            </div><!-- .co-summary -->
        </div><!-- .checkout-side -->

        <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
        <input type="hidden" name="woocommerce_checkout_place_order" value="1">

    </form>

</div><!-- .checkout-wrap -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
