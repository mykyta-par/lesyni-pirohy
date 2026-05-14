<?php
/**
 * Fallback template — WordPress requires this file.
 * Front page uses front-page.php; shop uses woocommerce/archive-product.php.
 */

get_header();
?>

<main class="page-content">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>">
                <h1><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>Сторінку не знайдено.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
