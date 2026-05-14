<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
        <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>"
            alt="<?php bloginfo( 'name' ); ?>"
        >
        <span><?php bloginfo( 'name' ); ?></span>
    </a>

    <button class="nav-toggle" aria-label="Відкрити меню" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>

    <nav class="site-nav" id="site-nav">
        <?php if ( is_front_page() ) : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
            <?php if ( $shop_url = get_permalink( wc_get_page_id( 'shop' ) ) ) : ?>
                <a href="<?php echo esc_url( $shop_url ); ?>">Пироги</a>
            <?php endif; ?>
            <a href="#popular">Популярні</a>
            <a href="#promos">Акції</a>
            <a href="#reviews">Відгуки</a>
            <a href="#contact">Контакти</a>
        <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
            <?php if ( $shop_url = get_permalink( wc_get_page_id( 'shop' ) ) ) : ?>
                <a href="<?php echo esc_url( $shop_url ); ?>"
                   class="<?php echo is_shop() || is_product_category() || is_product() ? 'active' : ''; ?>">
                    Пироги
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( home_url( '/#promos' ) ); ?>">Акції</a>
            <a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>">Відгуки</a>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Контакти</a>
        <?php endif; ?>
    </nav>

    <div class="header-cta">
        <a href="tel:+380632532696" class="header-phone">
            <span class="header-phone__label">Замовити</span>
            <span class="header-phone__number">+38 063 253 26 96</span>
        </a>
        <?php if ( function_exists( 'WC' ) ) : ?>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="header-cart" aria-label="Кошик">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <?php $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
                <?php if ( $count > 0 ) : ?>
                    <span class="header-cart__count"><?php echo esc_html( $count ); ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>
</header>
