<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo site-logo--footer">
                <img
                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>"
                    alt="<?php bloginfo( 'name' ); ?>"
                >
                <span><?php bloginfo( 'name' ); ?></span>
            </a>
            <p class="site-footer__tagline">Свіжі пироги з любов'ю — щодня з 10:00 до 18:30</p>
        </div>

        <div class="site-footer__links">
            <h4>Навігація</h4>
            <nav>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
                <?php if ( $shop_url = get_permalink( wc_get_page_id( 'shop' ) ) ) : ?>
                    <a href="<?php echo esc_url( $shop_url ); ?>">Каталог пирогів</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( home_url( '/#promos' ) ); ?>">Акції</a>
                <a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>">Відгуки</a>
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Контакти</a>
            </nav>
        </div>

        <div class="site-footer__contact">
            <h4>Контакти</h4>
            <p><a href="tel:+380632532696">+38 063 253 26 96</a></p>
            <p><a href="mailto:info@lesynpie.com.ua">info@lesynpie.com.ua</a></p>
            <p>Щодня 10:00 – 18:30</p>
            <div class="site-footer__socials">
                <a href="#" class="social-btn" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="#" class="social-btn" aria-label="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.24 8.24 0 0 0 4.84 1.55V6.79a4.85 4.85 0 0 1-1.07-.1z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. Всі права захищені.</p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
