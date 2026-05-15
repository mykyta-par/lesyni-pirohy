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
       Default zone polygons — Kyiv example (edit via WP admin or here)
       Each zone is an array of [lat, lng] pairs forming a closed polygon.
    ------------------------------------------------------------------ */

    // Green zone: central Kyiv (Shevchenkivskyi, Pecherskyi, Podilskyi core)
    const GREEN_POLYGON_DEFAULT = [
        [50.4730, 30.4630],
        [50.4890, 30.4880],
        [50.4980, 30.5240],
        [50.4920, 30.5640],
        [50.4750, 30.5780],
        [50.4530, 30.5680],
        [50.4350, 30.5440],
        [50.4270, 30.5100],
        [50.4380, 30.4720],
        [50.4570, 30.4580],
        [50.4730, 30.4630],
    ];

    // Yellow zone: broader Kyiv city area
    const YELLOW_POLYGON_DEFAULT = [
        [50.5280, 30.3680],
        [50.5560, 30.4200],
        [50.5720, 30.4980],
        [50.5710, 30.5760],
        [50.5500, 30.6380],
        [50.5140, 30.6840],
        [50.4720, 30.7060],
        [50.4240, 30.6920],
        [50.3880, 30.6460],
        [50.3700, 30.5800],
        [50.3720, 30.5080],
        [50.3900, 30.4420],
        [50.4260, 30.3920],
        [50.4700, 30.3620],
        [50.5120, 30.3580],
        [50.5280, 30.3680],
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
        $query = urlencode( $address . ', Київ, Україна' );
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
