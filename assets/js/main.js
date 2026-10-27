(() => {
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const menuBtn = document.getElementById('mobile-menu-toggle');
    const primaryNav = document.getElementById('primary-nav');
    if (menuBtn && primaryNav) {
        menuBtn.addEventListener('click', () => {
            const isOpen = primaryNav.classList.toggle('is-open');
            menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    const searchBtn = document.getElementById('search-toggle-btn');
    const searchBar = document.getElementById('header-search-bar');
    if (searchBtn && searchBar) {
        searchBtn.addEventListener('click', () => {
            const isOpen = searchBar.classList.toggle('is-open');
            searchBar.hidden = !isOpen;
            searchBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                const input = searchBar.querySelector('input[type="search"]');
                if (input) {
                    input.focus();
                }
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        if (primaryNav && primaryNav.classList.contains('is-open') && menuBtn) {
            primaryNav.classList.remove('is-open');
            menuBtn.setAttribute('aria-expanded', 'false');
            menuBtn.focus();
        }
        if (searchBar && searchBar.classList.contains('is-open') && searchBtn) {
            searchBar.classList.remove('is-open');
            searchBar.hidden = true;
            searchBtn.setAttribute('aria-expanded', 'false');
            searchBtn.focus();
        }
    });

    const carousel = document.querySelector('[data-carousel]');
    if (!carousel) {
        return;
    }

    const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = carousel.querySelector('[data-carousel-prev]');
    const nextBtn = carousel.querySelector('[data-carousel-next]');
    const liveRegion = carousel.querySelector('[data-carousel-liveregion]');

    if (slides.length <= 1) {
        return;
    }

    let activeIndex = 0;
    let timerId = null;

    function goToSlide(nextIndex, announce = false) {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, idx) => {
            const isCurrent = idx === activeIndex;
            slide.classList.toggle('is-active', isCurrent);
            slide.setAttribute('aria-hidden', isCurrent ? 'false' : 'true');
            if (isCurrent) {
                slide.removeAttribute('inert');
            } else {
                slide.setAttribute('inert', '');
            }
        });
        dots.forEach((dot, idx) => {
            const isCurrent = idx === activeIndex;
            dot.classList.toggle('is-active', isCurrent);
            if (isCurrent) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });

        if (announce && liveRegion) {
            const heading = slides[activeIndex].querySelector('.hero-heading');
            const label = heading ? heading.textContent.trim() : '';
            liveRegion.textContent = `Slide ${activeIndex + 1} of ${slides.length}: ${label}`;
        }
    }

    function startAutoPlay() {
        if (prefersReducedMotion) {
            return;
        }
        stopAutoPlay();
        timerId = window.setInterval(() => {
            goToSlide(activeIndex + 1, false);
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
            goToSlide(activeIndex - 1, true);
            startAutoPlay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goToSlide(activeIndex + 1, true);
            startAutoPlay();
        });
    }

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            goToSlide(idx, true);
            startAutoPlay();
        });
    });

    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goToSlide(activeIndex - 1, true);
            stopAutoPlay();
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            goToSlide(activeIndex + 1, true);
            stopAutoPlay();
        }
    });

    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    carousel.addEventListener('focusin', stopAutoPlay);
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            startAutoPlay();
        }
    });

    startAutoPlay();
})();
