<?php
/**
 * WooCommerce: Catalog ordering / sort dropdown
 * Overrides: woocommerce/templates/loop/orderby.php
 *
 * @version 9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', [
    'menu_order' => 'За замовчуванням',
    'popularity' => 'За популярністю',
    'rating'     => 'За рейтингом',
    'date'       => 'Спочатку нові',
    'price'      => 'Ціна: від низької',
    'price-desc' => 'Ціна: від високої',
] );

$orderby = isset( $_GET['orderby'] )
    ? wc_clean( wp_unslash( $_GET['orderby'] ) )
    : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
?>
<form class="catalog-sort__form" method="get">
    <select name="orderby" class="catalog-sort__select" aria-label="Сортування" onchange="this.form.submit()">
        <?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
            <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>>
                <?php echo esc_html( $name ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="paged" value="1">
    <?php wc_query_string_form_fields( null, [ 'orderby', 'submit', 'paged', 'product-page' ] ); ?>
</form>
