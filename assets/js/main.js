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
       Promo modals
    ------------------------------------------------------------------ */
    document.querySelectorAll('.promo-modal-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = document.getElementById(btn.dataset.modal);
            if (!modal) return;
            modal.classList.add('promo-modal--open');
            document.body.style.overflow = 'hidden';
        });
    });
    document.querySelectorAll('.promo-modal-close').forEach(function (el) {
        el.addEventListener('click', function () {
            var modal = el.closest('.promo-modal');
            if (!modal) return;
            modal.classList.remove('promo-modal--open');
            document.body.style.overflow = '';
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.promo-modal--open').forEach(function (modal) {
                modal.classList.remove('promo-modal--open');
                document.body.style.overflow = '';
            });
        }
    });

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

    // Custom WP AJAX handler: pass variation_id directly for variable products.
    // WooCommerce detects it's a variation and resolves parent + attributes internally.
    function lesyniAddToCart(itemId, qty, onSuccess, onError) {
        var data = window.lesyniData || {};
        var body = new URLSearchParams({
            nonce:    data.nonce   || '',
            item_id:  itemId,
            quantity: qty,
        });
        fetch(data.ajaxUrl || '/?wc-ajax=lesyni_add_to_cart', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            if (resp.success) {
                var count  = (resp.data && resp.data.count) || 0;
                var countEl = document.querySelector('.header-cart__count');
                if (countEl) {
                    countEl.textContent = count;
                    countEl.classList.toggle('header-cart__count--hidden', count === 0);
                }
                if (onSuccess) onSuccess();
                // Refresh drawer if open
                if (window._lesyniRefreshDrawer) window._lesyniRefreshDrawer();
            } else {
                if (onError) onError();
            }
        })
        .catch(function () { if (onError) onError(); });
    }

    document.querySelectorAll('.btn-add-cart[data-product-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId   = btn.dataset.productId;
            var productType = btn.dataset.type;
            var card        = btn.closest('.product-card');
            var itemId;

            if (productType === 'variable') {
                var activeOpt = card ? card.querySelector('.size-option.active') : null;
                var variationId = activeOpt ? activeOpt.dataset.variationId : '';
                if (!variationId) {
                    window.location.href = card
                        ? card.querySelector('.product-name a').href
                        : '#';
                    return;
                }
                itemId = variationId;
            } else {
                itemId = productId;
            }

            btn.disabled = true;
            btn.textContent = '...';

            lesyniAddToCart(itemId, 1, function () {
                btn.textContent = '✓ Додано';
                btn.classList.add('btn-add-cart--added');
                setTimeout(function () {
                    btn.textContent = 'В кошик';
                    btn.classList.remove('btn-add-cart--added');
                    btn.disabled = false;
                }, 2000);
            }, function () {
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
    var spSelectedAttributes  = {};

    function updateSpTotal() {
        var qty = parseInt(spQtyInput ? spQtyInput.value : 1, 10) || 1;
        if (spTotalEl) spTotalEl.textContent = Math.round(spSelectedPrice * qty);
    }

    if (spSizeCards.length) {
        var firstCard = spSizeCards[0];
        spSelectedVariationId = firstCard.dataset.variationId || '';
        spSelectedPrice       = parseFloat(firstCard.dataset.price) || 0;
        try { spSelectedAttributes = JSON.parse(firstCard.dataset.attributes || '{}'); } catch(e) {}
        updateSpTotal();

        spSizeCards.forEach(function (card) {
            card.addEventListener('click', function () {
                spSizeCards.forEach(function (c) { c.classList.remove('active'); });
                card.classList.add('active');
                spSelectedVariationId = card.dataset.variationId || '';
                spSelectedPrice       = parseFloat(card.dataset.price) || 0;
                try { spSelectedAttributes = JSON.parse(card.dataset.attributes || '{}'); } catch(e) {}
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
            var itemId      = (productType === 'variable') ? spSelectedVariationId : productId;

            if (productType === 'variable' && !spSelectedVariationId) return;

            spBtnCart.disabled = true;
            var labelEl = spBtnCart.querySelector('.sp-btn-cart__label');
            if (labelEl) labelEl.textContent = '...';

            lesyniAddToCart(itemId, qty, function () {
                if (labelEl) labelEl.textContent = '✓ Додано!';
                spBtnCart.classList.add('sp-btn-cart--added');
                setTimeout(function () {
                    if (labelEl) labelEl.textContent = 'Додати в кошик';
                    spBtnCart.classList.remove('sp-btn-cart--added');
                    spBtnCart.disabled = false;
                }, 2000);
            }, function () {
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
       Cart page: qty stepper + row total + summary recalc
    ------------------------------------------------------------------ */
    var cartForm      = document.getElementById('cart-form');
    var cartRows      = document.querySelectorAll('.cart-row[data-key]');
    var cartUpdateBtn = document.getElementById('cart-update-btn');
    var cartSummaryEl = document.querySelector('.cart-summary');

    if (cartForm && cartRows.length) {
        var cartUpdateTimer = null;
        var shipping = cartSummaryEl ? (parseFloat(cartSummaryEl.dataset.shipping) || 0) : 0;
        var discount = cartSummaryEl ? (parseFloat(cartSummaryEl.dataset.discount) || 0) : 0;
        var subtotalEl = document.getElementById('cart-sum-subtotal');
        var totalEl    = document.getElementById('cart-sum-total');

        function recalcCartSummary() {
            var subtotal = 0;
            cartRows.forEach(function (r) {
                var u = parseFloat(r.dataset.unit) || 0;
                var q = parseInt(r.querySelector('.ci-qty-val').textContent, 10) || 0;
                subtotal += Math.round(u * q);
            });
            var total = Math.round(subtotal - discount + shipping);
            if (subtotalEl) subtotalEl.textContent = subtotal;
            if (totalEl)    totalEl.textContent    = total;
        }

        cartRows.forEach(function (row) {
            var val   = row.querySelector('.ci-qty-val');
            var input = row.querySelector('.ci-qty-input');
            var sumEl = row.querySelector('.ci-row-sum');
            var unit  = parseFloat(row.dataset.unit) || 0;
            var minus = row.querySelector('.ci-qty-btn--minus');
            var plus  = row.querySelector('.ci-qty-btn--plus');

            function syncRow() {
                var q = parseInt(val.textContent, 10) || 1;
                if (input) input.value = q;
                if (sumEl) sumEl.textContent = Math.round(unit * q);
                recalcCartSummary();
            }

            if (minus) {
                minus.addEventListener('click', function () {
                    var q = parseInt(val.textContent, 10) || 1;
                    if (q > 1) { val.textContent = q - 1; syncRow(); scheduleUpdate(); }
                });
            }
            if (plus) {
                plus.addEventListener('click', function () {
                    var q = parseInt(val.textContent, 10) || 1;
                    if (q < 20) { val.textContent = q + 1; syncRow(); scheduleUpdate(); }
                });
            }
        });

        function scheduleUpdate() {
            clearTimeout(cartUpdateTimer);
            cartUpdateTimer = setTimeout(function () {
                if (cartUpdateBtn) cartUpdateBtn.click();
            }, 1200);
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

/* ==========================================================================
   One-page Cart + Checkout (oco-)
   ========================================================================== */
(function () {
    'use strict';

    var ocoGrid = document.querySelector('.oco-grid');
    if (!ocoGrid) return;

    /* ── Constants ──────────────────────────────────────────────── */
    var MONTHS   = ['січ','лют','бер','кві','тра','чер','лип','сер','вер','жов','лис','гру'];
    var WEEKDAYS = ['Нд','Пн','Вт','Ср','Чт','Пт','Сб'];

    // Read config from HTML data attribute (CSP-safe — no inline script required)
    var cfg = {};
    (function () {
        var el = document.getElementById('lesyni-config');
        if (el && el.dataset.cfg) {
            try { cfg = JSON.parse(el.dataset.cfg); } catch (e) {}
        }
    }());
    var GREEN_FREE_FROM   = cfg.greenFreeFrom  || 600;
    var GREEN_COST        = cfg.greenCost      || 100;
    var YELLOW_FREE_FROM  = cfg.yellowFreeFrom || 800;
    var YELLOW_COST       = cfg.yellowCost     || 150;
    var OUT_OF_ZONE_LABEL = cfg.outOfZoneLabel || 'Уточнимо можливість доставки з менеджером';

    var deliveryType   = 'courier';
    var deliveryCost   = 0;
    var detectedZone   = null; // 'green' | 'yellow' | 'outside' | null
    var selectedDate   = null;
    var selectedTime   = '14:00–15:00';

    /* ── Date picker ────────────────────────────────────────────── */
    var picker = document.getElementById('oco-date-picker');
    if (picker) {
        var today = new Date();
        for (var i = 0; i < 14; i++) {
            var d = new Date(today);
            d.setDate(today.getDate() + i);
            var isToday    = i === 0;
            var isTomorrow = i === 1;
            var card = document.createElement('button');
            card.type = 'button';
            card.className = 'oco-date-card' + (isToday ? ' oco-date--today oco-date--active' : '');
            card.dataset.date = d.toISOString().slice(0, 10);
            var dayName = isToday ? 'Сьогодні' : isTomorrow ? 'Завтра' : WEEKDAYS[d.getDay()];
            card.innerHTML =
                '<div class="oco-date-day-name">' + dayName + '</div>' +
                '<div class="oco-date-day-num">' + d.getDate() + '</div>' +
                '<div class="oco-date-day-month">' + MONTHS[d.getMonth()] + '</div>';
            card.addEventListener('click', (function (c) {
                return function () {
                    picker.querySelectorAll('.oco-date-card').forEach(function (x) { x.classList.remove('oco-date--active'); });
                    c.classList.add('oco-date--active');
                    selectedDate = c;
                    updateWhen();
                };
            })(card));
            picker.appendChild(card);
            if (isToday) selectedDate = card;
        }
    }

    /* ── When label ─────────────────────────────────────────────── */
    function updateWhen() {
        var whenEl = document.getElementById('oco-when-label');
        if (!whenEl) return;
        var dateLabel = 'сьогодні';
        if (selectedDate) {
            var dn = selectedDate.querySelector('.oco-date-day-name').textContent.toLowerCase();
            if (dn !== 'сьогодні' && dn !== 'завтра') {
                dateLabel = selectedDate.querySelector('.oco-date-day-num').textContent + ' ' +
                            selectedDate.querySelector('.oco-date-day-month').textContent;
            } else {
                dateLabel = dn;
            }
        }
        if (deliveryType === 'pickup') {
            whenEl.textContent = 'самовивіз, ' + dateLabel;
        } else {
            whenEl.textContent = dateLabel + ', ' + selectedTime;
        }
        var dtInput = document.getElementById('oco-delivery-time-val');
        if (dtInput) dtInput.value = dateLabel + ', ' + selectedTime;
    }

    /* ── Time slots ─────────────────────────────────────────────── */
    document.querySelectorAll('.oco-time-slot').forEach(function (slot) {
        slot.addEventListener('click', function () {
            document.querySelectorAll('.oco-time-slot').forEach(function (s) { s.classList.remove('oco-time-slot--active'); });
            slot.classList.add('oco-time-slot--active');
            selectedTime = slot.dataset.time || slot.textContent.trim();
            updateWhen();
        });
    });

    /* ── Cart recalc ────────────────────────────────────────────── */
    var summaryEl = document.querySelector('.oco-summary');
    var initDiscount = summaryEl ? (parseFloat(summaryEl.dataset.discount) || 0) : 0;

    function recalc() {
        var rows = document.querySelectorAll('#oco-cart-items .oco-row');
        var subtotal = 0;
        var itemCount = 0;

        rows.forEach(function (row) {
            var unit = parseFloat(row.dataset.unit) || 0;
            var qty  = parseInt(row.querySelector('.oco-qty-val').textContent, 10) || 1;
            var sum  = Math.round(unit * qty);
            var sumEl = row.querySelector('.oco-row-sum');
            if (sumEl) sumEl.textContent = sum;
            subtotal  += sum;
            itemCount += qty;
        });

        var discount = initDiscount;
        var shipping = 0;

        // Calc shipping based on detected zone (if courier delivery)
        var freeHint    = document.getElementById('oco-cart-free-hint');
        var shippingVal = document.getElementById('oco-sum-shipping');
        var shippingLbl = document.getElementById('oco-shipping-label');

        if (deliveryType === 'pickup' || deliveryType === 'np') {
            shipping = deliveryCost;
        } else if (detectedZone === 'green') {
            shipping = subtotal >= GREEN_FREE_FROM ? 0 : GREEN_COST;
            if (freeHint) {
                if (shipping === 0) {
                    freeHint.textContent = '✓ Доставка безкоштовна (зелена зона)';
                    freeHint.style.color = '#7a9b6e';
                } else {
                    freeHint.textContent = 'Ще ' + (GREEN_FREE_FROM - subtotal) + ' грн до безкоштовної доставки';
                    freeHint.style.color = '#c4845a';
                }
            }
            if (shippingVal) { shippingVal.textContent = shipping === 0 ? 'Безкоштовно' : shipping + ' грн'; shippingVal.style.color = shipping === 0 ? '#7a9b6e' : '#3d3d3d'; }
        } else if (detectedZone === 'yellow') {
            shipping = subtotal >= YELLOW_FREE_FROM ? 0 : YELLOW_COST;
            if (freeHint) {
                freeHint.textContent = shipping === 0
                    ? '✓ Доставка безкоштовна (жовта зона)'
                    : 'Ще ' + (YELLOW_FREE_FROM - subtotal) + ' грн до безкоштовної доставки';
                freeHint.style.color = shipping === 0 ? '#7a9b6e' : '#c4845a';
            }
            if (shippingVal) { shippingVal.textContent = shipping === 0 ? 'Безкоштовно' : shipping + ' грн'; shippingVal.style.color = shipping === 0 ? '#7a9b6e' : '#3d3d3d'; }
        } else if (detectedZone === 'outside') {
            shipping = 0;
            if (freeHint) { freeHint.textContent = ''; }
            if (shippingVal) { shippingVal.textContent = OUT_OF_ZONE_LABEL; shippingVal.style.color = '#c4845a'; shippingVal.style.fontSize = '12px'; }
        } else {
            // Zone not yet detected — default courier display
            shipping = 0;
            if (freeHint) {
                if (subtotal > 0) {
                    freeHint.textContent = 'Введіть адресу для розрахунку доставки';
                    freeHint.style.color = '#999';
                } else {
                    freeHint.textContent = '';
                }
            }
        }

        var total = Math.max(0, subtotal - discount + shipping);

        // Update sidebar
        var subtotalEl  = document.getElementById('oco-sum-subtotal');
        var totalEl     = document.getElementById('oco-sum-total');
        var countEl     = document.getElementById('oco-items-count');
        var cartCountEl = document.getElementById('oco-cart-count');
        var cartSubEl   = document.getElementById('oco-cart-subtotal');
        var totalItemsEl = document.getElementById('oco-total-items');

        if (subtotalEl)   subtotalEl.textContent   = subtotal;
        if (totalEl)      totalEl.textContent      = total;
        if (countEl)      countEl.textContent      = itemCount;
        if (cartCountEl)  cartCountEl.textContent  = itemCount;
        if (cartSubEl)    cartSubEl.textContent    = subtotal;

        // Sync mobile sticky bar
        var stickyTotal = document.getElementById('oco-sticky-total');
        if (stickyTotal) stickyTotal.textContent = total;
        if (totalItemsEl) {
            var n = itemCount;
            var str;
            if     (n % 10 === 1 && n % 100 !== 11) str = n + ' позиція';
            else if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) str = n + ' позиції';
            else   str = n + ' позицій';
            totalItemsEl.textContent = str;
        }

        // Sync hidden qty inputs for WC cart update
        var hiddenDiv = document.getElementById('oco-cart-hidden-inputs');
        if (hiddenDiv) {
            hiddenDiv.innerHTML = '';
            rows.forEach(function (row) {
                var key = row.dataset.key;
                var qty = parseInt(row.querySelector('.oco-qty-val').textContent, 10) || 1;
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'cart[' + key + '][qty]';
                inp.value = qty;
                hiddenDiv.appendChild(inp);
            });
        }
    }

    /* ── Cart qty stepper ───────────────────────────────────────── */
    var cartUpdateTimer = null;

    document.querySelectorAll('#oco-cart-items .oco-row').forEach(function (row) {
        var val   = row.querySelector('.oco-qty-val');
        var minus = row.querySelector('.oco-qty-btn--minus');
        var plus  = row.querySelector('.oco-qty-btn--plus');

        if (minus) minus.addEventListener('click', function () {
            var q = parseInt(val.textContent, 10) || 1;
            if (q > 1) { val.textContent = q - 1; recalc(); scheduleCartUpdate(); }
        });
        if (plus) plus.addEventListener('click', function () {
            var q = parseInt(val.textContent, 10) || 1;
            if (q < 20) { val.textContent = q + 1; recalc(); scheduleCartUpdate(); }
        });
    });

    function scheduleCartUpdate() {
        clearTimeout(cartUpdateTimer);
        cartUpdateTimer = setTimeout(function () {
            var form = document.getElementById('oco-cart-update-form');
            if (!form) return;
            // recalc() has already written latest quantities into oco-cart-hidden-inputs
            var formData = new FormData(form);
            formData.append('update_cart', 'Update Cart');
            fetch(form.action, {
                method:      'POST',
                body:        new URLSearchParams(formData),
                credentials: 'same-origin',
            }).catch(function () {});
            // Fire-and-forget: WC updates the session, page stays intact
        }, 1500);
    }

    /* ── Cart AJAX remove ───────────────────────────────────────── */
    document.querySelectorAll('.oco-ci-remove').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var row = btn.closest('.oco-row');
            var href = btn.getAttribute('href');
            if (row) {
                row.style.transition = 'opacity .2s, transform .2s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(function () {
                    row.remove();
                    recalc();
                }, 200);
            }
            // Also perform the WC remove via fetch
            if (href) {
                fetch(href, { method: 'GET', credentials: 'same-origin' }).catch(function () {});
            }
        });
    });

    /* ── Coupon apply ───────────────────────────────────────────── */
    var couponBtn   = document.getElementById('oco-coupon-btn');
    var couponInput = document.getElementById('oco-coupon-input');
    if (couponBtn && couponInput) {
        couponBtn.addEventListener('click', function () {
            var code = couponInput.value.trim();
            if (!code) return;
            var body = new URLSearchParams({ coupon_code: code, apply_coupon: 'Apply coupon' });
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin',
            }).then(function () {
                window.location.reload();
            }).catch(function () {
                window.location.reload();
            });
        });
    }

    /* ── Delivery method ────────────────────────────────────────── */
    var addrSection = document.getElementById('oco-address-section');
    var whenSection = document.getElementById('oco-when-section');
    var addrTitle   = document.getElementById('oco-address-title');
    var addrHint    = document.getElementById('oco-address-hint');
    var shippingLbl = document.getElementById('oco-shipping-label');
    var shippingVal = document.getElementById('oco-sum-shipping');
    var dtTypeInput = document.getElementById('oco-delivery-type-val');

    document.querySelectorAll('#oco-delivery-options .oco-opt-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('#oco-delivery-options .oco-opt-card').forEach(function (c) {
                c.classList.remove('oco-opt--active');
            });
            card.classList.add('oco-opt--active');
            deliveryType = card.dataset.delivery || 'courier';
            deliveryCost = parseFloat(card.dataset.cost) || 0;
            if (dtTypeInput) dtTypeInput.value = deliveryType;

            if (deliveryType === 'pickup') {
                if (addrSection) addrSection.style.display = 'none';
                if (whenSection) whenSection.style.display = '';
                if (shippingLbl) shippingLbl.textContent = 'Самовивіз';
                if (shippingVal) { shippingVal.textContent = 'Безкоштовно'; shippingVal.style.color = '#7a9b6e'; }
            } else if (deliveryType === 'np') {
                if (addrSection) {
                    addrSection.style.display = '';
                    if (addrTitle) addrTitle.textContent = 'Відділення Нової Пошти';
                    if (addrHint)  addrHint.textContent  = 'Вкажіть місто та номер відділення';
                }
                if (whenSection) whenSection.style.display = 'none';
                if (shippingLbl) shippingLbl.textContent = 'Нова Пошта';
                if (shippingVal) { shippingVal.textContent = deliveryCost + ' грн'; shippingVal.style.color = '#3d3d3d'; }
            } else {
                if (addrSection) {
                    addrSection.style.display = '';
                    if (addrTitle) addrTitle.textContent = 'Адреса доставки';
                    if (addrHint)  addrHint.textContent  = 'Доставка по Дніпру · безкоштовно від 600 грн';
                }
                if (whenSection) whenSection.style.display = '';
                if (shippingLbl) shippingLbl.textContent = 'Доставка по Дніпру';
            }

            recalc();
            updateWhen();
        });
    });

    /* ── Map (Leaflet) ──────────────────────────────────────────── */
    var ocoMap      = null;
    var ocoMarker   = null;
    var greenLayer  = null;
    var yellowLayer = null;

    function initZoneMap() {
        if (ocoMap) return;
        var mapEl = document.getElementById('oco-zone-map');
        if (!mapEl || typeof L === 'undefined') return;

        try {
            ocoMap = L.map('oco-zone-map', {
                zoomControl:        true,
                scrollWheelZoom:    false,
                attributionControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(ocoMap);

            var greenPoly  = (cfg.greenPolygon  && cfg.greenPolygon.length)  ? cfg.greenPolygon  : [];
            var yellowPoly = (cfg.yellowPolygon && cfg.yellowPolygon.length) ? cfg.yellowPolygon : [];
            var boundsLayers = [];

            if (yellowPoly.length >= 3) {
                try {
                    yellowLayer = L.polygon(yellowPoly, {
                        color: '#b8940a', fillColor: '#f5d85a',
                        fillOpacity: 0.22, weight: 2, dashArray: '6 4',
                    }).addTo(ocoMap);
                    yellowLayer.bindTooltip(
                        '<b>Жовта зона</b><br>' + YELLOW_COST + ' грн (від ' + YELLOW_FREE_FROM + ' грн — безкоштовно)',
                        { sticky: true, className: 'oco-map-tooltip oco-map-tooltip--yellow' }
                    );
                    boundsLayers.push(yellowLayer);
                } catch (polyErr) {
                    console.warn('Yellow polygon error:', polyErr);
                }
            }

            if (greenPoly.length >= 3) {
                try {
                    greenLayer = L.polygon(greenPoly, {
                        color: '#3a7230', fillColor: '#7ab86e',
                        fillOpacity: 0.28, weight: 2,
                    }).addTo(ocoMap);
                    greenLayer.bindTooltip(
                        '<b>Зелена зона</b><br>' + GREEN_COST + ' грн (від ' + GREEN_FREE_FROM + ' грн — безкоштовно)',
                        { sticky: true, className: 'oco-map-tooltip oco-map-tooltip--green' }
                    );
                    boundsLayers.push(greenLayer);
                } catch (polyErr) {
                    console.warn('Green polygon error:', polyErr);
                }
            }

            if (boundsLayers.length) {
                try {
                    var group = L.featureGroup(boundsLayers);
                    var bounds = group.getBounds();
                    if (bounds && bounds.isValid()) {
                        ocoMap.fitBounds(bounds, { padding: [24, 24] });
                    } else {
                        ocoMap.setView([48.4647, 35.0462], 11);
                    }
                } catch (boundsErr) {
                    console.warn('fitBounds error:', boundsErr);
                    ocoMap.setView([48.4647, 35.0462], 11);
                }
            } else {
                ocoMap.setView([48.4647, 35.0462], 11);
            }
        } catch (mapErr) {
            console.error('Map init error:', mapErr);
            ocoMap = null;
        }
    }

    function updateMapMarker(lat, lng, zone) {
        if (!ocoMap) initZoneMap();
        if (!ocoMap) return;

        var color = zone === 'green' ? '#3a7230' : (zone === 'yellow' ? '#b8940a' : '#c45a5a');

        if (greenLayer)  greenLayer.setStyle({ fillOpacity: zone === 'green'  ? 0.45 : 0.18 });
        if (yellowLayer) yellowLayer.setStyle({ fillOpacity: zone === 'yellow' ? 0.40 : 0.15 });

        if (ocoMarker) {
            ocoMarker.setLatLng([lat, lng]);
            ocoMarker.setStyle({ fillColor: color });
        } else {
            ocoMarker = L.circleMarker([lat, lng], {
                radius:      11,
                fillColor:   color,
                color:       '#ffffff',
                weight:      3,
                fillOpacity: 0.95,
            }).addTo(ocoMap);
        }

        var zoneLabels = {
            green:   '🟢 Зелена зона',
            yellow:  '🟡 Жовта зона',
            outside: '❗ Поза зонами',
        };
        ocoMarker.bindPopup(
            '<b>' + (zoneLabels[zone] || 'Адреса') + '</b><br>Ваша адреса доставки',
            { closeButton: false }
        ).openPopup();

        ocoMap.setView([lat, lng], 14, { animate: true, duration: 0.6 });
    }

    /* ── Address geocoding + zone detection ────────────────────── */
    var zoneCheckTimer = null;
    var streetInput    = document.getElementById('oco-street');
    var houseInput     = document.getElementById('oco-house');

    function showZoneIndicator(state, message) {
        var el = document.getElementById('oco-zone-indicator');
        if (!el) return;
        el.className = 'oco-zone-indicator oco-zone--' + state;
        el.textContent = message;
        el.style.display = 'flex';
    }

    function scheduleZoneCheck() {
        clearTimeout(zoneCheckTimer);
        var street = streetInput ? streetInput.value.trim() : '';
        if (!street) return;

        showZoneIndicator('checking', '🔍 Визначаємо зону доставки…');

        zoneCheckTimer = setTimeout(function () {
            var house = houseInput ? houseInput.value.trim() : '';
            checkZone(street + (house ? ', ' + house : ''));
        }, 900);
    }

    function checkZone(address) {
        var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
        var body = new URLSearchParams({
            action:  'lesyni_check_zone',
            nonce:   cfg.nonce || '',
            address: address,
        });

        fetch(ajaxUrl, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:        body.toString(),
            credentials: 'same-origin',
        })
        .then(function (r) {
            // Read response text first for better error diagnosis
            return r.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // PHP error or HTML returned — log to console for debugging
                    console.error('lesyni_check_zone non-JSON response:', text.slice(0, 300));
                    throw new Error('invalid_json');
                }
            });
        })
        .then(function (data) {
            if (!data.success) {
                var msg = data.data && data.data.message;
                if (msg === 'geocode_failed') {
                    showZoneIndicator('unknown', '⚠️ Адресу не знайдено — перевірте написання вулиці');
                } else if (msg === 'bad_nonce') {
                    window.location.reload();
                } else {
                    showZoneIndicator('unknown', '⚠️ Не вдалося визначити адресу');
                }
                detectedZone = null;
                recalc();
                return;
            }
            detectedZone = data.data.zone;
            // Update zone indicator and shipping display first (must not be blocked by map errors)
            try { applyZoneUI(detectedZone, data.data.rates); } catch (e) { console.error('applyZoneUI:', e); }
            recalc();
            // Update map marker separately so any Leaflet error stays isolated
            try { updateMapMarker(data.data.lat, data.data.lng, detectedZone); } catch (e) { console.error('updateMapMarker:', e); }
        })
        .catch(function (err) {
            if (err && err.message === 'invalid_json') {
                showZoneIndicator('unknown', '⚠️ Помилка сервера (перевірте консоль)');
            } else {
                showZoneIndicator('unknown', '⚠️ Немає з\'єднання з сервером');
            }
            detectedZone = null;
        });
    }

    function applyZoneUI(zone, rates) {
        var shippingVal = document.getElementById('oco-sum-shipping');
        var shippingLbl = document.getElementById('oco-shipping-label');
        var subtotal = 0;
        document.querySelectorAll('#oco-cart-items .oco-row').forEach(function (row) {
            subtotal += Math.round((parseFloat(row.dataset.unit) || 0) *
                        (parseInt(row.querySelector('.oco-qty-val').textContent, 10) || 1));
        });

        if (zone === 'green') {
            var freeFrom = rates ? rates.green.free_from : GREEN_FREE_FROM;
            var cost     = rates ? rates.green.cost      : GREEN_COST;
            showZoneIndicator('green', '🟢 Зелена зона · ' + (subtotal >= freeFrom ? 'безкоштовно' : cost + ' грн (від ' + freeFrom + ' грн — безкоштовно)'));
            if (shippingLbl) shippingLbl.textContent = 'Доставка (зелена зона)';
        } else if (zone === 'yellow') {
            var freeFromY = rates ? rates.yellow.free_from : YELLOW_FREE_FROM;
            var costY     = rates ? rates.yellow.cost      : YELLOW_COST;
            showZoneIndicator('yellow', '🟡 Жовта зона · ' + (subtotal >= freeFromY ? 'безкоштовно' : costY + ' грн (від ' + freeFromY + ' грн — безкоштовно)'));
            if (shippingLbl) shippingLbl.textContent = 'Доставка (жовта зона)';
        } else {
            showZoneIndicator('outside', '❗ ' + OUT_OF_ZONE_LABEL);
            if (shippingLbl) shippingLbl.textContent = 'Доставка';
            if (shippingVal) { shippingVal.textContent = 'Уточнюється'; shippingVal.style.color = '#c4845a'; shippingVal.style.fontSize = '12px'; }
        }
    }

    if (streetInput) streetInput.addEventListener('input', scheduleZoneCheck);
    if (houseInput)  houseInput.addEventListener('input',  scheduleZoneCheck);

    // Init map immediately so zones are visible before address is entered
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initZoneMap);
    } else {
        setTimeout(initZoneMap, 100);
    }

    /* ── Payment method ─────────────────────────────────────────── */
    var pmInput = document.getElementById('oco-payment-method-val');
    document.querySelectorAll('#oco-payment-options .oco-opt-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('#oco-payment-options .oco-opt-card').forEach(function (c) {
                c.classList.remove('oco-opt--active');
            });
            card.classList.add('oco-opt--active');
            if (pmInput) pmInput.value = card.dataset.payment || 'cod';
        });
    });

    /* ── Place order ────────────────────────────────────────────── */
    var placeBtn = document.getElementById('oco-place-btn');
    if (placeBtn) {
        placeBtn.addEventListener('click', function () {
            // Validate required UI fields
            var firstName = document.getElementById('oco-first-name');
            var phone     = document.getElementById('oco-phone');
            var terms     = document.getElementById('oco-terms-check');

            if (firstName && !firstName.value.trim()) {
                firstName.focus();
                firstName.style.borderColor = '#c45a5a';
                return;
            }
            if (phone && !phone.value.trim()) {
                phone.focus();
                phone.style.borderColor = '#c45a5a';
                return;
            }
            if (terms && !terms.checked) {
                terms.closest('.oco-check').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Copy UI values to hidden WC form fields
            var fullName = (firstName ? firstName.value.trim() : '').split(' ');
            var setHidden = function (id, val) {
                var el = document.getElementById(id);
                if (el) el.value = val;
            };
            setHidden('wc-billing_first_name', fullName[0] || '');
            setHidden('wc-billing_last_name',  fullName.slice(1).join(' ') || '');
            setHidden('wc-billing_phone',       phone ? phone.value.trim() : '');
            var emailEl = document.getElementById('oco-email');
            setHidden('wc-billing_email', emailEl ? emailEl.value.trim() : '');

            // Build address
            var street   = document.getElementById('oco-street');
            var house    = document.getElementById('oco-house');
            var apt      = document.getElementById('oco-apt');
            var entrance = document.getElementById('oco-entrance');
            var floor    = document.getElementById('oco-floor');
            var addr = '';
            if (street)   addr += street.value.trim();
            if (house && house.value.trim())    addr += ', ' + house.value.trim();
            if (apt && apt.value.trim())        addr += ', кв.' + apt.value.trim();
            if (entrance && entrance.value.trim()) addr += ', п.' + entrance.value.trim();
            if (floor && floor.value.trim())    addr += ', пов.' + floor.value.trim();
            setHidden('wc-billing_address_1', addr);

            var noteEl = document.getElementById('oco-note');
            setHidden('wc-order_comments', noteEl ? noteEl.value.trim() : '');

            var giftEl = document.getElementById('oco-gift-check');
            setHidden('wc-lesyni_gift', giftEl && giftEl.checked ? '1' : '');

            placeBtn.disabled = true;
            placeBtn.textContent = '⏳ Оформлення…';

            var errBox = document.getElementById('oco-checkout-errors');
            if (errBox) errBox.style.display = 'none';

            var form = document.getElementById('oco-wc-form');
            var formData = new FormData(form);

            fetch('/?wc-ajax=checkout', {
                method:      'POST',
                body:        new URLSearchParams(formData),
                credentials: 'same-origin',
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.result === 'success') {
                    window.location.href = data.redirect;
                } else {
                    placeBtn.disabled = false;
                    placeBtn.textContent = 'Підтвердити замовлення →';
                    if (errBox && data.messages) {
                        errBox.innerHTML = data.messages;
                        errBox.style.display = 'block';
                        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (data.reload) window.location.reload();
                }
            })
            .catch(function () {
                placeBtn.disabled = false;
                placeBtn.textContent = 'Підтвердити замовлення →';
                if (errBox) {
                    errBox.innerHTML = '<p>Помилка з\'єднання. Спробуйте ще раз.</p>';
                    errBox.style.display = 'block';
                }
            });
        });
    }

    /* ── Init ───────────────────────────────────────────────────── */
    recalc();
    updateWhen();

})();

/* ==========================================================================
   Cart Drawer
   ========================================================================== */
(function () {
    'use strict';

    var cartDrawer    = document.getElementById('cart-drawer');
    var drawerBody    = document.getElementById('cart-drawer-body');
    var drawerFoot    = document.getElementById('cart-drawer-foot');
    var drawerTotal   = document.getElementById('cart-drawer-total');
    var drawerTrigger = document.getElementById('cart-drawer-trigger');
    var drawerClose   = document.getElementById('cart-drawer-close');
    var drawerBackdrop = document.getElementById('cart-drawer-backdrop');

    if (!cartDrawer) return;

    function openCartDrawer() {
        cartDrawer.classList.add('open');
        cartDrawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        loadDrawerContents();
    }

    function closeCartDrawer() {
        cartDrawer.classList.remove('open');
        cartDrawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function loadDrawerContents() {
        drawerBody.innerHTML = '<div class="cart-drawer__loading"><span class="cart-drawer__spinner"></span></div>';
        drawerFoot.style.display = 'none';

        fetch('/?wc-ajax=lesyni_cart_drawer', { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                if (!resp.success) { drawerBody.innerHTML = ''; return; }
                var data  = resp.data;
                var items = data.items || [];

                if (!items.length) {
                    drawerBody.innerHTML = '<div class="cart-drawer__empty"><div class="cart-drawer__empty-icon">🧺</div><p>Кошик порожній</p></div>';
                    drawerFoot.style.display = 'none';
                    return;
                }

                var html = '';
                items.forEach(function (item) {
                    var img = item.thumb
                        ? '<img class="cart-drawer__item-img" src="' + item.thumb + '" alt="' + item.name + '">'
                        : '<div class="cart-drawer__item-img cart-drawer__item-img--placeholder">🥧</div>';
                    var variation = item.variation
                        ? '<p class="cart-drawer__item-variation">' + item.variation + '</p>'
                        : '';
                    html += '<div class="cart-drawer__item" data-key="' + item.key + '">'
                        + img
                        + '<div class="cart-drawer__item-info">'
                        +   '<div class="cart-drawer__item-top">'
                        +     '<p class="cart-drawer__item-name">' + item.name + '</p>'
                        +     '<button class="cart-drawer__remove" data-key="' + item.key + '" aria-label="Видалити">×</button>'
                        +   '</div>'
                        +   variation
                        +   '<div class="cart-drawer__item-row">'
                        +     '<div class="cart-drawer__qty">'
                        +       '<button class="cart-drawer__qty-btn" data-key="' + item.key + '" data-delta="-1">−</button>'
                        +       '<span class="cart-drawer__qty-val" data-key="' + item.key + '" data-unit="' + item.price + '">' + item.qty + '</span>'
                        +       '<button class="cart-drawer__qty-btn" data-key="' + item.key + '" data-delta="1">+</button>'
                        +     '</div>'
                        +     '<span class="cart-drawer__item-price">' + item.subtotal + ' грн</span>'
                        +   '</div>'
                        + '</div>'
                        + '</div>';
                });
                drawerBody.innerHTML = html;

                if (drawerTotal) drawerTotal.textContent = data.total + ' грн';
                drawerFoot.style.display = '';

                attachDrawerItemHandlers();
            })
            .catch(function () {
                drawerBody.innerHTML = '';
            });
    }

    function updateCartCount(count) {
        var el = document.querySelector('.header-cart__count');
        if (el) {
            el.textContent = count;
            el.classList.toggle('header-cart__count--hidden', count === 0);
        }
    }

    function recalcDrawerTotal() {
        var total = 0;
        drawerBody.querySelectorAll('.cart-drawer__qty-val').forEach(function (v) {
            total += Math.round((parseFloat(v.dataset.unit) || 0) * (parseInt(v.textContent, 10) || 0));
        });
        if (drawerTotal) drawerTotal.textContent = total + ' грн';
    }

    function attachDrawerItemHandlers() {
        // Remove buttons
        drawerBody.querySelectorAll('.cart-drawer__remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key     = btn.dataset.key;
                var itemEl  = drawerBody.querySelector('.cart-drawer__item[data-key="' + key + '"]');

                // Remove from DOM immediately
                if (itemEl) {
                    itemEl.style.transition = 'opacity .2s, transform .2s';
                    itemEl.style.opacity    = '0';
                    itemEl.style.transform  = 'translateX(20px)';
                    setTimeout(function () {
                        itemEl.remove();
                        recalcDrawerTotal();
                        var remaining = drawerBody.querySelectorAll('.cart-drawer__item');
                        if (!remaining.length) {
                            drawerBody.innerHTML = '<div class="cart-drawer__empty"><div class="cart-drawer__empty-icon">🧺</div><p>Кошик порожній</p></div>';
                            drawerFoot.style.display = 'none';
                        }
                    }, 200);
                }

                fetch('/?wc-ajax=lesyni_cart_remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ key: key }).toString(),
                })
                .then(function (r) { return r.json(); })
                .then(function (resp) {
                    if (resp.success) updateCartCount(resp.data.count || 0);
                });
            });
        });

        // Qty +/- buttons
        drawerBody.querySelectorAll('.cart-drawer__qty-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key   = btn.dataset.key;
                var delta = parseInt(btn.dataset.delta, 10);
                var valEl = drawerBody.querySelector('.cart-drawer__qty-val[data-key="' + key + '"]');
                if (!valEl) return;
                var qty   = parseInt(valEl.textContent, 10) + delta;
                if (qty < 0) return;

                // Optimistic UI
                var itemEl = btn.closest('.cart-drawer__item');
                if (qty === 0) {
                    itemEl.style.transition = 'opacity .2s, transform .2s';
                    itemEl.style.opacity    = '0';
                    itemEl.style.transform  = 'translateX(20px)';
                    setTimeout(function () {
                        itemEl.remove();
                        recalcDrawerTotal();
                        if (!drawerBody.querySelectorAll('.cart-drawer__item').length) {
                            drawerBody.innerHTML = '<div class="cart-drawer__empty"><div class="cart-drawer__empty-icon">🧺</div><p>Кошик порожній</p></div>';
                            drawerFoot.style.display = 'none';
                        }
                    }, 200);
                } else {
                    valEl.textContent = qty;
                    var unit    = parseFloat(valEl.dataset.unit) || 0;
                    var priceEl = itemEl.querySelector('.cart-drawer__item-price');
                    if (priceEl) priceEl.textContent = Math.round(unit * qty) + ' грн';
                    recalcDrawerTotal();
                }

                fetch('/?wc-ajax=lesyni_cart_update_qty', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ key: key, qty: qty }).toString(),
                })
                .then(function (r) { return r.json(); })
                .then(function (resp) {
                    if (resp.success) updateCartCount(resp.data.count || 0);
                })
                .catch(function () {});
            });
        });
    }

    // Expose so lesyniAddToCart can refresh on success
    window._lesyniRefreshDrawer = function () {
        if (cartDrawer.classList.contains('open')) loadDrawerContents();
    };

    if (drawerTrigger)  drawerTrigger.addEventListener('click', openCartDrawer);
    if (drawerClose)    drawerClose.addEventListener('click', closeCartDrawer);
    if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeCartDrawer);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && cartDrawer.classList.contains('open')) closeCartDrawer();
    });

})();
