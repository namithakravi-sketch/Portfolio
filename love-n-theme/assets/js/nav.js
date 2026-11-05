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
	// Height of the announcement bars above the menu row. Measured from the bars
	// themselves, so it stays correct while the header is pinned (iPad Safari
	// fires resize events mid-scroll as its toolbar shows and hides).
	function barsHeight() {
		var h = 0;
		for (var el = header.firstElementChild; el && el !== bar; el = el.nextElementSibling) {
			var cs = getComputedStyle(el);
			h += el.getBoundingClientRect().height + parseFloat(cs.marginTop) + parseFloat(cs.marginBottom);
		}
		return Math.round(h);
	}
	function place() {
		if (!header || !bar) return;
		var bh = barsHeight();
		header.style.top = (adminBarOffset() - bh) + 'px';
		root.classList.toggle('ln-stuck', window.scrollY > bh + 4);
	}
	if (header && bar) {
		place();
		window.addEventListener('resize', place);
		var bhCache = barsHeight();
		window.addEventListener('resize', function () { bhCache = barsHeight(); });
		window.addEventListener('scroll', function () {
			root.classList.toggle('ln-stuck', window.scrollY > bhCache + 4);
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
