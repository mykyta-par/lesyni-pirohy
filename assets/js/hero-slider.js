(function () {
    var hs = document.getElementById('hs');
    if (!hs) return;

    var slides = Array.from(hs.querySelectorAll('.hs-slide'));
    var dots   = Array.from(hs.querySelectorAll('.hs-dot'));
    var bar    = hs.querySelector('.hs-progress-bar');
    var curEl  = hs.querySelector('#hs-cur');
    var total  = slides.length;
    var cur    = 0;
    var timer  = null;
    var DELAY  = 6000;

    function goTo(idx) {
        slides[cur].classList.remove('active');
        if (dots[cur]) dots[cur].classList.remove('active');
        cur = ((idx % total) + total) % total;
        slides[cur].classList.add('active');
        if (dots[cur]) dots[cur].classList.add('active');
        if (curEl) curEl.textContent = String(cur + 1).padStart(2, '0');
        animateBar();
    }

    function animateBar() {
        if (!bar) return;
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.offsetWidth; // reflow
        bar.style.transition = 'width ' + DELAY + 'ms linear';
        bar.style.width = '100%';
    }

    function startAuto() {
        clearInterval(timer);
        timer = setInterval(function () { goTo(cur + 1); }, DELAY);
        animateBar();
    }

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); startAuto(); });
    });

    hs.querySelectorAll('.hs-arrow').forEach(function (arrow) {
        arrow.addEventListener('click', function () {
            goTo(cur + parseInt(arrow.dataset.step, 10));
            startAuto();
        });
    });

    hs.addEventListener('mouseenter', function () { clearInterval(timer); if (bar) { bar.style.transition = 'none'; } });
    hs.addEventListener('mouseleave', startAuto);

    startAuto();
}());
