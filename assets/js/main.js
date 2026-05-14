/* Лесині Пироги — Main JavaScript */
(function () {
    'use strict';

    /* ------------------------------------------------------------------
       Mobile nav toggle
    ------------------------------------------------------------------ */
    const navToggle = document.querySelector('.nav-toggle');
    const siteNav   = document.querySelector('.site-nav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = siteNav.classList.toggle('open');
            navToggle.classList.toggle('open', isOpen);
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        // Close nav when a link is clicked
        siteNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                siteNav.classList.remove('open');
                navToggle.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ------------------------------------------------------------------
       Sticky header shadow
    ------------------------------------------------------------------ */
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => {
            header.style.boxShadow = window.scrollY > 10
                ? '0 4px 24px rgba(0,0,0,.1)'
                : '0 2px 16px rgba(0,0,0,.04)';
        };
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ------------------------------------------------------------------
       Catalog: category tab filter
    ------------------------------------------------------------------ */
    const tabs = document.querySelectorAll('.category-tab[data-category]');
    const cards = document.querySelectorAll('.product-card[data-category]');

    if (tabs.length && cards.length) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const selected = tab.dataset.category;
                cards.forEach(card => {
                    const show = selected === 'all' || card.dataset.category === selected;
                    card.style.display = show ? '' : 'none';
                });
            });
        });
    }

    /* ------------------------------------------------------------------
       Catalog: size selector (price switcher)
    ------------------------------------------------------------------ */
    document.querySelectorAll('.size-selector').forEach(selector => {
        const options    = selector.querySelectorAll('.size-option');
        const card       = selector.closest('.product-card');
        if (!card) return;
        const priceEl    = card.querySelector('.price-value');

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                options.forEach(o => o.classList.remove('active'));
                opt.classList.add('active');

                if (priceEl) {
                    const price = opt.dataset.size === 'small'
                        ? selector.dataset.smallPrice
                        : selector.dataset.largePrice;
                    if (price) priceEl.textContent = price;
                }
            });
        });
    });

    /* ------------------------------------------------------------------
       Smooth scroll for anchor links
    ------------------------------------------------------------------ */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            const offset = (header ? header.offsetHeight : 72) + 8;
            const top    = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });

    /* ------------------------------------------------------------------
       Popular card hover ripple (subtle touch)
    ------------------------------------------------------------------ */
    document.querySelectorAll('.popular-card, .product-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.willChange = 'transform';
        });
        card.addEventListener('mouseleave', () => {
            card.style.willChange = '';
        });
    });

})();
