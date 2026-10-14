(() => {
  'use strict';
  let sending = false;
  let pending = null;
  const storageKey = 'agrolink-pedido-pendente-v1';
  try { pending = JSON.parse(sessionStorage.getItem(storageKey) || 'null'); } catch (_) {}

  async function jsonRequest(url, options = {}) {
    const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options });
    let data;
    try { data = await response.json(); }
    catch (_) { throw new Error('O servidor não respondeu corretamente. Confira o Apache e o banco.'); }
    if (!response.ok || data.success === false) {
      const error = new Error(data.message || 'Não foi possível enviar o pedido.');
      error.httpStatus = response.status;
      throw error;
    }
    return data;
  }
  function requestId() {
    return Array.from(crypto.getRandomValues(new Uint8Array(16)), value => value.toString(16).padStart(2, '0')).join('');
  }

  // Captura antes do listener antigo de script.js, evitando dois envios.
  document.addEventListener('submit', async event => {
    const form = event.target;
    if (form.id !== 'checkout-form') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (sending || !form.reportValidity()) return;
    const button = form.querySelector('[type="submit"]');
    const originalText = button.textContent;
    sending = true;
    button.disabled = true;
    button.textContent = 'Enviando pedido…';
    try {
      if (typeof state === 'undefined' || typeof cartItems !== 'function' || typeof renderCart !== 'function') {
        throw new Error('O código da sacola mudou. Confira a integração com script.js.');
      }
      const session = await jsonRequest('api/user-session.php');
      if (!session.loggedIn) {
        alert('Entre na sua conta para finalizar. Sua sacola está salva neste navegador.');
        window.location.assign('conta.php');
        return;
      }
      const fields = new FormData(form);
      const payload = {
        customer: {
          name: String(fields.get('customerName') || '').trim(),
          email: String(fields.get('customerEmail') || '').trim(),
          phone: String(fields.get('customerPhone') || '').trim(),
          region: String(fields.get('deliveryRegion') || ''),
          address: String(fields.get('deliveryAddress') || '').trim()
        },
        paymentMethod: String(fields.get('paymentMethod') || ''),
        items: cartItems().map(product => ({ productId: product.id, quantity: Number(state.cart[product.id]) }))
      };
      const signature = JSON.stringify([session.userId, payload]);
      if (!pending || pending.signature !== signature) pending = { signature, requestId: requestId() };
      try { sessionStorage.setItem(storageKey, JSON.stringify(pending)); } catch (_) {}
      const result = await jsonRequest('api/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': session.csrf },
        body: JSON.stringify({ ...payload, requestId: pending.requestId })
      });
      state.receipt = result.order;
      state.cart = {};
      try { localStorage.setItem('agrolink-cart', '{}'); } catch (_) {}
      pending = null;
      try { sessionStorage.removeItem(storageKey); } catch (_) {}
      state.checkoutStep = 'success';
      if (typeof renderCartCount === 'function') renderCartCount();
      renderCart();
      const statusElement = document.getElementById('status');
      if (statusElement) statusElement.textContent = `Pedido ${result.order.code} recebido pela equipe Agrolink.`;
    } catch (error) {
      alert(error.message);
      if (error.httpStatus === 401) window.location.assign('conta.php');
    } finally {
      sending = false;
      button.disabled = false;
      button.textContent = originalText;
    }
  }, true);
})();
