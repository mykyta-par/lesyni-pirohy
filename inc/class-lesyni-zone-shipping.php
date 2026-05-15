<?php
/**
 * Lesyni Zone Shipping — custom WooCommerce shipping method
 *
 * Delivery zones (polygon coordinates [lat, lng]) and free-shipping
 * thresholds are configurable in the WP admin → WooCommerce → Settings →
 * Shipping → Lesyni Zone Shipping.
 *
 * Zone data is also stored in the WC session so the rate calculated by
 * the AJAX geocoder is respected when the order is actually placed.
 */

defined( 'ABSPATH' ) || exit;

class Lesyni_Zone_Shipping extends WC_Shipping_Method {

    /* ------------------------------------------------------------------
       Default zone polygons — Dnipro (edit via WP admin or here)
       Each zone is an array of [lat, lng] pairs forming a closed polygon.
    ------------------------------------------------------------------ */

    // Green zone: central Dnipro (Tsentralnyi, Sobornyi, core of Shevchenkivskyi)
    const GREEN_POLYGON_DEFAULT = [
        [48.5000, 35.0000],
        [48.5100, 35.0310],
        [48.5060, 35.0700],
        [48.4900, 35.0900],
        [48.4700, 35.0850],
        [48.4480, 35.0700],
        [48.4380, 35.0380],
        [48.4430, 35.0040],
        [48.4620, 34.9880],
        [48.4820, 34.9840],
        [48.5000, 35.0000],
    ];

    // Yellow zone: broader Dnipro city area
    const YELLOW_POLYGON_DEFAULT = [
        [48.5640, 34.8820],
        [48.5830, 34.9560],
        [48.5830, 35.0540],
        [48.5700, 35.1360],
        [48.5450, 35.2060],
        [48.5100, 35.2500],
        [48.4680, 35.2580],
        [48.4270, 35.2360],
        [48.3960, 35.1960],
        [48.3760, 35.1280],
        [48.3680, 35.0440],
        [48.3780, 34.9620],
        [48.4090, 34.8940],
        [48.4510, 34.8520],
        [48.5100, 34.8460],
        [48.5640, 34.8820],
    ];

    public function __construct() {
        $this->id                 = 'lesyni_zone';
        $this->method_title       = 'Lesyni Zone Shipping';
        $this->method_description = 'Автоматична вартість доставки залежно від зони (за адресою клієнта)';
        $this->supports           = [ 'shipping-zones' ];

        $this->init();
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->title            = $this->get_option( 'title', 'Доставка по Києву' );
        $this->enabled          = $this->get_option( 'enabled', 'yes' );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => 'Увімкнено',
                'type'    => 'checkbox',
                'default' => 'yes',
            ],
            'title' => [
                'title'   => 'Назва методу',
                'type'    => 'text',
                'default' => 'Доставка по Києву',
            ],
            'green_free_from' => [
                'title'       => 'Зелена зона: безкоштовно від (грн)',
                'type'        => 'number',
                'default'     => 600,
                'description' => 'Мінімальна сума кошика для безкоштовної доставки в зеленій зоні',
                'desc_tip'    => true,
            ],
            'green_cost' => [
                'title'   => 'Зелена зона: вартість доставки (грн)',
                'type'    => 'number',
                'default' => 100,
            ],
            'yellow_free_from' => [
                'title'       => 'Жовта зона: безкоштовно від (грн)',
                'type'        => 'number',
                'default'     => 800,
                'description' => 'Мінімальна сума кошика для безкоштовної доставки в жовтій зоні',
                'desc_tip'    => true,
            ],
            'yellow_cost' => [
                'title'   => 'Жовта зона: вартість доставки (грн)',
                'type'    => 'number',
                'default' => 150,
            ],
            'out_of_zone_label' => [
                'title'   => 'Текст поза зоною',
                'type'    => 'text',
                'default' => 'Уточнимо можливість доставки з менеджером',
            ],
        ];
    }

    public function calculate_shipping( $package = [] ) {
        $session_data = WC()->session ? WC()->session->get( 'lesyni_zone_data' ) : null;

        if ( ! $session_data ) {
            // No zone detected yet — offer a generic rate so checkout doesn't block
            $this->add_rate( [
                'id'    => $this->get_rate_id( 'pending' ),
                'label' => 'Введіть адресу для розрахунку доставки',
                'cost'  => 0,
                'meta_data' => [ 'lesyni_zone' => 'pending' ],
            ] );
            return;
        }

        $zone    = $session_data['zone'];    // 'green' | 'yellow' | 'outside'
        $subtotal = $package['cart_subtotal'] ?? (float) WC()->cart->get_subtotal();

        if ( $zone === 'green' ) {
            $free_from = (float) $this->get_option( 'green_free_from', 600 );
            $cost      = (float) $this->get_option( 'green_cost', 100 );
            $label     = $this->title . ' (зелена зона)';
            $final_cost = $subtotal >= $free_from ? 0 : $cost;
            $this->add_rate( [
                'id'    => $this->get_rate_id( 'green' ),
                'label' => $label,
                'cost'  => $final_cost,
                'meta_data' => [ 'lesyni_zone' => 'green' ],
            ] );

        } elseif ( $zone === 'yellow' ) {
            $free_from  = (float) $this->get_option( 'yellow_free_from', 800 );
            $cost       = (float) $this->get_option( 'yellow_cost', 150 );
            $label      = $this->title . ' (жовта зона)';
            $final_cost = $subtotal >= $free_from ? 0 : $cost;
            $this->add_rate( [
                'id'    => $this->get_rate_id( 'yellow' ),
                'label' => $label,
                'cost'  => $final_cost,
                'meta_data' => [ 'lesyni_zone' => 'yellow' ],
            ] );

        } else {
            // Outside any zone
            $out_label = $this->get_option( 'out_of_zone_label', 'Уточнимо можливість доставки з менеджером' );
            $this->add_rate( [
                'id'    => $this->get_rate_id( 'outside' ),
                'label' => $out_label,
                'cost'  => 0,
                'meta_data' => [ 'lesyni_zone' => 'outside' ],
            ] );
        }
    }

    /* ------------------------------------------------------------------
       Static helpers — used both here and in the AJAX handler
    ------------------------------------------------------------------ */

    /**
     * Point-in-polygon test using ray casting algorithm.
     * @param float $lat
     * @param float $lng
     * @param array $polygon  Array of [lat, lng] pairs
     * @return bool
     */
    public static function point_in_polygon( $lat, $lng, array $polygon ) {
        $n      = count( $polygon );
        $inside = false;
        $j      = $n - 1;

        for ( $i = 0; $i < $n; $i++ ) {
            $xi = $polygon[ $i ][0];
            $yi = $polygon[ $i ][1];
            $xj = $polygon[ $j ][0];
            $yj = $polygon[ $j ][1];

            if ( ( ( $yi > $lng ) !== ( $yj > $lng ) ) &&
                 ( $lat < ( $xj - $xi ) * ( $lng - $yi ) / ( $yj - $yi ) + $xi ) ) {
                $inside = ! $inside;
            }
            $j = $i;
        }

        return $inside;
    }

    /**
     * Detect zone for given coordinates.
     * Returns 'green' | 'yellow' | 'outside'.
     */
    public static function detect_zone( $lat, $lng ) {
        // Try to load custom polygons from WP options (set via admin)
        $green_polygon  = get_option( 'lesyni_green_polygon',  self::GREEN_POLYGON_DEFAULT );
        $yellow_polygon = get_option( 'lesyni_yellow_polygon', self::YELLOW_POLYGON_DEFAULT );

        if ( self::point_in_polygon( $lat, $lng, $green_polygon ) ) {
            return 'green';
        }
        if ( self::point_in_polygon( $lat, $lng, $yellow_polygon ) ) {
            return 'yellow';
        }
        return 'outside';
    }

    /**
     * Geocode a Ukrainian address string using Nominatim (OpenStreetMap).
     * Returns ['lat' => float, 'lng' => float] or false on failure.
     */
    public static function geocode( $address ) {
        // Append city hint to improve accuracy
        $query = urlencode( $address . ', Дніпро, Україна' );
        $url   = 'https://nominatim.openstreetmap.org/search?q=' . $query
               . '&format=json&limit=1&addressdetails=0'
               . '&accept-language=uk';

        $response = wp_remote_get( $url, [
            'timeout'    => 8,
            'user-agent' => 'LesyniPirohy/1.0 (delivery-zone-check; ' . get_bloginfo( 'url' ) . ')',
            'headers'    => [ 'Referer' => get_bloginfo( 'url' ) ],
        ] );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body[0]['lat'] ) ) return false;

        return [
            'lat' => (float) $body[0]['lat'],
            'lng' => (float) $body[0]['lon'],
        ];
    }
}
