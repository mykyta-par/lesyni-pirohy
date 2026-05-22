<?php

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LESYNI_GMAPS_KEY', 'AIzaSyBfHzeh5KhIQ-yIipIPCdtj5BZTNwO2T3M' );

/* -----------------------------------------------------------------------
   Helpers
----------------------------------------------------------------------- */
function lesyni_get_ukraine_zone() {
    return WC_Shipping_Zones::get_zone_matching_package( [
        'destination' => [ 'country' => 'UA', 'state' => '', 'postcode' => '' ],
    ] );
}

function lesyni_is_np_shipping_enabled() {
    $zone = lesyni_get_ukraine_zone();
    foreach ( $zone->get_shipping_methods( true ) as $method ) {
        if ( $method->id === 'nova_poshta_shipping' ) return true;
    }
    return false;
}

function lesyni_get_np_rate_key() {
    $zone = lesyni_get_ukraine_zone();
    foreach ( $zone->get_shipping_methods( true ) as $method ) {
        if ( $method->id === 'nova_poshta_shipping' ) {
            return 'nova_poshta_shipping:' . $method->get_instance_id();
        }
    }
    return 'nova_poshta_shipping';
}

function lesyni_get_np_display_cost() {
    $zone = lesyni_get_ukraine_zone();
    foreach ( $zone->get_shipping_methods( true ) as $method ) {
        if ( $method->id === 'nova_poshta_shipping' ) {
            $cost = $method->get_option( 'cost' );
            if ( $cost !== null && $cost !== '' ) return (float) $cost;
        }
    }
    return (float) get_option( 'lesyni_np_cost', 80 );
}

function lesyni_get_np_api_key() {
    $zone = lesyni_get_ukraine_zone();
    foreach ( $zone->get_shipping_methods( true ) as $method ) {
        if ( $method->id === 'nova_poshta_shipping' ) {
            foreach ( [ 'api_key', 'apiKey', 'nova_poshta_api_key', 'key' ] as $opt ) {
                $val = $method->get_option( $opt );
                if ( $val ) return $val;
            }
        }
    }
    return get_option( 'lesyni_np_api_key', '' );
}

/* -----------------------------------------------------------------------
   Nova Poshta API — AJAX proxies (city search + warehouse list)
----------------------------------------------------------------------- */
add_action( 'wp_ajax_lesyni_np_cities',        'lesyni_np_cities_handler' );
add_action( 'wp_ajax_nopriv_lesyni_np_cities', 'lesyni_np_cities_handler' );
function lesyni_np_cities_handler() {
    $q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
    if ( mb_strlen( $q ) < 2 ) wp_send_json_success( [] );

    $api_key = lesyni_get_np_api_key();

    $resp = wp_remote_post( 'https://api.novaposhta.ua/v2.0/json/', [
        'headers' => [ 'Content-Type' => 'application/json' ],
        'timeout' => 8,
        'body'    => wp_json_encode( [
            'apiKey'           => $api_key,
            'modelName'        => 'Address',
            'calledMethod'     => 'searchSettlements',
            'methodProperties' => [ 'CityName' => $q, 'Limit' => '12', 'Language' => 'ua' ],
        ] ),
    ] );
    if ( is_wp_error( $resp ) ) wp_send_json_error( 'request_failed' );

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $cities = [];
    foreach ( $body['data'][0]['Addresses'] ?? [] as $a ) {
        $cities[] = [ 'ref' => $a['DeliveryCity'] ?? $a['Ref'] ?? '', 'label' => $a['Present'] ?? '' ];
    }
    wp_send_json_success( $cities );
}

add_action( 'wp_ajax_lesyni_np_warehouses',        'lesyni_np_warehouses_handler' );
add_action( 'wp_ajax_nopriv_lesyni_np_warehouses', 'lesyni_np_warehouses_handler' );
function lesyni_np_warehouses_handler() {
    $ref = sanitize_text_field( wp_unslash( $_GET['ref'] ?? '' ) );
    if ( ! $ref ) wp_send_json_success( [] );

    $api_key = lesyni_get_np_api_key();

    $resp = wp_remote_post( 'https://api.novaposhta.ua/v2.0/json/', [
        'headers' => [ 'Content-Type' => 'application/json' ],
        'timeout' => 8,
        'body'    => wp_json_encode( [
            'apiKey'           => $api_key,
            'modelName'        => 'AddressGeneral',
            'calledMethod'     => 'getWarehouses',
            'methodProperties' => [ 'CityRef' => $ref, 'Language' => 'ua', 'Limit' => '300' ],
        ] ),
    ] );
    if ( is_wp_error( $resp ) ) wp_send_json_error( 'request_failed' );

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $warehouses = [];
    foreach ( $body['data'] ?? [] as $w ) {
        $warehouses[] = [ 'ref' => $w['Ref'] ?? '', 'label' => $w['Description'] ?? '', 'number' => $w['Number'] ?? '' ];
    }
    wp_send_json_success( $warehouses );
}

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

function lesyni_nav_fallback() {
    $shop = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : '';
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Головна</a></li>';
    if ( $shop ) {
        echo '<li><a href="' . esc_url( $shop ) . '">Пироги</a></li>';
    }
    echo '<li><a href="' . esc_url( home_url( '/#promos' ) ) . '">Акції</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#reviews' ) ) . '">Відгуки</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">Контакти</a></li>';
    echo '</ul>';
}

/* -----------------------------------------------------------------------
   Custom Post Type: Акції (Promotions)
----------------------------------------------------------------------- */
function lesyni_register_promo_cpt() {
    register_post_type( 'lesyni_promo', [
        'labels' => [
            'name'               => 'Акції',
            'singular_name'      => 'Акція',
            'add_new'            => 'Додати акцію',
            'add_new_item'       => 'Нова акція',
            'edit_item'          => 'Редагувати акцію',
            'all_items'          => 'Всі акції',
            'menu_name'          => 'Акції',
        ],
        'public'        => true,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-tag',
        'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'has_archive'   => false,
        'rewrite'       => [ 'slug' => 'promo' ],
    ] );
}
add_action( 'init', 'lesyni_register_promo_cpt' );

// Meta box for promo icon (emoji)
function lesyni_promo_meta_box() {
    add_meta_box(
        'lesyni_promo_icon',
        'Іконка (emoji)',
        function( $post ) {
            $icon = get_post_meta( $post->ID, '_promo_icon', true );
            wp_nonce_field( 'lesyni_promo_icon', 'lesyni_promo_icon_nonce' );
            echo '<input type="text" name="promo_icon" value="' . esc_attr( $icon ) . '" style="width:100%;font-size:24px;" placeholder="напр. 🎂">';
            echo '<p style="color:#666;font-size:12px;margin-top:6px;">Введіть будь-який emoji як іконку акції</p>';
        },
        'lesyni_promo',
        'side'
    );
}
add_action( 'add_meta_boxes', 'lesyni_promo_meta_box' );

function lesyni_promo_save_meta( $post_id ) {
    if ( ! isset( $_POST['lesyni_promo_icon_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['lesyni_promo_icon_nonce'], 'lesyni_promo_icon' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( isset( $_POST['promo_icon'] ) ) {
        update_post_meta( $post_id, '_promo_icon', sanitize_text_field( $_POST['promo_icon'] ) );
    }
}
add_action( 'save_post_lesyni_promo', 'lesyni_promo_save_meta' );

// Seed default promos on first load
function lesyni_seed_promos() {
    if ( get_option( 'lesyni_promos_seeded' ) ) return;

    $defaults = [
        [ 'title' => 'День народження?',  'content' => 'Отримай знижку 10% при замовленні. Мінімум замовлення 700 грн',                       'icon' => '🎂' ],
        [ 'title' => 'Напиши відгук',     'content' => 'Отримай Яблучний пиріг у подарунок при наступному замовленні',                         'icon' => '⭐' ],
    ];

    foreach ( $defaults as $i => $promo ) {
        $id = wp_insert_post( [
            'post_type'    => 'lesyni_promo',
            'post_title'   => $promo['title'],
            'post_content' => $promo['content'],
            'post_status'  => 'publish',
            'menu_order'   => $i + 1,
        ] );
        if ( $id && ! is_wp_error( $id ) ) {
            update_post_meta( $id, '_promo_icon', $promo['icon'] );
        }
    }

    update_option( 'lesyni_promos_seeded', true );
}
add_action( 'init', 'lesyni_seed_promos' );

/* -----------------------------------------------------------------------
   Reviews CPT
----------------------------------------------------------------------- */
function lesyni_register_review_cpt() {
    register_post_type( 'lesyni_review', [
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'labels'       => [
            'name'          => 'Відгуки',
            'singular_name' => 'Відгук',
            'add_new'       => 'Додати відгук',
            'add_new_item'  => 'Новий відгук',
            'edit_item'     => 'Редагувати відгук',
            'all_items'     => 'Всі відгуки',
        ],
        'supports'     => [ 'title', 'editor' ],
        'has_archive'  => false,
        'rewrite'      => false,
    ] );
}
add_action( 'init', 'lesyni_register_review_cpt' );

function lesyni_review_meta_box() {
    add_meta_box(
        'lesyni_review_meta',
        'Деталі відгуку',
        'lesyni_review_meta_box_html',
        'lesyni_review',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'lesyni_review_meta_box' );

function lesyni_review_meta_box_html( $post ) {
    wp_nonce_field( 'lesyni_review_save', 'lesyni_review_nonce' );
    $stars = get_post_meta( $post->ID, '_review_stars', true ) ?: '5';
    $date  = get_post_meta( $post->ID, '_review_date',  true ) ?: '';
    ?>
    <p>
        <label style="display:block;font-weight:600;margin-bottom:4px;">Зірки</label>
        <select name="review_stars" style="width:100%">
            <?php foreach ( [ 5, 4, 3, 2, 1 ] as $n ) : ?>
                <option value="<?php echo $n; ?>" <?php selected( $stars, $n ); ?>><?php echo str_repeat( '★', $n ) . str_repeat( '☆', 5 - $n ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label style="display:block;font-weight:600;margin-bottom:4px;">Коли (наприклад: 2 дні тому)</label>
        <input type="text" name="review_date" value="<?php echo esc_attr( $date ); ?>" placeholder="2 дні тому" style="width:100%">
    </p>
    <?php
}

function lesyni_review_save_meta( $post_id ) {
    if ( ! isset( $_POST['lesyni_review_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['lesyni_review_nonce'], 'lesyni_review_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    update_post_meta( $post_id, '_review_stars', intval( $_POST['review_stars'] ?? 5 ) );
    update_post_meta( $post_id, '_review_date',  sanitize_text_field( $_POST['review_date'] ?? '' ) );
}
add_action( 'save_post_lesyni_review', 'lesyni_review_save_meta' );

function lesyni_seed_reviews() {
    if ( get_option( 'lesyni_reviews_seeded' ) ) return;

    $reviews = [
        [
            'name'  => 'Марія',
            'text'  => 'Найкращі пироги в місті! Дуже смачно, много начинки, свіже тісто. М\'ясний просто божественний!',
            'stars' => 5,
            'date'  => '2 дні тому',
        ],
        [
            'name'  => 'Андрій',
            'text'  => 'Жульєн з дуже правильним балансом грибів та курки. Доставили вчасно. Всім рекомендую!',
            'stars' => 5,
            'date'  => '5 днів тому',
        ],
        [
            'name'  => 'Олена',
            'text'  => 'Гарна альтернатива піці! Колеги в офісі були в захваті. Красиво упаковано, дуже смачно!',
            'stars' => 5,
            'date'  => '1 тиждень тому',
        ],
    ];

    foreach ( $reviews as $r ) {
        $id = wp_insert_post( [
            'post_type'    => 'lesyni_review',
            'post_status'  => 'publish',
            'post_title'   => $r['name'],
            'post_content' => $r['text'],
        ] );
        if ( $id && ! is_wp_error( $id ) ) {
            update_post_meta( $id, '_review_stars', $r['stars'] );
            update_post_meta( $id, '_review_date',  $r['date'] );
        }
    }

    update_option( 'lesyni_reviews_seeded', true );
}
add_action( 'init', 'lesyni_seed_reviews' );

/* -----------------------------------------------------------------------
   Scripts & Styles
----------------------------------------------------------------------- */
function lesyni_enqueue_assets() {
	// Google Fonts
	wp_enqueue_style(
		'lesyni-fonts',
		'https://fonts.googleapis.com/css2?family=Philosopher:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap',
		[],
		null
	);

	// Leaflet + Google Places — тільки на сторінці кошика
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
		wp_enqueue_script(
			'google-places',
			'https://maps.googleapis.com/maps/api/js?key=' . LESYNI_GMAPS_KEY . '&libraries=places&language=uk',
			[],
			null,
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

	// Main script (depends on Leaflet + Google Places when on cart page)
	$script_deps = is_cart() ? [ 'leaflet', 'google-places' ] : [];
	wp_enqueue_script(
		'lesyni-main',
		get_template_directory_uri() . '/assets/js/main.js',
		$script_deps,
		filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);

	// Detect the LiqPay gateway ID (handles liqpay, liqpay_woocommerce, woo_liqpay, etc.)
	$liqpay_id = '';
	if ( function_exists( 'WC' ) && WC()->payment_gateways ) {
		foreach ( array_keys( WC()->payment_gateways()->payment_gateways() ) as $gw_id ) {
			if ( stripos( $gw_id, 'liqpay' ) !== false ) {
				$liqpay_id = $gw_id;
				break;
			}
		}
	}

	wp_localize_script( 'lesyni-main', 'lesyniData', [
		'ajaxUrl'   => home_url( '/?wc-ajax=lesyni_add_to_cart' ),
		'nonce'     => wp_create_nonce( 'lesyni_add_to_cart' ),
		'npAjaxUrl' => admin_url( 'admin-ajax.php' ),
		'liqpayId'  => $liqpay_id,
	] );

}
add_action( 'wp_enqueue_scripts', 'lesyni_enqueue_assets' );

/* -----------------------------------------------------------------------
   Custom AJAX: add to cart (simple & variable products)
   Pass item_id = variation_id for variable, product_id for simple.
   WooCommerce resolves the variation automatically.
----------------------------------------------------------------------- */
function lesyni_ajax_add_to_cart() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( [ 'message' => 'WooCommerce not ready' ], 500 );
		return;
	}
	$item_id  = absint( $_POST['item_id'] ?? 0 );
	$quantity = max( 1, absint( $_POST['quantity'] ?? 1 ) );
	if ( ! $item_id ) {
		wp_send_json_error( [ 'message' => 'Invalid product' ], 400 );
		return;
	}
	// Passing variation_id as first argument works: WC detects it's a
	// variation and automatically resolves the parent + attributes.
	$key = WC()->cart->add_to_cart( $item_id, $quantity );
	if ( $key ) {
		wp_send_json_success( [ 'count' => WC()->cart->get_cart_contents_count() ] );
	} else {
		wp_send_json_error( [ 'message' => 'Could not add to cart' ] );
	}
}
add_action( 'wc_ajax_lesyni_add_to_cart',        'lesyni_ajax_add_to_cart' );
add_action( 'wc_ajax_nopriv_lesyni_add_to_cart', 'lesyni_ajax_add_to_cart' );

/* -----------------------------------------------------------------------
   AJAX: get cart drawer contents
----------------------------------------------------------------------- */
function lesyni_ajax_cart_drawer() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error();
		return;
	}
	$items = [];
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$product = $item['data'];
		if ( ! $product ) continue;
		$name    = $product->get_name();
		$qty     = $item['quantity'];
		$price   = (float) $product->get_price();
		$thumb   = get_the_post_thumbnail_url( $item['product_id'], 'thumbnail' );
		$variation_label = '';
		if ( ! empty( $item['variation'] ) ) {
			$parts = [];
			foreach ( $item['variation'] as $attr => $val ) {
				if ( ! $val ) continue;
				$taxonomy = str_replace( 'attribute_', '', $attr );
				$term     = get_term_by( 'slug', $val, $taxonomy );
				$parts[]  = $term ? $term->name : $val;
			}
			$variation_label = implode( ', ', $parts );
		}
		$items[] = [
			'key'       => $key,
			'name'      => $name,
			'qty'       => $qty,
			'price'     => $price,
			'subtotal'  => round( $price * $qty ),
			'thumb'     => $thumb ?: '',
			'variation' => $variation_label,
		];
	}
	wp_send_json_success( [
		'items' => $items,
		'total' => round( (float) WC()->cart->get_subtotal() ),
		'count' => WC()->cart->get_cart_contents_count(),
	] );
}
add_action( 'wc_ajax_lesyni_cart_drawer',        'lesyni_ajax_cart_drawer' );
add_action( 'wc_ajax_nopriv_lesyni_cart_drawer', 'lesyni_ajax_cart_drawer' );

function lesyni_ajax_cart_update_qty() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { wp_send_json_error(); return; }
	$key = sanitize_text_field( $_POST['key'] ?? '' );
	$qty = max( 0, (int) ( $_POST['qty'] ?? 1 ) );
	if ( ! $key ) { wp_send_json_error(); return; }
	WC()->cart->set_quantity( $key, $qty, true );
	wp_send_json_success( [ 'count' => WC()->cart->get_cart_contents_count() ] );
}
add_action( 'wc_ajax_lesyni_cart_update_qty',        'lesyni_ajax_cart_update_qty' );
add_action( 'wc_ajax_nopriv_lesyni_cart_update_qty', 'lesyni_ajax_cart_update_qty' );

function lesyni_ajax_cart_remove() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { wp_send_json_error(); return; }
	$key = sanitize_text_field( $_POST['key'] ?? '' );
	if ( ! $key ) { wp_send_json_error(); return; }
	WC()->cart->remove_cart_item( $key );
	wp_send_json_success( [ 'count' => WC()->cart->get_cart_contents_count() ] );
}
add_action( 'wc_ajax_lesyni_cart_remove',        'lesyni_ajax_cart_remove' );
add_action( 'wc_ajax_nopriv_lesyni_cart_remove', 'lesyni_ajax_cart_remove' );


// Make cart page behave as checkout so NP plugin loads its scripts + fields
add_action( 'wp', function () {
    if ( is_page( wc_get_page_id( 'cart' ) ) ) {
        add_filter( 'woocommerce_is_checkout', '__return_true' );
    }
} );

// Output config as a hidden HTML element (data attribute) — CSP-safe, no inline script needed.
// Runs at priority 5 so the <div> is in the DOM before footer scripts execute.
add_action( 'wp_footer', function () {
	$config = [
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'nonce'          => wp_create_nonce( 'lesyni_zone_nonce' ),
		'greenFreeFrom'  => (int) get_option( 'lesyni_green_free_from',  600 ),
		'greenCost'      => (int) get_option( 'lesyni_green_cost',        100 ),
		'yellowFreeFrom' => (int) get_option( 'lesyni_yellow_free_from', 800 ),
		'yellowCost'     => (int) get_option( 'lesyni_yellow_cost',      150 ),
		'outOfZoneLabel' => get_option( 'lesyni_out_of_zone_label', 'Уточнимо можливість доставки з менеджером' ),
		'greenPolygon'   => get_option( 'lesyni_green_polygon',  Lesyni_Zone_Shipping::GREEN_POLYGON_DEFAULT ),
		'yellowPolygon'  => get_option( 'lesyni_yellow_polygon', Lesyni_Zone_Shipping::YELLOW_POLYGON_DEFAULT ),
	];
	echo '<div id="lesyni-config" hidden data-cfg="' . esc_attr( wp_json_encode( $config, JSON_UNESCAPED_UNICODE ) ) . '"></div>' . "\n";
}, 5 );

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
remove_action( 'woocommerce_before_shop_loop',    'woocommerce_result_count', 20 );

/* -----------------------------------------------------------------------
   Nova Poshta: thermal packaging (as products) + minimum order validation
----------------------------------------------------------------------- */
define( 'LESYNI_THERMAL_SMALL_ID', 33000 );
define( 'LESYNI_THERMAL_LARGE_ID', 33001 );

add_action( 'woocommerce_checkout_create_order', function ( $order ) {
    $methods = isset( $_POST['shipping_method'] ) ? (array) $_POST['shipping_method'] : [];
    $is_np   = false;
    foreach ( $methods as $m ) {
        if ( strpos( (string) $m, 'nova_poshta_shipping' ) !== false ) { $is_np = true; break; }
    }
    if ( ! $is_np ) return;

    $count = 0;
    foreach ( WC()->cart->get_cart() as $item ) $count += (int) $item['quantity'];

    $product_id = $count <= 3 ? LESYNI_THERMAL_SMALL_ID : LESYNI_THERMAL_LARGE_ID;
    if ( ! $product_id ) return;

    $product = wc_get_product( $product_id );
    if ( ! $product ) return;

    $line_item = new WC_Order_Item_Product();
    $line_item->set_props( [
        'product'  => $product,
        'quantity' => 1,
        'subtotal' => wc_get_price_excluding_tax( $product ),
        'total'    => wc_get_price_excluding_tax( $product ),
    ] );
    $order->add_item( $line_item );
} );

add_action( 'woocommerce_checkout_process', function () {
    $methods = isset( $_POST['shipping_method'] ) ? (array) $_POST['shipping_method'] : [];
    $is_np = false;
    foreach ( $methods as $m ) {
        if ( strpos( (string) $m, 'nova_poshta_shipping' ) !== false ) { $is_np = true; break; }
    }
    if ( ! $is_np ) return;
    if ( WC()->cart->get_subtotal() < 1000 ) {
        wc_add_notice( 'Мінімальна сума замовлення для доставки Новою Поштою — 1000 грн.', 'error' );
    }
}, 5 );

/* -----------------------------------------------------------------------
   Remove wc-ukr-shipping plugin validation — we handle NP fields ourselves
----------------------------------------------------------------------- */
add_action( 'woocommerce_checkout_process', function () {
    global $wp_filter;
    foreach ( [ 'woocommerce_checkout_process', 'woocommerce_after_checkout_validation' ] as $hook ) {
        if ( ! isset( $wp_filter[ $hook ] ) ) continue;
        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $fn = $callback['function'];
                if ( is_array( $fn ) && is_object( $fn[0] ) ) {
                    $class = get_class( $fn[0] );
                    if ( strpos( $class, 'WCUkrShipping' ) !== false || strpos( $class, 'kirillbdev' ) !== false ) {
                        remove_action( $hook, $fn, $priority );
                    }
                }
            }
        }
    }
}, 1 );

/* -----------------------------------------------------------------------
   WooCommerce: Thank you page — show NP delivery details
----------------------------------------------------------------------- */
add_action( 'woocommerce_thankyou', function ( $order_id ) {
    $np_city   = get_post_meta( $order_id, '_np_city',   true );
    $np_branch = get_post_meta( $order_id, '_np_branch', true );
    if ( ! $np_city && ! $np_branch ) return;
    echo '<section class="woocommerce-columns woocommerce-columns--2 col2-set addresses">';
    echo '<div class="woocommerce-column woocommerce-column--1">';
    echo '<h2 class="woocommerce-column__title">Доставка Нова Пошта</h2>';
    echo '<address>';
    if ( $np_city )   echo '<strong>Місто:</strong> ' . esc_html( $np_city ) . '<br>';
    if ( $np_branch ) echo '<strong>Відділення:</strong> ' . esc_html( $np_branch );
    echo '</address>';
    echo '</div></section>';
}, 20 );

/* -----------------------------------------------------------------------
   WooCommerce: Checkout — save custom order fields
----------------------------------------------------------------------- */
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
    $fields = [
        'delivery_time'     => 'Час доставки',
        'delivery_type'     => 'Тип доставки',
        'lesyni_gift'       => 'Подарунок',
        'lesyni_subscribe'  => 'Підписка на новини',
        'np_city'           => 'Місто НП',
        'np_branch'         => 'Відділення НП',
    ];
    foreach ( $fields as $key => $label ) {
        if ( isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ) {
            update_post_meta( $order_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    // Add visible order note when gift is requested (only if feature is enabled)
    if ( get_option( 'lesyni_gift_enabled', 'no' ) === 'yes'
        && ! empty( $_POST['lesyni_gift'] ) && $_POST['lesyni_gift'] === '1' ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $order->add_order_note( '🎁 Подарунок: додати рукописну листівку та святкову стрічку' );
        }
    }
} );

// Show gift info in order confirmation emails
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text ) {
    // Nova Poshta address block
    $np_city   = get_post_meta( $order->get_id(), '_np_city',   true );
    $np_branch = get_post_meta( $order->get_id(), '_np_branch', true );
    if ( $np_city || $np_branch ) {
        if ( $plain_text ) {
            echo "\n📦 Нова Пошта: м. " . $np_city . ", відділення " . $np_branch . "\n";
        } else {
            echo '<p style="margin:16px 0;padding:12px 16px;background:#f5f5f5;border-left:4px solid #c4845a;font-family:Arial,sans-serif;font-size:14px;">
                📦 <strong>Нова Пошта:</strong> м. ' . esc_html( $np_city ) . ', відділення ' . esc_html( $np_branch ) . '
            </p>';
        }
    }

    // Gift block
    $gift = get_post_meta( $order->get_id(), '_lesyni_gift', true );
    if ( $gift !== '1' ) return;

    if ( $plain_text ) {
        echo "\n🎁 Це подарунок — додамо рукописну листівку та святкову стрічку.\n";
    } else {
        echo '<p style="margin:16px 0;padding:12px 16px;background:#fff8e1;border-left:4px solid #e07a3f;font-family:Arial,sans-serif;font-size:14px;">
            🎁 <strong>Це подарунок</strong> — додамо рукописну листівку та святкову стрічку (безкоштовно).
        </p>';
    }
}, 10, 3 );

// Show custom fields in order admin
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    $fields = [
        '_delivery_type' => 'Тип доставки',
        '_delivery_time' => 'Час доставки',
        '_lesyni_gift'   => 'Подарунок',
        '_np_city'       => 'Місто НП',
        '_np_branch'     => 'Відділення НП',
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
   Override ALL WC shipping rates with a single dynamic lesyni rate.
   This replaces "безкоштовна доставка" / "коштовна доставка" (and any
   other methods in the matched zone) so checkout always gets one rate
   with the correct cost based on the detected delivery zone.
----------------------------------------------------------------------- */
add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
    if ( ! function_exists( 'WC' ) || ! WC()->session ) return $rates;

    $zone_data = WC()->session->get( 'lesyni_zone_data' );
    $subtotal  = isset( $package['cart_subtotal'] )
        ? (float) $package['cart_subtotal']
        : (float) WC()->cart->get_subtotal();

    // Courier rate — cost depends on detected zone
    if ( $zone_data && in_array( $zone_data['zone'], [ 'green', 'yellow' ], true ) ) {
        $zone = $zone_data['zone'];
        if ( $zone === 'green' ) {
            $free_from    = (float) get_option( 'lesyni_green_free_from',  600 );
            $courier_cost = $subtotal >= $free_from ? 0 : (float) get_option( 'lesyni_green_cost', 100 );
        } else {
            $free_from    = (float) get_option( 'lesyni_yellow_free_from', 800 );
            $courier_cost = $subtotal >= $free_from ? 0 : (float) get_option( 'lesyni_yellow_cost', 150 );
        }
        $courier_label = $courier_cost === 0.0 ? 'Безкоштовна доставка' : 'Доставка по Дніпру — ' . (int) $courier_cost . ' грн';
    } else {
        $courier_cost  = 0;
        $courier_label = 'Доставка (уточнюється)';
    }

    $result = [
        'lesyni_zone_rate'   => new WC_Shipping_Rate( 'lesyni_zone_rate',   $courier_label, $courier_cost, [], 'lesyni_zone' ),
        'lesyni_pickup_rate' => new WC_Shipping_Rate( 'lesyni_pickup_rate', 'Самовивіз',    0,             [], 'lesyni_zone' ),
    ];

    // Include Nova Poshta: prefer plugin's own rate, fall back to creating one with plugin's method ID
    if ( lesyni_is_np_shipping_enabled() ) {
        $np_added = false;
        foreach ( $rates as $key => $rate ) {
            if ( $rate->get_method_id() === 'nova_poshta_shipping' ) {
                $result[ $key ] = $rate;
                $np_added = true;
            }
        }
        if ( ! $np_added ) {
            $np_rate_key          = lesyni_get_np_rate_key();
            $np_cost              = lesyni_get_np_display_cost();
            $result[ $np_rate_key ] = new WC_Shipping_Rate( $np_rate_key, 'Нова Пошта', $np_cost, [], 'nova_poshta_shipping' );
        }
    }

    return $result;
}, 20, 2 );

/* -----------------------------------------------------------------------
   AJAX: geocode address → detect zone → save to WC session
----------------------------------------------------------------------- */
function lesyni_ajax_check_zone() {
    // Non-fatal nonce check — return JSON error instead of die(-1)
    $nonce_ok = isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'lesyni_zone_nonce' );
    if ( ! $nonce_ok ) {
        wp_send_json_error( [ 'message' => 'bad_nonce' ] );
    }

    $address = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
    if ( ! $address ) {
        wp_send_json_error( [ 'message' => 'empty_address' ] );
    }

    $coords = Lesyni_Zone_Shipping::geocode( $address );
    if ( ! $coords ) {
        wp_send_json_error( [ 'message' => 'geocode_failed' ] );
    }

    $zone = Lesyni_Zone_Shipping::detect_zone( $coords['lat'], $coords['lng'] );

    // Save to WC session for shipping rate calculation
    if ( function_exists( 'WC' ) && WC()->session ) {
        WC()->session->set( 'lesyni_zone_data', [
            'zone'    => $zone,
            'lat'     => $coords['lat'],
            'lng'     => $coords['lng'],
            'address' => $address,
        ] );
        // Reset cached shipping so it recalculates with new zone
        WC()->session->set( 'shipping_for_package_0', null );
        // Set courier as default only if customer hasn't chosen NP or pickup
        $current = WC()->session->get( 'chosen_shipping_methods', [] );
        $np_key  = lesyni_get_np_rate_key();
        if ( empty( $current ) || $current[0] === 'lesyni_zone_rate' || $current[0] === '' ) {
            WC()->session->set( 'chosen_shipping_methods', [ 'lesyni_zone_rate' ] );
        }
    }

    $rates = [
        'green'  => [
            'free_from' => (int) get_option( 'lesyni_green_free_from',  600 ),
            'cost'      => (int) get_option( 'lesyni_green_cost',        100 ),
        ],
        'yellow' => [
            'free_from' => (int) get_option( 'lesyni_yellow_free_from', 800 ),
            'cost'      => (int) get_option( 'lesyni_yellow_cost',      150 ),
        ],
    ];

    wp_send_json_success( [
        'zone'  => $zone,
        'lat'   => $coords['lat'],
        'lng'   => $coords['lng'],
        'rates' => $rates,
    ] );
}
add_action( 'wp_ajax_lesyni_check_zone',        'lesyni_ajax_check_zone' );
add_action( 'wp_ajax_nopriv_lesyni_check_zone', 'lesyni_ajax_check_zone' );

// Ukraine has no mandatory state/oblast for checkout — remove the requirement
// so our single-city form doesn't fail WC validation
add_filter( 'woocommerce_get_country_locale', function ( $locale ) {
    $locale['UA']['state']['required'] = false;
    $locale['UA']['state']['hidden']   = true;
    return $locale;
} );

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

add_action( 'woocommerce_update_options_lesyni_zones', function () {
    woocommerce_update_options( lesyni_zone_settings_fields() );
    update_option( 'lesyni_gift_enabled', isset( $_POST['lesyni_gift_enabled'] ) ? 'yes' : 'no' );
} );

function lesyni_zone_settings_fields() {
    return [
        [
            'title' => 'Зони доставки Lesyni Pirohy',
            'type'  => 'title',
            'id'    => 'lesyni_zones_section',
        ],
        [
            'title' => 'Nova Poshta API ключ',
            'type'  => 'text',
            'id'    => 'lesyni_np_api_key',
            'desc'  => 'Для автодоповнення міст і відділень. Отримати: cabinet.novaposhta.ua → Налаштування → Безпека.',
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
            'title'   => 'Опція «Це подарунок»',
            'type'    => 'checkbox',
            'id'      => 'lesyni_gift_enabled',
            'default' => 'no',
            'desc'    => 'Показувати чекбокс «Це подарунок — додамо рукописну листівку та святкову стрічку» на сторінці кошика',
        ],
        [
            'type' => 'sectionend',
            'id'   => 'lesyni_zones_section',
        ],
    ];
}

/* -----------------------------------------------------------------------
   Hero section meta box (front page editor)
----------------------------------------------------------------------- */
function lesyni_hero_meta_box() {
    add_meta_box(
        'lesyni_hero',
        '🏠 Hero — головний банер',
        'lesyni_hero_meta_box_html',
        'page',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'lesyni_hero_meta_box' );

function lesyni_hero_meta_box_html( $post ) {
    // Show only on front page
    if ( (int) get_option( 'page_on_front' ) !== $post->ID ) {
        echo '<p style="color:#999;">Ці налаштування активні тільки на сторінці, що призначена головною (<em>Налаштування → Читання</em>).</p>';
        return;
    }
    wp_nonce_field( 'lesyni_hero_save', 'lesyni_hero_nonce' );
    $f = function( $key ) use ( $post ) {
        return esc_attr( get_post_meta( $post->ID, '_hero_' . $key, true ) );
    };
    $bg_id  = (int) get_post_meta( $post->ID, '_hero_bg_id', true );
    $bg_url = $bg_id ? wp_get_attachment_image_url( $bg_id, 'large' ) : '';
    ?>
    <style>
        .hero-mb label { display:block; font-weight:600; margin:14px 0 4px; font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#555; }
        .hero-mb input[type=text], .hero-mb textarea { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:4px; font-size:14px; }
        .hero-mb textarea { height:72px; resize:vertical; }
        .hero-mb .hero-mb-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .hero-mb .hero-bg-preview { max-width:200px; border-radius:6px; margin-top:8px; display:<?php echo $bg_url ? 'block' : 'none'; ?>; }
    </style>
    <div class="hero-mb">
        <label>Рядок над заголовком</label>
        <input type="text" name="hero_eyebrow" value="<?php echo $f('eyebrow'); ?>" placeholder="Домашня пекарня · Дніпро">

        <label>Заголовок</label>
        <input type="text" name="hero_title" value="<?php echo $f('title'); ?>" placeholder="Від щирого серця до вашого столу">

        <label>Підзаголовок</label>
        <textarea name="hero_subtitle" placeholder="Ми команда пекарів, закоханих в свою справу."><?php echo esc_textarea( get_post_meta( $post->ID, '_hero_subtitle', true ) ); ?></textarea>

        <label>Теги (кожен з нового рядка)</label>
        <textarea name="hero_badges" placeholder="Щодня свіже&#10;Доставка Дніпром&#10;Замовлення з 10:00"><?php echo esc_textarea( get_post_meta( $post->ID, '_hero_badges', true ) ); ?></textarea>

        <div class="hero-mb-row">
            <div>
                <label>Кнопка 1 — текст</label>
                <input type="text" name="hero_btn1_text" value="<?php echo $f('btn1_text'); ?>" placeholder="Замовити">
            </div>
            <div>
                <label>Кнопка 1 — посилання</label>
                <input type="text" name="hero_btn1_url" value="<?php echo $f('btn1_url'); ?>" placeholder="tel:+380632532696">
            </div>
        </div>

        <div class="hero-mb-row">
            <div>
                <label>Кнопка 2 — текст</label>
                <input type="text" name="hero_btn2_text" value="<?php echo $f('btn2_text'); ?>" placeholder="Переглянути меню">
            </div>
            <div>
                <label>Кнопка 2 — посилання (порожньо = каталог)</label>
                <input type="text" name="hero_btn2_url" value="<?php echo $f('btn2_url'); ?>" placeholder="">
            </div>
        </div>

        <label>Фонове зображення</label>
        <div>
            <input type="hidden" name="hero_bg_id" id="hero_bg_id" value="<?php echo esc_attr( $bg_id ?: '' ); ?>">
            <button type="button" class="button" id="hero_bg_btn">Вибрати зображення</button>
            <button type="button" class="button" id="hero_bg_remove" style="<?php echo $bg_url ? '' : 'display:none;'; ?>">Видалити</button>
            <img src="<?php echo esc_url( $bg_url ); ?>" id="hero_bg_preview" class="hero-bg-preview">
        </div>
    </div>
    <script>
    (function(){
        var frame;
        document.getElementById('hero_bg_btn').addEventListener('click', function(){
            if(frame){ frame.open(); return; }
            frame = wp.media({ title:'Фон hero-секції', multiple:false, library:{type:'image'}, button:{text:'Вибрати'} });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                document.getElementById('hero_bg_id').value = att.id;
                document.getElementById('hero_bg_preview').src = att.url;
                document.getElementById('hero_bg_preview').style.display = 'block';
                document.getElementById('hero_bg_remove').style.display = '';
            });
            frame.open();
        });
        document.getElementById('hero_bg_remove').addEventListener('click', function(){
            document.getElementById('hero_bg_id').value = '';
            document.getElementById('hero_bg_preview').src = '';
            document.getElementById('hero_bg_preview').style.display = 'none';
            this.style.display = 'none';
        });
    }());
    </script>
    <?php
}

function lesyni_hero_save_meta( $post_id ) {
    if ( ! isset( $_POST['lesyni_hero_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['lesyni_hero_nonce'], 'lesyni_hero_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    $fields = [ 'eyebrow', 'title', 'subtitle', 'badges', 'btn1_text', 'btn1_url', 'btn2_text', 'btn2_url' ];
    foreach ( $fields as $key ) {
        if ( isset( $_POST[ 'hero_' . $key ] ) ) {
            update_post_meta( $post_id, '_hero_' . $key, sanitize_textarea_field( $_POST[ 'hero_' . $key ] ) );
        }
    }
    $bg_id = isset( $_POST['hero_bg_id'] ) ? (int) $_POST['hero_bg_id'] : 0;
    if ( $bg_id ) {
        update_post_meta( $post_id, '_hero_bg_id', $bg_id );
    } else {
        delete_post_meta( $post_id, '_hero_bg_id' );
    }
}
add_action( 'save_post_page', 'lesyni_hero_save_meta' );

// Enqueue media uploader on page edit screen
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook === 'post.php' || $hook === 'post-new.php' ) {
        wp_enqueue_media();
    }
} );

/* -----------------------------------------------------------------------
   Admin: Dashboard widget — time slot management
----------------------------------------------------------------------- */
function lesyni_all_slots() {
    return [
        'Якнайшвидше',
        '09:00–10:00', '10:00–11:00', '11:00–12:00', '12:00–13:00',
        '13:00–14:00', '14:00–15:00', '15:00–16:00', '16:00–17:00',
        '17:00–18:00', '18:00–19:00',
    ];
}

// AJAX save handler
add_action( 'wp_ajax_lesyni_save_slots', function () {
    check_ajax_referer( 'lesyni_save_slots', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'forbidden' );

    $slots      = lesyni_all_slots();
    $post_slots = isset( $_POST['slots'] ) ? (array) $_POST['slots'] : [];
    $new_disabled = [];
    foreach ( $slots as $slot ) {
        if ( ! in_array( $slot, $post_slots, true ) ) {
            $new_disabled[] = $slot;
        }
    }
    update_option( 'lesyni_disabled_slots', $new_disabled );
    wp_send_json_success();
} );

add_action( 'wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'lesyni_slots_widget',
        '⏰ Слоти доставки',
        'lesyni_slots_widget_render'
    );
} );

function lesyni_slots_widget_render() {
    $disabled = (array) get_option( 'lesyni_disabled_slots', [] );
    $nonce    = wp_create_nonce( 'lesyni_save_slots' );
    $ajax_url = admin_url( 'admin-ajax.php' );
    ?>
    <div id="lesyni-slots-msg" style="display:none;font-weight:600;margin-bottom:10px;"></div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;" id="lesyni-slots-wrap">
    <?php foreach ( lesyni_all_slots() as $slot ) :
        $active = ! in_array( $slot, $disabled, true );
        $bg     = $active ? '#edf7ed' : '#fce8e8';
        $border = $active ? '#b5d6b5' : '#f5b8b8';
        $color  = $active ? '#2d6a2d' : '#b84040';
        ?>
        <label data-slot="<?php echo esc_attr( $slot ); ?>"
               style="display:flex;align-items:center;gap:5px;cursor:pointer;
                      padding:7px 12px;border-radius:7px;font-size:13px;font-weight:500;
                      background:<?php echo $bg; ?>;border:1.5px solid <?php echo $border; ?>;
                      color:<?php echo $color; ?>;">
            <input type="checkbox" value="<?php echo esc_attr( $slot ); ?>"
                   <?php checked( $active ); ?> style="margin:0;cursor:pointer;">
            <?php echo esc_html( $slot ); ?>
        </label>
    <?php endforeach; ?>
    </div>
    <p style="color:#999;font-size:12px;margin:0 0 10px;">
        ✓ відмічено = доступний · знято = прихований для клієнта
    </p>
    <button id="lesyni-slots-save" class="button button-primary">Зберегти</button>
    <script>
    (function(){
        var wrap = document.getElementById('lesyni-slots-wrap');
        var msg  = document.getElementById('lesyni-slots-msg');

        // Update label colours when toggling
        wrap.addEventListener('change', function(e){
            if (e.target.type !== 'checkbox') return;
            var lbl    = e.target.closest('label');
            var active = e.target.checked;
            lbl.style.background   = active ? '#edf7ed' : '#fce8e8';
            lbl.style.borderColor  = active ? '#b5d6b5' : '#f5b8b8';
            lbl.style.color        = active ? '#2d6a2d' : '#b84040';
        });

        document.getElementById('lesyni-slots-save').addEventListener('click', function(){
            var active = [];
            wrap.querySelectorAll('input[type=checkbox]:checked').forEach(function(cb){
                active.push(cb.value);
            });
            var body = new URLSearchParams({
                action: 'lesyni_save_slots',
                nonce:  '<?php echo $nonce; ?>',
            });
            active.forEach(function(s){ body.append('slots[]', s); });

            fetch('<?php echo esc_url( $ajax_url ); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body.toString(),
            })
            .then(function(r){ return r.json(); })
            .then(function(r){
                msg.textContent   = r.success ? '✓ Збережено' : '✗ Помилка';
                msg.style.color   = r.success ? '#3a7230' : '#b84040';
                msg.style.display = 'block';
                setTimeout(function(){ msg.style.display = 'none'; }, 3000);
            });
        });
    }());
    </script>
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

// Disable WooCommerce shipping debug notices
add_filter( 'option_woocommerce_shipping_debug_mode', '__return_zero' );
