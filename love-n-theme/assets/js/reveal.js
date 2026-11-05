/*
 * Gentle scroll animations: content fades up softly as it comes into view.
 * Skipped entirely for visitors who prefer reduced motion, and if JavaScript
 * is off everything simply shows.
 */
(function () {
	if (!('IntersectionObserver' in window)) return;
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

	var selectors = [
		'main .ln-section .ln-section-head',
		'main .ln-club-feature .wp-block-column',
		'main .ln-grid > .ln-card-photo',
		'main .wp-block-gallery > .wp-block-image',
		'main .ln-mags > .ln-mag',
		'main .ln-portrait',
		'main .ln-steps > .wp-block-column',
		'main .ln-insta > a, main .ln-insta--live .sbi_item',
		'main .ln-page-hero > *',
		'main .ln-band--rose.ln-center > *',
		'main .wp-block-details'
	].join(',');

	var items = Array.prototype.slice.call(document.querySelectorAll(selectors));
	if (!items.length) return;

	// Stagger siblings in the same row/grid a little.
	items.forEach(function (el) {
		var sibs = Array.prototype.filter.call(el.parentNode.children, function (c) { return items.indexOf(c) !== -1; });
		var i = sibs.indexOf(el);
		el.style.setProperty('--ln-delay', Math.min(i, 5) * 90 + 'ms');
		el.classList.add('ln-reveal');
	});

	var io = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) {
			if (e.isIntersecting) {
				e.target.classList.add('ln-in');
				io.unobserve(e.target);
			}
		});
	}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

	items.forEach(function (el) { io.observe(el); });
})();
