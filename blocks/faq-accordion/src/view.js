const collapseSiblings = (items, current) => {
	items.forEach((item) => {
		if (item === current) {
			return;
		}

		const answer = item.querySelector('.faq-answer');
		const trigger = item.querySelector('.faq-question');

		if (!answer || !trigger) {
			return;
		}

		item.classList.remove('is-open');
		trigger.setAttribute('aria-expanded', 'false');
		answer.setAttribute('aria-hidden', 'true');
		if (!answer.hasAttribute('hidden')) {
			answer.setAttribute('hidden', '');
		}
		answer.style.maxHeight = '';
	});
};

const expandItem = (item, items) => {
	const answer = item.querySelector('.faq-answer');
	const trigger = item.querySelector('.faq-question');

	if (!answer || !trigger) {
		return;
	}

	const isOpen = item.classList.contains('is-open');

	if (isOpen) {
		item.classList.remove('is-open');
		trigger.setAttribute('aria-expanded', 'false');
		answer.setAttribute('aria-hidden', 'true');
		answer.setAttribute('hidden', '');
		answer.style.maxHeight = '';
		return;
	}

	collapseSiblings(items, item);

	item.classList.add('is-open');
	trigger.setAttribute('aria-expanded', 'true');
	answer.removeAttribute('hidden');
	answer.setAttribute('aria-hidden', 'false');
	answer.style.maxHeight = `${answer.scrollHeight}px`;
};

const initFaqAccordions = () => {
	const accordions = document.querySelectorAll('.faq-accordion-block');

	accordions.forEach((wrapper) => {
		const items = Array.from(wrapper.querySelectorAll('.faq-accordion-item'));

		if (!items.length) {
			return;
		}

		items.forEach((item, index) => {
			const trigger = item.querySelector('.faq-question');
			const answer = item.querySelector('.faq-answer');

			if (!trigger || !answer) {
				return;
			}

			const shouldBeOpen = item.classList.contains('is-open') || index === 0;

			if (shouldBeOpen) {
				item.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
				answer.removeAttribute('hidden');
				answer.setAttribute('aria-hidden', 'false');
				answer.style.maxHeight = `${answer.scrollHeight}px`;
			} else {
				trigger.setAttribute('aria-expanded', 'false');
				answer.setAttribute('aria-hidden', 'true');
				answer.setAttribute('hidden', '');
			}

			trigger.addEventListener('click', () => expandItem(item, items));
		});

		const adjustHeights = () => {
			items.forEach((item) => {
				if (!item.classList.contains('is-open')) {
					return;
				}
				const answer = item.querySelector('.faq-answer');
				if (answer) {
					answer.style.maxHeight = `${answer.scrollHeight}px`;
				}
			});
		};

		window.addEventListener('resize', adjustHeights);
	});
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initFaqAccordions);
} else {
	initFaqAccordions();
}
