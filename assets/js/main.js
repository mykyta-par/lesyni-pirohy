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
       Single product: size card selection + total price
    ------------------------------------------------------------------ */
    var spSizeCards = document.querySelectorAll('.sp-size-card');
    var spBtnCart   = document.querySelector('.sp-btn-cart');
    var spTotalEl   = document.getElementById('sp-total');
    var spQtyInput  = document.querySelector('.sp-qty-input');

    var spSelectedVariationId = '';
    var spSelectedPrice       = 0;

    function updateSpTotal() {
        var qty = parseInt(spQtyInput ? spQtyInput.value : 1, 10) || 1;
        if (spTotalEl) spTotalEl.textContent = Math.round(spSelectedPrice * qty);
    }

    if (spSizeCards.length) {
        var firstCard = spSizeCards[0];
        spSelectedVariationId = firstCard.dataset.variationId || '';
        spSelectedPrice       = parseFloat(firstCard.dataset.price) || 0;
        updateSpTotal();

        spSizeCards.forEach(function (card) {
            card.addEventListener('click', function () {
                spSizeCards.forEach(function (c) { c.classList.remove('active'); });
                card.classList.add('active');
                spSelectedVariationId = card.dataset.variationId || '';
                spSelectedPrice       = parseFloat(card.dataset.price) || 0;
                updateSpTotal();
            });
        });
    } else if (spBtnCart && spBtnCart.dataset.price) {
        spSelectedPrice = parseFloat(spBtnCart.dataset.price) || 0;
        updateSpTotal();
    }

    /* ------------------------------------------------------------------
       Single product: qty stepper
    ------------------------------------------------------------------ */
    if (spQtyInput) {
        var spMinusBtn = document.querySelector('.sp-qty-btn--minus');
        var spPlusBtn  = document.querySelector('.sp-qty-btn--plus');

        if (spMinusBtn) {
            spMinusBtn.addEventListener('click', function () {
                var v = parseInt(spQtyInput.value, 10) || 1;
                if (v > 1) { spQtyInput.value = v - 1; updateSpTotal(); }
            });
        }
        if (spPlusBtn) {
            spPlusBtn.addEventListener('click', function () {
                var v   = parseInt(spQtyInput.value, 10) || 1;
                var max = parseInt(spQtyInput.max, 10) || 20;
                if (v < max) { spQtyInput.value = v + 1; updateSpTotal(); }
            });
        }
    }

    /* ------------------------------------------------------------------
       Single product: add to cart
    ------------------------------------------------------------------ */
    if (spBtnCart) {
        spBtnCart.addEventListener('click', function () {
            var productId   = spBtnCart.dataset.productId;
            var productType = spBtnCart.dataset.type;
            var qty         = parseInt(spQtyInput ? spQtyInput.value : 1, 10) || 1;
            var body;

            if (productType === 'variable') {
                if (!spSelectedVariationId) return;
                body = new URLSearchParams({
                    product_id:   productId,
                    variation_id: spSelectedVariationId,
                    quantity:     qty,
                });
            } else {
                body = new URLSearchParams({
                    product_id: productId,
                    quantity:   qty,
                });
            }

            spBtnCart.disabled = true;
            var labelEl = spBtnCart.querySelector('.sp-btn-cart__label');
            if (labelEl) labelEl.textContent = '...';

            fetch('/?wc-ajax=add_to_cart', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString(),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && !data.error) {
                    if (labelEl) labelEl.textContent = '✓ Додано!';
                    spBtnCart.classList.add('sp-btn-cart--added');

                    if (data.fragments) {
                        Object.keys(data.fragments).forEach(function (selector) {
                            var el = document.querySelector(selector);
                            if (el) {
                                var tmp = document.createElement('div');
                                tmp.innerHTML = data.fragments[selector];
                                if (tmp.firstElementChild) el.replaceWith(tmp.firstElementChild);
                            }
                        });
                    }

                    setTimeout(function () {
                        if (labelEl) labelEl.textContent = 'Додати в кошик';
                        spBtnCart.classList.remove('sp-btn-cart--added');
                        spBtnCart.disabled = false;
                    }, 2000);
                } else {
                    if (labelEl) labelEl.textContent = 'Додати в кошик';
                    spBtnCart.disabled = false;
                }
            })
            .catch(function () {
                if (labelEl) labelEl.textContent = 'Додати в кошик';
                spBtnCart.disabled = false;
            });
        });
    }

    /* ------------------------------------------------------------------
       Single product: share button
    ------------------------------------------------------------------ */
    var spShareBtn = document.querySelector('.sp-share-btn');
    if (spShareBtn) {
        spShareBtn.addEventListener('click', function () {
            if (navigator.share) {
                navigator.share({ title: document.title, url: location.href });
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(location.href);
            }
        });
    }

    /* ------------------------------------------------------------------
       Cart page: qty stepper + row total update
    ------------------------------------------------------------------ */
    var cartForm     = document.getElementById('cart-form');
    var cartRows     = document.querySelectorAll('.cart-row[data-key]');
    var cartUpdateBtn = document.getElementById('cart-update-btn');

    if (cartForm && cartRows.length) {
        var cartUpdateTimer = null;

        cartRows.forEach(function (row) {
            var val     = row.querySelector('.ci-qty-val');
            var input   = row.querySelector('.ci-qty-input');
            var sumEl   = row.querySelector('.ci-row-sum');
            var unit    = parseFloat(row.dataset.unit) || 0;
            var minus   = row.querySelector('.ci-qty-btn--minus');
            var plus    = row.querySelector('.ci-qty-btn--plus');

            function syncRow() {
                var q = parseInt(val.textContent, 10) || 1;
                if (input)  input.value = q;
                if (sumEl)  sumEl.textContent = Math.round(unit * q);
            }

            if (minus) {
                minus.addEventListener('click', function () {
                    var q = parseInt(val.textContent, 10) || 1;
                    if (q > 1) {
                        val.textContent = q - 1;
                        syncRow();
                        scheduleUpdate();
                    }
                });
            }
            if (plus) {
                plus.addEventListener('click', function () {
                    var q = parseInt(val.textContent, 10) || 1;
                    if (q < 20) {
                        val.textContent = q + 1;
                        syncRow();
                        scheduleUpdate();
                    }
                });
            }
        });

        function scheduleUpdate() {
            clearTimeout(cartUpdateTimer);
            cartUpdateTimer = setTimeout(function () {
                if (cartUpdateBtn) cartUpdateBtn.click();
            }, 800);
        }
    }

    /* ------------------------------------------------------------------
       Checkout: delivery option cards
    ------------------------------------------------------------------ */
    var coDeliveryOptions = document.getElementById('co-delivery-options');
    var coAddressBlock    = document.getElementById('co-address-block');

    if (coDeliveryOptions) {
        coDeliveryOptions.querySelectorAll('.co-opt-card').forEach(function (card) {
            card.addEventListener('click', function () {
                coDeliveryOptions.querySelectorAll('.co-opt-card').forEach(function (c) {
                    c.classList.remove('co-opt-card--active');
                });
                card.classList.add('co-opt-card--active');

                var radioInput = card.querySelector('.co-opt-radio-input');
                if (radioInput) radioInput.checked = true;

                // Show/hide address block
                if (coAddressBlock) {
                    var hasAddr = card.getAttribute('data-has-address');
                    if (hasAddr === '0') {
                        coAddressBlock.classList.add('co-address-block--hidden');
                    } else {
                        coAddressBlock.classList.remove('co-address-block--hidden');
                    }
                }

                // Update shipping cost label in summary
                var priceEl = card.querySelector('.co-opt-price');
                var label   = document.getElementById('co-shipping-label');
                if (label && priceEl) {
                    label.textContent = priceEl.textContent.trim();
                    label.style.color = card.querySelector('.co-opt-price--free') ? '#7a9b6e' : '';
                }
            });
        });
    }

    /* ------------------------------------------------------------------
       Checkout: time slot selector
    ------------------------------------------------------------------ */
    var coTimeSlots   = document.querySelectorAll('.co-time-slot');
    var coTimeInput   = document.getElementById('delivery_time_input');

    coTimeSlots.forEach(function (slot) {
        slot.addEventListener('click', function () {
            coTimeSlots.forEach(function (s) { s.classList.remove('co-time-slot--active'); });
            slot.classList.add('co-time-slot--active');
            if (coTimeInput) coTimeInput.value = slot.dataset.time || slot.textContent.trim();
        });
    });

    /* ------------------------------------------------------------------
       Checkout: CTA button → triggers WC #place_order
    ------------------------------------------------------------------ */
    var coCtaBtn    = document.getElementById('co-place-order-cta');
    var coPlaceOrder = document.getElementById('place_order');

    if (coCtaBtn) {
        coCtaBtn.addEventListener('click', function () {
            // If WC's own button exists, click it (triggers WC validation + AJAX)
            if (coPlaceOrder) {
                coPlaceOrder.click();
            } else {
                // Fallback: submit the form directly
                var form = document.getElementById('checkout-form');
                if (form) form.submit();
            }
        });

        // Reflect WC's loading state on our button
        document.addEventListener('checkout_error', function () {
            coCtaBtn.textContent = 'Підтвердити замовлення';
            coCtaBtn.disabled = false;
        });
    }

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
