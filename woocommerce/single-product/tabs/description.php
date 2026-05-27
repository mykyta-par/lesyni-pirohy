<?php
/**
 * Description tab content — two-column layout
 * Overrides: woocommerce/templates/single-product/tabs/description.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $product;
$id = $product->get_id();

$quote_text   = get_post_meta( $id, '_desc_quote_text',   true );
$quote_author = get_post_meta( $id, '_desc_quote_author', true );
$aside_title  = get_post_meta( $id, '_desc_aside_title',  true ) ?: 'Підходить для';
$aside_raw    = get_post_meta( $id, '_desc_aside_items',  true );
$aside_note   = get_post_meta( $id, '_desc_aside_note',   true );

$aside_items = [];
if ( $aside_raw ) {
    foreach ( array_filter( array_map( 'trim', explode( "\n", $aside_raw ) ) ) as $line ) {
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $aside_items[] = [ 'name' => $parts[0], 'detail' => $parts[1] ?? '' ];
    }
}

$has_aside = ! empty( $aside_items );
$has_quote = $quote_text;
?>
<div class="sp-desc-grid<?php echo ! $has_aside ? ' sp-desc-grid--full' : ''; ?>">

    <div class="sp-desc-main">
        <?php the_content(); ?>
        <?php if ( $has_quote ) : ?>
        <blockquote class="sp-desc-quote">
            <?php echo wp_kses_post( $quote_text ); ?>
            <?php if ( $quote_author ) : ?>
            <cite class="sp-desc-quote__author"><?php echo esc_html( $quote_author ); ?></cite>
            <?php endif; ?>
        </blockquote>
        <?php endif; ?>
    </div>

    <?php if ( $has_aside ) : ?>
    <aside class="sp-desc-aside">
        <div class="sp-desc-aside__title"><?php echo esc_html( $aside_title ); ?></div>
        <ul class="sp-desc-aside__list">
            <?php foreach ( $aside_items as $item ) : ?>
            <li>
                <strong><?php echo esc_html( $item['name'] ); ?></strong>
                <?php if ( $item['detail'] ) : ?>
                <span><?php echo esc_html( $item['detail'] ); ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if ( $aside_note ) : ?>
        <p class="sp-desc-aside__note"><?php echo esc_html( $aside_note ); ?></p>
        <?php endif; ?>
    </aside>
    <?php endif; ?>

</div>
