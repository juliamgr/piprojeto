(() => {
  'use strict';
  let requestNumber = 0;
  async function fillCheckout(form) {
    if (!form || !form.isConnected) return;
    const request = ++requestNumber;
    const submit = form.querySelector('[type="submit"]');
    let message = form.querySelector('[data-profile-message]');
    if (!message) {
      message = document.createElement('p');
      message.dataset.profileMessage = '';
      message.className = 'checkout-note';
      message.setAttribute('role', 'status');
      form.querySelector('.checkout-fields').before(message);
    }
    submit.disabled = true;
    message.textContent = 'Carregando os dados da sua conta…';
    const controls = form.elements;
    const initial = {};
    for (const key of ['customerName', 'customerEmail', 'customerPhone', 'deliveryRegion', 'deliveryAddress']) {
      initial[key] = controls.namedItem(key).value;
    }
    try {
      const response = await fetch('api/user-session.php', { credentials: 'same-origin', cache: 'no-store' });
      const session = await response.json();
      if (!response.ok || !session.success) throw new Error('Não foi possível carregar sua conta.');
      if (!form.isConnected || request !== requestNumber) return;
      if (!session.loggedIn || !session.customer) {
        message.replaceChildren(document.createTextNode('Entre na sua conta para usar os dados salvos. '));
        const link = document.createElement('a');
        link.href = 'conta.php'; link.textContent = 'Entrar na minha conta';
        message.append(link);
        for (const key of Object.keys(initial)) controls.namedItem(key).value = '';
        return;
      }
      const customer = session.customer;
      const address = [customer.address, customer.neighborhood, customer.zip_code ? `CEP ${customer.zip_code}` : ''].filter(Boolean).join(', ');
      const values = { customerName: customer.name, customerEmail: customer.email, customerPhone: customer.phone, deliveryRegion: customer.city, deliveryAddress: address };
      for (const [key, value] of Object.entries(values)) {
        const control = controls.namedItem(key);
        // A slow response must not erase delivery details the customer is typing.
        if (control.value === initial[key] || key === 'customerName' || key === 'customerEmail') control.value = value || '';
      }
      controls.namedItem('customerName').readOnly = true;
      controls.namedItem('customerEmail').readOnly = true;
      message.textContent = controls.namedItem('deliveryRegion').value
        ? 'Dados preenchidos com sua conta. Você pode ajustar a entrega para este pedido. '
        : 'Sua cidade não está entre as regiões atendidas. Escolha uma região disponível para esta entrega. ';
      const link = document.createElement('a');
      link.href = 'conta.php'; link.textContent = 'Editar dados da conta';
      message.append(link);
      const addressField = controls.namedItem('deliveryAddress');
      const validateAddress = () => addressField.setCustomValidity(addressField.value.length > 220 ? 'Resuma o endereço de entrega para até 220 caracteres.' : '');
      addressField.addEventListener('input', validateAddress);
      validateAddress();
      submit.disabled = false;
    } catch (_) {
      if (!form.isConnected || request !== requestNumber) return;
      message.textContent = 'Não foi possível carregar os dados da conta. ';
      const retry = document.createElement('button');
      retry.type = 'button'; retry.textContent = 'Tentar novamente';
      retry.addEventListener('click', () => fillCheckout(form));
      message.append(retry);
    }
  }
  document.addEventListener('agrolink:checkout-ready', event => fillCheckout(event.detail));
  window.addEventListener('pageshow', () => fillCheckout(document.getElementById('checkout-form')));
})();
