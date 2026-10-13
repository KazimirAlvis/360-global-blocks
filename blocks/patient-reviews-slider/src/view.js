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

	slides.forEach((slide, idx) => {
  slide.classList.toggle('active', idx === newIndex);
  slide.inert = idx !== newIndex;
  slide.setAttribute('aria-hidden', String(idx !== newIndex));
 });

	const dots = root.querySelectorAll('.patient-reviews-slider-dot');
	dots.forEach((dot, idx) => {
  dot.classList.toggle('active', idx === newIndex);
  dot.setAttribute('aria-current', String(idx === newIndex));
 });
 const status = root.querySelector('[data-review-slide-status]');
 if (status) status.textContent = `Slide ${newIndex + 1} of ${total}`;
}

function initSlider(root) {
	const container = root?.querySelector?.('.patient-reviews-slider-container');
	if (!container || container.dataset.sliderInitialized === 'true') {
		return;
	}

	container.dataset.sliderInitialized = 'true';
	setupResponsiveReviews(root, container);
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

}

function setupResponsiveReviews(root, container) {
 const track = container.querySelector('.patient-reviews-slider-track');
 const cards = Array.from(track.querySelectorAll('.patient-review-card'));
 const dots = root.querySelector('.patient-reviews-slider-dots');
 const controls = root.querySelector('.patient-reviews-slider-controls');
 if (!track || !dots || !controls) return;
 const media = window.matchMedia('(max-width: 900px)');
 const status = document.createElement('p');
 status.className = 'screen-reader-text'; status.dataset.reviewSlideStatus = '';
 status.setAttribute('role', 'status'); status.setAttribute('aria-live', 'polite'); root.append(status);
 root.setAttribute('role', 'region'); root.setAttribute('aria-roledescription', 'carousel');
 root.setAttribute('aria-label', root.querySelector('h2')?.textContent || 'Patient reviews');
 function arrange() {
  const size = media.matches ? 1 : 3;
  track.replaceChildren(); dots.replaceChildren();
  for (let i = 0; i < cards.length; i += size) {
   const slide = document.createElement('div'); slide.className = 'patient-reviews-slide';
   const grid = document.createElement('div'); grid.className = 'patient-reviews-slide-grid';
   cards.slice(i, i + size).forEach(card => grid.append(card)); slide.append(grid); track.append(slide);
   const index = i / size;
   slide.setAttribute('role', 'group'); slide.setAttribute('aria-label', `Slide ${index + 1}`);
   const dot = document.createElement('button'); dot.type = 'button'; dot.className = 'patient-reviews-slider-dot';
   dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
   dot.addEventListener('click', () => updateSlider(root, container, index)); dots.append(dot);
  }
  controls.hidden = track.children.length < 2;
  updateSlider(root, container, 0);
 }
 arrange(); media.addEventListener('change', arrange);
}

if (typeof window !== 'undefined') {
	document.addEventListener('DOMContentLoaded', () => {
		document.querySelectorAll(ROOT_SELECTOR).forEach(initSlider);
	});
}
