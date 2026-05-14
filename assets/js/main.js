/* Лесині Пироги — Main JavaScript */
(function () {
    'use strict';

    /* ------------------------------------------------------------------
       Mobile nav toggle
    ------------------------------------------------------------------ */
    var navToggle = document.querySelector('.nav-toggle');
    var siteNav   = document.querySelector('.site-nav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function () {
            var isOpen = siteNav.classList.toggle('open');
            navToggle.classList.toggle('open', isOpen);
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });
        siteNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                siteNav.classList.remove('open');
                navToggle.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ------------------------------------------------------------------
       Sticky header shadow
    ------------------------------------------------------------------ */
    var header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', function () {
            header.style.boxShadow = window.scrollY > 10
                ? '0 4px 24px rgba(0,0,0,.1)'
                : '0 2px 16px rgba(0,0,0,.04)';
        }, { passive: true });
    }

    /* ------------------------------------------------------------------
       Catalog: category tab filter
    ------------------------------------------------------------------ */
    var tabs  = document.querySelectorAll('.category-tab[data-category]');
    var cards = document.querySelectorAll('.product-card[data-category]');

    if (tabs.length && cards.length) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');
                var selected = tab.dataset.category;
                cards.forEach(function (card) {
                    var show = selected === 'all' || card.dataset.category === selected;
                    card.style.display = show ? '' : 'none';
                });
            });
        });
    }

    /* ------------------------------------------------------------------
       Size selector — switch price when toggling Small / Large
    ------------------------------------------------------------------ */
    document.querySelectorAll('.size-selector').forEach(function (selector) {
        var options  = selector.querySelectorAll('.size-option');
        var card     = selector.closest('.product-card');
        if (!card) return;
        var priceEl  = card.querySelector('.price-value');

        options.forEach(function (opt) {
            opt.addEventListener('click', function () {
                options.forEach(function (o) { o.classList.remove('active'); });
                opt.classList.add('active');
                if (priceEl && opt.dataset.price) {
                    priceEl.textContent = Math.round(parseFloat(opt.dataset.price));
                }
            });
        });
    });

    /* ------------------------------------------------------------------
       Add to cart — unified button for simple & variable products
    ------------------------------------------------------------------ */
    document.querySelectorAll('.btn-add-cart[data-product-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId   = btn.dataset.productId;
            var productType = btn.dataset.type;
            var card        = btn.closest('.product-card');
            var body;

            if (productType === 'variable') {
                // Get selected variation id from active size option
                var activeOpt = card ? card.querySelector('.size-option.active') : null;
                var variationId = activeOpt ? activeOpt.dataset.variationId : '';
                if (!variationId) {
                    // No variation selected — open product page
                    window.location.href = card
                        ? card.querySelector('.product-name a').href
                        : '#';
                    return;
                }
                body = new URLSearchParams({
                    product_id:   productId,
                    variation_id: variationId,
                    quantity:     1,
                });
            } else {
                body = new URLSearchParams({
                    product_id: productId,
                    quantity:   1,
                });
            }

            btn.disabled = true;
            btn.textContent = '...';

            fetch('/?wc-ajax=add_to_cart', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString(),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && !data.error) {
                    btn.textContent = '✓ Додано';
                    btn.classList.add('btn-add-cart--added');

                    // Update cart count in header
                    var countEl = document.querySelector('.header-cart__count');
                    if (data.cart_contents_count) {
                        if (countEl) {
                            countEl.textContent = data.cart_contents_count;
                        } else {
                            var cartLink = document.querySelector('.header-cart');
                            if (cartLink) {
                                var span = document.createElement('span');
                                span.className = 'header-cart__count';
                                span.textContent = data.cart_contents_count;
                                cartLink.appendChild(span);
                            }
                        }
                    }

                    setTimeout(function () {
                        btn.textContent = 'В кошик';
                        btn.classList.remove('btn-add-cart--added');
                        btn.disabled = false;
                    }, 2000);
                } else {
                    // Fallback: navigate to product page
                    var link = card ? card.querySelector('.product-name a') : null;
                    if (link) window.location.href = link.href;
                    btn.disabled = false;
                    btn.textContent = 'В кошик';
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = 'В кошик';
            });
        });
    });

    /* ------------------------------------------------------------------
       Smooth scroll for anchor links
    ------------------------------------------------------------------ */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(anchor.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            var offset = (header ? header.offsetHeight : 72) + 8;
            window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
        });
    });

})();
