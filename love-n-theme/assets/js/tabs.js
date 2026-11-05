/*
 * Love, N tabs: a Buttons block with class "ln-tabs" whose links point to
 * section ids (#artworks, #animation, #branding) shows one section at a time.
 * Without JavaScript every section simply shows, one after another.
 */
(function () {
	var bar = document.querySelector('.ln-tabs');
	if (!bar) return;
	var links = Array.prototype.slice.call(bar.querySelectorAll('a[href^="#"]'));
	var pairs = links.map(function (a) {
		return { link: a, panel: document.getElementById(a.getAttribute('href').slice(1)) };
	}).filter(function (p) { return p.panel; });
	if (pairs.length < 2) return;

	bar.setAttribute('role', 'tablist');
	pairs.forEach(function (p) {
		var id = p.panel.id;
		p.link.setAttribute('role', 'tab');
		p.link.id = p.link.id || 'ln-tab-' + id;
		p.link.setAttribute('aria-controls', id);
		p.panel.setAttribute('role', 'tabpanel');
		p.panel.setAttribute('aria-labelledby', p.link.id);
		p.panel.setAttribute('tabindex', '-1');
	});

	function show(id) {
		pairs.forEach(function (p) {
			var on = p.panel.id === id;
			p.panel.hidden = !on;
			p.panel.classList.toggle('is-active', on);
			p.link.setAttribute('aria-selected', on ? 'true' : 'false');
			p.link.tabIndex = on ? 0 : -1;
			p.link.parentNode.classList.toggle('is-active', on);
		});
	}
	function idFromHash() {
		var h = (location.hash || '').slice(1);
		if (h === 'illustration') h = 'artworks'; // old links
		return pairs.some(function (p) { return p.panel.id === h; }) ? h : null;
	}

	pairs.forEach(function (p, i) {
		p.link.addEventListener('click', function (e) {
			e.preventDefault();
			show(p.panel.id);
			if (history.replaceState) history.replaceState(null, '', '#' + p.panel.id);
		});
		p.link.addEventListener('keydown', function (e) {
			var step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
			if (!step) return;
			e.preventDefault();
			var next = pairs[(i + step + pairs.length) % pairs.length];
			next.link.focus();
			next.link.click();
		});
	});
	window.addEventListener('hashchange', function () { var id = idFromHash(); if (id) show(id); });

	show(idFromHash() || pairs[0].panel.id);
})();
