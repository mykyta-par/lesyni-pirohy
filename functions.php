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

	// Leaflet map — тільки на сторінці кошика
	if ( is_cart() ) {
		wp_enqueue_style(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			[],
			'1.9.4'
		);
		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			[],
			'1.9.4',
			true
		);
	}

	// Main stylesheet
	wp_enqueue_style(
		'lesyni-main',
		get_template_directory_uri() . '/assets/css/main.css',
		[ 'lesyni-fonts' ],
		filemtime( get_template_directory() . '/assets/css/main.css' )
	);

	// Main script (depends on Leaflet when on cart page)
	$script_deps = is_cart() ? [ 'leaflet' ] : [];
	wp_enqueue_script(
		'lesyni-main',
		get_template_directory_uri() . '/assets/js/main.js',
		$script_deps,
		filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);

	// Pass AJAX URL + zone config to JS
	wp_localize_script( 'lesyni-main', 'lesyniData', [
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'shopUrl'      => get_permalink( wc_get_page_id( 'shop' ) ),
		'homeUrl'      => home_url( '/' ),
		'nonce'        => wp_create_nonce( 'lesyni_zone_nonce' ),
		'greenFreeFrom'  => (int) get_option( 'lesyni_green_free_from',  600 ),
		'greenCost'      => (int) get_option( 'lesyni_green_cost',        100 ),
		'yellowFreeFrom' => (int) get_option( 'lesyni_yellow_free_from', 800 ),
		'yellowCost'     => (int) get_option( 'lesyni_yellow_cost',      150 ),
		'outOfZoneLabel' => get_option( 'lesyni_out_of_zone_label', 'Уточнимо можливість доставки з менеджером' ),
		'greenPolygon'   => get_option( 'lesyni_green_polygon',  Lesyni_Zone_Shipping::GREEN_POLYGON_DEFAULT ),
		'yellowPolygon'  => get_option( 'lesyni_yellow_polygon', Lesyni_Zone_Shipping::YELLOW_POLYGON_DEFAULT ),
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
   WooCommerce: Checkout — save custom order fields
----------------------------------------------------------------------- */
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
    $fields = [
        'delivery_time'     => 'Час доставки',
        'delivery_type'     => 'Тип доставки',
        'lesyni_gift'       => 'Подарунок',
        'lesyni_subscribe'  => 'Підписка на новини',
    ];
    foreach ( $fields as $key => $label ) {
        if ( isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ) {
            update_post_meta( $order_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
} );

// Show custom fields in order admin
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    $fields = [
        '_delivery_type' => 'Тип доставки',
        '_delivery_time' => 'Час доставки',
        '_lesyni_gift'   => 'Подарунок',
    ];
    foreach ( $fields as $key => $label ) {
        $val = get_post_meta( $order->get_id(), $key, true );
        if ( $val ) {
            echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $val ) . '</p>';
        }
    }
} );

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
   Delivery zone shipping — load class + register WC method
----------------------------------------------------------------------- */
require_once get_template_directory() . '/inc/class-lesyni-zone-shipping.php';

add_filter( 'woocommerce_shipping_methods', function ( $methods ) {
    $methods['lesyni_zone'] = 'Lesyni_Zone_Shipping';
    return $methods;
} );

/* -----------------------------------------------------------------------
   AJAX: geocode address → detect zone → save to WC session
----------------------------------------------------------------------- */
function lesyni_ajax_check_zone() {
    check_ajax_referer( 'lesyni_zone_nonce', 'nonce' );

    $address = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
    if ( ! $address ) {
        wp_send_json_error( [ 'message' => 'empty_address' ] );
    }

    $coords = Lesyni_Zone_Shipping::geocode( $address );
    if ( ! $coords ) {
        wp_send_json_error( [ 'message' => 'geocode_failed' ] );
    }

    $zone = Lesyni_Zone_Shipping::detect_zone( $coords['lat'], $coords['lng'] );

    // Store result in WC session so the shipping method can read it
    if ( WC()->session ) {
        WC()->session->set( 'lesyni_zone_data', [
            'zone'    => $zone,
            'lat'     => $coords['lat'],
            'lng'     => $coords['lng'],
            'address' => $address,
        ] );
    }

    // Invalidate WC shipping cache so new rate is recalculated
    WC()->cart->calculate_shipping();

    $rates = [
        'green'   => [
            'free_from' => (int) get_option( 'lesyni_green_free_from',  600 ),
            'cost'      => (int) get_option( 'lesyni_green_cost',        100 ),
        ],
        'yellow'  => [
            'free_from' => (int) get_option( 'lesyni_yellow_free_from', 800 ),
            'cost'      => (int) get_option( 'lesyni_yellow_cost',      150 ),
        ],
    ];

    wp_send_json_success( [
        'zone'   => $zone,
        'lat'    => $coords['lat'],
        'lng'    => $coords['lng'],
        'rates'  => $rates,
    ] );
}
add_action( 'wp_ajax_lesyni_check_zone',        'lesyni_ajax_check_zone' );
add_action( 'wp_ajax_nopriv_lesyni_check_zone', 'lesyni_ajax_check_zone' );

/* -----------------------------------------------------------------------
   Admin: Delivery Zone settings page (WooCommerce → Settings → Lesyni Zones)
----------------------------------------------------------------------- */
add_filter( 'woocommerce_settings_tabs_array', function ( $tabs ) {
    $tabs['lesyni_zones'] = 'Зони доставки';
    return $tabs;
}, 50 );

add_action( 'woocommerce_settings_tabs_lesyni_zones', function () {
    woocommerce_admin_fields( lesyni_zone_settings_fields() );
} );

add_action( 'woocommerce_update_settings_lesyni_zones', function () {
    woocommerce_update_options( lesyni_zone_settings_fields() );
} );

function lesyni_zone_settings_fields() {
    return [
        [
            'title' => 'Зони доставки Lesyni Pirohy',
            'type'  => 'title',
            'id'    => 'lesyni_zones_section',
        ],
        [
            'title'   => 'Зелена зона: безкоштовно від',
            'type'    => 'number',
            'id'      => 'lesyni_green_free_from',
            'default' => 600,
            'desc'    => 'грн — мінімальна сума кошика',
        ],
        [
            'title'   => 'Зелена зона: вартість доставки',
            'type'    => 'number',
            'id'      => 'lesyni_green_cost',
            'default' => 100,
            'desc'    => 'грн',
        ],
        [
            'title'   => 'Жовта зона: безкоштовно від',
            'type'    => 'number',
            'id'      => 'lesyni_yellow_free_from',
            'default' => 800,
            'desc'    => 'грн',
        ],
        [
            'title'   => 'Жовта зона: вартість доставки',
            'type'    => 'number',
            'id'      => 'lesyni_yellow_cost',
            'default' => 150,
            'desc'    => 'грн',
        ],
        [
            'title'   => 'Текст поза зоною доставки',
            'type'    => 'text',
            'id'      => 'lesyni_out_of_zone_label',
            'default' => 'Уточнимо можливість доставки з менеджером',
        ],
        [
            'type' => 'sectionend',
            'id'   => 'lesyni_zones_section',
        ],
    ];
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
