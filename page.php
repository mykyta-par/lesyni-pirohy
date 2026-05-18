<?php
/**
 * Generic static page template.
 */

get_header();

// WooCommerce full-width pages (cart, checkout, account) handle their own layout
$is_woo_fullwidth = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );

// Pages with WooCommerce product shortcodes need catalog layout
$content = get_the_content();
$is_woo_catalog = function_exists( 'has_shortcode' ) && (
    has_shortcode( $content, 'product_category' ) ||
    has_shortcode( $content, 'products' ) ||
    has_shortcode( $content, 'product_cat' )
);

if ( $is_woo_fullwidth ) : ?>

    <div class="woo-page-wrap">
        <?php the_content(); ?>
    </div>

<?php elseif ( $is_woo_catalog ) : ?>

    <div class="catalog-hero">
        <nav class="catalog-hero__breadcrumbs" aria-label="Breadcrumbs">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
            <span class="sep" aria-hidden="true">›</span>
            <span><?php the_title(); ?></span>
        </nav>
        <h1 class="catalog-hero__title"><?php the_title(); ?></h1>
    </div>

    <div class="catalog-container">
        <?php the_content(); ?>
    </div>

<?php else : ?>

    <main class="page-content">
        <?php while ( have_posts() ) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </main>

<?php endif;

get_footer();

