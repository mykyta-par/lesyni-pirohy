<?php
/**
 * WooCommerce: Product loop item (card)
 * Overrides: woocommerce/templates/content-product.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
    return;
}

/* -- helpers ----------------------------------------------------------- */
$is_variable = $product->is_type( 'variable' );
$is_on_sale  = $product->is_on_sale();
$is_featured = $product->is_featured();
$pie_class   = lesyni_pie_visual_class( $product );
$has_image   = has_post_thumbnail( $product->get_id() );

// Badge
$badge_text  = '';
$badge_class = '';
if ( $is_on_sale ) {
    $reg  = (float) $product->get_regular_price();
    $sale = (float) $product->get_sale_price();
    $pct  = $reg > 0 ? round( ( ( $reg - $sale ) / $reg ) * 100 ) : 0;
    $badge_text  = '−' . $pct . '%';
    $badge_class = 'product-badge--sale';
} elseif ( $is_featured ) {
    $badge_text  = 'Хіт';
    $badge_class = 'product-badge--hit';
} elseif ( ( time() - strtotime( $product->get_date_created() ) ) < 30 * DAY_IN_SECONDS ) {
    $badge_text  = 'Новинка';
    $badge_class = 'product-badge--new';
}

// Category
$cats     = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
$cat_name = '';
$cat_slug = '';
if ( $cats ) {
    $first_cat = get_term( $cats[0], 'product_cat' );
    if ( $first_cat && ! is_wp_error( $first_cat ) ) {
        $cat_name = $first_cat->name;
        $cat_slug = $first_cat->slug;
    }
}

// Short description — strip all HTML tags
$raw_desc  = $product->get_short_description() ?: $product->get_description();
$short_desc = wp_strip_all_tags( $raw_desc );
$short_desc = wp_trim_words( $short_desc, 20, '...' );

// Rating
$avg_rating   = $product->get_average_rating();
$review_count = $product->get_review_count();

// --- Variations (for size selector) ------------------------------------
$variation_options = array();
if ( $is_variable ) {
    /** @var WC_Product_Variable $product */
    foreach ( $product->get_available_variations() as $var ) {
        $attrs    = $var['attributes'];
        $attr_key = key( $attrs );
        $slug     = reset( $attrs );

        if ( $slug ) {
            // Try to get proper display name from taxonomy term
            $taxonomy = str_replace( 'attribute_', '', $attr_key );
            $term     = get_term_by( 'slug', urldecode( $slug ), $taxonomy );
            $label    = $term ? $term->name : urldecode( $slug );

            $variation_options[] = array(
                'label'        => $label,
                'price'        => (float) $var['display_price'],
                'weight'       => $var['weight'] ?: '',
                'variation_id' => (int) $var['variation_id'],
            );
        }
    }
}

$has_sizes    = $is_variable && count( $variation_options ) >= 2;
$first_price  = $has_sizes ? $variation_options[0]['price'] : (float) $product->get_price();

// data-category for JS filter
$data_cats = implode( ' ', array_map( function( $id ) {
    $t = get_term( $id, 'product_cat' );
    return ( $t && ! is_wp_error( $t ) ) ? $t->slug : '';
}, $cats ) );
?>

<div class="product-card"
     data-id="<?php echo esc_attr( $product->get_id() ); ?>"
     data-category="<?php echo esc_attr( $data_cats ); ?>"
     data-type="<?php echo $is_variable ? 'variable' : 'simple'; ?>">

    <?php if ( $badge_text ) : ?>
        <span class="product-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
    <?php endif; ?>

    <!-- Image -->
    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"
       class="product-image" tabindex="-1" aria-hidden="true">
        <?php if ( $has_image ) : ?>
            <?php echo get_the_post_thumbnail(
                $product->get_id(),
                'woocommerce_thumbnail',
                array( 'alt' => esc_attr( $product->get_name() ) )
            ); ?>
        <?php else : ?>
            <span class="pie-visual <?php echo esc_attr( $pie_class ); ?>"></span>
        <?php endif; ?>
    </a>

    <!-- Body -->
    <div class="product-body">

        <?php if ( $cat_name ) : ?>
            <p class="product-category"><?php echo esc_html( $cat_name ); ?></p>
        <?php endif; ?>

        <h3 class="product-name">
            <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
                <?php echo esc_html( $product->get_name() ); ?>
            </a>
        </h3>

        <?php if ( $short_desc ) : ?>
            <p class="product-description"><?php echo esc_html( $short_desc ); ?></p>
        <?php endif; ?>

        <?php if ( $avg_rating > 0 ) : ?>
            <div class="product-specs">
                <span class="product-rating">
                    ★ <?php echo number_format( $avg_rating, 1 ); ?>
                    <?php if ( $review_count > 0 ) : ?>
                        <span style="font-weight:400;color:#bbb">(<?php echo esc_html( $review_count ); ?>)</span>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ( $has_sizes ) : ?>
            <!-- Size selector -->
            <div class="size-selector"
                 data-small-price="<?php echo esc_attr( $variation_options[0]['price'] ); ?>"
                 data-large-price="<?php echo esc_attr( $variation_options[1]['price'] ); ?>">
                <?php foreach ( $variation_options as $i => $opt ) : ?>
                    <button
                        class="size-option <?php echo $i === 0 ? 'active' : ''; ?>"
                        data-size="<?php echo $i === 0 ? 'small' : 'large'; ?>"
                        data-variation-id="<?php echo esc_attr( $opt['variation_id'] ); ?>"
                        data-price="<?php echo esc_attr( $opt['price'] ); ?>">
                        <span class="size-option__label"><?php echo esc_html( $opt['label'] ); ?></span>
                        <?php if ( $opt['weight'] ) : ?>
                            <span class="size-option__weight"><?php echo esc_html( $opt['weight'] ); ?> г</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Price + Add to cart -->
        <div class="product-footer">
            <div class="product-price">
                <span class="price-value"><?php echo number_format( $first_price, 0, '', '' ); ?></span>
                <span class="product-price-unit">грн</span>
            </div>

            <?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
                <button
                    class="btn-add-cart"
                    data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                    data-type="<?php echo $is_variable ? 'variable' : 'simple'; ?>"
                    data-cart-url="<?php echo $is_variable ? '' : esc_url( $product->add_to_cart_url() ); ?>"
                    aria-label="<?php echo esc_attr( 'В кошик: ' . $product->get_name() ); ?>">
                    В кошик
                </button>
            <?php else : ?>
                <span class="btn-add-cart btn-add-cart--disabled">Немає</span>
            <?php endif; ?>
        </div>

    </div><!-- .product-body -->
</div><!-- .product-card -->
