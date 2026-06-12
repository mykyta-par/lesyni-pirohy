<?php
/**
 * Email footer — overrides woocommerce/templates/emails/email-footer.php
 *
 * @version 10.4.0
 */
defined( 'ABSPATH' ) || exit;

$phone   = get_theme_mod( 'footer_phone',   '+38 063 253 26 96' );
$email   = get_theme_mod( 'footer_email',   'info@lesynpie.com.ua' );
$address = get_theme_mod( 'footer_address', 'вул. Воскресенська, 41, Дніпро' );

$phone_href = 'tel:+' . preg_replace( '/[^0-9]/', '', $phone );
?>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Footer -->
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer">
                        <tr>
                            <td>
                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                    <tr>
                                        <td colspan="2" id="credit">
                                            <p>
                                                <strong style="color:#2d2d2d;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong><br>
                                                <a href="tel:<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>
                                                &nbsp;·&nbsp;
                                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                                            </p>
                                            <p><?php echo esc_html( $address ); ?></p>
                                            <p style="margin-top:12px;border-top:1px solid #f0e5d9;padding-top:12px;">
                                                <?php
                                                $privacy_url = get_privacy_policy_url();
                                                $terms_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'terms' ) : '';
                                                if ( $privacy_url ) {
                                                    echo '<a href="' . esc_url( $privacy_url ) . '">Конфіденційність</a>&nbsp;&nbsp;';
                                                }
                                                if ( $terms_url ) {
                                                    echo '<a href="' . esc_url( $terms_url ) . '">Умови обслуговування</a>';
                                                }
                                                ?>
                                            </p>
                                            <p>© <?php echo date( 'Y' ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div><!-- #template_container -->
            </td>
        </tr>
    </table>
</div><!-- #wrapper -->

</body>
</html>
