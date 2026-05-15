<?php
/**
 * Generic static page template.
 */

get_header();

// WooCommerce full-width pages (cart, checkout, account) handle their own layout
$is_woo_fullwidth = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );

if ( $is_woo_fullwidth ) : ?>

    <div class="woo-page-wrap">
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

