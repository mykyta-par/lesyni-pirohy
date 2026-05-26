<?php
/**
 * Reviews template — overrides woocommerce/templates/single-product-reviews.php
 * Wraps review count in <strong> for orange accent styling.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
    return;
}
?>
<div id="reviews" class="woocommerce-Reviews">
    <div id="comments">
        <h2 class="woocommerce-Reviews-title">
            <?php
            $count = $product->get_review_count();
            if ( $count && wc_reviews_enabled() ) {
                /* Simple Ukrainian pluralisation: 1→відгук, 2-4→відгуки, 5+→відгуків */
                if ( $count % 100 >= 11 && $count % 100 <= 19 ) {
                    $label = 'відгуків';
                } elseif ( $count % 10 === 1 ) {
                    $label = 'відгук';
                } elseif ( $count % 10 >= 2 && $count % 10 <= 4 ) {
                    $label = 'відгуки';
                } else {
                    $label = 'відгуків';
                }
                echo '<strong>' . esc_html( $count ) . ' ' . esc_html( $label ) . '</strong> для ' . esc_html( get_the_title() );
            } else {
                esc_html_e( 'Reviews', 'woocommerce' );
            }
            ?>
        </h2>

        <?php if ( have_comments() ) : ?>
            <ol class="commentlist">
                <?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [ 'callback' => 'woocommerce_comments' ] ) ); ?>
            </ol>

            <?php
            if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
                echo '<nav class="woocommerce-pagination">';
                paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', [
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                    'type'      => 'list',
                ] ) );
                echo '</nav>';
            }
            ?>

        <?php else : ?>
            <p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
        <?php endif; ?>
    </div>

    <?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
        <div id="review_form_wrapper">
            <?php
            $commenter = wp_get_current_commenter();
            comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', [
                'title_reply'          => esc_html__( 'Add a review', 'woocommerce' ),
                'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
                'title_reply_before'   => '<span id="reply-title" class="comment-reply-title">',
                'title_reply_after'    => '</span>',
                'comment_notes_before' => '',
                'comment_notes_after'  => '',
                'fields'               => [
                    'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" required /></p>',
                    'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" required /></p>',
                ],
                'label_submit'  => esc_html__( 'Submit', 'woocommerce' ),
                'logged_in_as'  => '',
                'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
            ] ) );
            ?>
        </div>
    <?php endif; ?>
</div>
