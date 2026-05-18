<?php
/**
 * Single Promo page template
 */

get_header();

$icon      = get_post_meta( get_the_ID(), '_promo_icon', true ) ?: '🎁';
$has_thumb = has_post_thumbnail();
?>

<div class="promo-single-wrap">

    <nav class="promo-single__breadcrumbs">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
        <span aria-hidden="true"> › </span>
        <a href="<?php echo esc_url( home_url( '/#promos' ) ); ?>">Акції</a>
        <span aria-hidden="true"> › </span>
        <span><?php the_title(); ?></span>
    </nav>

    <article class="promo-single">

        <div class="promo-single__icon">
            <?php if ( $has_thumb ) : ?>
                <?php the_post_thumbnail( 'medium' ); ?>
            <?php else : ?>
                <span class="promo-single__emoji"><?php echo esc_html( $icon ); ?></span>
            <?php endif; ?>
        </div>

        <div class="promo-single__body">
            <h1 class="promo-single__title"><?php the_title(); ?></h1>
            <div class="promo-single__content">
                <?php the_content(); ?>
            </div>
            <a href="<?php echo esc_url( home_url( '/#promos' ) ); ?>" class="btn btn--outline btn--sm">← Всі акції</a>
        </div>

    </article>

</div>

<?php get_footer(); ?>
