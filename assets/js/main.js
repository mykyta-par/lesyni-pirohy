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
       Catalog: ordering dropdown — WooCommerce relies on jQuery to submit
       the orderby form; this vanilla JS fallback covers cases where WC's
       own script hasn't bound the handler yet.
    ------------------------------------------------------------------ */
    document.addEventListener('change', function (e) {
        if (e.target && e.target.matches('.woocommerce-ordering select')) {
            var form = e.target.closest('form');
            if (form) form.submit();
        }
    });

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

            var isRound = btn.classList.contains('btn-add-cart--round');
            var origHTML = btn.innerHTML;
            btn.disabled = true;
            if (!isRound) btn.textContent = '...';

            lesyniAddToCart(itemId, 1, function () {
                btn.classList.add('btn-add-cart--added');
                btn.innerHTML = isRound ? '✓' : '✓ Додано';
                setTimeout(function () {
                    btn.classList.remove('btn-add-cart--added');
                    btn.innerHTML = origHTML;
                    btn.disabled = false;
                }, 2000);
            }, function () {
                btn.disabled = false;
                btn.innerHTML = origHTML;
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

    var npCityRef    = '';
    var npCityName   = '';
    var npBranchName = '';
    var prevPayment  = 'cod'; // payment method before NP was selected

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
        // always write hidden input first, regardless of whether the label element exists
        var dtInput = document.getElementById('oco-delivery-time-val');
        if (dtInput) dtInput.value = dateLabel + ', ' + selectedTime;
        var whenEl = document.getElementById('oco-when-label');
        if (!whenEl) return;
        if (deliveryType === 'pickup') {
            whenEl.textContent = 'самовивіз, ' + dateLabel;
        } else {
            whenEl.textContent = dateLabel + ', ' + selectedTime;
        }
    }

    /* ── Time slots ─────────────────────────────────────────────── */
    var adminDisabledSlots = (window.lesyniData && lesyniData.disabledSlots) ? lesyniData.disabledSlots : [];

    function initTimeSlots() {
        var now = new Date();
        var cur = now.getHours() * 60 + now.getMinutes();
        // "Якнайшвидше" available during working hours 09:00–18:30
        var WORK_START   = 9 * 60;
        var ASAP_CUTOFF  = 18 * 60 + 30;

        var firstEnabled = null;
        var asapSlot     = null;

        document.querySelectorAll('.oco-time-slot').forEach(function (slot) {
            var time = slot.dataset.time || '';
            var disabled = adminDisabledSlots.indexOf(time) !== -1;

            if (!disabled) {
                if (time === 'Якнайшвидше') {
                    asapSlot = slot;
                    disabled = cur < WORK_START || cur > ASAP_CUTOFF;
                } else {
                    // Parse start hour from "HH:MM–HH:MM"
                    var m = time.match(/^(\d+):(\d+)/);
                    if (m) {
                        var slotStart = parseInt(m[1], 10) * 60 + parseInt(m[2], 10);
                        disabled = cur >= slotStart;
                    }
                }
            }

            slot.disabled = disabled;
            slot.classList.toggle('oco-time-slot--disabled', disabled);
            if (!disabled && !firstEnabled) firstEnabled = slot;
        });

        // Auto-select: Якнайшвидше if available, else first enabled slot
        var toSelect = (asapSlot && !asapSlot.disabled) ? asapSlot : firstEnabled;
        if (toSelect) {
            document.querySelectorAll('.oco-time-slot').forEach(function (s) { s.classList.remove('oco-time-slot--active'); });
            toSelect.classList.add('oco-time-slot--active');
            selectedTime = toSelect.dataset.time || toSelect.textContent.trim();
        }
    }

    initTimeSlots();

    document.querySelectorAll('.oco-time-slot').forEach(function (slot) {
        slot.addEventListener('click', function () {
            if (slot.classList.contains('oco-time-slot--disabled')) return;
            document.querySelectorAll('.oco-time-slot').forEach(function (s) { s.classList.remove('oco-time-slot--active'); });
            slot.classList.add('oco-time-slot--active');
            selectedTime = slot.dataset.time || slot.textContent.trim();
            updateWhen();
        });
    });

    /* ── Free delivery progress bar ────────────────────────────── */
    var NP_MIN_ORDER = 1000;

    function updateFreeBar(subtotal) {
        if (deliveryType === 'np') {
            var remaining = Math.max(0, NP_MIN_ORDER - subtotal);
            var pct       = Math.min(100, Math.round(subtotal / NP_MIN_ORDER * 100));
            ['', '-summary'].forEach(function (suffix) {
                var wrap = document.getElementById('oco-free-bar-wrap' + suffix);
                var fill = document.getElementById('oco-free-bar-fill' + suffix);
                var text = document.getElementById('oco-free-bar-text' + suffix);
                if (!wrap) return;
                wrap.style.display = '';
                if (fill) {
                    fill.style.width = pct + '%';
                    fill.classList.toggle('oco-free-bar__fill--done', remaining === 0);
                }
                if (text) {
                    text.innerHTML = remaining > 0
                        ? 'Ще <strong>' + remaining + ' грн</strong> до мінімального замовлення НП'
                        : '<span class="oco-free-bar__done">✓ Мінімальна сума досягнута</span>';
                }
            });
            return;
        }

        var freeFrom  = detectedZone === 'yellow' ? YELLOW_FREE_FROM : GREEN_FREE_FROM;
        var remaining = Math.max(0, freeFrom - subtotal);
        var pct       = freeFrom > 0 ? Math.min(100, Math.round(subtotal / freeFrom * 100)) : 100;
        // Hide bar if zone not yet known (address not entered), outside zone, or pickup selected
        var hide      = detectedZone === null || detectedZone === 'outside' || deliveryType === 'pickup';

        ['', '-summary'].forEach(function (suffix) {
            var wrap = document.getElementById('oco-free-bar-wrap' + suffix);
            var fill = document.getElementById('oco-free-bar-fill' + suffix);
            var text = document.getElementById('oco-free-bar-text' + suffix);
            if (!wrap) return;

            if (hide) { wrap.style.display = 'none'; return; }
            wrap.style.display = '';

            if (fill) {
                fill.style.width = pct + '%';
                fill.classList.toggle('oco-free-bar__fill--done', remaining === 0);
            }
            if (text) {
                text.innerHTML = remaining > 0
                    ? 'Ще <strong>' + remaining + ' грн</strong> до безкоштовної доставки'
                    : '<span class="oco-free-bar__done">Безкоштовна доставка</span>';
            }
        });
    }

    /* ── Cart recalc ────────────────────────────────────────────── */
    var summaryEl = document.querySelector('.oco-summary');
    var initDiscount = summaryEl ? (parseFloat(summaryEl.dataset.discount) || 0) : 0;

    function recalc() {
        var rows = document.querySelectorAll('#oco-cart-items .oco-row');
        var subtotal = 0;
        var itemCount = 0;

        rows.forEach(function (row) {
            if (row.dataset.packaging) return; // skip packaging rows
            var unit  = parseFloat(row.dataset.unit) || 0;
            var qtyEl = row.querySelector('.oco-qty-val');
            var qty   = qtyEl ? (parseInt(qtyEl.textContent, 10) || 1) : 1;
            var sum   = Math.round(unit * qty);
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

        if (deliveryType === 'pickup') {
            shipping = deliveryCost;
            if (freeHint) { freeHint.textContent = ''; }
        } else if (deliveryType === 'np') {
            shipping = deliveryCost;
            var npRemaining = Math.max(0, NP_MIN_ORDER - subtotal);
            if (freeHint) {
                if (npRemaining > 0) {
                    freeHint.textContent = 'Ще ' + npRemaining + ' грн до мінімального замовлення';
                    freeHint.style.color = '#c4845a';
                } else {
                    freeHint.textContent = '✓ Мінімальна сума досягнута';
                    freeHint.style.color = '#7a9b6e';
                }
            }
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

        // Packaging cart rows + summary row for Nova Poshta
        var pkgSmallRow  = document.getElementById('oco-packaging-small');
        var pkgLargeRow  = document.getElementById('oco-packaging-large');
        var packagingRow = document.getElementById('oco-packaging-row');
        var packagingLbl = document.getElementById('oco-packaging-label');
        var packagingEl  = document.getElementById('oco-sum-packaging');
        var packaging = 0;
        if (deliveryType === 'np') {
            var isSmall = itemCount <= 3;
            packaging = isSmall ? 100 : 150;
            if (pkgSmallRow)  pkgSmallRow.style.display  = isSmall ? '' : 'none';
            if (pkgLargeRow)  pkgLargeRow.style.display  = isSmall ? 'none' : '';
            if (packagingRow) packagingRow.style.display = '';
            if (packagingLbl) packagingLbl.textContent   = isSmall ? 'Термопакування мале' : 'Термопакування велике';
            if (packagingEl)  packagingEl.textContent    = packaging;
        } else {
            if (pkgSmallRow)  pkgSmallRow.style.display  = 'none';
            if (pkgLargeRow)  pkgLargeRow.style.display  = 'none';
            if (packagingRow) packagingRow.style.display = 'none';
        }

        var total = Math.max(0, subtotal - discount + shipping + packaging);

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
        if (cartSubEl)    cartSubEl.textContent    = subtotal + packaging;
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
                if (row.dataset.packaging) return;
                var key = row.dataset.key;
                if (!key) return;
                var qtyEl = row.querySelector('.oco-qty-val');
                var qty = qtyEl ? (parseInt(qtyEl.textContent, 10) || 1) : 1;
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'cart[' + key + '][qty]';
                inp.value = qty;
                hiddenDiv.appendChild(inp);
            });
        }

        updateFreeBar(subtotal);
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

    /* ── Payment method helpers ─────────────────────────────────── */
    function getLiqpayId() {
        // Use PHP-detected ID first, then fall back to any card with 'liqpay' in its data-payment
        var phpId = (typeof lesyniData !== 'undefined' && lesyniData.liqpayId) ? lesyniData.liqpayId : '';
        if (phpId) return phpId;
        var found = document.querySelector('#oco-payment-options .oco-opt-card[data-payment*="liqpay"]');
        return found ? found.dataset.payment : '';
    }

    function restrictToLiqpay() {
        var liqpayId = getLiqpayId();
        var pmInput  = document.getElementById('oco-payment-method-val');
        // If we can't identify the LiqPay card, don't hide anything
        if (!liqpayId) return;
        document.querySelectorAll('#oco-payment-options .oco-opt-card').forEach(function (c) {
            if (c.dataset.payment === liqpayId) {
                c.classList.add('oco-opt--active');
                c.style.display = '';
            } else {
                if (c.classList.contains('oco-opt--active')) prevPayment = c.dataset.payment;
                c.classList.remove('oco-opt--active');
                c.style.display = 'none';
            }
        });
        if (pmInput) pmInput.value = liqpayId;
    }

    function restorePaymentMethods() {
        var pmInput = document.getElementById('oco-payment-method-val');
        var hasActive = false;
        document.querySelectorAll('#oco-payment-options .oco-opt-card').forEach(function (c) {
            c.style.display = '';
            if (c.dataset.payment === prevPayment) {
                c.classList.add('oco-opt--active');
                hasActive = true;
            } else {
                c.classList.remove('oco-opt--active');
            }
        });
        // fallback: activate first card
        if (!hasActive) {
            var first = document.querySelector('#oco-payment-options .oco-opt-card');
            if (first) { first.classList.add('oco-opt--active'); prevPayment = first.dataset.payment || 'cod'; }
        }
        if (pmInput) pmInput.value = prevPayment;
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

            // Tell WC which rate to use — prefer explicit rate-id if present
            var smInput = document.getElementById('oco-shipping-method-val');
            if (smInput) {
                var rateId = card.dataset.rateId;
                if (rateId) {
                    smInput.value = rateId;
                } else if (deliveryType === 'pickup') {
                    smInput.value = 'lesyni_pickup_rate';
                } else if (deliveryType === 'np') {
                    smInput.value = 'lesyni_np_rate';
                } else {
                    smInput.value = 'lesyni_zone_rate';
                }
            }

            var npFields = document.getElementById('oco-np-fields');
            if (deliveryType === 'pickup') {
                if (addrSection) addrSection.style.display = 'none';
                if (npFields)    npFields.style.display    = 'none';
                if (whenSection) whenSection.style.display = '';
                if (shippingLbl) shippingLbl.textContent = 'Самовивіз';
                if (shippingVal) { shippingVal.textContent = 'Безкоштовно'; shippingVal.style.color = '#7a9b6e'; }
                restorePaymentMethods();
            } else if (deliveryType === 'np') {
                if (addrSection) addrSection.style.display = 'none';
                if (npFields)    npFields.style.display    = '';
                if (whenSection) whenSection.style.display = 'none';
                if (shippingLbl) shippingLbl.textContent = 'Нова Пошта';
                if (shippingVal) { shippingVal.textContent = 'за тарифами перевізника'; shippingVal.style.color = '#999'; shippingVal.style.fontWeight = '400'; shippingVal.style.fontSize = '12px'; }
                restrictToLiqpay();
            } else {
                if (addrSection) {
                    addrSection.style.display = '';
                    if (addrTitle) addrTitle.textContent = 'Адреса доставки';
                    if (addrHint)  addrHint.textContent  = 'Доставка по Дніпру · безкоштовно від 600 грн';
                }
                if (npFields)    npFields.style.display    = 'none';
                if (whenSection) whenSection.style.display = '';
                if (shippingLbl) shippingLbl.textContent = 'Доставка по Дніпру';
                restorePaymentMethods();
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
            // shipping_method[0] is always lesyni_zone_rate — cost is set server-side via woocommerce_package_rates
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
            var qtyEl = row.querySelector('.oco-qty-val');
            subtotal += Math.round((parseFloat(row.dataset.unit) || 0) *
                        (qtyEl ? parseInt(qtyEl.textContent, 10) || 1 : 1));
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

    // Auto-run zone check on page load if address fields are already prefilled
    function autoCheckZoneIfPrefilled() {
        var street = streetInput ? streetInput.value.trim() : '';
        var house  = houseInput  ? houseInput.value.trim()  : '';
        if (street && house) {
            showZoneIndicator('checking', '🔍 Визначаємо зону доставки…');
            checkZone(street + ', ' + house);
        }
    }

    function initGooglePlaces() {
        if (!streetInput) return;
        if (!window.google || !window.google.maps || !window.google.maps.places) return;
        var dniproBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(47.90, 34.75),
            new google.maps.LatLng(48.60, 35.35)
        );
        var gAutocomplete = new google.maps.places.Autocomplete(streetInput, {
            types:                ['address'],
            componentRestrictions: { country: 'ua' },
            bounds:               dniproBounds,
            fields:               ['address_components'],
        });
        gAutocomplete.addListener('place_changed', function () {
            var place = gAutocomplete.getPlace();
            if (!place || !place.address_components) return;
            var street = '', houseNum = '';
            place.address_components.forEach(function (c) {
                if (c.types.indexOf('route') !== -1)         street   = c.long_name;
                if (c.types.indexOf('street_number') !== -1) houseNum = c.long_name;
            });
            if (street) streetInput.value = street;
            if (houseNum && houseInput && !houseInput.value) houseInput.value = houseNum;
            clearTimeout(zoneCheckTimer);
            var fullAddr = streetInput.value + (houseNum ? ', ' + houseNum : '');
            showZoneIndicator('checking', '🔍 Визначаємо зону доставки…');
            checkZone(fullAddr);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initZoneMap();
            autoCheckZoneIfPrefilled();
            initGooglePlaces();
        });
    } else {
        setTimeout(function () {
            initZoneMap();
            autoCheckZoneIfPrefilled();
            initGooglePlaces();
        }, 100);
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

    /* ── Nova Poshta city + warehouse autocomplete ──────────────── */
    (function () {
        var cityInput    = document.getElementById('np-city-input');
        var cityList     = document.getElementById('np-city-list');
        var branchInput  = document.getElementById('np-branch-input');
        var branchList   = document.getElementById('np-branch-list');
        if (!cityInput) return;

        var allWarehouses = [];
        var npAjax = (window.lesyniData && lesyniData.npAjaxUrl) ? lesyniData.npAjaxUrl : '/wp-admin/admin-ajax.php';

        function showBranchSuggestions(q) {
            var filtered = q
                ? allWarehouses.filter(function (w) { return w.label.toLowerCase().indexOf(q.toLowerCase()) !== -1; })
                : allWarehouses.slice(0, 50);
            branchList.innerHTML = '';
            if (!filtered.length) {
                branchList.innerHTML = '<li class="oco-autocomplete-item oco-np-loading">Нічого не знайдено</li>';
                branchList.style.display = 'block';
                return;
            }
            filtered.slice(0, 80).forEach(function (w) {
                var li = document.createElement('li');
                li.className = 'oco-autocomplete-item';
                li.textContent = w.label;
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    branchInput.value = w.label;
                    npBranchName = w.label;
                    branchList.style.display = 'none';
                    branchInput.style.borderColor = '';
                });
                branchList.appendChild(li);
            });
            branchList.style.display = 'block';
        }

        function resetBranch() {
            allWarehouses = [];
            branchInput.value = '';
            branchInput.placeholder = '— спочатку оберіть місто —';
            branchInput.disabled = true;
            branchList.style.display = 'none';
            npCityRef    = '';
            npCityName   = '';
            npBranchName = '';
        }

        function loadWarehouses(ref) {
            branchInput.disabled = true;
            branchInput.placeholder = 'Завантаження…';
            branchInput.value = '';
            npBranchName = '';
            allWarehouses = [];
            fetch(npAjax + '?action=lesyni_np_warehouses&ref=' + encodeURIComponent(ref))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    allWarehouses = (data.success && data.data) ? data.data : [];
                    branchInput.disabled = allWarehouses.length === 0;
                    branchInput.placeholder = allWarehouses.length ? 'Введіть номер або назву' : 'Відділення не знайдені';
                })
                .catch(function () {
                    branchInput.placeholder = 'Помилка завантаження';
                });
        }

        /* City autocomplete */
        var cityDebounce = null;
        cityInput.addEventListener('input', function () {
            clearTimeout(cityDebounce);
            var q = cityInput.value.trim();
            npCityRef = '';
            npCityName = '';
            if (q.length < 2) { cityList.style.display = 'none'; resetBranch(); return; }
            cityDebounce = setTimeout(function () {
                cityList.innerHTML = '<li class="oco-autocomplete-item oco-np-loading">Пошук…</li>';
                cityList.style.display = 'block';
                fetch(npAjax + '?action=lesyni_np_cities&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var cities = (data.success && data.data) ? data.data : [];
                        cityList.innerHTML = '';
                        if (!cities.length) {
                            cityList.innerHTML = '<li class="oco-autocomplete-item oco-np-loading">Нічого не знайдено</li>';
                            return;
                        }
                        cities.forEach(function (city) {
                            var li = document.createElement('li');
                            li.className = 'oco-autocomplete-item';
                            li.textContent = city.label;
                            li.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                cityInput.value = city.label;
                                npCityRef  = city.ref;
                                npCityName = city.label;
                                cityList.style.display = 'none';
                                cityInput.style.borderColor = '';
                                loadWarehouses(city.ref);
                            });
                            cityList.appendChild(li);
                        });
                    })
                    .catch(function () {
                        cityList.innerHTML = '<li class="oco-autocomplete-item oco-np-loading">Помилка пошуку</li>';
                    });
            }, 350);
        });

        cityInput.addEventListener('blur', function () {
            setTimeout(function () { cityList.style.display = 'none'; }, 150);
        });

        /* Branch autocomplete */
        branchInput.addEventListener('focus', function () {
            if (allWarehouses.length) showBranchSuggestions(branchInput.value.trim());
        });
        branchInput.addEventListener('input', function () {
            npBranchName = '';
            if (allWarehouses.length) showBranchSuggestions(branchInput.value.trim());
        });
        branchInput.addEventListener('blur', function () {
            setTimeout(function () { branchList.style.display = 'none'; }, 150);
        });
    }());

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
                var termsLabel = terms.closest('.oco-check');
                termsLabel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                termsLabel.classList.add('oco-check--error');
                setTimeout(function () { termsLabel.classList.remove('oco-check--error'); }, 2500);
                return;
            }
            if (deliveryType === 'np') {
                var npCityEl   = document.getElementById('np-city-input');
                var npBranchEl = document.getElementById('np-branch-input');
                // Min order 1000 UAH
                var subtotalNow = parseInt(document.getElementById('oco-sum-subtotal') ? document.getElementById('oco-sum-subtotal').textContent : '0', 10) || 0;
                if (subtotalNow < 1000) {
                    var errBox2 = document.getElementById('oco-checkout-errors');
                    if (errBox2) {
                        errBox2.innerHTML = '<ul class="woocommerce-error"><li>Мінімальна сума замовлення для доставки Новою Поштою — 1000 грн.</li></ul>';
                        errBox2.style.display = 'block';
                        errBox2.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
                if (!npCityRef) {
                    if (npCityEl) { npCityEl.focus(); npCityEl.style.borderColor = '#c45a5a'; }
                    return;
                }
                if (!npBranchName) {
                    if (npBranchEl) { npBranchEl.focus(); npBranchEl.style.borderColor = '#c45a5a'; }
                    return;
                }
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
            if (deliveryType === 'np') {
                setHidden('wc-np_city',          npCityName);
                setHidden('wc-np_branch',        npBranchName);
                setHidden('wc-billing_city',     npCityName);
                setHidden('wc-billing_address_1', npBranchName);
            } else {
                var street   = document.getElementById('oco-street');
                var house    = document.getElementById('oco-house');
                var apt      = document.getElementById('oco-apt');
                var entrance = document.getElementById('oco-entrance');
                var floor    = document.getElementById('oco-floor');
                var addr = '';
                if (street)   addr += street.value.trim();
                if (house && house.value.trim())       addr += ', ' + house.value.trim();
                if (apt && apt.value.trim())           addr += ', кв.' + apt.value.trim();
                if (entrance && entrance.value.trim()) addr += ', п.' + entrance.value.trim();
                if (floor && floor.value.trim())       addr += ', пов.' + floor.value.trim();
                setHidden('wc-billing_address_1', addr);
            }

            var noteEl = document.getElementById('oco-note');
            setHidden('wc-order_comments', noteEl ? noteEl.value.trim() : '');

            var giftEl = document.getElementById('oco-gift-check');
            setHidden('wc-lesyni_gift', giftEl && giftEl.checked ? '1' : '');

            placeBtn.disabled = true;
            placeBtn.textContent = '⏳ Оформлення…';

            var errBox = document.getElementById('oco-checkout-errors');
            if (errBox) errBox.style.display = 'none';

            updateWhen(); // refresh hidden input with latest selected date+time
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

    /* ── Phone mask: +38 (0XX) XXX-XX-XX ───────────────────────── */
    var phoneInput = document.getElementById('oco-phone');
    if (phoneInput) {
        function phoneDigits(val) {
            var d = val.replace(/\D/g, '');
            if (d.slice(0, 2) === '38') d = d.slice(2); // always strip country code
            return d.slice(0, 10);
        }
        function phoneFormat(d) {
            if (!d) return '';
            var s = '+38 ';
            if (d.length <= 3) return s + '(' + d;
            s += '(' + d.slice(0, 3) + ') ';
            if (d.length <= 6) return s + d.slice(3);
            s += d.slice(3, 6) + '-';
            if (d.length <= 8) return s + d.slice(6);
            return s + d.slice(6, 8) + '-' + d.slice(8);
        }
        phoneInput.addEventListener('input', function () {
            var d = phoneDigits(phoneInput.value);
            var f = phoneFormat(d);
            phoneInput.value = f;
            phoneInput.setSelectionRange(f.length, f.length);
        });
        phoneInput.addEventListener('focus', function () {
            if (!phoneInput.value) phoneInput.value = '+38 ';
            var l = phoneInput.value.length;
            phoneInput.setSelectionRange(l, l);
        });
        phoneInput.addEventListener('blur', function () {
            if (phoneInput.value === '+38 ') phoneInput.value = '';
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

/* ==========================================================================
   Pie Calculator
========================================================================== */
(function () {
    var section = document.querySelector('.lp-calc');
    if (!section) return;

    var LARGE_SLICES = 8;
    var SMALL_SLICES = 6;
    var MAX_ADULTS   = 100;
    var MAX_CHILDREN = 50;

    var EVENT_NORMS = {
        'table':  { adult: 3, kid: 2, label: '3 шматочки на дорослого, 2 на дитину (стіл лише з пирогами)' },
        'buffet': { adult: 2, kid: 1, label: '2 шматочки на дорослого, 1 на дитину (фуршет)' }
    };

    var state = { adults: 0, children: 0, event: 'table', size: 'large' };

    var rangeInputs = section.querySelectorAll('[data-range]');
    var counters    = section.querySelectorAll('[data-counter]');
    var groups      = section.querySelectorAll('[data-group]');
    var rLarge      = section.querySelector('[data-result="large"]');
    var rSmall      = section.querySelector('[data-result="small"]');
    var cardLarge   = section.querySelector('[data-rcard="large"]');
    var cardSmall   = section.querySelector('[data-rcard="small"]');
    var info        = section.querySelector('[data-info]');
    var cta         = section.querySelector('[data-cta]');

    function updateRangeTrack(el) {
        var pct = ((el.value - el.min) / (el.max - el.min)) * 100;
        el.style.setProperty('--pct', pct + '%');
    }

    function plural(n, forms) {
        var last = n % 10, last2 = n % 100;
        if (last2 >= 11 && last2 <= 14) return forms[2];
        if (last === 1)                  return forms[0];
        if (last >= 2 && last <= 4)      return forms[1];
        return forms[2];
    }

    function recalc() {
        var norms       = EVENT_NORMS[state.event];
        var totalSlices = state.adults * norms.adult + state.children * norms.kid;
        var large = 0, small = 0;

        if (totalSlices > 0) {
            if (state.size === 'large') {
                large = Math.ceil(totalSlices / LARGE_SLICES);
            } else {
                small = Math.ceil(totalSlices / SMALL_SLICES);
            }
        }

        rLarge.textContent = large;
        rSmall.textContent = small;
        cardLarge.classList.toggle('has-value', large > 0);
        cardSmall.classList.toggle('has-value', small > 0);

        var guests    = state.adults + state.children;
        var totalPies = large + small;
        if (guests === 0) {
            info.textContent = 'Вкажіть кількість гостей, щоб побачити рекомендацію';
        } else {
            info.textContent = 'На ' + guests + ' ' + plural(guests, ['гостя', 'гостей', 'гостей']) +
                ' рекомендуємо ' + totalPies + ' ' + plural(totalPies, ['пиріг', 'пироги', 'пирогів']);
        }
        cta.classList.toggle('is-active', guests > 0);
    }

    rangeInputs.forEach(function (range) {
        updateRangeTrack(range);
        range.addEventListener('input', function () {
            var key = range.dataset.range;
            state[key] = parseInt(range.value, 10);
            counters.forEach(function (c) { if (c.dataset.counter === key) c.textContent = state[key]; });
            updateRangeTrack(range);
            recalc();
        });
    });

    groups.forEach(function (group) {
        var key = group.dataset.group;
        group.querySelectorAll('.lp-calc-opt').forEach(function (opt) {
            opt.addEventListener('click', function () {
                group.querySelectorAll('.lp-calc-opt').forEach(function (o) { o.classList.remove('is-active'); });
                opt.classList.add('is-active');
                state[key] = opt.dataset.value;
                recalc();
            });
        });
    });

    cta.addEventListener('click', function () {
        if (cta.classList.contains('is-active')) {
            window.location.href = cta.dataset.url || '/shop/';
        }
    });

    recalc();
})();
