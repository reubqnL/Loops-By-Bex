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

(() => {
    const mainImg = document.querySelector('[data-product-main-img]');
    const thumbs = Array.from(document.querySelectorAll('[data-product-thumb]'));
    if (!mainImg || thumbs.length === 0) {
        return;
    }

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', (event) => {
            const newSrc = thumb.getAttribute('data-src');
            const newAlt = thumb.getAttribute('data-alt');
            if (!newSrc) {
                return;
            }
            event.preventDefault();
            mainImg.setAttribute('src', newSrc);
            if (newAlt) {
                mainImg.setAttribute('alt', newAlt);
            }
            thumbs.forEach((btn) => {
                const active = btn === thumb;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        });
    });
})();

(() => {
    const selects = Array.from(document.querySelectorAll('select[data-color-select]'));
    if (selects.length === 0) {
        return;
    }

    const svgNs = 'http://www.w3.org/2000/svg';
    const knownColors = new Set(['blue', 'green', 'indigo', 'orange', 'red', 'violet', 'yellow']);
    const instances = [];

    function dotClassFor(value) {
        const key = String(value || '').trim().toLowerCase();
        return knownColors.has(key) ? `color-dot color-dot--${key}` : 'color-dot';
    }

    function createChevronSvg() {
        const svg = document.createElementNS(svgNs, 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2.2');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        svg.classList.add('color-chevron');
        const poly = document.createElementNS(svgNs, 'polyline');
        poly.setAttribute('points', '6 9 12 15 18 9');
        svg.appendChild(poly);
        return svg;
    }

    function createCheckSvg() {
        const svg = document.createElementNS(svgNs, 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2.5');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        svg.classList.add('color-check');
        const poly = document.createElementNS(svgNs, 'polyline');
        poly.setAttribute('points', '20 6 9 17 4 12');
        svg.appendChild(poly);
        return svg;
    }

    function closeAllExcept(exceptInstance) {
        instances.forEach((inst) => {
            if (inst !== exceptInstance) {
                inst.close(false);
            }
        });
    }

    selects.forEach((select, selectIdx) => {
        if (select.dataset.enhanced === 'true' || select.options.length === 0) {
            return;
        }
        select.dataset.enhanced = 'true';
        select.classList.add('is-enhanced');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const hostCard = select.closest('.product-card, .cart-item-row');
        const linkedLabel = (select.labels && select.labels[0])
            || (select.id ? document.querySelector(`label[for="${select.id.replace(/"/g, '\\"')}"]`) : null);

        const wrapper = document.createElement('div');
        wrapper.className = 'color-dropdown';
        const instance = { wrapper, close: () => {} };

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'color-dropdown-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const menuId = `color-listbox-${selectIdx}`;
        trigger.setAttribute('aria-controls', menuId);
        if (linkedLabel && linkedLabel.textContent.trim() !== '') {
            trigger.setAttribute('aria-label', linkedLabel.textContent.trim());
        } else {
            trigger.setAttribute('aria-label', 'Select colour');
        }

        const triggerLeft = document.createElement('span');
        triggerLeft.className = 'color-swatch-label';
        const triggerDot = document.createElement('span');
        const triggerText = document.createElement('span');
        triggerLeft.appendChild(triggerDot);
        triggerLeft.appendChild(triggerText);

        trigger.appendChild(triggerLeft);
        trigger.appendChild(createChevronSvg());

        const menu = document.createElement('ul');
        menu.id = menuId;
        menu.className = 'color-dropdown-menu';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;

        let focusedIndex = Math.max(0, select.selectedIndex);

        const optionEls = Array.from(select.options).map((opt, optIdx) => {
            const li = document.createElement('li');
            li.className = 'color-dropdown-option';
            li.setAttribute('role', 'option');
            li.dataset.value = opt.value;

            const left = document.createElement('span');
            left.className = 'color-swatch-label';
            const dot = document.createElement('span');
            dot.className = dotClassFor(opt.value);
            const txt = document.createElement('span');
            txt.textContent = opt.textContent;
            left.appendChild(dot);
            left.appendChild(txt);

            li.appendChild(left);
            li.appendChild(createCheckSvg());

            li.addEventListener('mouseenter', () => {
                setFocusedOption(optIdx);
            });

            li.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                chooseOption(optIdx, true);
            });

            menu.appendChild(li);
            return li;
        });

        function syncFromSelect() {
            const idx = Math.max(0, select.selectedIndex);
            const currentOpt = select.options[idx];
            const val = currentOpt ? currentOpt.value : '';
            const label = currentOpt ? currentOpt.textContent : '';
            triggerDot.className = dotClassFor(val);
            triggerText.textContent = label;
            optionEls.forEach((li, i) => {
                li.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            focusedIndex = idx;
        }

        function setFocusedOption(nextIdx) {
            if (optionEls.length === 0) {
                return;
            }
            focusedIndex = (nextIdx + optionEls.length) % optionEls.length;
            optionEls.forEach((li, i) => {
                li.classList.toggle('is-focused', i === focusedIndex);
            });
            optionEls[focusedIndex].scrollIntoView({ block: 'nearest' });
        }

        function openMenu() {
            closeAllExcept(instance);
            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const shouldDropUp = spaceBelow < 235 && rect.top > 235;
            menu.classList.toggle('is-dropup', shouldDropUp);
            menu.hidden = false;
            trigger.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            if (hostCard) {
                hostCard.classList.add('is-dropdown-open');
            }
            setFocusedOption(Math.max(0, select.selectedIndex));
        }

        function closeMenu(restoreFocus) {
            if (menu.hidden) {
                return;
            }
            menu.hidden = true;
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            optionEls.forEach((li) => li.classList.remove('is-focused'));
            if (hostCard) {
                hostCard.classList.remove('is-dropdown-open');
            }
            if (restoreFocus) {
                trigger.focus();
            }
        }

        function chooseOption(idx, userInitiated) {
            const targetOpt = select.options[idx];
            if (!targetOpt) {
                return;
            }
            const changed = select.selectedIndex !== idx;
            Array.from(select.options).forEach((o, i) => {
                o.selected = i === idx;
            });
            select.selectedIndex = idx;
            select.value = targetOpt.value;
            syncFromSelect();
            closeMenu(userInitiated);

            if (changed) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
                if (userInitiated && select.getAttribute('data-auto-submit') === 'true' && select.form) {
                    select.form.submit();
                }
            }
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (menu.hidden) {
                openMenu();
            } else {
                closeMenu(false);
            }
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (menu.hidden) {
                    openMenu();
                } else {
                    setFocusedOption(focusedIndex + 1);
                }
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (menu.hidden) {
                    openMenu();
                } else {
                    setFocusedOption(focusedIndex - 1);
                }
            } else if (event.key === 'Home' && !menu.hidden) {
                event.preventDefault();
                setFocusedOption(0);
            } else if (event.key === 'End' && !menu.hidden) {
                event.preventDefault();
                setFocusedOption(optionEls.length - 1);
            } else if ((event.key === 'Enter' || event.key === ' ') && !menu.hidden) {
                event.preventDefault();
                chooseOption(focusedIndex, true);
            } else if (event.key === 'Escape' && !menu.hidden) {
                event.preventDefault();
                closeMenu(true);
            } else if (event.key === 'Tab' && !menu.hidden) {
                closeMenu(false);
            }
        });

        if (linkedLabel && !linkedLabel.classList.contains('sr-only')) {
            linkedLabel.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                trigger.focus();
                if (menu.hidden) {
                    openMenu();
                } else {
                    closeMenu(false);
                }
            });
        }

        select.addEventListener('change', (event) => {
            syncFromSelect();
            if (event.isTrusted && select.getAttribute('data-auto-submit') === 'true' && select.form) {
                select.form.submit();
            }
        });

        syncFromSelect();
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        select.insertAdjacentElement('afterend', wrapper);

        instance.close = closeMenu;
        instances.push(instance);
    });

    document.addEventListener('click', (event) => {
        instances.forEach((inst) => {
            if (!inst.wrapper.contains(event.target)) {
                inst.close(false);
            }
        });
    });
})();

