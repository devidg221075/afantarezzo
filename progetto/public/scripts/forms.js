(function () {
  'use strict';
  document.querySelectorAll('[data-async-form]').forEach(function (form) {
    var feedback = form.parentElement.querySelector('[data-form-feedback]');
    var button = form.querySelector('button[type="submit"]');
    if (!feedback || !button || !window.fetch || !window.FormData) return;
    var title = feedback.querySelector('[data-feedback-title]');
    var message = feedback.querySelector('[data-feedback-message]');
    var label = feedback.querySelector('[data-feedback-label]');
    var icon = feedback.querySelector('[data-feedback-icon]');
    var actions = feedback.querySelector('[data-feedback-actions]');
    var originalText = button.textContent;
    var sending = false;

    function show(kind, heading, text, finished) {
      feedback.dataset.state = kind;
      title.textContent = heading;
      message.textContent = text;
      label.textContent = kind === 'success' ? 'Conferma' : 'Avviso';
      icon.textContent = kind === 'success' ? '✓' : '!';
      actions.hidden = !finished;
      form.hidden = finished;
      feedback.hidden = false;
      feedback.focus();
    }

    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (sending) return;
      sending = true;
      button.disabled = true;
      button.textContent = 'Invio in corso…';
      feedback.hidden = true;
      try {
        var response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', redirect: 'error' });
        var text = (await response.text()).trim();
        var plain = (response.headers.get('content-type') || '').toLowerCase().startsWith('text/plain');
        if (!plain || !text || text.length > 1200) throw new Error('Risposta non riconosciuta');
        if (response.ok) {
          show('success', 'Richiesta inviata', text, true);
        } else {
          var saved = text.indexOf('La richiesta è stata registrata') === 0;
          show('error', saved ? 'Richiesta registrata, notifica non inviata' : 'Non è stato possibile inviare', text, saved);
          if (!saved && window.turnstile) {
            try { window.turnstile.reset(form.querySelector('.cf-turnstile')); } catch (_) { /* La risposta resta comunque visibile. */ }
          }
        }
      } catch (_) {
        show('error', 'Non possiamo confermare l’invio', 'Non abbiamo ricevuto una risposta dal servizio. La richiesta potrebbe essere stata registrata: prima di riprovare, contatta AFANT per evitare duplicati.', true);
      } finally {
        sending = false;
        button.disabled = false;
        button.textContent = originalText;
      }
    });
  });
})();
