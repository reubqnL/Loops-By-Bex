(() => {
    const menuBtn = document.getElementById('mobile-menu-toggle');
    const primaryNav = document.getElementById('primary-nav');
    if (menuBtn && primaryNav) {
        menuBtn.addEventListener('click', () => {
            primaryNav.classList.toggle('is-open');
        });
    }

    const searchBtn = document.getElementById('search-toggle-btn');
    const searchBar = document.getElementById('header-search-bar');
    if (searchBtn && searchBar) {
        searchBtn.addEventListener('click', () => {
            const isOpen = searchBar.classList.toggle('is-open');
            if (isOpen) {
                const input = searchBar.querySelector('input[type="search"]');
                if (input) {
                    input.focus();
                }
            }
        });
    }

    const carousel = document.querySelector('[data-carousel]');
    if (!carousel) {
        return;
    }

    const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = carousel.querySelector('[data-carousel-prev]');
    const nextBtn = carousel.querySelector('[data-carousel-next]');

    if (slides.length <= 1) {
        return;
    }

    let activeIndex = 0;
    let timerId = null;

    function goToSlide(nextIndex) {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, idx) => {
            slide.classList.toggle('is-active', idx === activeIndex);
        });
        dots.forEach((dot, idx) => {
            dot.classList.toggle('is-active', idx === activeIndex);
        });
    }

    function startAutoPlay() {
        stopAutoPlay();
        timerId = window.setInterval(() => {
            goToSlide(activeIndex + 1);
        }, 6500);
    }

    function stopAutoPlay() {
        if (timerId !== null) {
            window.clearInterval(timerId);
            timerId = null;
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            goToSlide(activeIndex - 1);
            startAutoPlay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goToSlide(activeIndex + 1);
            startAutoPlay();
        });
    }

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            goToSlide(idx);
            startAutoPlay();
        });
    });

    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);

    startAutoPlay();
})();
