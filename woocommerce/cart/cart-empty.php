<?php
/**
 * WooCommerce: Empty cart page
 * Overrides: woocommerce/templates/cart/cart-empty.php
 */

defined( 'ABSPATH' ) || exit;

$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
?>

<div class="oco-wrap">

    <div class="oco-breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span aria-hidden="true"> › </span>
        <span>Кошик</span>
    </div>

    <div class="oco-page-head">
        <div>
            <h1 class="oco-page-title">Кошик</h1>
        </div>
    </div>

    <?php woocommerce_output_all_notices(); ?>

    <!-- Empty state -->
    <div class="oco-empty">
        <div class="oco-empty-illustration">
            <div class="oco-empty-basket">🧺</div>
            <div class="oco-empty-crumb oco-empty-crumb--1"></div>
            <div class="oco-empty-crumb oco-empty-crumb--2"></div>
            <div class="oco-empty-crumb oco-empty-crumb--3"></div>
            <div class="oco-empty-crumb oco-empty-crumb--4"></div>
        </div>
        <h2 class="oco-empty-title">Ваш кошик порожній</h2>
        <p class="oco-empty-text">Тут поки що тихо… Гайда обирати — поки всі не розібрали. Можемо привезти теплим уже сьогодні.</p>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="oco-empty-cta">
            <span>Повернутись в магазин</span>
            <span class="oco-empty-cta-arrow">→</span>
        </a>
        <?php do_action( 'woocommerce_cart_is_empty' ); ?>
    </div>

    <?php
    $suggest_products = wc_get_products( [
        'status'  => 'publish',
        'limit'   => 4,
        'orderby' => 'popularity',
        'order'   => 'DESC',
    ] );
    if ( ! empty( $suggest_products ) ) : ?>
    <div class="oco-suggest">
        <div class="oco-suggest-head">
            <h2 class="oco-suggest-title">Можливо, вас зацікавить</h2>
            <a href="<?php echo esc_url( $shop_url ); ?>" class="oco-suggest-link">Усі пироги →</a>
        </div>
        <div class="oco-suggest-grid">
            <?php foreach ( $suggest_products as $sp ) :
                $sp_url   = get_permalink( $sp->get_id() );
                $sp_name  = $sp->get_name();
                $sp_price = (float) $sp->get_price();
                $sp_cat   = '';
                $sp_terms = get_the_terms( $sp->get_id(), 'product_cat' );
                if ( $sp_terms && ! is_wp_error( $sp_terms ) ) {
                    $sp_cat = $sp_terms[0]->name;
                }
                $sp_img_id  = $sp->get_image_id();
                $sp_img_url = $sp_img_id ? wp_get_attachment_image_url( $sp_img_id, 'medium' ) : '';
            ?>
            <a href="<?php echo esc_url( $sp_url ); ?>" class="oco-suggest-card">
                <div class="oco-suggest-card-img">
                    <?php if ( $sp_img_url ) : ?>
                        <img src="<?php echo esc_url( $sp_img_url ); ?>" alt="<?php echo esc_attr( $sp_name ); ?>">
                    <?php else : ?>
                        🥧
                    <?php endif; ?>
                </div>
                <div class="oco-suggest-card-body">
                    <?php if ( $sp_cat ) : ?>
                        <span class="oco-suggest-card-cat"><?php echo esc_html( $sp_cat ); ?></span>
                    <?php endif; ?>
                    <span class="oco-suggest-card-name"><?php echo esc_html( $sp_name ); ?></span>
                    <div class="oco-suggest-card-foot">
                        <span class="oco-suggest-card-price">
                            <?php echo esc_html( number_format( $sp_price, 0, '.', ' ' ) ); ?><span class="oco-suggest-card-price-unit"> грн</span>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
