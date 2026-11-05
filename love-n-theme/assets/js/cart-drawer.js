/**
 * Cart drawer: opens from the header cart icon and after "Add to cart".
 * Talks to the WooCommerce Store API, so it works while the store is in
 * "coming soon" mode. Without JavaScript the icon is a plain link to the cart.
 */
(function () {
	var cfg = window.lnCartConfig || {};
	var drawer = document.getElementById('ln-cart-drawer');
	var nonce = null;
	var cart = null;
	var lastFocus = null;

	function q(sel, root) { return (root || document).querySelector(sel); }

	function request(path, body) {
		var opts = { credentials: 'same-origin', headers: { 'Content-Type': 'application/json' } };
		if (body) {
			opts.method = 'POST';
			opts.body = JSON.stringify(body);
			if (nonce) opts.headers.Nonce = nonce;
		}
		return fetch(cfg.api + path, opts).then(function (r) {
			var n = r.headers.get('Nonce');
			if (n) nonce = n;
			return r.json().then(function (data) {
				if (!r.ok) throw data;
				return data;
			});
		});
	}

	function load() { return request('cart').then(render); }

	function post(path, body) {
		var go = function () { return request(path, body); };
		return (nonce ? go() : request('cart').then(go)).then(render);
	}

	function minor(value, t) { return parseInt(value, 10) / Math.pow(10, t.currency_minor_unit || 0); }

	function money(value, t) {
		var n = minor(value, t);
		try {
			return new Intl.NumberFormat('en-IN', { style: 'currency', currency: t.currency_code, maximumFractionDigits: n % 1 ? 2 : 0 }).format(n);
		} catch (e) {
			return (t.currency_prefix || '') + n.toFixed(2) + (t.currency_suffix || '');
		}
	}

	function fxLine(value, t) {
		if (!window.lnFx || t.currency_code !== 'INR') return '';
		return window.lnFx.format(minor(value, t));
	}

	function decode(html) {
		var t = document.createElement('textarea');
		t.innerHTML = html;
		return t.value;
	}

	function el(tag, cls, text) {
		var e = document.createElement(tag);
		if (cls) e.className = cls;
		if (text != null) e.textContent = text;
		return e;
	}

	function setCount(n) {
		document.querySelectorAll('[data-ln-cart-count]').forEach(function (b) {
			b.textContent = n;
			b.hidden = !n;
		});
		document.querySelectorAll('[data-ln-cart-open]').forEach(function (a) {
			a.setAttribute('aria-label', n ? 'Cart, ' + n + (n === 1 ? ' item' : ' items') : 'Cart');
		});
	}

	function message(text) {
		var m = q('[data-ln-cart-msg]', drawer);
		if (!m) return;
		m.textContent = text || '';
		m.hidden = !text;
	}

	function render(data) {
		cart = data;
		setCount(data.items_count || 0);
		if (!drawer) return data;
		var body = q('[data-ln-cart-body]', drawer);
		var foot = q('[data-ln-cart-foot]', drawer);
		var t = data.totals || {};
		body.textContent = '';

		if (!data.items || !data.items.length) {
			var empty = el('div', 'ln-drawer__empty');
			empty.appendChild(el('p', null, 'Your cart is empty.'));
			var shop = el('a', 'ln-arrow', 'Visit the Mail Club');
			shop.href = '/mail-club/';
			empty.appendChild(shop);
			body.appendChild(empty);
			foot.hidden = true;
			return data;
		}

		var list = el('ul', 'ln-drawer__items');
		data.items.forEach(function (item) {
			var it = item.totals || {};
			var li = el('li', 'ln-drawer__item');
			var img = item.images && item.images[0];
			if (img) {
				var im = el('img');
				im.src = img.thumbnail || img.src;
				im.alt = '';
				im.loading = 'lazy';
				li.appendChild(im);
			} else {
				li.appendChild(el('span', 'ln-drawer__ph'));
			}
			var info = el('div', 'ln-drawer__info');
			var name = el('a', 'ln-drawer__name');
			name.href = item.permalink;
			name.textContent = decode(item.name); // Store API names may hold entities like &amp;.
			info.appendChild(name);
			var line = parseInt(it.line_subtotal, 10) + parseInt(it.line_subtotal_tax || 0, 10);
			var price = el('p', 'ln-drawer__price', money(line, it));
			var fx = fxLine(line, it);
			if (fx) price.appendChild(el('span', 'ln-drawer__price-fx', ' ' + fx));
			info.appendChild(price);

			var row = el('div', 'ln-drawer__row');
			var box = el('div', 'ln-qty__box ln-drawer__qty');
			var max = item.quantity_limits ? item.quantity_limits.maximum : 99;
			var minus = el('button', 'ln-qty__btn', '−');
			minus.type = 'button';
			minus.setAttribute('aria-label', 'Decrease quantity of ' + decode(item.name));
			minus.disabled = item.quantity <= 1;
			var qty = el('span', 'ln-drawer__qty-n', String(item.quantity));
			qty.setAttribute('aria-live', 'polite');
			var plus = el('button', 'ln-qty__btn', '+');
			plus.type = 'button';
			plus.setAttribute('aria-label', 'Increase quantity of ' + decode(item.name));
			plus.disabled = item.quantity >= max;
			minus.addEventListener('click', function () { update(item.key, item.quantity - 1); });
			plus.addEventListener('click', function () { update(item.key, item.quantity + 1); });
			box.appendChild(minus);
			box.appendChild(qty);
			box.appendChild(plus);
			row.appendChild(box);
			var rm = el('button', 'ln-drawer__remove', 'Remove');
			rm.type = 'button';
			rm.setAttribute('aria-label', 'Remove ' + decode(item.name));
			rm.addEventListener('click', function () { remove(item.key); });
			row.appendChild(rm);
			info.appendChild(row);
			li.appendChild(info);
			list.appendChild(li);
		});
		body.appendChild(list);

		var sub = parseInt(t.total_items, 10) + parseInt(t.total_items_tax || 0, 10);
		q('[data-ln-cart-total]', drawer).textContent = money(sub, t);
		var fxEl = q('[data-ln-cart-fx]', drawer);
		var fxTotal = fxLine(sub, t);
		fxEl.textContent = fxTotal ? fxTotal + ' in ' + window.lnFx.currency + ' (approximate). You’ll pay in ₹.' : '';
		fxEl.hidden = !fxTotal;
		foot.hidden = false;
		return data;
	}

	function busy(on) { if (drawer) drawer.classList.toggle('is-busy', on); }

	function fail(err) {
		message((err && err.message) ? err.message.replace(/<[^>]+>/g, '') : 'Something went wrong. Please try again.');
		busy(false);
	}

	function update(key, quantity) {
		message('');
		busy(true);
		post('cart/update-item', { key: key, quantity: quantity }).then(function () { busy(false); }, fail);
	}

	function remove(key) {
		message('');
		busy(true);
		post('cart/remove-item', { key: key }).then(function () { busy(false); }, fail);
	}

	function trap(e) {
		if (e.key === 'Escape') { close(); return; }
		if (e.key !== 'Tab') return;
		var f = Array.prototype.filter.call(drawer.querySelectorAll('a[href], button:not([disabled])'), function (x) { return x.offsetParent !== null; });
		if (!f.length) return;
		var first = f[0], last = f[f.length - 1];
		if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
	}

	function open(data) {
		if (!drawer) { window.location.href = cfg.cartUrl; return; }
		lastFocus = document.activeElement;
		drawer.hidden = false;
		document.documentElement.classList.add('ln-drawer-open');
		requestAnimationFrame(function () { drawer.classList.add('is-open'); });
		document.querySelectorAll('[data-ln-cart-open]').forEach(function (a) { a.setAttribute('aria-expanded', 'true'); });
		q('.ln-drawer__panel', drawer).focus();
		document.addEventListener('keydown', trap);
		message('');
		if (data && data.items) render(data);
		else {
			if (!cart) q('[data-ln-cart-body]', drawer).innerHTML = '<p class="ln-drawer__loading">Loading your cart…</p>';
			load().catch(fail);
		}
	}

	function close() {
		if (!drawer || drawer.hidden) return;
		drawer.classList.remove('is-open');
		document.documentElement.classList.remove('ln-drawer-open');
		document.removeEventListener('keydown', trap);
		document.querySelectorAll('[data-ln-cart-open]').forEach(function (a) { a.setAttribute('aria-expanded', 'false'); });
		setTimeout(function () { drawer.hidden = true; }, 300);
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}

	/** Adds a product and opens the drawer. Returns a promise. */
	function add(id, quantity) {
		return post('cart/add-item', { id: id, quantity: quantity || 1 }).then(function (data) {
			open(data);
			return data;
		});
	}

	window.lnCart = { open: open, close: close, add: add, refresh: load };

	document.addEventListener('click', function (e) {
		var opener = e.target.closest('[data-ln-cart-open]');
		if (opener && drawer) { e.preventDefault(); open(); return; }
		if (e.target.closest('[data-ln-cart-close]')) close();
	});

	// Shop buttons that add to the cart with WooCommerce's own scripts.
	document.body.addEventListener('wc-blocks_added_to_cart', function () { open(); });
	if (window.jQuery) window.jQuery(document.body).on('added_to_cart', function () { open(); });

	if (window.lnFx) window.lnFx.onChange(function () { if (cart) render(cart); });

	load().catch(function () {});
})();
