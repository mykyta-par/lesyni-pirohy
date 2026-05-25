<footer class="site-footer">
    <div class="site-footer__strip"></div>

    <div class="site-footer__main">
        <!-- Brand -->
        <div class="site-footer__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-footer__brand-row">
                <img
                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>"
                    alt="Лесині Пироги"
                    class="site-footer__brand-img"
                >
                <div class="site-footer__brand-name">
                    <?php echo esc_html( get_bloginfo( 'name' ) ?: 'Лесині Пироги' ); ?>
                    <small>з любов'ю до 2009 року</small>
                </div>
            </a>
            <p>Сімейна пекарня в серці Дніпра. Готуємо смачні пироги за домашніми рецептами щодня.</p>
            <div class="site-footer__socials">
                <a href="https://www.facebook.com/lesyni.pyrogy" target="_blank" rel="noopener noreferrer" class="site-footer__social" aria-label="Facebook">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5 21V13.5H7V10.5H9.5V8.25C9.5 5.85 10.95 4.5 13.1 4.5C14.13 4.5 15.21 4.68 15.21 4.68V7.25H13.91C12.63 7.25 12.25 8.02 12.25 8.82V10.5H15.09L14.65 13.5H12.25V21H9.5Z"/></svg>
                </a>
                <a href="https://www.instagram.com/lesyni_pyrogy/" target="_blank" rel="noopener noreferrer" class="site-footer__social" aria-label="Instagram">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                </a>
                <a href="#" class="site-footer__social" aria-label="TikTok">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.24 8.24 0 0 0 4.84 1.55V6.79a4.85 4.85 0 0 1-1.07-.1z"/></svg>
                </a>
            </div>
        </div>

        <!-- Nav -->
        <div class="site-footer__nav">
            <div>
                <h4>Каталог</h4>
                <nav>
                    <?php if ( $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' ) : ?>
                        <a href="<?php echo esc_url( $shop_url ); ?>">Солоні пироги</a>
                        <a href="<?php echo esc_url( $shop_url ); ?>">Солодкі пироги</a>
                        <a href="<?php echo esc_url( $shop_url ); ?>">Набори</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( home_url( '/#promos' ) ); ?>">Акції</a>
                </nav>
            </div>
            <div>
                <h4>Інформація</h4>
                <nav>
                    <a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>">Відгуки</a>
                    <?php
                    $delivery_page = get_page_by_path( 'delivery' );
                    if ( ! $delivery_page ) {
                        $pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-delivery.php' ] );
                        $delivery_page = $pages ? $pages[0] : null;
                    }
                    if ( $delivery_page ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $delivery_page->ID ) ); ?>">Доставка та оплата</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Контакти</a>
                </nav>
            </div>
        </div>

        <!-- Contact cards -->
        <div class="site-footer__cards">
            <a href="tel:+380632532696" class="site-footer__card">
                <div class="site-footer__card-icon">📞</div>
                <div>
                    <div class="site-footer__card-label">Замовити по телефону</div>
                    <div class="site-footer__card-value">+38 063 253 26 96</div>
                </div>
            </a>
            <a href="mailto:info@lesynpie.com.ua" class="site-footer__card">
                <div class="site-footer__card-icon">✉️</div>
                <div>
                    <div class="site-footer__card-label">Написати листа</div>
                    <div class="site-footer__card-value">info@lesynpie.com.ua</div>
                </div>
            </a>
            <a href="https://maps.google.com/?q=вул.+Воскресенська,+41,+Дніпро" target="_blank" rel="noopener noreferrer" class="site-footer__card">
                <div class="site-footer__card-icon">📍</div>
                <div>
                    <div class="site-footer__card-label">Знайти на карті</div>
                    <div class="site-footer__card-value">вул. Воскресенська, 41</div>
                </div>
            </a>
            <div class="site-footer__card">
                <div class="site-footer__card-icon">🕘</div>
                <div>
                    <div class="site-footer__card-label">Графік роботи</div>
                    <div class="site-footer__card-value">Щодня 9:00 — 18:30</div>
                </div>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="site-footer__copy">
            © <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?> · Зроблено з <span aria-hidden="true" style="color:var(--color-primary)">♥</span> в Дніпрі
        </div>
        <div class="site-footer__bottom-links">
            <a href="#">Конфіденційність</a>
            <a href="#">Умови</a>
        </div>
    </div>
</footer>

<?php if ( function_exists( 'WC' ) ) : ?>
<div class="cart-drawer" id="cart-drawer" aria-hidden="true">
    <div class="cart-drawer__backdrop" id="cart-drawer-backdrop"></div>
    <div class="cart-drawer__panel" role="dialog" aria-label="Кошик">
        <div class="cart-drawer__head">
            <span class="cart-drawer__title">Кошик</span>
            <button class="cart-drawer__close" id="cart-drawer-close" aria-label="Закрити">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="cart-drawer__body" id="cart-drawer-body">
            <div class="cart-drawer__loading">
                <span class="cart-drawer__spinner"></span>
            </div>
        </div>
        <div class="cart-drawer__foot" id="cart-drawer-foot" style="display:none">
            <div class="cart-drawer__total-row">
                <span>Разом:</span>
                <strong id="cart-drawer-total">0 грн</strong>
            </div>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn--primary btn--lg cart-drawer__checkout">Оформити замовлення →</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
