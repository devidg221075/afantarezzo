(function () {
  'use strict';
  var toggle = document.querySelector('[data-search-toggle]');
  var panel = document.getElementById('ricerca');
  if (!toggle || !panel) return;
  var input = panel.querySelector('input');
  var list = panel.querySelector('[data-search-results]');
  var status = panel.querySelector('[data-search-status]');
  var closeBtn = panel.querySelector('[data-search-close]');
  var base = document.currentScript ? document.currentScript.src : '';
  var indice = null, loading = false, pending = false;

  function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
  function load(cb) {
    if (indice) return cb();
    if (loading) { pending = true; return; }
    loading = true;
    var s = document.createElement('script');
    s.src = new URL('indice-ricerca.js', base || location.href).href;
    s.onload = function () { indice = (window.AFANT_INDICE || []).map(function (v) { v._t = norm(v.t); v._c = norm((v.c || '') + ' ' + (v.k || '')); v._x = norm(v.x); return v; }); loading = false; cb(); if (pending) { pending = false; cb(); } };
    s.onerror = function () { loading = false; status.textContent = 'Ricerca non disponibile al momento.'; };
    document.head.appendChild(s);
  }
  function snippet(v, tokens) {
    var raw = v.x || '', n = norm(raw), pos = -1, i;
    for (i = 0; i < tokens.length; i++) { pos = n.indexOf(tokens[i]); if (pos >= 0) break; }
    if (pos < 0) return raw.slice(0, 130) + (raw.length > 130 ? '…' : '');
    var a = Math.max(0, pos - 50), b = Math.min(raw.length, pos + 100);
    return (a > 0 ? '…' : '') + raw.slice(a, b) + (b < raw.length ? '…' : '');
  }
  function mark(el, text, tokens) {
    var n = norm(text), ranges = [], i, p;
    tokens.forEach(function (t) { p = n.indexOf(t); while (p >= 0) { ranges.push([p, p + t.length]); p = n.indexOf(t, p + t.length); } });
    ranges.sort(function (a, b) { return a[0] - b[0]; });
    var last = 0;
    ranges.forEach(function (r) {
      if (r[0] < last) return;
      el.appendChild(document.createTextNode(text.slice(last, r[0])));
      var m = document.createElement('mark'); m.textContent = text.slice(r[0], r[1]); el.appendChild(m); last = r[1];
    });
    el.appendChild(document.createTextNode(text.slice(last)));
  }
  function search() {
    var q = norm(input.value).trim();
    list.textContent = '';
    if (q.length < 2) { status.textContent = q ? 'Scrivi almeno due lettere.' : ''; return; }
    var tokens = q.split(/\s+/).filter(Boolean);
    var res = [];
    indice.forEach(function (v) {
      var score = 0, ok = tokens.every(function (t) {
        var s = 0;
        if (v._t.indexOf(t) >= 0) s += 6 + (v._t.indexOf(t) === 0 ? 2 : 0);
        if (v._c.indexOf(t) >= 0) s += 3;
        if (v._x.indexOf(t) >= 0) s += 1;
        score += s; return s > 0;
      });
      if (ok) { if (v._t === q) score += 20; if (v.k === 'Pagina') score += 2; res.push({ v: v, s: score }); }
    });
    res.sort(function (a, b) { return b.s - a.s || String(b.v.d).localeCompare(String(a.v.d)); });
    status.textContent = res.length ? res.length + (res.length === 1 ? ' risultato' : ' risultati') + (res.length > 12 ? ' (mostrati i primi 12)' : '') : 'Nessun risultato per “' + input.value.trim() + '”.';
    res.slice(0, 12).forEach(function (r) {
      var v = r.v, li = document.createElement('li'), a = document.createElement('a');
      a.href = v.u;
      if (/\.(pdf|pptx?|docx?)$/i.test(v.u)) a.setAttribute('download', '');
      var meta = document.createElement('span'); meta.className = 'rs-meta'; meta.textContent = v.k + (v.d ? ' · ' + v.d : '');
      var t = document.createElement('strong'); mark(t, v.t, tokens);
      a.appendChild(meta); a.appendChild(t);
      var sn = v.x ? snippet(v, tokens) : '';
      if (sn) { var p = document.createElement('span'); p.className = 'rs-snip'; mark(p, sn, tokens); a.appendChild(p); }
      li.appendChild(a); list.appendChild(li);
    });
  }
  var timer;
  function open() {
    panel.hidden = false; toggle.setAttribute('aria-expanded', 'true');
    load(function () { if (input.value) search(); });
    input.focus(); input.select();
  }
  function close(back) {
    panel.hidden = true; toggle.setAttribute('aria-expanded', 'false');
    if (back) toggle.focus();
  }
  toggle.addEventListener('click', function () { panel.hidden ? open() : close(true); });
  closeBtn.addEventListener('click', function () { close(true); });
  input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { load(search); }, 90); });
  panel.addEventListener('keydown', function (e) {
    var links = Array.prototype.slice.call(list.querySelectorAll('a')), i = links.indexOf(document.activeElement);
    if (e.key === 'Escape') { close(true); }
    else if (e.key === 'ArrowDown') { e.preventDefault(); (links[i + 1] || links[0] || input).focus(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); (i <= 0 ? input : links[i - 1]).focus(); }
    else if (e.key === 'Enter' && document.activeElement === input && links[0]) { links[0].click(); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === '/' && panel.hidden && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || '') && !e.ctrlKey && !e.metaKey) { e.preventDefault(); open(); }
  });
  document.addEventListener('click', function (e) {
    if (!panel.hidden && !panel.contains(e.target) && !toggle.contains(e.target)) close(false);
  });
})();
