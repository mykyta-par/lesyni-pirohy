<?php
/**
 * WooCommerce: Product loop item (card)
 * Overrides: woocommerce/templates/content-product.php
 */

defined( 'ABSPATH' ) || exit;

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
    $badge_text  = '−' . round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 ) . '%';
    $badge_class = 'product-badge--sale';
} elseif ( $is_featured ) {
    $badge_text  = 'Хіт';
    $badge_class = 'product-badge--hit';
} elseif ( ( time() - strtotime( $product->get_date_created() ) ) < 30 * DAY_IN_SECONDS ) {
    $badge_text  = 'Новинка';
    $badge_class = 'product-badge--new';
}

// Category
$cats = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
$cat_name = '';
if ( $cats ) {
    $first_cat = get_term( $cats[0], 'product_cat' );
    if ( $first_cat && ! is_wp_error( $first_cat ) ) {
        $cat_name = $first_cat->name;
    }
}

// Short description
$short_desc = $product->get_short_description() ?: wp_trim_words( $product->get_description(), 18 );

// Variations for size selector
$variations_data = [];
if ( $is_variable ) {
    /** @var WC_Product_Variable $product */
    $variations      = $product->get_available_variations();
    foreach ( $variations as $var ) {
        $attr = $var['attributes'];
        // Look for a Size/Розмір attribute
        $size_key = null;
        foreach ( $attr as $key => $val ) {
            if ( stripos( $key, 'size' ) !== false || stripos( $key, 'розмір' ) !== false || stripos( $key, 'pa_rozmir' ) !== false ) {
                $size_key = $val;
                break;
            }
        }
        if ( $size_key ) {
            $variations_data[ strtolower( $size_key ) ] = [
                'price'       => $var['display_price'],
                'weight'      => $var['weight'] ?: '',
                'variation_id'=> $var['variation_id'],
            ];
        }
    }
}

// Rating
$avg_rating   = $product->get_average_rating();
$review_count = $product->get_review_count();

// Data attributes for product card (for JS category filter)
$data_cats = implode( ' ', array_map( function( $id ) {
    $t = get_term( $id, 'product_cat' );
    return $t ? $t->slug : '';
}, $cats ) );
?>

<div class="product-card" data-id="<?php echo esc_attr( $product->get_id() ); ?>" data-category="<?php echo esc_attr( $data_cats ); ?>">

    <?php if ( $badge_text ) : ?>
        <span class="product-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
    <?php endif; ?>

    <!-- Image -->
    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="product-image" tabindex="-1" aria-hidden="true">
        <?php if ( $has_image ) : ?>
            <?php echo get_the_post_thumbnail( $product->get_id(), 'woocommerce_thumbnail', [ 'alt' => esc_attr( $product->get_name() ) ] ); ?>
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

        <!-- Specs / rating -->
        <?php if ( $avg_rating > 0 ) : ?>
            <div class="product-specs">
                <span class="product-rating">
                    ★ <?php echo number_format( $avg_rating, 1 ); ?>
                    <?php if ( $review_count > 0 ) : ?>
                        <span style="font-weight:400;color:#999">(<?php echo esc_html( $review_count ); ?>)</span>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ( $is_variable && count( $variations_data ) >= 2 ) : ?>
            <!-- Size selector for Small / Large variable products -->
            <?php
            $keys     = array_keys( $variations_data );
            $is_small = in_array( 'малий', $keys, true )  || in_array( 'small', $keys, true );
            $is_large = in_array( 'великий', $keys, true ) || in_array( 'large', $keys, true );
            $first_var = reset( $variations_data );
            $last_var  = end( $variations_data );
            ?>
            <div class="size-selector"
                 data-small-price="<?php echo esc_attr( $first_var['price'] ); ?>"
                 data-large-price="<?php echo esc_attr( $last_var['price'] ); ?>">

                <?php foreach ( $variations_data as $size_label => $vdata ) : ?>
                    <?php $is_first = ( array_key_first( $variations_data ) === $size_label ); ?>
                    <button
                        class="size-option <?php echo $is_first ? 'active' : ''; ?>"
                        data-size="<?php echo esc_attr( $size_label ); ?>"
                        data-variation-id="<?php echo esc_attr( $vdata['variation_id'] ); ?>">
                        <span class="size-option__label"><?php echo esc_html( ucfirst( $size_label ) ); ?></span>
                        <?php if ( $vdata['weight'] ) : ?>
                            <span class="size-option__weight"><?php echo esc_html( $vdata['weight'] ); ?> г</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Price + Add to cart -->
        <div class="product-footer">
            <div class="product-price">
                <?php if ( $is_variable ) : ?>
                    <span class="price-value"><?php echo wc_price( $product->get_variation_price( 'min' ) ); ?></span>
                <?php else : ?>
                    <span class="price-value"><?php echo wc_price( $product->get_price() ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
                <?php if ( $is_variable ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="btn-add-cart">
                        Обрати
                    </a>
                <?php else : ?>
                    <a
                        href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                        data-quantity="1"
                        data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                        class="btn-add-cart add_to_cart_button ajax_add_to_cart">
                        В кошик
                    </a>
                <?php endif; ?>
            <?php else : ?>
                <span class="btn-add-cart" style="opacity:.5;cursor:default">Немає</span>
            <?php endif; ?>
        </div>
    </div>
</div>
