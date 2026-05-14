<?php
/**
 * WooCommerce: Single product page
 * Overrides: woocommerce/templates/single-product.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
do_action( 'woocommerce_before_single_product' );

while ( have_posts() ) :
    the_post();
    global $product;

    /* -- data ---------------------------------------------------------- */
    $cats = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
    $cat_name = '';
    $cat_term = null;
    if ( ! empty( $cats ) ) {
        $t = get_term( $cats[0], 'product_cat' );
        if ( $t && ! is_wp_error( $t ) ) {
            $cat_name = $t->name;
            $cat_term = $t;
        }
    }

    $main_img_id = $product->get_image_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $all_imgs    = $main_img_id ? array_merge( [ $main_img_id ], $gallery_ids ) : $gallery_ids;
    $short_desc  = wp_strip_all_tags( $product->get_short_description() );
    $avg_rating  = $product->get_average_rating();
    ?>

    <?php woocommerce_output_all_notices(); ?>

    <!-- Breadcrumbs -->
    <nav class="sp-breadcrumbs" aria-label="Breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Пироги</a>
        <?php if ( $cat_name && $cat_term ) : ?>
            <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
            <a href="<?php echo esc_url( get_term_link( $cat_term ) ); ?>"><?php echo esc_html( $cat_name ); ?></a>
        <?php endif; ?>
        <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
        <span><?php the_title(); ?></span>
    </nav>

    <!-- Main layout: gallery + details -->
    <div class="sp-layout" id="product-<?php the_ID(); ?>">

        <!-- Gallery -->
        <div class="sp-gallery">
            <?php if ( ! empty( $all_imgs ) ) : ?>
                <div class="sp-gallery__main">
                    <?php echo wp_get_attachment_image(
                        $all_imgs[0], 'large', false,
                        [ 'id' => 'sp-main-image', 'class' => 'sp-main-image', 'alt' => esc_attr( $product->get_name() ) ]
                    ); ?>
                </div>
                <?php if ( count( $all_imgs ) > 1 ) : ?>
                    <div class="sp-gallery__thumbs">
                        <?php foreach ( $all_imgs as $i => $img_id ) :
                            $full_src  = wp_get_attachment_image_url( $img_id, 'large' );
                            $thumb_src = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                        ?>
                            <button
                                class="sp-gallery__thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                                type="button"
                                data-full="<?php echo esc_url( $full_src ); ?>"
                                aria-label="Фото <?php echo $i + 1; ?>">
                                <img src="<?php echo esc_url( $thumb_src ); ?>" alt="">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="sp-gallery__placeholder">
                    <span class="pie-visual pie-visual--lg <?php echo esc_attr( lesyni_pie_visual_class( $product ) ); ?>"></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product details -->
        <div class="sp-details">

            <?php if ( $cat_name ) : ?>
                <p class="sp-category"><?php echo esc_html( $cat_name ); ?></p>
            <?php endif; ?>

            <h1 class="sp-title"><?php the_title(); ?></h1>

            <?php if ( $avg_rating > 0 ) : ?>
                <div class="sp-rating">
                    <span class="sp-rating__stars">
                        <?php
                        $r = (float) $avg_rating;
                        for ( $i = 1; $i <= 5; $i++ ) {
                            $class = $i <= floor( $r ) ? 'star--full' : ( ( $i - 0.5 ) <= $r ? 'star--half' : 'star--empty' );
                            echo '<span class="star ' . $class . '">★</span>';
                        }
                        ?>
                    </span>
                    <span class="sp-rating__score"><?php echo number_format( $avg_rating, 1 ); ?></span>
                    <?php if ( $product->get_review_count() > 0 ) : ?>
                        <a href="#tab-reviews" class="sp-rating__link">
                            (<?php echo esc_html( $product->get_review_count() ); ?> відгуків)
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( $short_desc ) : ?>
                <p class="sp-excerpt"><?php echo esc_html( $short_desc ); ?></p>
            <?php endif; ?>

            <!-- WooCommerce add-to-cart form (price + variations + button) -->
            <?php woocommerce_template_single_add_to_cart(); ?>

            <!-- SKU / category meta -->
            <div class="sp-meta">
                <?php woocommerce_template_single_meta(); ?>
            </div>

        </div><!-- .sp-details -->
    </div><!-- .sp-layout -->

    <!-- Tabs: Description / Attributes / Reviews -->
    <div class="sp-tabs-wrap">
        <?php woocommerce_output_product_data_tabs(); ?>
    </div>

    <!-- Related products -->
    <?php
    $related_ids = wc_get_related_products( $product->get_id(), 4 );
    if ( ! empty( $related_ids ) ) :
        $rel_q = new WP_Query( [
            'post_type'      => 'product',
            'post__in'       => $related_ids,
            'posts_per_page' => 4,
            'orderby'        => 'post__in',
        ] );
        if ( $rel_q->have_posts() ) :
    ?>
    <div class="sp-related">
        <div class="sp-related__inner">
            <h3 class="sp-related__title">Схожі товари</h3>
            <div class="products-grid">
                <?php while ( $rel_q->have_posts() ) : $rel_q->the_post(); ?>
                    <?php wc_get_template_part( 'content', 'product' ); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
    <?php endif; endif; ?>

    <?php do_action( 'woocommerce_after_single_product' ); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
