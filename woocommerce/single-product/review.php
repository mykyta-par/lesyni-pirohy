<?php
/**
 * Single product review — overrides woocommerce/templates/single-product/review.php
 * Replaces Gravatar with an initial-letter circle matching the Reviews_Tab.html mockup.
 *
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$rating   = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
$author   = get_comment_author( $comment );
$initial  = strtoupper( mb_substr( $author, 0, 1, 'UTF-8' ) );

$star_path = 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z';
$stars_html = '';
for ( $i = 1; $i <= 5; $i++ ) {
    $color = $i <= $rating ? '#e07a3f' : 'rgba(224,122,63,0.2)';
    $stars_html .= '<svg viewBox="0 0 24 24" fill="' . $color . '" width="16" height="16" aria-hidden="true"><path d="' . $star_path . '"/></svg>';
}
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>" class="comment_container">

        <div class="review-avatar-initial"><?php echo esc_html( $initial ); ?></div>

        <div class="comment-text">
            <?php do_action( 'woocommerce_review_before_comment_text', $comment ); ?>

            <div class="review-star-rating" role="img" aria-label="<?php printf( esc_attr__( 'Rated %d out of 5', 'woocommerce' ), $rating ); ?>">
                <span class="review-stars"><?php echo $stars_html; // phpcs:ignore ?></span>
                <span class="review-rating-text">Оцінено в <strong><?php echo esc_html( $rating ); ?> з 5</strong></span>
            </div>

            <p class="meta">
                <strong itemprop="author" class="woocommerce-review__author"><?php comment_author(); ?></strong>

                <?php if ( $verified ) : ?>
                    <em class="woocommerce-review__verified verified"><?php esc_html_e( 'verified owner', 'woocommerce' ); ?></em>
                <?php endif; ?>

                <span class="woocommerce-review__dash">&ndash;</span>
                <time itemprop="datePublished" class="woocommerce-review__timestamp" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
            </p>

            <div class="description" itemprop="reviewBody"><?php comment_text(); ?></div>

            <?php do_action( 'woocommerce_review_after_comment_text', $comment ); ?>
        </div>

    </div>
</li>
