(() => {
  'use strict';
  if (window.agrolinkAccountLoaded) return;
  window.agrolinkAccountLoaded = true;
  let updating = false;
  const icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/><path d="M9 3v15M15 6v15"/></svg>';
  function show(element, visible) {
    if (!element) return;
    element.hidden = !visible;
    if (visible) element.style.removeProperty('display');
    else element.style.setProperty('display','none','important');
  }
  async function refreshAccount() {
    if (updating) return;
    updating = true;
    try {
      const response = await fetch('api/user-session.php',{credentials:'same-origin',cache:'no-store'});
      if (!response.ok) throw new Error('Conta indisponível');
      const session = await response.json();
      const logged = session.loggedIn === true;
      const header = document.querySelector('.header-actions');
      let account = document.getElementById('cliente-conta');
      if (!account && header) {
        account = document.createElement('a'); account.id = 'cliente-conta';
        account.className = 'signup-open'; header.prepend(account);
      }
      if (account) {
        account.href = 'conta.php';
        const first = String(session.name || '').trim().split(/\s+/)[0];
        account.textContent = logged ? (first ? `Olá, ${first} · Minha conta` : 'Minha conta') : 'Entrar';
        show(account,true);
      }
      // Retire the extra link created by an older version of the account script.
      show(document.getElementById('cliente-perfil'),false);
      document.querySelectorAll('[data-open-signup]').forEach(button=>show(button,!logged));
      let tracking = document.getElementById('cliente-entregas');
      if (!tracking && header) {
        tracking = document.createElement('a'); tracking.id = 'cliente-entregas';
        tracking.className = 'delivery-route-link'; tracking.href = 'pedidos.php';
        tracking.title = 'Acompanhar minhas entregas';
        tracking.setAttribute('aria-label','Acompanhar minhas entregas');
        tracking.innerHTML = icon; header.append(tracking);
      }
      show(tracking,logged);
    } catch (_) {
      // A failed session lookup must not leave account actions from a past session.
      show(document.getElementById('cliente-entregas'),false);
    } finally { updating = false; }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',refreshAccount);
  else refreshAccount();
  window.addEventListener('pageshow',refreshAccount);
  window.addEventListener('focus',refreshAccount);
})();
