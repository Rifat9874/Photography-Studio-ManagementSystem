// =====================================================================
// Photo Studio - client side JavaScript (no libraries)
//  1. theme / sidebar / toasts / confirm dialog / quick search
//  2. table + list enhancer (search, filter tabs, sort, pagination)
//  3. AJAX + JSON: dashboard numbers, charts, calendar, username check,
//     packages + photographers for the booking wizard
//  4. forms: password toggle + strength, image preview, validation
// =====================================================================
(function () {
	'use strict';

	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
	var PS = window.PS || { url: '', role: '', nav: [] };
	var SVGNS = 'http://www.w3.org/2000/svg';

	function h(tag, attrs, kids) {
		var n = document.createElement(tag);
		if (attrs) { for (var k in attrs) { if (k === 'text') n.textContent = attrs[k]; else if (k === 'class') n.className = attrs[k]; else n.setAttribute(k, attrs[k]); } }
		(kids || []).forEach(function (c) { if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
		return n;
	}
	function s(tag, attrs) {
		var n = document.createElementNS(SVGNS, tag);
		for (var k in attrs) { n.setAttribute(k, attrs[k]); }
		return n;
	}
	function fmtMoney(n) { return 'Tk ' + Math.round(n).toLocaleString('en-US'); }
	function getJSON(url) {
		return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
	}
	function cssVar(name) { return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
	function debounce(fn, ms) { var t; return function () { var a = arguments, c = this; clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms); }; }

	// ------------------------------------------------------------ 1. shell
	var themeBtn = $('#themeBtn');
	if (themeBtn) {
		themeBtn.addEventListener('click', function () {
			var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
			document.documentElement.setAttribute('data-theme', next);
			localStorage.setItem('ps-theme', next);
		});
	}

	var sidebar = $('#sidebar'), backdrop = $('#sidebarBackdrop');
	function closeMenu() { if (sidebar) { sidebar.classList.remove('open'); backdrop.classList.remove('show'); } }
	if ($('#menuBtn')) {
		$('#menuBtn').addEventListener('click', function () { sidebar.classList.add('open'); backdrop.classList.add('show'); });
		backdrop.addEventListener('click', closeMenu);
	}
	if ($('#collapseBtn')) {
		$('#collapseBtn').addEventListener('click', function () {
			var on = document.documentElement.classList.toggle('sb-collapsed');
			localStorage.setItem('ps-collapsed', on ? '1' : '0');
		});
	}

	// toasts disappear alone
	$$('.toast').forEach(function (t, i) {
		var close = function () { t.classList.add('out'); setTimeout(function () { t.remove(); }, 320); };
		setTimeout(close, 4800 + i * 600);
		t.addEventListener('click', close);
	});

	// confirm dialog for any form / link with data-confirm
	var confirmModal = $('#confirmModal');
	function askConfirm(text, title, yesLabel) {
		return new Promise(function (resolve) {
			if (!confirmModal) { resolve(window.confirm(text)); return; }
			$('#confirmTitle').textContent = title || 'Are you sure?';
			$('#confirmText').textContent = text || 'This action cannot be undone.';
			$('#confirmYes').textContent = yesLabel || 'Yes, continue';
			confirmModal.hidden = false;
			$('#confirmNo').focus();
			var done = function (v) {
				confirmModal.hidden = true;
				$('#confirmYes').onclick = $('#confirmNo').onclick = null;
				document.removeEventListener('keydown', onKey);
				resolve(v);
			};
			var onKey = function (e) { if (e.key === 'Escape') done(false); };
			document.addEventListener('keydown', onKey);
			$('#confirmYes').onclick = function () { done(true); };
			$('#confirmNo').onclick = function () { done(false); };
			confirmModal.onclick = function (e) { if (e.target === confirmModal) done(false); };
		});
	}
	document.addEventListener('submit', function (e) {
		var f = e.target;
		if (f.dataset && f.dataset.confirm && !f.dataset.confirmed) {
			e.preventDefault();
			askConfirm(f.dataset.confirm, f.dataset.confirmTitle, f.dataset.confirmYes).then(function (ok) {
				if (ok) { f.dataset.confirmed = '1'; HTMLFormElement.prototype.submit.call(f); }
			});
			return;
		}
		// stop double submits
		if (f.method && f.method.toLowerCase() === 'post' && !e.defaultPrevented) {
			var btn = $('button[type=submit], input[type=submit]', f);
			if (btn) { setTimeout(function () { btn.disabled = true; }, 0); }
		}
	});
	window.addEventListener('pageshow', function (e) {
		if (e.persisted) { $$('form button[type=submit]:disabled, form input[type=submit]:disabled').forEach(function (b) { b.disabled = false; }); }
	});

	// quick search / command palette (Ctrl + K)
	var palette = $('#palette'), pInput = $('#paletteInput'), pList = $('#paletteList'), pSel = 0, pShown = [];
	function renderPalette() {
		var q = pInput.value.trim().toLowerCase();
		pShown = (PS.nav || []).filter(function (n) { return n.label.toLowerCase().indexOf(q) !== -1; });
		pList.innerHTML = '';
		if (!pShown.length) { pList.appendChild(h('div', { class: 'palette-empty', text: 'No page found' })); return; }
		pSel = Math.min(pSel, pShown.length - 1);
		pShown.forEach(function (n, i) {
			var a = h('a', { class: 'palette-item' + (i === pSel ? ' sel' : ''), href: n.href, text: n.label });
			pList.appendChild(a);
		});
	}
	function openPalette() { if (!palette) return; palette.hidden = false; pInput.value = ''; pSel = 0; renderPalette(); pInput.focus(); }
	function closePalette() { if (palette) palette.hidden = true; }
	if (palette) {
		$('#paletteBtn').addEventListener('click', openPalette);
		pInput.addEventListener('input', function () { pSel = 0; renderPalette(); });
		palette.addEventListener('click', function (e) { if (e.target === palette) closePalette(); });
		pInput.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowDown') { pSel = Math.min(pSel + 1, pShown.length - 1); renderPalette(); e.preventDefault(); }
			if (e.key === 'ArrowUp') { pSel = Math.max(pSel - 1, 0); renderPalette(); e.preventDefault(); }
			if (e.key === 'Enter' && pShown[pSel]) { window.location.href = pShown[pSel].href; }
			if (e.key === 'Escape') closePalette();
		});
	}
	document.addEventListener('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k' && palette) { e.preventDefault(); palette.hidden ? openPalette() : closePalette(); }
	});

	// ------------------------------------------------------------ 2. table + list enhancer
	// A table with data-table (or a div with data-list) gets search, tabs, sorting and pages.
	function enhance(root) {
		var isTable = root.tagName === 'TABLE';
		var body = isTable ? root.tBodies[0] : root;
		var items = isTable ? $$('tr', body).filter(function (r) { return !r.classList.contains('js-empty'); }) : $$('[data-item]', root);
		if (!items.length) return;
		var size = parseInt(root.dataset.pageSize || '10', 10);
		var state = { q: '', status: 'all', col: -1, dir: 1, page: 1, size: size };
		var texts = items.map(function (r) { return (r.dataset.search || r.textContent).toLowerCase().replace(/\s+/g, ' '); });
		var host = isTable ? root.closest('.table-wrap') || root : root;

		// toolbar
		var tools = h('div', { class: 'table-tools' });
		var box = h('div', { class: 'input-icon' });
		box.innerHTML = '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
		var search = h('input', { type: 'search', placeholder: root.dataset.placeholder || 'Search...', 'aria-label': 'Search' });
		box.appendChild(search);
		tools.appendChild(box);
		var count = h('span', { class: 'table-count' });
		tools.appendChild(count);
		var pager = h('div', { class: 'pager' });

		if (isTable) {
			host.parentNode.insertBefore(tools, host);
			host.parentNode.insertBefore(pager, host.nextSibling);
		} else {
			root.parentNode.insertBefore(tools, root);
			root.parentNode.insertBefore(pager, root.nextSibling);
			tools.style.border = '1px solid var(--border)'; tools.style.borderRadius = 'var(--radius)'; tools.style.background = 'var(--surface)'; tools.style.marginBottom = '16px';
			pager.style.border = '0'; pager.style.paddingLeft = '0'; pager.style.paddingRight = '0';
		}

		var empty = null;
		function ensureEmpty() {
			if (empty) return empty;
			if (isTable) {
				var cols = root.tHead ? root.tHead.rows[0].cells.length : 1;
				empty = h('tr', { class: 'js-empty' });
				var td = h('td', { colspan: cols });
				td.innerHTML = '<div class="empty"><b>Nothing found</b>Try a different search or filter.</div>';
				empty.appendChild(td);
			} else {
				empty = h('div', { class: 'card card-pad js-empty' });
				empty.innerHTML = '<div class="empty"><b>Nothing found</b>Try a different search or filter.</div>';
			}
			return empty;
		}

		function matches() {
			return items.filter(function (r, i) {
				if (state.status !== 'all' && (r.dataset.status || '') !== state.status) return false;
				return state.q === '' || texts[i].indexOf(state.q) !== -1;
			});
		}

		function render() {
			var list = matches();
			var pages = Math.max(1, Math.ceil(list.length / state.size));
			if (state.page > pages) state.page = pages;
			var from = (state.page - 1) * state.size, to = from + state.size;
			items.forEach(function (r) { r.hidden = true; });
			list.forEach(function (r, i) { r.hidden = !(i >= from && i < to); });
			if (!list.length) { body.appendChild(ensureEmpty()); empty.hidden = false; } else if (empty) { empty.hidden = true; }
			count.textContent = list.length + (list.length === 1 ? ' record' : ' records');

			pager.innerHTML = '';
			pager.style.display = list.length > state.size ? '' : 'none';
			pager.appendChild(h('small', { text: 'Showing ' + (list.length ? from + 1 : 0) + '-' + Math.min(to, list.length) + ' of ' + list.length }));
			var btns = h('div', { class: 'pager-btns' });
			var mk = function (label, page, dis, on) {
				var b = h('button', { type: 'button', text: label });
				if (dis) b.disabled = true;
				if (on) b.className = 'on';
				b.addEventListener('click', function () { state.page = page; render(); });
				btns.appendChild(b);
			};
			mk('‹', state.page - 1, state.page === 1);
			for (var p = 1; p <= pages; p++) {
				if (pages > 7 && p !== 1 && p !== pages && Math.abs(p - state.page) > 1) { if (p === 2 || p === pages - 1) btns.appendChild(h('span', { text: '…', style: 'padding:0 4px;color:var(--muted)' })); continue; }
				mk(String(p), p, false, p === state.page);
			}
			mk('›', state.page + 1, state.page === pages);
			pager.appendChild(btns);
		}

		search.addEventListener('input', debounce(function () { state.q = search.value.trim().toLowerCase(); state.page = 1; render(); }, 120));

		// status tabs: <div class="tabs" data-tabs-for="id">
		var tabs = root.id ? $('[data-tabs-for="' + root.id + '"]') : null;
		if (tabs) {
			$$('.tab', tabs).forEach(function (t) {
				var st = t.dataset.status;
				var c = st === 'all' ? items.length : items.filter(function (r) { return (r.dataset.status || '') === st; }).length;
				var badge = $('.count', t); if (badge) badge.textContent = c;
				t.addEventListener('click', function () {
					$$('.tab', tabs).forEach(function (x) { x.classList.remove('active'); });
					t.classList.add('active'); state.status = st; state.page = 1; render();
				});
			});
		}
		// any <select data-filter-for="id" data-col="role"> filters by a data attribute
		if (root.id) {
			$$('select[data-filter-for="' + root.id + '"]').forEach(function (sel) {
				sel.dataset.bound = '1';
				tools.insertBefore(sel, count);
				sel.addEventListener('change', function () { state.status = sel.value; state.page = 1; render(); });
			});
		}

		// sorting (tables only)
		if (isTable && root.tHead) {
			$$('th', root.tHead).forEach(function (th, idx) {
				if (th.dataset.nosort !== undefined || th.textContent.trim() === '' || /action/i.test(th.textContent)) return;
				th.classList.add('sortable');
				th.addEventListener('click', function () {
					state.dir = (state.col === idx) ? -state.dir : 1; state.col = idx;
					$$('th', root.tHead).forEach(function (x) { x.classList.remove('sort-asc', 'sort-desc'); });
					th.classList.add(state.dir === 1 ? 'sort-asc' : 'sort-desc');
					var val = function (r) {
						var c = r.cells[idx]; if (!c) return '';
						var v = (c.dataset.sort !== undefined ? c.dataset.sort : c.textContent).trim();
						var n = parseFloat(v.replace(/[^0-9.\-]/g, ''));
						return (/^[A-Za-z]/.test(v) || isNaN(n)) ? v.toLowerCase() : n;
					};
					var pairs = items.map(function (r, i) { return { r: r, t: texts[i] }; });
					pairs.sort(function (a, b) { var x = val(a.r), y = val(b.r); return (x > y ? 1 : x < y ? -1 : 0) * state.dir; });
					items = pairs.map(function (p) { return p.r; }); texts = pairs.map(function (p) { return p.t; });
					items.forEach(function (r) { body.appendChild(r); });
					state.page = 1; render();
				});
			});
		}
		render();
	}
	$$('table[data-table], [data-list]').forEach(enhance);

	// ------------------------------------------------------------ 3. numbers + charts
	function animateNumber(el, to) {
		var fmt = el.dataset.format || 'int';
		var from = parseFloat(el.dataset.shown || '0');
		var t0 = null, dur = 900;
		function show(v) {
			if (fmt === 'money') el.textContent = fmtMoney(v);
			else if (fmt === 'rating') el.textContent = v.toFixed(1);
			else el.textContent = Math.round(v).toLocaleString('en-US');
		}
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { show(to); el.dataset.shown = to; return; }
		function tick(ts) {
			if (t0 === null) t0 = ts;
			var p = Math.min((ts - t0) / dur, 1), e = 1 - Math.pow(1 - p, 3);
			show(from + (to - from) * e);
			if (p < 1) requestAnimationFrame(tick); else el.dataset.shown = to;
		}
		requestAnimationFrame(tick);
	}
	$$('[data-stat]').forEach(function (el) { animateNumber(el, parseFloat(el.dataset.value || '0')); });

	function statusColor(name) { return cssVar('--c-' + name) || '#94a3b8'; }

	function drawDonut(box, labels, values) {
		box.innerHTML = '';
		var total = values.reduce(function (a, b) { return a + b; }, 0);
		if (!total) { box.appendChild(h('div', { class: 'chart-empty', text: 'No bookings yet' })); return; }
		var wrap = h('div', { class: 'donut-wrap' });
		var svg = s('svg', { viewBox: '0 0 200 200' });
		var C = 2 * Math.PI * 72, acc = 0;
		svg.appendChild(s('circle', { cx: 100, cy: 100, r: 72, fill: 'none', stroke: cssVar('--border'), 'stroke-width': 26 }));
		values.forEach(function (v, i) {
			if (!v) return;
			var len = (v / total) * C;
			var c = s('circle', { cx: 100, cy: 100, r: 72, fill: 'none', stroke: statusColor(labels[i]), 'stroke-width': 26, 'stroke-dasharray': '0 ' + C, 'stroke-dashoffset': -acc, transform: 'rotate(-90 100 100)' });
			c.style.transition = 'stroke-dasharray .9s cubic-bezier(.2,.8,.2,1)';
			var title = s('title', {}); title.textContent = labels[i] + ': ' + v; c.appendChild(title);
			svg.appendChild(c);
			requestAnimationFrame(function () { requestAnimationFrame(function () { c.setAttribute('stroke-dasharray', Math.max(len - 2, 0) + ' ' + C); }); });
			acc += len;
		});
		var n = s('text', { x: 100, y: 104, 'text-anchor': 'middle', class: 'ch-center-num' }); n.textContent = total; svg.appendChild(n);
		var l = s('text', { x: 100, y: 124, 'text-anchor': 'middle', class: 'ch-center-label' }); l.textContent = 'bookings'; svg.appendChild(l);
		wrap.appendChild(h('div', { class: 'chart' }, [svg]));
		var legend = h('div', { class: 'legend', style: 'margin:0;flex-direction:column;gap:9px' });
		labels.forEach(function (lab, i) {
			var i1 = h('i'); i1.style.background = statusColor(lab);
			legend.appendChild(h('span', { class: 'legend-item' }, [i1, lab.charAt(0).toUpperCase() + lab.slice(1) + ' · ' + values[i]]));
		});
		wrap.appendChild(legend);
		box.appendChild(wrap);
	}

	function chartFrame(w, hgt, maxV, fmt) {
		var svg = s('svg', { viewBox: '0 0 ' + w + ' ' + hgt, preserveAspectRatio: 'xMidYMid meet' });
		var defs = s('defs', {});
		var g1 = s('linearGradient', { id: 'gradBar', x1: 0, y1: 0, x2: 0, y2: 1 });
		g1.appendChild(s('stop', { offset: '0%', 'stop-color': cssVar('--primary-2') || '#8b5cf6' }));
		g1.appendChild(s('stop', { offset: '100%', 'stop-color': cssVar('--primary') || '#6366f1' }));
		var g2 = s('linearGradient', { id: 'gradArea', x1: 0, y1: 0, x2: 0, y2: 1 });
		g2.appendChild(s('stop', { offset: '0%', 'stop-color': cssVar('--primary') || '#6366f1', 'stop-opacity': .28 }));
		g2.appendChild(s('stop', { offset: '100%', 'stop-color': cssVar('--primary') || '#6366f1', 'stop-opacity': 0 }));
		defs.appendChild(g1); defs.appendChild(g2); svg.appendChild(defs);
		var L = 52, B = 30, T = 14, R = 12;
		for (var i = 0; i <= 4; i++) {
			var y = T + (hgt - T - B) * (1 - i / 4);
			svg.appendChild(s('line', { x1: L, x2: w - R, y1: y, y2: y, class: 'ch-grid' }));
			var t = s('text', { x: L - 8, y: y + 4, 'text-anchor': 'end', class: 'ch-text' });
			var v = maxV * i / 4; t.textContent = fmt(v); svg.appendChild(t);
		}
		return { svg: svg, L: L, B: B, T: T, R: R };
	}
	function niceMax(v) { if (v <= 0) return 4; var p = Math.pow(10, Math.floor(Math.log10(v))); var n = v / p; return (n <= 1 ? 1 : n <= 2 ? 2 : n <= 4 ? 4 : n <= 5 ? 5 : 10) * p; }
	function shortNum(v) { return v >= 1000000 ? (v / 1000000).toFixed(1).replace('.0', '') + 'M' : v >= 1000 ? (v / 1000).toFixed(v >= 10000 ? 0 : 1).replace('.0', '') + 'k' : String(Math.round(v)); }

	function tip(box) {
		var t = h('div', { class: 'chart-tip' }); box.appendChild(t); return t;
	}

	function drawBars(box, labels, values, fmt) {
		box.innerHTML = '';
		var W = 560, H = 250, max = niceMax(Math.max.apply(null, values.concat([0])));
		var f = chartFrame(W, H, max, shortNum);
		var band = (W - f.L - f.R) / labels.length, bw = Math.min(46, band * .56);
		var t = tip(box);
		values.forEach(function (v, i) {
			var bh = (H - f.T - f.B) * (v / max), x = f.L + band * i + (band - bw) / 2, y = H - f.B - bh;
			var r = s('rect', { x: x, y: H - f.B, width: bw, height: 0, rx: 7, class: 'ch-bar' });
			r.style.transition = 'y .8s cubic-bezier(.2,.8,.2,1) ' + (i * 60) + 'ms, height .8s cubic-bezier(.2,.8,.2,1) ' + (i * 60) + 'ms';
			f.svg.appendChild(r);
			requestAnimationFrame(function () { requestAnimationFrame(function () { r.setAttribute('y', y); r.setAttribute('height', Math.max(bh, v > 0 ? 3 : 0)); }); });
			var lb = s('text', { x: x + bw / 2, y: H - 10, 'text-anchor': 'middle', class: 'ch-text' }); lb.textContent = labels[i]; f.svg.appendChild(lb);
			r.addEventListener('mousemove', function () { t.textContent = labels[i] + ': ' + fmt(v); t.style.left = ((x + bw / 2) / W * 100) + '%'; t.style.top = (y / H * 100) + '%'; t.style.opacity = 1; });
			r.addEventListener('mouseleave', function () { t.style.opacity = 0; });
		});
		box.insertBefore(h('div', { class: 'chart' }, [f.svg]), t);
		box.lastChild.style.position = 'relative';
		box.querySelector('.chart').appendChild(t);
	}

	function drawLine(box, labels, values, fmt) {
		box.innerHTML = '';
		var W = 560, H = 250, max = niceMax(Math.max.apply(null, values.concat([0])));
		var f = chartFrame(W, H, max, shortNum);
		var step = (W - f.L - f.R) / Math.max(labels.length - 1, 1);
		var pts = values.map(function (v, i) { return [f.L + step * i, H - f.B - (H - f.T - f.B) * (v / max)]; });
		var d = pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0] + ' ' + p[1]; }).join(' ');
		f.svg.appendChild(s('path', { d: d + ' L' + pts[pts.length - 1][0] + ' ' + (H - f.B) + ' L' + pts[0][0] + ' ' + (H - f.B) + ' Z', class: 'ch-area' }));
		f.svg.appendChild(s('path', { d: d, class: 'ch-line' }));
		var t = tip(box);
		pts.forEach(function (p, i) {
			var c = s('circle', { cx: p[0], cy: p[1], r: 5, class: 'ch-dot' }); f.svg.appendChild(c);
			var lb = s('text', { x: p[0], y: H - 10, 'text-anchor': 'middle', class: 'ch-text' }); lb.textContent = labels[i]; f.svg.appendChild(lb);
			c.addEventListener('mousemove', function () { t.textContent = labels[i] + ': ' + fmt(values[i]); t.style.left = (p[0] / W * 100) + '%'; t.style.top = (p[1] / H * 100) + '%'; t.style.opacity = 1; });
			c.addEventListener('mouseleave', function () { t.style.opacity = 0; });
		});
		var wrap = h('div', { class: 'chart' }, [f.svg]); wrap.appendChild(t); box.appendChild(wrap);
	}

	function loadDashboard() {
		if (!$('[data-stat]') && !$('[data-chart]')) return;
		getJSON(PS.url + '/controller/dashboard-stats.php').then(function (data) {
			if (!data.success) return;
			$$('[data-stat]').forEach(function (el) {
				var v = data.stats[el.dataset.stat];
				if (v !== undefined && parseFloat(el.dataset.value) !== parseFloat(v)) { el.dataset.value = v; animateNumber(el, parseFloat(v)); }
			});
			var note = $('#ajax_note'); if (note) note.textContent = data.message;
			var st = data.charts.status;
			$$('[data-chart="status"]').forEach(function (b) { drawDonut(b, st.labels, st.values); });
			if (data.charts.months) {
				$$('[data-chart="revenue"]').forEach(function (b) { drawBars(b, data.charts.months.labels, data.charts.months.revenue, fmtMoney); });
				$$('[data-chart="bookings"]').forEach(function (b) { drawLine(b, data.charts.months.labels, data.charts.months.bookings, function (v) { return v + ' bookings'; }); });
			}
		}).catch(function () { var note = $('#ajax_note'); if (note) note.textContent = 'Live update is not available right now.'; });
	}
	loadDashboard();

	// ------------------------------------------------------------ calendar (AJAX + JSON)
	var cal = $('#calendar');
	if (cal) {
		var cur = new Date(); cur.setDate(1);
		var events = [], selected = new Date().toISOString().slice(0, 10);
		var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
		var iso = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
		var linkFor = { customer: '/view/customer/my-bookings.php', photographer: '/view/photographer/shoots.php', manager: '/view/manager/bookings.php', admin: '' };

		var loadMonth = function () {
			$('#calTitle').textContent = monthNames[cur.getMonth()] + ' ' + cur.getFullYear();
			getJSON(PS.url + '/controller/calendar-data.php?month=' + cur.getFullYear() + '-' + pad(cur.getMonth() + 1)).then(function (d) { events = d.events || []; drawCal(); showDay(selected); });
		};
		var drawCal = function () {
			cal.innerHTML = '';
			['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(function (n) { cal.appendChild(h('div', { class: 'cal-dow', text: n })); });
			var first = new Date(cur.getFullYear(), cur.getMonth(), 1);
			var start = new Date(first); start.setDate(1 - ((first.getDay() + 6) % 7));
			var today = iso(new Date());
			for (var i = 0; i < 42; i++) {
				var d = new Date(start); d.setDate(start.getDate() + i);
				var key = iso(d);
				if (i >= 35 && d.getMonth() !== cur.getMonth()) break;
				var cell = h('div', { class: 'cal-day' + (d.getMonth() !== cur.getMonth() ? ' other' : '') + (key === today ? ' today' : '') + (key === selected ? ' sel' : ''), 'data-date': key });
				cell.appendChild(h('span', { class: 'cal-num', text: d.getDate() }));
				var evs = events.filter(function (e) { return e.date === key; });
				evs.slice(0, 2).forEach(function (e) { cell.appendChild(h('span', { class: 'cal-ev ev-' + e.status, text: e.title })); });
				if (evs.length > 2) cell.appendChild(h('div', { class: 'cal-more', text: '+' + (evs.length - 2) + ' more' }));
				cell.addEventListener('click', function () { selected = this.dataset.date; $$('.cal-day', cal).forEach(function (x) { x.classList.remove('sel'); }); this.classList.add('sel'); showDay(selected); });
				cal.appendChild(cell);
			}
		};
		var showDay = function (key) {
			var side = $('#calDay'); side.innerHTML = '';
			var dt = new Date(key + 'T00:00:00');
			$('#calDayTitle').textContent = dt.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
			var evs = events.filter(function (e) { return e.date === key; });
			if (!evs.length) { side.innerHTML = '<div class="empty"><b>Nothing scheduled</b>This day is clear.</div>'; return; }
			evs.forEach(function (e) {
				var item = h('div', { class: 'cal-side-item' });
				item.appendChild(h('div', { class: 'flex between items-center gap-1' }, [h('b', { text: e.title }), h('span', { class: 'badge badge-' + e.status, text: e.status })]));
				item.appendChild(h('small', { text: e.time + (e.id && e.type === 'booking' ? ' · Booking #' + e.id : '') }));
				if (e.who) item.appendChild(h('small', { text: e.who }));
				if (e.location) item.appendChild(h('small', { text: e.location }));
				side.appendChild(item);
			});
			if (linkFor[PS.role]) { side.appendChild(h('a', { class: 'btn btn-soft btn-sm mt-2', href: PS.url + linkFor[PS.role], text: 'Open bookings' })); }
		};
		$('#calPrev').addEventListener('click', function () { cur.setMonth(cur.getMonth() - 1); loadMonth(); });
		$('#calNext').addEventListener('click', function () { cur.setMonth(cur.getMonth() + 1); loadMonth(); });
		$('#calToday').addEventListener('click', function () { cur = new Date(); cur.setDate(1); selected = iso(new Date()); loadMonth(); });
		loadMonth();
	}

	// ------------------------------------------------------------ 4. forms
	// show / hide password
	$$('.pw-toggle').forEach(function (b) {
		b.addEventListener('click', function () {
			var inp = b.parentNode.querySelector('input');
			var show = inp.type === 'password';
			inp.type = show ? 'text' : 'password';
			$$('.eye', b).forEach(function (x) { x.hidden = show; });
			$$('.eye-off', b).forEach(function (x) { x.hidden = !show; });
		});
	});

	// password strength meter
	$$('input[data-strength]').forEach(function (inp) {
		var wrap = inp.closest('.field');
		var meter = h('div', { class: 'pw-meter' }, [h('i')]);
		var text = h('div', { class: 'pw-text' });
		wrap.appendChild(meter); wrap.appendChild(text);
		inp.addEventListener('input', function () {
			var v = inp.value, sc = 0;
			if (v.length >= 6) sc++; if (v.length >= 10) sc++;
			if (/[A-Z]/.test(v) && /[a-z]/.test(v)) sc++;
			if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) sc++;
			if (!v) sc = 0;
			var names = ['', 'Weak', 'Fair', 'Good', 'Strong'], cols = ['transparent', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
			var bar = meter.firstChild; bar.style.width = (sc * 25) + '%'; bar.style.background = cols[sc];
			text.textContent = v ? 'Password strength: ' + names[sc] : '';
		});
	});

	// live username check
	var uname = $('#username[data-check]');
	if (uname) {
		var msg = $('#username_error');
		uname.addEventListener('input', debounce(function () {
			var v = uname.value.trim();
			if (v.length < 3) { msg.textContent = ''; return; }
			getJSON(PS.url + '/controller/check-username.php?username=' + encodeURIComponent(v)).then(function (d) {
				msg.textContent = d.message; msg.className = d.available ? 'ok' : 'error';
			});
		}, 350));
	}

	// register form: client side validation
	var reg = $('form[data-validate="register"]');
	if (reg) {
		reg.addEventListener('submit', function (e) {
			var rules = [
				['name', function (v) { return v.trim().length >= 3; }, 'Name must be at least 3 characters'],
				['username', function (v) { return /^[A-Za-z0-9_.]{3,30}$/.test(v); }, 'Use 3-30 letters, numbers, dot or underscore'],
				['email', function (v) { return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v); }, 'Please write a valid email'],
				['phone', function (v) { return /^[0-9]{11}$/.test(v); }, 'Phone must be exactly 11 digits'],
				['password', function (v) { return v.length >= 6; }, 'Password must be at least 6 characters'],
				['confirm_password', function (v) { return v === $('#password').value; }, 'Passwords do not match']
			];
			var bad = false;
			rules.forEach(function (r) {
				var inp = $('#' + r[0]), err = $('#' + r[0] + '_error');
				if (!err) return;
				if (!r[1](inp.value)) { err.textContent = r[2]; err.className = 'error'; if (!bad) inp.focus(); bad = true; }
				else if (r[0] !== 'username') { err.textContent = ''; }
			});
			if (bad) e.preventDefault();
		});
		var roleRadios = $$('input[name="role"]', reg), specBox = $('#specialization_box');
		var toggleSpec = function () { var r = $('input[name="role"]:checked', reg); specBox.hidden = !(r && r.value === 'photographer'); };
		roleRadios.forEach(function (r) { r.addEventListener('change', toggleSpec); }); toggleSpec();
	}

	// demo accounts on the login page
	$$('.demo-list button').forEach(function (b) {
		b.addEventListener('click', function () { $('#login_id').value = b.dataset.u; $('#password').value = b.dataset.p; $('#password').focus(); });
	});

	// file input preview
	$$('input[type=file][data-preview]').forEach(function (inp) {
		inp.addEventListener('change', function () {
			var box = $('#' + inp.dataset.preview); if (!box) return;
			box.innerHTML = '';
			if (inp.files && inp.files[0] && inp.files[0].type.indexOf('image/') === 0) {
				var img = h('img', { alt: 'Preview' }); img.src = URL.createObjectURL(inp.files[0]);
				box.appendChild(img); box.className = 'img-preview';
			}
		});
	});

	// gallery: lightbox + filters
	var gallery = $('#gallery');
	if (gallery) {
		var filt = { cat: 'all', pg: 'all' };
		var applyGallery = function () {
			var shown = 0;
			$$('.g-item', gallery).forEach(function (g) {
				var ok = (filt.cat === 'all' || g.dataset.cat === filt.cat) && (filt.pg === 'all' || g.dataset.pg === filt.pg);
				g.hidden = !ok; if (ok) shown++;
			});
			var em = $('#galleryEmpty'); if (em) em.hidden = shown > 0;
			var lab = $('#pgFilterLabel');
			if (lab) { lab.hidden = filt.pg === 'all'; }
		};
		$$('[data-gal-cat]').forEach(function (b) {
			b.addEventListener('click', function () {
				$$('[data-gal-cat]').forEach(function (x) { x.classList.remove('active'); }); b.classList.add('active');
				filt.cat = b.dataset.galCat; applyGallery();
			});
		});
		$$('[data-gal-pg]').forEach(function (b) {
			b.addEventListener('click', function (e) { e.preventDefault(); filt.pg = b.dataset.galPg; $('#pgFilterName').textContent = b.dataset.name; applyGallery(); gallery.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
		});
		var clear = $('#pgFilterClear'); if (clear) clear.addEventListener('click', function () { filt.pg = 'all'; applyGallery(); });
		gallery.addEventListener('click', function (e) {
			var g = e.target.closest('.g-item'); if (!g || !g.dataset.full) return;
			var lb = h('div', { class: 'lightbox' }, [h('div', {}, [h('img', { src: g.dataset.full, alt: '' }), h('p', { text: g.dataset.caption || '' })])]);
			var close = function () { lb.remove(); document.removeEventListener('keydown', esc); };
			var esc = function (ev) { if (ev.key === 'Escape') close(); };
			lb.addEventListener('click', close); document.addEventListener('keydown', esc); document.body.appendChild(lb);
		});
		applyGallery();
	}

	// package page: category filter
	$$('[data-pkg-cat]').forEach(function (b) {
		b.addEventListener('click', function () {
			$$('[data-pkg-cat]').forEach(function (x) { x.classList.remove('active'); }); b.classList.add('active');
			$$('.pkg-card[data-cat]').forEach(function (c) { c.parentNode.hidden = !(b.dataset.pkgCat === 'all' || c.dataset.cat === b.dataset.pkgCat); });
		});
	});

	// ------------------------------------------------------------ booking wizard
	var wiz = $('#bookingForm');
	if (wiz) {
		var pkgBox = $('#pkgChoices'), sel = { cat: '', pkg: null };
		var sum = { cat: $('#sumCat'), pkg: $('#sumPkg'), dur: $('#sumDur'), date: $('#sumDate'), time: $('#sumTime'), loc: $('#sumLoc'), total: $('#sumTotal') };
		var upd = function () {
			sum.cat.textContent = sel.cat || '-';
			sum.pkg.textContent = sel.pkg ? sel.pkg.name : '-';
			sum.dur.textContent = (sel.pkg && sel.pkg.duration) || '-';
			sum.total.textContent = sel.pkg ? fmtMoney(parseFloat(sel.pkg.price)) : 'Tk 0';
			var d = $('#booking_date').value;
			sum.date.textContent = d ? new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) : '-';
			var t = $('input[name=booking_time]:checked'); sum.time.textContent = t ? t.value : '-';
			var l = $('#location').value.trim(); sum.loc.textContent = l || '-';
		};
		var loadPackages = function (catId, catName, preselect) {
			sel.cat = catName; sel.pkg = null; pkgBox.innerHTML = '<div class="muted">Loading packages...</div>'; upd();
			getJSON(PS.url + '/controller/get-packages.php?category_id=' + encodeURIComponent(catId)).then(function (data) {
				pkgBox.innerHTML = '';
				if (!data.packages.length) { pkgBox.appendChild(h('div', { class: 'muted', text: 'No package in this category yet.' })); return; }
				data.packages.forEach(function (p) {
					var lab = h('label', { class: 'choice' });
					var inp = h('input', { type: 'radio', name: 'package_id', value: p.id, required: 'required' });
					var box = h('span', { class: 'choice-box' }, [
						h('span', { class: 'choice-title', text: p.name }),
						h('span', { class: 'choice-sub', text: (p.duration || '') + (p.inclusions ? ' · ' + p.inclusions : '') }),
						h('span', { class: 'choice-price', text: fmtMoney(parseFloat(p.price)) })
					]);
					lab.appendChild(inp); lab.appendChild(box); pkgBox.appendChild(lab);
					inp.addEventListener('change', function () { sel.pkg = p; upd(); });
					if (preselect && String(p.id) === String(preselect)) { inp.checked = true; sel.pkg = p; upd(); }
				});
			});
		};
		$$('input[name=category_id]', wiz).forEach(function (r) {
			r.addEventListener('change', function () { loadPackages(r.value, r.dataset.name, null); });
		});
		var pre = wiz.dataset.prePkg, preCat = wiz.dataset.preCat;
		if (preCat) { var rc = $('input[name=category_id][value="' + preCat + '"]'); if (rc) { rc.checked = true; loadPackages(rc.value, rc.dataset.name, pre); } }

		var checkFree = function () {
			var d = $('#booking_date').value, t = $('input[name=booking_time]:checked');
			var box = $('#availBox'); if (!d) { box.className = 'avail-box'; box.textContent = 'Pick a date to see which photographers are free.'; return; }
			box.className = 'avail-box'; box.textContent = 'Checking photographers...';
			getJSON(PS.url + '/controller/get-photographers.php?date=' + d + (t ? '&slot=' + t.value : '')).then(function (data) {
				var free = data.photographers.filter(function (p) { return !p.busy; });
				box.innerHTML = '';
				if (!free.length) { box.className = 'avail-box bad'; box.textContent = 'No photographer has marked this ' + (t ? 'slot' : 'date') + ' as free yet. You can still send the request, the manager will arrange one.'; return; }
				box.className = 'avail-box good';
				box.appendChild(h('b', { text: free.length + ' photographer' + (free.length > 1 ? 's' : '') + ' free: ' }));
				box.appendChild(document.createTextNode(free.map(function (p) { return p.name + ' (' + p.specialization + (p.time_slot && !t ? ', ' + p.time_slot : '') + (p.avg_rating ? ', ★' + p.avg_rating : '') + ')'; }).join(' · ')));
			});
		};
		$('#booking_date').addEventListener('change', function () { upd(); checkFree(); });
		$$('input[name=booking_time]').forEach(function (r) { r.addEventListener('change', function () { upd(); checkFree(); }); });
		$('#location').addEventListener('input', upd);
		wiz.addEventListener('submit', function (e) {
			if (!sel.pkg) { e.preventDefault(); alert('Please choose a category and a package first'); return; }
			if ($('#location').value.trim().length < 5) { e.preventDefault(); $('#location_error').textContent = 'Please write the full location'; $('#location').focus(); }
		});
		upd(); if ($('#booking_date').value) checkFree();
	}

	// assign page: click anywhere on the photographer card (handled by label), nothing else needed
})();

// scroll reveal for the public landing page
(function () {
	var items = document.querySelectorAll('.reveal');
	if (!items.length) return;
	if (!('IntersectionObserver' in window)) { items.forEach(function (i) { i.classList.add('in'); }); return; }
	var io = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
	}, { threshold: .12 });
	items.forEach(function (i) { io.observe(i); });
})();
