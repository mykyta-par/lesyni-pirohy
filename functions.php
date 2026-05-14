<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* -----------------------------------------------------------------------
   Theme setup
----------------------------------------------------------------------- */
function lesyni_setup() {
	load_theme_textdomain( 'lesyni-pirohy', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// WooCommerce support
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
		'product_grid'          => [
			'default_columns' => 3,
			'default_rows'    => 4,
			'min_columns'     => 1,
			'max_columns'     => 4,
		],
	] );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( [
		'primary' => __( 'Головне меню', 'lesyni-pirohy' ),
	] );
}
add_action( 'after_setup_theme', 'lesyni_setup' );

/* -----------------------------------------------------------------------
   Scripts & Styles
----------------------------------------------------------------------- */
function lesyni_enqueue_assets() {
	// Google Fonts
	wp_enqueue_style(
		'lesyni-fonts',
		'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Poppins:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap',
		[],
		null
	);

	// Main stylesheet
	wp_enqueue_style(
		'lesyni-main',
		get_template_directory_uri() . '/assets/css/main.css',
		[ 'lesyni-fonts' ],
		filemtime( get_template_directory() . '/assets/css/main.css' )
	);

	// Main script
	wp_enqueue_script(
		'lesyni-main',
		get_template_directory_uri() . '/assets/js/main.js',
		[],
		filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);

	// Localise AJAX URL for future use
	wp_localize_script( 'lesyni-main', 'lesyniData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'shopUrl' => get_permalink( wc_get_page_id( 'shop' ) ),
		'homeUrl' => home_url( '/' ),
	] );
}
add_action( 'wp_enqueue_scripts', 'lesyni_enqueue_assets' );

// Disable default WooCommerce stylesheet (we ship our own)
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Register cart count as a WooCommerce fragment so it updates after AJAX add-to-cart
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['span.header-cart__count'] = '<span class="header-cart__count'
        . ( $count > 0 ? '' : ' header-cart__count--hidden' ) . '">'
        . esc_html( $count ) . '</span>';
    return $fragments;
} );

/* -----------------------------------------------------------------------
   Content width
----------------------------------------------------------------------- */
if ( ! isset( $content_width ) ) {
	$content_width = 1400;
}

/* -----------------------------------------------------------------------
   Widgets
----------------------------------------------------------------------- */
function lesyni_register_widgets() {
	register_sidebar( [
		'name'          => __( 'Sidebar каталогу', 'lesyni-pirohy' ),
		'id'            => 'catalog-sidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	] );
}
add_action( 'widgets_init', 'lesyni_register_widgets' );

/* -----------------------------------------------------------------------
   WooCommerce: remove default wrappers so we control the markup
----------------------------------------------------------------------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );

/* -----------------------------------------------------------------------
   Helper: map product category slug → pie visual CSS class
----------------------------------------------------------------------- */
function lesyni_pie_visual_class( $product ) {
	$name = strtolower( $product->get_name() );
	$map  = [
		'яблуч'  => 'pie-apple',
		'вишн'   => 'pie-cherry',
		'жульєн' => 'pie-julien',
		'укра'   => 'pie-ukrainian',
		'риб'    => 'pie-fish',
		'сирн'   => 'pie-cheese',
		'маков'  => 'pie-poppy',
		"м'ясн"  => 'pie-meat',
		'набір'  => 'pie-set',
	];
	foreach ( $map as $fragment => $class ) {
		if ( mb_strpos( $name, $fragment ) !== false ) {
			return $class;
		}
	}
	return 'pie-default';
}
