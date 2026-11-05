/**
 * Approximate prices in the visitor's own currency.
 * Prices are charged in INR; this only shows a converted view.
 * The currency is guessed from the device's time zone (no location lookup)
 * and can be changed by the visitor; the choice is remembered.
 */
(function () {
	var rates = window.lnFxRates || {};
	var KEY = 'lnCurrency';

	// Time zone → currency. Zones not listed fall back by region below.
	var ZONES = {
		'Asia/Kolkata': 'INR', 'Asia/Calcutta': 'INR',
		'Europe/London': 'GBP', 'Europe/Belfast': 'GBP', 'Europe/Guernsey': 'GBP', 'Europe/Jersey': 'GBP', 'Europe/Isle_of_Man': 'GBP',
		'Europe/Zurich': 'CHF', 'Europe/Oslo': 'NOK', 'Europe/Stockholm': 'SEK', 'Europe/Copenhagen': 'DKK',
		'Europe/Warsaw': 'PLN', 'Europe/Prague': 'CZK', 'Europe/Budapest': 'HUF', 'Europe/Bucharest': 'RON', 'Europe/Istanbul': 'TRY', 'Asia/Istanbul': 'TRY',
		'America/Toronto': 'CAD', 'America/Vancouver': 'CAD', 'America/Edmonton': 'CAD', 'America/Winnipeg': 'CAD', 'America/Halifax': 'CAD',
		'America/Regina': 'CAD', 'America/St_Johns': 'CAD', 'America/Montreal': 'CAD', 'America/Moncton': 'CAD', 'America/Whitehorse': 'CAD',
		'America/Mexico_City': 'MXN', 'America/Monterrey': 'MXN', 'America/Tijuana': 'MXN', 'America/Cancun': 'MXN',
		'America/Sao_Paulo': 'BRL', 'America/Fortaleza': 'BRL', 'America/Recife': 'BRL', 'America/Manaus': 'BRL', 'America/Bahia': 'BRL',
		'Pacific/Auckland': 'NZD', 'Pacific/Chatham': 'NZD', 'Pacific/Honolulu': 'USD',
		'Asia/Singapore': 'SGD', 'Asia/Dubai': 'AED', 'Asia/Riyadh': 'SAR', 'Asia/Qatar': 'QAR', 'Asia/Kuwait': 'KWD', 'Asia/Muscat': 'OMR', 'Asia/Bahrain': 'BHD',
		'Asia/Tokyo': 'JPY', 'Asia/Hong_Kong': 'HKD', 'Asia/Kuala_Lumpur': 'MYR', 'Asia/Kuching': 'MYR', 'Asia/Bangkok': 'THB',
		'Asia/Jakarta': 'IDR', 'Asia/Makassar': 'IDR', 'Asia/Manila': 'PHP', 'Asia/Seoul': 'KRW', 'Asia/Shanghai': 'CNY', 'Asia/Chongqing': 'CNY', 'Asia/Taipei': 'TWD',
		'Asia/Colombo': 'LKR', 'Asia/Kathmandu': 'NPR', 'Asia/Katmandu': 'NPR', 'Asia/Dhaka': 'BDT', 'Asia/Karachi': 'PKR', 'Asia/Jerusalem': 'ILS', 'Asia/Tel_Aviv': 'ILS',
		'Africa/Johannesburg': 'ZAR', 'Africa/Lagos': 'NGN', 'Africa/Nairobi': 'KES', 'Africa/Cairo': 'EGP'
	};

	function guess() {
		var tz = '';
		try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
		if (ZONES[tz]) return ZONES[tz];
		if (/^Europe\//.test(tz)) return 'EUR';
		if (/^Australia\//.test(tz)) return 'AUD';
		if (/^America\//.test(tz)) return 'USD';
		return 'USD';
	}

	function stored() {
		try { return localStorage.getItem(KEY); } catch (e) { return null; }
	}

	var current = stored();
	if (!current || !rates[current]) current = guess();
	if (!rates[current]) current = rates.USD ? 'USD' : null;

	var listeners = [];

	function format(amount, code) {
		try {
			return new Intl.NumberFormat(undefined, { style: 'currency', currency: code, maximumFractionDigits: amount >= 1000 ? 0 : 2 }).format(amount);
		} catch (e) {
			return code + ' ' + amount.toFixed(2);
		}
	}

	function label(code) {
		try {
			var n = new Intl.DisplayNames(undefined, { type: 'currency' }).of(code);
			return n ? code + ' · ' + n : code;
		} catch (e) {
			return code;
		}
	}

	window.lnFx = {
		/** Currency being shown, or null when no rates are available. */
		get currency() { return current; },
		/** True when a converted amount should be shown (not INR, rates known). */
		active: function () { return !!current && current !== 'INR' && !!rates[current]; },
		/** "≈ $21.15" for an INR amount, or '' when not active. */
		format: function (inr) {
			if (!this.active() || !isFinite(inr)) return '';
			return '≈ ' + format(inr * rates[current], current);
		},
		/** Fills a <select> with the available currencies. */
		fillSelect: function (select) {
			Object.keys(rates).sort().forEach(function (code) {
				var o = document.createElement('option');
				o.value = code;
				o.textContent = label(code);
				if (code === current) o.selected = true;
				select.appendChild(o);
			});
		},
		set: function (code) {
			if (!rates[code]) return;
			current = code;
			try { localStorage.setItem(KEY, code); } catch (e) {}
			listeners.forEach(function (fn) { fn(code); });
		},
		onChange: function (fn) { listeners.push(fn); }
	};
})();
