<?php
/**
 * Reviews template — overrides woocommerce/templates/single-product-reviews.php
 * Only change vs original: review count wrapped in <strong> for orange accent.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
    return;
}
?><div id="reviews" class="woocommerce-Reviews">
	<div id="comments">
		<h2 class="woocommerce-Reviews-title">
			<?php
			$count = $product->get_review_count();
			if ( $count && wc_reviews_enabled() ) {
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
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>

		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

		<div class="review_form_wrapper">
			<?php
			$commenter    = wp_get_current_commenter();
			$comment_form = array(
				'title_reply'          => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
				'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
				'title_reply_before'   => '<span id="reply-title" class="comment-reply-title">',
				'title_reply_after'    => '</span>',
				'comment_notes_before' => '',
				'comment_notes_after'  => '<div class="comment-form-rating"><label for="rating">Ваша оцінка&nbsp;<span class="required">*</span></label><p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p></div>',
				'fields'               => array(
					'author' => '<p class="comment-form-author"><label for="author">Ім\'я&nbsp;<span class="required">*</span></label> <input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" required /></p>',
					'email'  => '<p class="comment-form-email"><label for="email">Email&nbsp;<span class="required">*</span></label> <input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-required="true" required /></p>',
					'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' ) . ' /> <label for="wp-comment-cookies-consent">Зберегти моє ім\'я та email для наступних відгуків</label></p>',
				),
				'label_submit'  => esc_html__( 'Submit', 'woocommerce' ),
				'logged_in_as'  => '',
				'comment_field' => '<p class="comment-form-comment"><label for="comment">Ваш відгук&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" required></textarea></p>',
			);

			comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
			?>
		</div>

	<?php endif; ?>
</div>
