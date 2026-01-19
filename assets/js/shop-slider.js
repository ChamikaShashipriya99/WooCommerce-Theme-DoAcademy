/**
 * Simple shop hero slider
 * - Vanilla JS, no dependencies
 * - Auto-plays with pause on hover/focus
 * - Keyboard friendly: left/right arrows, dots focusable
 */
(function () {
	const slider = document.querySelector('.shop-hero-slider');
	if (!slider) return;

	const track = slider.querySelector('[data-slider-track]');
	const slides = Array.from(slider.querySelectorAll('.shop-hero-slide'));
	const dotsContainer = slider.querySelector('[data-slider-dots]');
	const prevBtn = slider.querySelector('[data-slider-prev]');
	const nextBtn = slider.querySelector('[data-slider-next]');

	if (!track || slides.length <= 1) return;

	let current = 0;
	let timer = null;
	const interval = 5500;

	const setActive = (index) => {
		slides.forEach((slide, idx) => {
			slide.classList.toggle('is-active', idx === index);
			slide.setAttribute('aria-hidden', idx === index ? 'false' : 'true');
		});
		if (dotsContainer) {
			const dots = Array.from(dotsContainer.querySelectorAll('.shop-hero-slider__dot'));
			dots.forEach((dot, idx) => {
				dot.classList.toggle('is-active', idx === index);
				if (idx === index) {
					dot.setAttribute('aria-current', 'true');
				} else {
					dot.removeAttribute('aria-current');
				}
			});
		}
		track.style.transform = `translateX(-${index * 100}%)`;
		current = index;
	};

	const next = () => setActive((current + 1) % slides.length);
	const prev = () => setActive((current - 1 + slides.length) % slides.length);

	const startAuto = () => {
		stopAuto();
		timer = window.setInterval(next, interval);
	};

	const stopAuto = () => {
		if (timer) {
			window.clearInterval(timer);
			timer = null;
		}
	};

	// Navigation buttons
	prevBtn?.addEventListener('click', () => {
		prev();
		startAuto();
	});
	nextBtn?.addEventListener('click', () => {
		next();
		startAuto();
	});

	// Dots
	if (dotsContainer) {
		dotsContainer.addEventListener('click', (event) => {
			const dot = event.target.closest('[data-slider-dot]');
			if (!dot) return;
			const idx = Number(dot.getAttribute('data-slider-dot'));
			if (!Number.isNaN(idx)) {
				setActive(idx);
				startAuto();
			}
		});
	}

	// Keyboard support on slider container
	slider.addEventListener('keydown', (event) => {
		if (event.key === 'ArrowRight') {
			event.preventDefault();
			next();
			startAuto();
		}
		if (event.key === 'ArrowLeft') {
			event.preventDefault();
			prev();
			startAuto();
		}
	});

	// Pause on hover/focus, resume on leave/blur
	const pauseEvents = ['mouseenter', 'focusin'];
	const resumeEvents = ['mouseleave', 'focusout'];

	pauseEvents.forEach((evt) => slider.addEventListener(evt, stopAuto));
	resumeEvents.forEach((evt) => slider.addEventListener(evt, startAuto));

	// Initialize layout and autoplay
	track.style.width = `${slides.length * 100}%`;
	slides.forEach((slide) => {
		slide.style.width = `${100 / slides.length}%`;
	});
	setActive(0);
	startAuto();
})();

