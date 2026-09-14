document.addEventListener('DOMContentLoaded', function () {

    const wrapper  = document.querySelector('.testimonials-carousel-wrapper');
    const track    = document.getElementById('testimonialsTrack');
    const prevBtn  = document.getElementById('prevTestimonial');
    const nextBtn  = document.getElementById('nextTestimonial');
    const dotsWrap = document.querySelector('.carousel-dots');

    if (track) {
        const cards      = Array.from(track.querySelectorAll('.testimonial-card'));
        const totalCards = cards.length;

        // How many cards visible per viewport width
        function getPerView() {
            if (window.innerWidth <= 768) return 1;
            if (window.innerWidth <= 992) return 2;
            return 3;
        }

        let perView      = getPerView();
        let maxIndex     = Math.max(totalCards - perView, 0);
        let currentIndex = 0;

        // Build pagination dots dynamically
        function buildDots() {
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            for (let i = 0; i <= maxIndex; i++) {
                const dot = document.createElement('span');
                dot.className = 'dot' + (i === 0 ? ' active' : '');
                dot.dataset.index = i;
                dot.addEventListener('click', () => goTo(i));
                dotsWrap.appendChild(dot);
            }
        }

        // Distance to move per slide = card width + gap
        function getStep() {
            const style = getComputedStyle(track);
            const gap   = parseFloat(style.gap) || 0;
            const cardW = cards[0].getBoundingClientRect().width;
            return cardW + gap;
        }

        function goTo(index) {
            currentIndex = Math.max(0, Math.min(index, maxIndex));
            const step = getStep();
            track.style.transform = `translateX(-${currentIndex * step}px)`;

            if (dotsWrap) {
                dotsWrap.querySelectorAll('.dot').forEach((d, i) => {
                    d.classList.toggle('active', i === currentIndex);
                });
            }
        }

        function nextSlide() { goTo(currentIndex >= maxIndex ? 0 : currentIndex + 1); }
        function prevSlide() { goTo(currentIndex <= 0 ? maxIndex : currentIndex - 1); }

        // Recalculate layout on resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const newPerView = getPerView();
                if (newPerView !== perView) {
                    perView      = newPerView;
                    maxIndex     = Math.max(totalCards - perView, 0);
                    currentIndex = 0;
                    buildDots();
                } else {
                    maxIndex = Math.max(totalCards - perView, 0);
                }
                goTo(currentIndex);
            }, 150);
        });

        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);

        buildDots();
        goTo(0);

        /* ---- Optional: auto-play every 5s, pause on hover ----
        let auto = setInterval(nextSlide, 5000);
        wrapper.addEventListener('mouseenter', () => clearInterval(auto));
        wrapper.addEventListener('mouseleave', () => auto = setInterval(nextSlide, 5000));
        ------------------------------------------------------- */
    }

    /* Navigation active state */
    const navLinks = document.querySelectorAll('.nav ul li a');
    const sections = document.querySelectorAll('section[id]');

    function setActiveLink() {
        let currentSection = '';
        const scrollPos = window.scrollY + 150;

        if (sections.length > 0) {
            sections.forEach(section => {
                const sectionTop    = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    currentSection = section.getAttribute('id');
                }
            });
        }

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            link.classList.remove('active');

            if (href === 'contact.php' && window.location.pathname.includes('contact.php')) {
                link.classList.add('active');
                return;
            }

            if (sections.length > 0 && href === '#' + currentSection) {
                link.classList.add('active');
            }
        });
    }

    setActiveLink();
    window.addEventListener('scroll', setActiveLink);

    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});