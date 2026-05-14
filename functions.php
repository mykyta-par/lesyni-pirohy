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
   WooCommerce: Nutritional Value — admin fields
----------------------------------------------------------------------- */
add_filter( 'woocommerce_product_data_tabs', function ( $tabs ) {
    $tabs['lesyni_nutrition'] = [
        'label'  => 'Поживна цінність',
        'target' => 'lesyni_nutrition_data',
        'class'  => [],
    ];
    return $tabs;
} );

add_action( 'woocommerce_product_data_panels', function () {
    echo '<div id="lesyni_nutrition_data" class="panel woocommerce_options_panel">';
    woocommerce_wp_text_input( [ 'id' => '_nutrition_calories', 'label' => 'Калорійність (ккал / 100 г)', 'type' => 'number', 'placeholder' => 'напр. 248' ] );
    woocommerce_wp_text_input( [ 'id' => '_nutrition_protein',  'label' => 'Білки (г / 100 г)',           'type' => 'number', 'placeholder' => 'напр. 12.4' ] );
    woocommerce_wp_text_input( [ 'id' => '_nutrition_fat',      'label' => 'Жири (г / 100 г)',            'type' => 'number', 'placeholder' => 'напр. 14.2' ] );
    woocommerce_wp_text_input( [ 'id' => '_nutrition_carbs',    'label' => 'Вуглеводи (г / 100 г)',       'type' => 'number', 'placeholder' => 'напр. 18.6' ] );
    echo '</div>';
} );

add_action( 'woocommerce_process_product_meta', function ( $post_id ) {
    foreach ( [ '_nutrition_calories', '_nutrition_protein', '_nutrition_fat', '_nutrition_carbs' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
} );

/* -----------------------------------------------------------------------
   WooCommerce: Nutritional Value — frontend tab
----------------------------------------------------------------------- */
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
    global $product;
    if ( ! $product ) return $tabs;
    if ( get_post_meta( $product->get_id(), '_nutrition_calories', true ) ) {
        $tabs['lesyni_nutrition'] = [
            'title'    => 'Поживна цінність',
            'priority' => 25,
            'callback' => 'lesyni_nutrition_tab_content',
        ];
    }
    return $tabs;
} );

function lesyni_nutrition_tab_content() {
    global $product;
    $id       = $product->get_id();
    $calories = get_post_meta( $id, '_nutrition_calories', true );
    $protein  = get_post_meta( $id, '_nutrition_protein',  true );
    $fat      = get_post_meta( $id, '_nutrition_fat',      true );
    $carbs    = get_post_meta( $id, '_nutrition_carbs',    true );

    $items = [
        [ 'value' => $calories, 'unit' => 'ккал', 'label' => 'Калорійність' ],
        [ 'value' => $protein,  'unit' => 'г',    'label' => 'Білки' ],
        [ 'value' => $fat,      'unit' => 'г',    'label' => 'Жири' ],
        [ 'value' => $carbs,    'unit' => 'г',    'label' => 'Вуглеводи' ],
    ];
    ?>
    <p class="nutrition-per100">на 100 г продукту</p>
    <div class="nutrition-grid">
        <?php foreach ( $items as $item ) : ?>
            <?php if ( $item['value'] !== '' ) : ?>
                <div class="nutrition-card">
                    <span class="nutrition-card__value"><?php echo esc_html( $item['value'] ); ?></span>
                    <span class="nutrition-card__unit"><?php echo esc_html( $item['unit'] ); ?></span>
                    <span class="nutrition-card__label"><?php echo esc_html( $item['label'] ); ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
}

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
