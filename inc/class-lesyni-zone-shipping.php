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

    // Green zone: з Google My Maps ("Безкоштовна Доставка від 600 грн")
    const GREEN_POLYGON_DEFAULT = [
        [48.4872423, 34.9970396],
        [48.4789427, 34.9957357],
        [48.4752518, 34.9954516],
        [48.469647,  34.9848215],
        [48.4663779, 34.9787509],
        [48.463052,  34.9830232],
        [48.4612769, 34.9848494],
        [48.4581359, 34.9825771],
        [48.4392702, 34.9926458],
        [48.4374195, 35.001787 ],
        [48.4323229, 34.9992548],
        [48.4287349, 35.0144468],
        [48.4244063, 35.0211416],
        [48.4233231, 35.0265492],
        [48.4241216, 35.0287805],
        [48.4283352, 35.0401963],
        [48.4328776, 35.0469332],
        [48.4374195, 35.0534997],
        [48.4441097, 35.0590137],
        [48.4498885, 35.0648723],
        [48.4398118, 35.0738834],
        [48.4465578, 35.0818661],
        [48.4777501, 35.0775838],
        [48.4823479, 35.0804802],
        [48.493961,  35.0675892],
        [48.4970376, 35.036839 ],
        [48.4856845, 35.0188241],
        [48.4872423, 34.9970396],
    ];

    // Yellow zone: 3 окремих полігони з Google My Maps ("від 800 грн")
    // Структура: масив полігонів, кожен полігон — масив [lat, lng]
    const YELLOW_POLYGON_DEFAULT = [
        [ // Полігон 1 — захід (лівий берег)
            [48.4740142, 34.9041276],
            [48.4556883, 34.9241691],
            [48.4429359, 34.9463135],
            [48.4402029, 34.9613339],
            [48.4389502, 34.9737793],
            [48.4415694, 34.9799592],
            [48.4399182, 34.9918897],
            [48.4581359, 34.9825771],
            [48.4612769, 34.9848494],
            [48.4628528, 34.9829588],
            [48.4663779, 34.9787509],
            [48.4681548, 34.9818076],
            [48.469647,  34.9848215],
            [48.4752518, 34.9954516],
            [48.4768308, 34.9955376],
            [48.4782816, 34.9956663],
            [48.4796613, 34.9958379],
            [48.4810126, 34.9960525],
            [48.4872423, 34.9969537],
            [48.49276,   34.9809033],
            [48.494694,  34.9609906],
            [48.4944096, 34.9499184],
            [48.4879818, 34.9161869],
            [48.4848529, 34.9039131],
            [48.4816385, 34.9009948],
            [48.4740142, 34.9041276],
        ],
        [ // Полігон 2 — північ (правий берег, північ)
            [48.506806,  34.9709174],
            [48.4941914, 35.0036668],
            [48.493568,  35.0229562],
            [48.4970376, 35.0367532],
            [48.493961,  35.0675034],
            [48.4823479, 35.0803944],
            [48.4777501, 35.077498 ],
            [48.4707511, 35.0880552],
            [48.5004845, 35.1131741],
            [48.5041048, 35.0957152],
            [48.5116109, 35.0970026],
            [48.5168418, 35.0930974],
            [48.5382405, 35.1026247],
            [48.5480451, 35.0922391],
            [48.5485,    35.0832476],
            [48.5382718, 35.075631 ],
            [48.5388396, 35.0650307],
            [48.5367368, 35.0502678],
            [48.5288366, 35.0350758],
            [48.537703,  35.0204846],
            [48.5319059, 35.0058934],
            [48.5276429, 35.0101849],
            [48.5184337, 34.9928471],
            [48.5120658, 34.9834057],
            [48.5101325, 34.9846074],
            [48.506806,  34.9709174],
        ],
        [ // Полігон 3 — південь (правий берег, південь)
            [48.4461585, 35.0679625],
            [48.4498885, 35.0649152],
            [48.4427284, 35.0576831],
            [48.4385502, 35.0546686],
            [48.4363222, 35.0523197],
            [48.4283352, 35.0402392],
            [48.4233231, 35.0265921],
            [48.4236079, 35.0243606],
            [48.4241785, 35.021528 ],
            [48.4285071, 35.0148332],
            [48.4303285, 35.0077953],
            [48.4320951, 34.9993838],
            [48.4268553, 34.9962292],
            [48.4332049, 34.9811446],
            [48.429688,  34.9771859],
            [48.4260002, 34.9857154],
            [48.4219276, 34.9908438],
            [48.4101839, 34.9893785],
            [48.4036315, 34.9629429],
            [48.3959935, 34.9589505],
            [48.3963062, 34.9669104],
            [48.3956496, 34.9731535],
            [48.3917172, 34.9813074],
            [48.3954217, 34.9888605],
            [48.3946893, 34.9944068],
            [48.3824385, 34.9961653],
            [48.3818981, 35.0003812],
            [48.3835237, 35.0068289],
            [48.3889384, 35.027964 ],
            [48.3885395, 35.0414395],
            [48.3889384, 35.0565026],
            [48.3878555, 35.0599359],
            [48.3927001, 35.070493 ],
            [48.4030718, 35.0789044],
            [48.4068895, 35.0865433],
            [48.4075732, 35.0887749],
            [48.4088267, 35.0898048],
            [48.4158911, 35.0835393],
            [48.4247203, 35.0722097],
            [48.439584,  35.0739262],
            [48.4461585, 35.0679625],
        ],
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
        $green_polygon   = get_option( 'lesyni_green_polygon',  self::GREEN_POLYGON_DEFAULT );
        $yellow_polygons = get_option( 'lesyni_yellow_polygon', self::YELLOW_POLYGON_DEFAULT );

        if ( self::point_in_polygon( $lat, $lng, $green_polygon ) ) {
            return 'green';
        }

        // Yellow can be a single polygon or an array of polygons
        $is_multi = isset( $yellow_polygons[0][0] ) && is_array( $yellow_polygons[0][0] );
        $yellow_list = $is_multi ? $yellow_polygons : [ $yellow_polygons ];
        foreach ( $yellow_list as $poly ) {
            if ( self::point_in_polygon( $lat, $lng, $poly ) ) {
                return 'yellow';
            }
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
