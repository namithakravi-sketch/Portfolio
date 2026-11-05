/*
 * Sticky header: the two announcement bars scroll away and the name + menu row
 * stays at the top. Also adds a shadow once the header is floating.
 * Back link: goes to the previous page on this site, or to its fallback link.
 */
(function () {
	var header = document.querySelector('header.wp-block-template-part');
	var bar = header && header.querySelector('.ln-header');
	var root = document.documentElement;

	function adminBarOffset() {
		var ab = document.getElementById('wpadminbar');
		if (!ab) return 0;
		return getComputedStyle(ab).position === 'fixed' ? ab.offsetHeight : 0;
	}
	function place() {
		if (!header || !bar) return;
		var barsHeight = bar.offsetTop - header.offsetTop; // height of the bars above the menu row
		header.style.top = (adminBarOffset() - barsHeight) + 'px';
		root.classList.toggle('ln-stuck', window.scrollY > barsHeight + 4);
	}
	if (header && bar) {
		place();
		window.addEventListener('resize', place);
		window.addEventListener('scroll', function () {
			var barsHeight = bar.offsetTop - header.offsetTop;
			root.classList.toggle('ln-stuck', window.scrollY > barsHeight + 4);
		}, { passive: true });
		window.addEventListener('load', place);
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('a[data-ln-back]');
		if (!a) return;
		var ref = document.referrer;
		var sameSite = ref && ref.indexOf(location.origin) === 0 && ref.split('#')[0] !== location.href.split('#')[0];
		if (sameSite && history.length > 1) {
			e.preventDefault();
			history.back();
		}
	});
})();
