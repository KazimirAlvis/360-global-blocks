const ROOT_SELECTOR = '.patient-reviews-slider';

function updateSlider(root, container, targetIndex) {
	const track = container.querySelector('.patient-reviews-slider-track');
	if (!track) {
		return;
	}

	const slides = Array.from(track.children);
	const total = slides.length;
	if (!total) {
		return;
	}

	let newIndex = typeof targetIndex === 'number' ? targetIndex : parseInt(container.dataset.currentSlide || '0', 10);
	if (Number.isNaN(newIndex)) {
		newIndex = 0;
	}

	newIndex = ((newIndex % total) + total) % total;
	container.dataset.currentSlide = String(newIndex);
	track.style.transform = `translateX(-${newIndex * 100}%)`;

	slides.forEach((slide, idx) => slide.classList.toggle('active', idx === newIndex));

	const dots = root.querySelectorAll('.patient-reviews-slider-dot');
	dots.forEach((dot, idx) => dot.classList.toggle('active', idx === newIndex));
}

function initSlider(root) {
	const container = root?.querySelector?.('.patient-reviews-slider-container');
	if (!container || container.dataset.sliderInitialized === 'true') {
		return;
	}

	container.dataset.sliderInitialized = 'true';
	updateSlider(root, container, 0);

	const prev = root.querySelector('.patient-reviews-slider-nav.prev');
	const next = root.querySelector('.patient-reviews-slider-nav.next');

	if (prev) {
		prev.addEventListener('click', () => {
			const current = parseInt(container.dataset.currentSlide || '0', 10) || 0;
			updateSlider(root, container, current - 1);
		});
	}

	if (next) {
		next.addEventListener('click', () => {
			const current = parseInt(container.dataset.currentSlide || '0', 10) || 0;
			updateSlider(root, container, current + 1);
		});
	}

	const dots = root.querySelectorAll('.patient-reviews-slider-dot');
	dots.forEach((dot, index) => {
		dot.addEventListener('click', () => updateSlider(root, container, index));
	});
}

if (typeof window !== 'undefined') {
	document.addEventListener('DOMContentLoaded', () => {
		document.querySelectorAll(ROOT_SELECTOR).forEach(initSlider);
	});
}
