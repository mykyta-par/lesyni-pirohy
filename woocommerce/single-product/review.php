<?php
/**
 * Single product review — overrides woocommerce/templates/single-product/review.php
 * Replaces Gravatar with an initial-letter circle matching the Reviews_Tab.html mockup.
 */

defined( 'ABSPATH' ) || exit;

$rating   = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
$author   = get_comment_author( $comment );
$initial  = strtoupper( mb_substr( $author, 0, 1, 'UTF-8' ) );
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>" class="comment_container">

        <div class="review-avatar-initial"><?php echo esc_html( $initial ); ?></div>

        <div class="comment-text">
            <?php do_action( 'woocommerce_review_before_comment_text', $comment ); ?>

            <div class="star-rating">
                <?php echo wc_get_star_rating_html( $rating ); ?>
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
