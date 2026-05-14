<?php
/**
 * WooCommerce: Single product page
 * Overrides: woocommerce/templates/single-product.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
do_action( 'woocommerce_before_single_product' );

while ( have_posts() ) :
    the_post();
    global $product;

    /* ── data ──────────────────────────────────────────────────── */
    $cats = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
    $cat_name = '';
    $cat_term = null;
    if ( ! empty( $cats ) ) {
        $t = get_term( $cats[0], 'product_cat' );
        if ( $t && ! is_wp_error( $t ) ) { $cat_name = $t->name; $cat_term = $t; }
    }

    $main_img_id = $product->get_image_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $all_imgs    = $main_img_id ? array_merge( [ $main_img_id ], $gallery_ids ) : $gallery_ids;
    $short_desc  = wp_strip_all_tags( $product->get_short_description() );
    $avg_rating  = $product->get_average_rating();
    $is_variable = $product->is_type( 'variable' );

    /* initial price for purchase button */
    $initial_price = 0;
    if ( $is_variable ) {
        $vars = $product->get_available_variations();
        if ( ! empty( $vars ) ) {
            $vobj          = wc_get_product( $vars[0]['variation_id'] );
            $initial_price = $vobj ? (float) $vobj->get_price() : 0;
        }
    } else {
        $initial_price = (float) $product->get_price();
    }

    /* badge */
    $badge = '';
    if ( $product->is_on_sale() ) {
        $badge = 'Акція';
    } elseif ( $product->get_total_sales() > 20 ) {
        $badge = 'Хіт продажів';
    } elseif ( $product->get_date_created() &&
               ( time() - $product->get_date_created()->getTimestamp() ) < 30 * DAY_IN_SECONDS ) {
        $badge = 'Новинка';
    }
    ?>

    <?php woocommerce_output_all_notices(); ?>

    <div class="sp-wrap">

        <!-- ── Breadcrumbs ─────────────────────────────────────── -->
        <nav class="sp-breadcrumbs" aria-label="Breadcrumbs">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
            <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Пироги</a>
            <?php if ( $cat_name && $cat_term ) : ?>
                <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
                <a href="<?php echo esc_url( get_term_link( $cat_term ) ); ?>"><?php echo esc_html( $cat_name ); ?></a>
            <?php endif; ?>
            <span class="sp-breadcrumbs__sep" aria-hidden="true">›</span>
            <span class="sp-breadcrumbs__current"><?php the_title(); ?></span>
        </nav>

        <!-- ── Product top: gallery + info ─────────────────────── -->
        <div class="sp-product-top" id="product-<?php the_ID(); ?>">

            <!-- Gallery -->
            <div class="sp-gallery <?php echo count( $all_imgs ) > 1 ? 'sp-gallery--with-thumbs' : ''; ?>">

                <?php if ( ! empty( $all_imgs ) ) : ?>

                    <?php if ( count( $all_imgs ) > 1 ) : ?>
                    <div class="sp-gallery__thumbs">
                        <?php foreach ( $all_imgs as $i => $img_id ) :
                            $full_src  = wp_get_attachment_image_url( $img_id, 'large' );
                            $thumb_src = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                        ?>
                        <button
                            class="sp-gallery__thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                            type="button"
                            data-full="<?php echo esc_url( $full_src ); ?>"
                            aria-label="Фото <?php echo $i + 1; ?>">
                            <img src="<?php echo esc_url( $thumb_src ); ?>" alt="">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="sp-gallery__main">
                        <?php if ( $badge ) : ?>
                            <span class="sp-img-badge"><?php echo esc_html( $badge ); ?></span>
                        <?php endif; ?>
                        <?php echo wp_get_attachment_image(
                            $all_imgs[0], 'large', false,
                            [ 'id' => 'sp-main-image', 'class' => 'sp-main-image', 'alt' => esc_attr( $product->get_name() ) ]
                        ); ?>
                    </div>

                <?php else : ?>
                    <div class="sp-gallery__main sp-gallery__main--placeholder">
                        <span class="pie-visual pie-visual--lg <?php echo esc_attr( lesyni_pie_visual_class( $product ) ); ?>"></span>
                    </div>
                <?php endif; ?>

            </div><!-- .sp-gallery -->

            <!-- Info column -->
            <div class="sp-info">

                <?php if ( $cat_name ) : ?>
                    <p class="sp-info__category"><?php echo esc_html( $cat_name ); ?></p>
                <?php endif; ?>

                <h1 class="sp-info__title"><?php the_title(); ?></h1>

                <div class="sp-info__meta">
                    <?php if ( $avg_rating > 0 ) : ?>
                        <span class="sp-meta-rating">★ <?php echo number_format( $avg_rating, 1 ); ?></span>
                        <?php if ( $product->get_review_count() > 0 ) : ?>
                            <span class="sp-meta-dot">·</span>
                            <a href="#tab-reviews" class="sp-meta-link"><?php echo esc_html( $product->get_review_count() ); ?> відгуків</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php $sku = $product->get_sku(); if ( $sku ) : ?>
                        <span class="sp-meta-dot">·</span>
                        <span class="sp-meta-sku">Арт: <?php echo esc_html( $sku ); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ( $short_desc ) : ?>
                    <p class="sp-info__desc"><?php echo esc_html( $short_desc ); ?></p>
                <?php endif; ?>

                <!-- Highlights -->
                <div class="sp-highlights">
                    <div class="sp-highlight">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <div>
                            <div class="sp-highlight__label">Випікаємо</div>
                            <div class="sp-highlight__value">Під замовлення</div>
                        </div>
                    </div>
                    <div class="sp-highlight">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <div>
                            <div class="sp-highlight__label">Доставка</div>
                            <div class="sp-highlight__value">Протягом 2 год</div>
                        </div>
                    </div>
                </div>

                <!-- Size cards (variable product) -->
                <?php if ( $is_variable ) :
                    $variations = $product->get_available_variations();
                    if ( ! empty( $variations ) ) :
                ?>
                    <div class="sp-field-label">Оберіть розмір</div>
                    <div class="sp-size-cards">
                        <?php foreach ( $variations as $i => $var ) :
                            $var_obj = wc_get_product( $var['variation_id'] );
                            if ( ! $var_obj ) continue;

                            $var_price  = (float) $var_obj->get_price();
                            $var_weight = $var_obj->get_weight();
                            $var_desc   = wp_strip_all_tags( $var_obj->get_description() );

                            $size_label = '';
                            foreach ( $var['attributes'] as $attr_key => $attr_val ) {
                                $taxonomy = str_replace( 'attribute_', '', $attr_key );
                                if ( taxonomy_exists( $taxonomy ) ) {
                                    $term = get_term_by( 'slug', urldecode( $attr_val ), $taxonomy );
                                    $size_label = $term ? $term->name : urldecode( $attr_val );
                                } else {
                                    $size_label = urldecode( $attr_val );
                                }
                                break;
                            }

                            $meta_parts = [];
                            if ( $var_weight ) {
                                $w    = (float) $var_weight;
                                $unit = get_option( 'woocommerce_weight_unit', 'kg' );
                                $meta_parts[] = $unit === 'kg'
                                    ? round( $w * 1000 ) . ' г'
                                    : round( $w ) . ' г';
                            }
                            if ( $var_desc ) $meta_parts[] = $var_desc;
                            $meta_text = implode( ' · ', $meta_parts );
                        ?>
                        <button
                            class="sp-size-card <?php echo $i === 0 ? 'active' : ''; ?>"
                            type="button"
                            data-variation-id="<?php echo esc_attr( $var['variation_id'] ); ?>"
                            data-price="<?php echo esc_attr( $var_price ); ?>"
                            data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                            <div class="sp-size-card__name"><?php echo esc_html( $size_label ); ?></div>
                            <?php if ( $meta_text ) : ?>
                                <div class="sp-size-card__meta"><?php echo esc_html( $meta_text ); ?></div>
                            <?php endif; ?>
                            <div class="sp-size-card__price">
                                <?php echo (int) round( $var_price ); ?><span class="sp-size-card__unit">грн</span>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; endif; ?>

                <!-- Purchase row -->
                <div class="sp-field-label">Кількість</div>
                <div class="sp-purchase">

                    <div class="sp-qty">
                        <button type="button" class="sp-qty-btn sp-qty-btn--minus" aria-label="Зменшити">−</button>
                        <input class="sp-qty-input" type="text" inputmode="numeric" value="1" min="1" max="20" readonly>
                        <button type="button" class="sp-qty-btn sp-qty-btn--plus" aria-label="Збільшити">+</button>
                    </div>

                    <button
                        type="button"
                        class="sp-btn-cart"
                        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                        data-type="<?php echo esc_attr( $product->get_type() ); ?>"
                        data-price="<?php echo esc_attr( $initial_price ); ?>">
                        <span class="sp-btn-cart__label">Додати в кошик</span>
                        <?php if ( $initial_price > 0 ) : ?>
                        <span class="sp-btn-cart__price"><span id="sp-total"><?php echo (int) round( $initial_price ); ?></span> грн</span>
                        <?php endif; ?>
                    </button>

                    <button type="button" class="sp-share-btn" aria-label="Поділитися">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    </button>

                </div><!-- .sp-purchase -->

                <!-- Trust -->
                <div class="sp-trust">
                    <div class="sp-trust__item">Безкоштовна доставка від 600 грн</div>
                    <div class="sp-trust__item">Оплата онлайн або при отриманні</div>
                </div>

                <!-- WooCommerce meta -->
                <div class="sp-wc-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>

            </div><!-- .sp-info -->
        </div><!-- .sp-product-top -->

        <!-- ── Tabs ────────────────────────────────────────────── -->
        <div class="sp-tabs-wrap">
            <?php woocommerce_output_product_data_tabs(); ?>
        </div>

        <!-- ── Related products ────────────────────────────────── -->
        <?php
        $related_ids = wc_get_related_products( $product->get_id(), 4 );
        if ( ! empty( $related_ids ) ) :
            $rel_q = new WP_Query( [
                'post_type'      => 'product',
                'post__in'       => $related_ids,
                'posts_per_page' => 4,
                'orderby'        => 'post__in',
            ] );
            if ( $rel_q->have_posts() ) :
        ?>
        <div class="sp-related">
            <div class="sp-section-head">
                <h3 class="sp-section-title">З цим також беруть</h3>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="sp-section-link">Усі пироги →</a>
            </div>
            <div class="sp-related-grid">
                <?php while ( $rel_q->have_posts() ) : $rel_q->the_post();
                    $rel = wc_get_product( get_the_ID() );
                    if ( ! $rel ) continue;
                    $rel_img_id   = $rel->get_image_id();
                    $rel_price    = (float) $rel->get_price();
                    $rel_rating   = $rel->get_average_rating();
                    $rel_cats     = wc_get_product_term_ids( $rel->get_id(), 'product_cat' );
                    $rel_cat_name = '';
                    if ( ! empty( $rel_cats ) ) {
                        $rc = get_term( $rel_cats[0], 'product_cat' );
                        if ( $rc && ! is_wp_error( $rc ) ) $rel_cat_name = $rc->name;
                    }
                ?>
                <a href="<?php the_permalink(); ?>" class="sp-related-card">
                    <div class="sp-related-img">
                        <?php if ( $rel_img_id ) : ?>
                            <?php echo wp_get_attachment_image( $rel_img_id, 'medium', false, [ 'class' => 'sp-related-photo' ] ); ?>
                        <?php else : ?>
                            <span class="pie-visual pie-visual--md <?php echo esc_attr( lesyni_pie_visual_class( $rel ) ); ?>"></span>
                        <?php endif; ?>
                    </div>
                    <div class="sp-related-body">
                        <?php if ( $rel_cat_name ) : ?>
                            <div class="sp-related-cat"><?php echo esc_html( $rel_cat_name ); ?></div>
                        <?php endif; ?>
                        <div class="sp-related-name"><?php the_title(); ?></div>
                        <div class="sp-related-foot">
                            <div class="sp-related-price">
                                <?php echo (int) round( $rel_price ); ?><span class="sp-related-price-unit">грн</span>
                            </div>
                            <?php if ( $rel_rating > 0 ) : ?>
                                <div class="sp-related-rating">★ <?php echo number_format( $rel_rating, 1 ); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; endif; ?>

        <!-- ── Order CTA ───────────────────────────────────────── -->
        <div class="sp-order-cta">
            <div class="sp-order-cta__content">
                <h3 class="sp-order-cta__title">Хочете дізнатися більше?</h3>
                <p class="sp-order-cta__desc">Наш оператор підкаже, який розмір обрати, з чим краще подати та коли зручніше доставити.</p>
                <a href="tel:+380632532696" class="sp-order-cta__phone">+38 063 253 26 96</a>
            </div>
            <div class="sp-order-cta__actions">
                <a href="tel:+380632532696" class="sp-order-cta__btn">Зателефонувати</a>
            </div>
        </div>

    </div><!-- .sp-wrap -->

    <?php do_action( 'woocommerce_after_single_product' ); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
