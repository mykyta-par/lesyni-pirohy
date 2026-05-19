<?php get_header(); ?>

<?php
// Hero meta — use saved value if key exists (even if empty), fallback only on first visit
$_front_id   = get_option( 'page_on_front' );
$_hf = function( $key, $default ) use ( $_front_id ) {
    // metadata_exists returns false when key was never saved → use default
    // Once saved (even as empty string) → use the saved value
    return metadata_exists( 'post', $_front_id, '_hero_' . $key )
        ? get_post_meta( $_front_id, '_hero_' . $key, true )
        : $default;
};

$hero_eyebrow    = $_hf( 'eyebrow',   'Домашня пекарня · Дніпро' );
$hero_title      = $_hf( 'title',     "Від щирого серця\nдо вашого столу" );
$hero_subtitle   = $_hf( 'subtitle',  "Ми команда пекарів, закоханих в свою справу.\nСмачні, свіжі пироги на будь-яку нагоду!" );
$hero_badges_raw = $_hf( 'badges',    "Щодня свіже\nДоставка Дніпром\nЗамовлення з 10:00" );
$hero_badges     = array_filter( array_map( 'trim', explode( "\n", $hero_badges_raw ) ) );

$hero_btn1_text  = $_hf( 'btn1_text', 'Замовити' );
$hero_btn1_url   = $_hf( 'btn1_url',  'tel:+380632532696' );
$hero_btn2_text  = $_hf( 'btn2_text', 'Переглянути меню' );
$hero_btn2_url   = $_hf( 'btn2_url',  '' );

$hero_bg_id     = (int) get_post_meta( $_front_id, '_hero_bg_id', true );
$hero_bg_url    = $hero_bg_id ? wp_get_attachment_image_url( $hero_bg_id, 'full' ) : '';

// Build title lines: split on newline for <br> or keep as-is
$hero_title_html = implode( '<br>', array_map( 'esc_html', explode( "\n", $hero_title ) ) );
// Build subtitle lines
$hero_subtitle_html = implode( '<br>', array_map( 'esc_html', explode( "\n", $hero_subtitle ) ) );
?>

<!-- ======================================================================
   HERO
====================================================================== -->
<section class="hero"<?php if ( $hero_bg_url ) : ?> style="background-image:url('<?php echo esc_url( $hero_bg_url ); ?>');background-size:cover;background-position:center;"<?php endif; ?>>
    <div class="hero__decoration"></div>
    <div class="hero__content">
        <p class="hero__eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></p>
        <h1 class="hero__title"><?php echo $hero_title_html; ?></h1>
        <p class="hero__subtitle"><?php echo $hero_subtitle_html; ?></p>
        <div class="hero__cta">
            <?php if ( $hero_btn1_text && $hero_btn1_url ) : ?>
                <a href="<?php echo esc_url( $hero_btn1_url ); ?>" class="btn btn--primary btn--lg"><?php echo esc_html( $hero_btn1_text ); ?></a>
            <?php endif; ?>
            <?php if ( $hero_btn2_text ) :
                $btn2_href = $hero_btn2_url ?: ( function_exists('wc_get_page_id') ? get_permalink( wc_get_page_id('shop') ) : '' );
                if ( $btn2_href ) : ?>
                    <a href="<?php echo esc_url( $btn2_href ); ?>" class="btn btn--outline btn--lg"><?php echo esc_html( $hero_btn2_text ); ?></a>
                <?php endif;
            endif; ?>
        </div>
        <?php if ( ! empty( $hero_badges ) ) : ?>
        <div class="hero__badges">
            <?php foreach ( $hero_badges as $badge ) : ?>
                <span class="hero__badge"><?php echo esc_html( $badge ); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ======================================================================
   POPULAR PIES
====================================================================== -->
<section class="section" id="popular">
    <p class="section__eyebrow">Найбільше подобаються</p>
    <h2 class="section__title">Популярні пироги</h2>

    <?php
    $popular_products = function_exists( 'wc_get_products' )
        ? wc_get_products( [
            'limit'    => 4,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'status'   => 'publish',
            'featured' => true,
        ] )
        : [];
    ?>

    <div class="popular-grid">
        <?php if ( ! empty( $popular_products ) ) : ?>
            <?php foreach ( $popular_products as $product ) : ?>
                <?php
                $pie_class   = lesyni_pie_visual_class( $product );
                $price_html  = $product->get_price_html();
                $product_url = get_permalink( $product->get_id() );
                $has_image   = has_post_thumbnail( $product->get_id() );
                ?>
                <a href="<?php echo esc_url( $product_url ); ?>" class="popular-card">
                    <div class="popular-card__image <?php echo $has_image ? '' : 'popular-card__image--placeholder'; ?>">
                        <?php if ( $has_image ) : ?>
                            <?php echo get_the_post_thumbnail( $product->get_id(), 'medium', [ 'alt' => esc_attr( $product->get_name() ) ] ); ?>
                        <?php else : ?>
                            <span class="pie-visual <?php echo esc_attr( $pie_class ); ?>"></span>
                        <?php endif; ?>
                    </div>
                    <div class="popular-card__body">
                        <p class="popular-card__name"><?php echo esc_html( $product->get_name() ); ?></p>
                        <p class="popular-card__price"><?php echo wp_kses_post( $price_html ); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else : ?>
            <!-- Fallback cards when WooCommerce has no products yet -->
            <?php
            $fallback = [
                [ 'name' => 'Яблучний',   'price' => 'від 180 грн', 'class' => 'pie-apple'   ],
                [ 'name' => 'Вишневий',   'price' => 'від 200 грн', 'class' => 'pie-cherry'  ],
                [ 'name' => 'Жульєн',     'price' => 'від 220 грн', 'class' => 'pie-julien'  ],
                [ 'name' => 'М\'ясний',   'price' => 'від 240 грн', 'class' => 'pie-meat'    ],
            ];
            foreach ( $fallback as $item ) : ?>
                <div class="popular-card">
                    <div class="popular-card__image popular-card__image--placeholder">
                        <span class="pie-visual <?php echo esc_attr( $item['class'] ); ?>"></span>
                    </div>
                    <div class="popular-card__body">
                        <p class="popular-card__name"><?php echo esc_html( $item['name'] ); ?></p>
                        <p class="popular-card__price"><?php echo esc_html( $item['price'] ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ( $shop_url = get_permalink( wc_get_page_id( 'shop' ) ) ) : ?>
        <div class="section__more">
            <a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn--outline">Всі пироги →</a>
        </div>
    <?php endif; ?>
</section>

<!-- ======================================================================
   PROMOS
====================================================================== -->
<section class="section section--alt" id="promos">
    <p class="section__eyebrow">Спеціальні пропозиції</p>
    <h2 class="section__title">Акції цього місяця</h2>

    <?php
    $promos = new WP_Query( [
        'post_type'      => 'lesyni_promo',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
    ?>

    <?php if ( $promos->have_posts() ) : ?>
    <div class="promos-grid">
        <?php while ( $promos->have_posts() ) : $promos->the_post(); ?>
        <?php
        $icon      = get_post_meta( get_the_ID(), '_promo_icon', true ) ?: '🎁';
        $has_thumb = has_post_thumbnail();
        $short     = get_the_excerpt() ?: wp_trim_words( get_the_content(), 20, '…' );
        ?>
        <div class="promo-card">
            <div class="promo-card__icon<?php echo $has_thumb ? ' promo-card__icon--image' : ' promo-card__icon--emoji'; ?>">
                <?php if ( $has_thumb ) : ?>
                    <?php the_post_thumbnail( 'thumbnail' ); ?>
                <?php else : ?>
                    <?php echo esc_html( $icon ); ?>
                <?php endif; ?>
            </div>
            <h3 class="promo-card__title"><?php the_title(); ?></h3>
            <p class="promo-card__desc"><?php echo esc_html( $short ); ?></p>
            <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn--light btn--sm">Дізнатися більше</a>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php else : ?>
    <p style="text-align:center;color:#999;padding:40px 0;">Акцій поки немає. Додай їх в адмінці у розділі «Акції».</p>
    <?php endif; ?>

</section>

<!-- ======================================================================
   REVIEWS
====================================================================== -->
<section class="reviews-section" id="reviews">
    <p class="section__eyebrow">Що кажуть клієнти</p>
    <h2 class="section__title">Відгуки</h2>

    <?php
    $reviews = new WP_Query( [
        'post_type'      => 'lesyni_review',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ] );
    ?>

    <?php if ( $reviews->have_posts() ) : ?>
    <div class="reviews-grid">
        <?php while ( $reviews->have_posts() ) : $reviews->the_post();
            $stars     = (int) ( get_post_meta( get_the_ID(), '_review_stars', true ) ?: 5 );
            $date_text = get_post_meta( get_the_ID(), '_review_date', true );
            $name      = get_the_title();
            $avatar    = mb_strtoupper( mb_substr( $name, 0, 1 ) );
        ?>
        <div class="review-card">
            <div class="review-card__header">
                <div class="review-card__avatar"><?php echo esc_html( $avatar ); ?></div>
                <div class="review-card__meta">
                    <strong><?php echo esc_html( $name ); ?></strong>
                    <?php if ( $date_text ) : ?>
                        <span><?php echo esc_html( $date_text ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="review-card__stars"><?php echo str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ); ?></div>
            <p class="review-card__text"><?php echo esc_html( get_the_content() ); ?></p>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php endif; ?>

</section>

<!-- ======================================================================
   CONTACT
====================================================================== -->
<section class="contact-section" id="contact">
    <div class="contact-section__info">
        <h2 class="contact-section__title">Як замовити?</h2>

        <div class="contact-item">
            <p class="contact-item__label">Телефон</p>
            <a href="tel:+380632532696" class="contact-item__value">+38 063 253 26 96</a>
        </div>
        <div class="contact-item">
            <p class="contact-item__label">Час роботи</p>
            <p class="contact-item__value">Щодня з 10:00 до 18:30</p>
        </div>
        <div class="contact-item">
            <p class="contact-item__label">Email</p>
            <a href="mailto:info@lesynpie.com.ua" class="contact-item__value">info@lesynpie.com.ua</a>
        </div>
        <div class="contact-item">
            <p class="contact-item__label">Слідкуйте за нами</p>
            <div class="contact-socials">
                <a href="#" class="social-btn" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.24 8.24 0 0 0 4.84 1.55V6.79a4.85 4.85 0 0 1-1.07-.1z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="contact-section__map">
        <!-- Replace the div below with a Google Maps <iframe> embed -->
        <div class="map-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <p>Дніпро<br><small>Вставте посилання Google Maps</small></p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
