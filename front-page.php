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

$hero_type = get_post_meta( $_front_id, '_hero_type', true ) ?: 'static';

// Build title lines: split on newline for <br> or keep as-is
$hero_title_html = implode( '<br>', array_map( 'esc_html', explode( "\n", $hero_title ) ) );
// Build subtitle lines
$hero_subtitle_html = implode( '<br>', array_map( 'esc_html', explode( "\n", $hero_subtitle ) ) );
?>

<?php if ( $hero_type === 'static' ) : ?>
<!-- ======================================================================
   HERO (static banner)
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

<?php else : ?>
<!-- ======================================================================
   HERO (slider)
====================================================================== -->
<?php
$_slide_defs = [
    1 => [
        'eyebrow'   => 'Завжди свіже',
        'title'     => "Пироги — з {любов'ю} до вашого столу",
        'subtitle'  => 'Кожен пиріг печемо вранці того ж дня. Без консервантів, на справжньому маслі, з начинками від перевірених фермерів.',
        'bg'        => 'cream',
        'emoji'     => '🥧',
        'chip1'     => '⏱/Доставка/за 90 хв',
        'chip2'     => '⭐/Рейтинг/4.9 / 5',
        'btn1_text' => 'Замовити зараз',
        'btn1_url'  => '',
        'btn2_text' => 'Переглянути меню',
        'btn2_url'  => '',
    ],
    2 => [
        'eyebrow'   => 'Сезонна пропозиція',
        'title'     => 'Вишневий — повертається на {2 тижні}',
        'subtitle'  => 'Соковита вишня, ніжне пісочне тісто, легкий аромат ванілі.',
        'bg'        => 'rose',
        'emoji'     => '🍒',
        'chip1'     => '🔥/Залишилось/14 днів',
        'chip2'     => '💝/Від/120 грн',
        'btn1_text' => 'Замовити вишневий',
        'btn1_url'  => '',
        'btn2_text' => 'Усі сезонні',
        'btn2_url'  => '',
    ],
    3 => [
        'eyebrow'   => 'Для великої компанії',
        'title'     => 'Замов на 10+ людей — {знижка 15%}',
        'subtitle'  => 'Корпоратив, день народження, родинне свято? Підкажемо скільки і яких пирогів вибрати.',
        'bg'        => 'sage',
        'emoji'     => '🎂',
        'chip1'     => '🎁/Знижка/−15%',
        'chip2'     => '🚚/Доставка/0 грн',
        'btn1_text' => 'Розрахувати',
        'btn1_url'  => '',
        'btn2_text' => "Зв'язатись",
        'btn2_url'  => '',
    ],
];

$_hs_slides = [];
foreach ( $_slide_defs as $n => $def ) {
    $slide = [];
    foreach ( $def as $key => $default ) {
        $saved = get_post_meta( $_front_id, '_hero_slide_' . $n . '_' . $key, true );
        $slide[ $key ] = ( $saved !== '' ) ? $saved : $default;
    }
    $bg_att_id           = (int) get_post_meta( $_front_id, '_hero_slide_' . $n . '_bg_id', true );
    $slide['bg_url']     = $bg_att_id ? wp_get_attachment_image_url( $bg_att_id, 'full' ) : '';
    $bg_mob_id           = (int) get_post_meta( $_front_id, '_hero_slide_' . $n . '_bg_mobile_id', true );
    $slide['bg_mob_url'] = $bg_mob_id ? wp_get_attachment_image_url( $bg_mob_id, 'full' ) : '';
    $vis_att_id          = (int) get_post_meta( $_front_id, '_hero_slide_' . $n . '_visual_id', true );
    $slide['visual_url'] = $vis_att_id ? wp_get_attachment_image_url( $vis_att_id, 'full' ) : '';
    $_hs_slides[] = $slide;
}

$_hs_phone    = 'tel:+380632532696';
$_hs_shop_url = function_exists('wc_get_page_id') ? get_permalink( wc_get_page_id('shop') ) : '';

$_hs_title = function( $raw ) {
    return preg_replace( '/\{([^}]+)\}/', '<em>$1</em>', esc_html( $raw ) );
};

$_hs_chip = function( $raw ) {
    $p = array_map( 'trim', explode( '/', $raw, 3 ) );
    return [ 'ic' => $p[0] ?? '', 'lbl' => $p[1] ?? '', 'val' => $p[2] ?? '' ];
};

$_hs_total = count( $_hs_slides );
?>
<?php
$_hs_bg_css = '';
foreach ( $_hs_slides as $i => $slide ) {
    $n   = $i + 1;
    $dsk = $slide['bg_url'];
    $mob = $slide['bg_mob_url'];
    if ( ! $dsk && ! $mob ) continue;
    $base = $mob ?: $dsk;
    $_hs_bg_css .= "#hs-slide-{$n}{background-image:url('" . esc_url( $base ) . "');background-size:cover;background-position:center}";
    if ( $dsk && $mob && $dsk !== $mob ) {
        $_hs_bg_css .= "@media(min-width:769px){#hs-slide-{$n}{background-image:url('" . esc_url( $dsk ) . "')}}";
    }
}
?>
<div class="hs" id="hs">
    <?php if ( $_hs_bg_css ) : ?><style><?php echo $_hs_bg_css; ?></style><?php endif; ?>
    <div class="hs-progress"><div class="hs-progress-bar"></div></div>
    <div class="hs-counter"><strong id="hs-cur">01</strong><span> / <?php echo str_pad( $_hs_total, 2, '0', STR_PAD_LEFT ); ?></span></div>

    <?php foreach ( $_hs_slides as $i => $slide ) :
        $btn1_url = $slide['btn1_url'] ?: $_hs_phone;
        $btn2_url = $slide['btn2_url'] ?: $_hs_shop_url;
        $chip1    = $_hs_chip( $slide['chip1'] );
        $chip2    = $_hs_chip( $slide['chip2'] );
    ?>
    <div class="hs-slide<?php echo $i === 0 ? ' active' : ''; ?>" id="hs-slide-<?php echo $i + 1; ?>" data-bg="<?php echo esc_attr( $slide['bg'] ); ?>">
        <div class="hs-content">
            <div class="hs-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></div>
            <h1><?php echo $_hs_title( $slide['title'] ); ?></h1>
            <p><?php echo esc_html( $slide['subtitle'] ); ?></p>
            <div class="hs-actions">
                <?php if ( $btn1_url ) : ?>
                <a href="<?php echo esc_url( $btn1_url ); ?>" class="hs-btn-primary"><?php echo esc_html( $slide['btn1_text'] ); ?></a>
                <?php endif; ?>
                <?php if ( $slide['btn2_text'] && $btn2_url ) : ?>
                <a href="<?php echo esc_url( $btn2_url ); ?>" class="hs-btn-ghost"><?php echo esc_html( $slide['btn2_text'] ); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hs-visual">
            <div class="hs-pie">
                <?php if ( $slide['visual_url'] ) : ?>
                <img src="<?php echo esc_url( $slide['visual_url'] ); ?>" alt="">
                <?php else : ?>
                <?php echo esc_html( html_entity_decode( $slide['emoji'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?>
                <?php endif; ?>
            </div>
            <?php if ( $slide['chip1'] ) : $c = $chip1; ?>
            <div class="hs-chip top">
                <span class="ic"><?php echo esc_html( $c['ic'] ); ?></span>
                <span><span class="lbl"><?php echo esc_html( $c['lbl'] ); ?></span><span class="val"><?php echo esc_html( $c['val'] ); ?></span></span>
            </div>
            <?php endif; ?>
            <?php if ( $slide['chip2'] ) : $c = $chip2; ?>
            <div class="hs-chip bot">
                <span class="ic"><?php echo esc_html( $c['ic'] ); ?></span>
                <span><span class="lbl"><?php echo esc_html( $c['lbl'] ); ?></span><span class="val"><?php echo esc_html( $c['val'] ); ?></span></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="hs-nav">
        <div class="hs-dots">
            <?php for ( $i = 0; $i < $_hs_total; $i++ ) : ?>
            <button class="hs-dot<?php echo $i === 0 ? ' active' : ''; ?>" aria-label="Слайд <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>
    </div>
    <div class="hs-arrows">
        <button class="hs-arrow" data-step="-1" aria-label="Попередній">‹</button>
        <button class="hs-arrow" data-step="1" aria-label="Наступний">›</button>
    </div>
</div>
<?php endif; ?>

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
                $pid         = $product->get_id();
                $pie_class   = lesyni_pie_visual_class( $product );
                $product_url = get_permalink( $pid );
                $has_image   = has_post_thumbnail( $pid );
                $is_variable = $product->is_type( 'variable' );

                // Build variation options (same logic as content-product.php)
                $variation_options = [];
                if ( $is_variable ) {
                    foreach ( $product->get_available_variations() as $var ) {
                        $attrs    = $var['attributes'];
                        $attr_key = key( $attrs );
                        $slug     = reset( $attrs );
                        if ( ! $slug ) continue;
                        $taxonomy = str_replace( 'attribute_', '', $attr_key );
                        $term     = get_term_by( 'slug', urldecode( $slug ), $taxonomy );
                        $label    = $term ? $term->name : urldecode( $slug );
                        $w        = (float) $var['weight'];
                        $wu       = get_option( 'woocommerce_weight_unit', 'kg' );
                        $weight_g = $w > 0 ? ( $wu === 'kg' ? round( $w * 1000 ) : round( $w ) ) : '';
                        $variation_options[] = [
                            'label'        => $label,
                            'price'        => (float) $var['display_price'],
                            'weight'       => $weight_g,
                            'variation_id' => (int) $var['variation_id'],
                        ];
                    }
                }
                $has_sizes   = $is_variable && count( $variation_options ) >= 2;
                $first_price = $has_sizes ? $variation_options[0]['price'] : (float) $product->get_price();
                $purchasable = $product->is_purchasable() && $product->is_in_stock();
                $short_desc  = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
                $short_desc  = $short_desc ? wp_trim_words( $short_desc, 8, '' ) : '';
                ?>
                <div class="popular-card product-card" data-type="<?php echo $is_variable ? 'variable' : 'simple'; ?>">
                    <a href="<?php echo esc_url( $product_url ); ?>" class="popular-card__image <?php echo $has_image ? '' : 'popular-card__image--placeholder'; ?>" tabindex="-1" aria-hidden="true">
                        <?php if ( $has_image ) : ?>
                            <?php echo get_the_post_thumbnail( $pid, 'medium', [ 'alt' => esc_attr( $product->get_name() ) ] ); ?>
                        <?php else : ?>
                            <span class="pie-visual <?php echo esc_attr( $pie_class ); ?>"></span>
                        <?php endif; ?>
                    </a>
                    <div class="popular-card__body">
                        <a href="<?php echo esc_url( $product_url ); ?>" class="popular-card__name"><?php echo esc_html( $product->get_name() ); ?></a>
                        <?php if ( $has_sizes ) : ?>
                        <div class="size-selector size-selector--pop">
                            <?php foreach ( $variation_options as $i => $opt ) : ?>
                                <button class="size-option <?php echo $i === 0 ? 'active' : ''; ?>"
                                        data-variation-id="<?php echo esc_attr( $opt['variation_id'] ); ?>"
                                        data-price="<?php echo esc_attr( $opt['price'] ); ?>">
                                    <?php echo esc_html( $opt['label'] ); ?>
                                    <?php if ( $opt['weight'] ) : ?><small><?php echo esc_html( $opt['weight'] ); ?> г</small><?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="popular-card__footer">
                            <div class="popular-card__price">
                                <span class="price-value"><?php echo number_format( $first_price, 0, '', '' ); ?></span><small>грн</small>
                            </div>
                            <?php if ( $purchasable ) : ?>
                                <button class="btn-add-cart btn-add-cart--round"
                                        data-product-id="<?php echo esc_attr( $pid ); ?>"
                                        data-type="<?php echo $is_variable ? 'variable' : 'simple'; ?>"
                                        aria-label="В кошик: <?php echo esc_attr( $product->get_name() ); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                            <?php else : ?>
                                <button class="btn-add-cart btn-add-cart--round btn-add-cart--disabled" disabled>×</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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
<?php if ( get_theme_mod( 'homepage_show_reviews', true ) ) : ?>
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
<?php endif; ?>

<!-- ======================================================================
   CALCULATOR
====================================================================== -->
<?php if ( get_theme_mod( 'homepage_show_calculator', true ) ) : ?>
<?php
$calc_shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
?>
<section class="lp-calc" id="calculator">
    <div class="lp-calc-wrap">
        <div class="lp-calc-eyebrow">Розрахунок замовлення</div>
        <h2 class="lp-calc-title">Скільки пирогів замовити?</h2>
        <p class="lp-calc-sub">Підкажемо рекомендовану кількість для вашого столу</p>

        <div class="lp-calc-card">
            <div class="lp-calc-form">
                <div class="lp-calc-sliders">
                    <div class="lp-calc-slider-field">
                        <div class="lp-calc-slider-head">
                            <span class="lp-calc-label">Дорослих</span>
                            <span class="lp-calc-value" data-counter="adults">0</span>
                        </div>
                        <input type="range" class="lp-calc-range" data-range="adults" min="0" max="50" value="0" aria-label="Кількість дорослих">
                    </div>
                    <div class="lp-calc-slider-field">
                        <div class="lp-calc-slider-head">
                            <span class="lp-calc-label">Дітей</span>
                            <span class="lp-calc-value" data-counter="children">0</span>
                        </div>
                        <input type="range" class="lp-calc-range" data-range="children" min="0" max="30" value="0" aria-label="Кількість дітей">
                    </div>
                </div>

                <div>
                    <div class="lp-calc-label">Тип події</div>
                    <div class="lp-calc-opts" data-group="event">
                        <button class="lp-calc-opt is-active" data-value="table">Стіл лише з пирогами</button>
                        <button class="lp-calc-opt" data-value="buffet">Фуршет з іншими стравами</button>
                    </div>
                </div>

                <div>
                    <div class="lp-calc-label">Розмір пирогів</div>
                    <div class="lp-calc-opts" data-group="size">
                        <button class="lp-calc-opt is-active" data-value="large">Великі пироги</button>
                        <button class="lp-calc-opt" data-value="small">Маленькі пироги</button>
                    </div>
                </div>
            </div>

            <div class="lp-calc-summary">
                <div class="lp-calc-rlabel">Рекомендоване замовлення</div>
                <div class="lp-calc-results">
                    <div class="lp-calc-rcard" data-rcard="large">
                        <div class="lp-calc-rcard-label">Великі пироги</div>
                        <div><span class="lp-calc-rval" data-result="large">0</span><span class="lp-calc-runit">шт</span></div>
                    </div>
                    <div class="lp-calc-rcard is-small" data-rcard="small">
                        <div class="lp-calc-rcard-label">Маленькі пироги</div>
                        <div><span class="lp-calc-rval" data-result="small">0</span><span class="lp-calc-runit">шт</span></div>
                    </div>
                </div>
                <div class="lp-calc-info">
                    <span class="lp-calc-info-icon">i</span>
                    <span data-info>Вкажіть кількість гостей, щоб побачити рекомендацію</span>
                </div>
                <button class="lp-calc-cta" data-cta data-url="<?php echo esc_url( $calc_shop_url ); ?>">Перейти до замовлення</button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================================================================
   CONTACT
====================================================================== -->
<?php if ( get_theme_mod( 'homepage_show_contact', true ) ) : ?>
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
<?php endif; ?>

<?php get_footer(); ?>
