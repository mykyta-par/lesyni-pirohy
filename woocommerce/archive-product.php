<?php
/**
 * WooCommerce: Shop/Catalog archive page
 * Overrides: woocommerce/templates/archive-product.php
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php woocommerce_output_all_notices(); ?>

<!-- ======================================================================
   CATALOG HERO BANNER
====================================================================== -->
<div class="catalog-hero">
    <nav class="catalog-hero__breadcrumbs" aria-label="Breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span class="sep" aria-hidden="true">›</span>
        <?php
        if ( is_product_category() ) {
            $cat = get_queried_object();
            if ( $cat->parent ) {
                $parent = get_term( $cat->parent, 'product_cat' );
                echo '<a href="' . esc_url( get_term_link( $parent ) ) . '">' . esc_html( $parent->name ) . '</a>';
                echo '<span class="sep" aria-hidden="true">›</span>';
            }
            echo '<span>' . esc_html( $cat->name ) . '</span>';
        } else {
            echo '<span>Пироги</span>';
        }
        ?>
    </nav>

    <h1 class="catalog-hero__title">
        <?php
        if ( is_product_category() ) {
            single_term_title();
        } else {
            echo 'Наші пироги';
        }
        ?>
    </h1>

    <p class="catalog-hero__subtitle">
        <?php if ( is_product_category() && term_description() ) : ?>
            <?php echo wp_kses_post( term_description() ); ?>
        <?php else : ?>
            Оберіть улюблений смак — ми приготуємо його з душею і доставимо теплим до вашого столу.
        <?php endif; ?>
    </p>
</div>

<!-- ======================================================================
   CATALOG CONTROLS
====================================================================== -->
<div class="catalog-controls">
    <div class="category-tabs" role="tablist">
        <?php
        $current_cat  = is_product_category() ? get_queried_object()->slug : 'all';
        $shop_url     = get_permalink( wc_get_page_id( 'shop' ) );
        $all_count    = wp_count_posts( 'product' )->publish ?? '';

        echo '<a href="' . esc_url( $shop_url ) . '" class="category-tab' . ( ! is_product_category() ? ' active' : '' ) . '" role="tab">'
            . 'Усі<span class="tab-count">·' . esc_html( $all_count ) . '</span></a>';

        $cats = get_terms( [
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
        ] );
        if ( ! is_wp_error( $cats ) ) {
            foreach ( $cats as $cat ) {
                $active_class = ( is_product_category() && get_queried_object()->slug === $cat->slug ) ? ' active' : '';
                echo '<a href="' . esc_url( get_term_link( $cat ) ) . '"'
                    . ' class="category-tab' . $active_class . '"'
                    . ' role="tab">'
                    . esc_html( $cat->name )
                    . '<span class="tab-count">·' . esc_html( $cat->count ) . '</span>'
                    . '</a>';
            }
        }
        ?>
    </div>

    <div class="catalog-sort">
        <span class="catalog-sort__label">Сортувати:</span>
        <?php woocommerce_catalog_ordering(); ?>
    </div>
</div>

<!-- ======================================================================
   PRODUCTS
====================================================================== -->
<div class="catalog-container">
    <?php if ( woocommerce_product_loop() ) : ?>
        <div class="products-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php wc_get_template_part( 'content', 'product' ); ?>
            <?php endwhile; ?>
        </div>

        <?php
        // Pagination
        woocommerce_pagination();
        ?>

    <?php else : ?>
        <p class="woocommerce-info">
            За вашим запитом нічого не знайдено.
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Переглянути всі пироги →</a>
        </p>
    <?php endif; ?>

    <!-- ORDER CTA BANNER -->
    <div class="order-cta">
        <div class="order-cta__content">
            <h3 class="order-cta__title">Не знаєте, що обрати?</h3>
            <p class="order-cta__desc">Зателефонуйте — наш оператор порадить найкращий варіант саме для вашої нагоди.</p>
            <a href="tel:+380632532696" class="order-cta__phone">+38 063 253 26 96</a>
        </div>
        <div class="order-cta__actions">
            <a href="tel:+380632532696" class="order-cta__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.9 13.65 19.79 19.79 0 0 1 1.87 5.07 2 2 0 0 1 3.84 2.9h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17v-.08z"/></svg>
                Зателефонувати
            </a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
