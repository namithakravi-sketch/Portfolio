/*
 * Love, N artwork protection (deterrents only: no website can block screenshots).
 *  - no right-click / long-press menu or dragging on images
 *  - no copying images out of a selection
 *  - enlarged (lightbox) images blur while the window is not focused
 */
(function () {
	function isArt(el) {
		return el && el.closest && (el.tagName === 'IMG' || el.closest('.wp-lightbox-overlay, figure, .pswp'));
	}
	document.addEventListener('contextmenu', function (e) { if (isArt(e.target)) e.preventDefault(); }, true);
	document.addEventListener('dragstart', function (e) { if (e.target && e.target.tagName === 'IMG') e.preventDefault(); }, true);
	document.addEventListener('copy', function (e) {
		var sel = window.getSelection && window.getSelection();
		if (!sel || !sel.rangeCount) return;
		var frag = sel.getRangeAt(0).cloneContents();
		if (frag.querySelector && frag.querySelector('img')) {
			e.preventDefault();
			if (e.clipboardData) e.clipboardData.setData('text/plain', sel.toString());
		}
	}, true);

	var root = document.documentElement;
	window.addEventListener('blur', function () { root.classList.add('ln-unfocused'); });
	window.addEventListener('focus', function () { root.classList.remove('ln-unfocused'); });
	document.addEventListener('visibilitychange', function () { root.classList.toggle('ln-unfocused', document.hidden); });
})();
