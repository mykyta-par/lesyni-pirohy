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

                    // Update cart count via WooCommerce fragments
                    if (data.fragments) {
                        Object.keys(data.fragments).forEach(function (selector) {
                            var el = document.querySelector(selector);
                            if (el) {
                                var tmp = document.createElement('div');
                                tmp.innerHTML = data.fragments[selector];
                                if (tmp.firstElementChild) {
                                    el.replaceWith(tmp.firstElementChild);
                                }
                            }
                        });
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
       Single product: gallery thumbnail switcher
    ------------------------------------------------------------------ */
    var mainImage = document.getElementById('sp-main-image');
    if (mainImage) {
        document.querySelectorAll('.sp-gallery__thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                document.querySelectorAll('.sp-gallery__thumb').forEach(function (t) {
                    t.classList.remove('active');
                });
                thumb.classList.add('active');
                mainImage.style.opacity = '0';
                setTimeout(function () {
                    mainImage.src = thumb.dataset.full;
                    mainImage.style.opacity = '1';
                }, 150);
            });
        });
    }

    /* ------------------------------------------------------------------
       Single product: transform variation <select> into pill buttons
       Inserts pills BEFORE the hidden .variations table so they're visible.
       WooCommerce's variation JS still works via the hidden <select>.
    ------------------------------------------------------------------ */
    var variationsTable = document.querySelector('.sp-details table.variations');
    if (variationsTable) {
        var allPillsWrapper = document.createElement('div');
        allPillsWrapper.className = 'sp-size-selectors';

        variationsTable.querySelectorAll('tr').forEach(function (row) {
            var label  = row.querySelector('label');
            var select = row.querySelector('select');
            if (!select) return;

            var labelText = label ? label.textContent.trim() : '';

            var wrapper = document.createElement('div');
            wrapper.className = 'size-selector';

            if (labelText) {
                var title = document.createElement('p');
                title.className = 'size-selector__label';
                title.style.cssText = 'font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:8px;';
                title.textContent = labelText + ':';
                wrapper.appendChild(title);
            }

            var btnGroup = document.createElement('div');
            btnGroup.style.cssText = 'display:flex;gap:4px;background:#faf5f0;border:1px solid #e8d5c4;border-radius:10px;padding:4px;';

            var firstBtn = null;
            Array.from(select.options).forEach(function (opt) {
                if (!opt.value) return;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'size-option';
                btn.dataset.value = opt.value;
                btn.innerHTML = '<span class="size-option__label">' + opt.text + '</span>';
                if (!firstBtn) firstBtn = btn;

                btn.addEventListener('click', function () {
                    btnGroup.querySelectorAll('.size-option').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
                btnGroup.appendChild(btn);
            });

            wrapper.appendChild(btnGroup);
            allPillsWrapper.appendChild(wrapper);

            // Pre-select first real option
            if (firstBtn) {
                firstBtn.classList.add('active');
                var firstOpt = Array.from(select.options).find(function (o) { return o.value; });
                if (firstOpt) {
                    select.value = firstOpt.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        // Insert pill UI BEFORE the hidden variations table — outside it, so it stays visible
        variationsTable.parentNode.insertBefore(allPillsWrapper, variationsTable);
    }

    /* ------------------------------------------------------------------
       Single product: quantity +/- stepper
    ------------------------------------------------------------------ */
    document.querySelectorAll('.sp-details .quantity').forEach(function (wrap) {
        var input = wrap.querySelector('.qty');
        if (!input) return;

        var minus = document.createElement('button');
        minus.type = 'button';
        minus.className = 'qty-btn qty-btn--minus';
        minus.setAttribute('aria-label', 'Зменшити');
        minus.textContent = '−';

        var plus = document.createElement('button');
        plus.type = 'button';
        plus.className = 'qty-btn qty-btn--plus';
        plus.setAttribute('aria-label', 'Збільшити');
        plus.textContent = '+';

        wrap.insertBefore(minus, input);
        wrap.appendChild(plus);

        minus.addEventListener('click', function () {
            var v = parseInt(input.value, 10) || 1;
            if (v > (parseInt(input.min, 10) || 1)) {
                input.value = v - 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        plus.addEventListener('click', function () {
            var v   = parseInt(input.value, 10) || 1;
            var max = parseInt(input.max, 10);
            if (!max || v < max) {
                input.value = v + 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
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
