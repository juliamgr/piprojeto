(() => {
  'use strict';
  const { labels, steps, estimate } = window.DeliveryModel;
  const list = document.getElementById('orders');
  const notice = document.getElementById('update-message');
  const dialog = document.getElementById('delivery-dialog');
  const details = document.getElementById('delivery-details');
  const refresh = document.getElementById('refresh');
  const older = document.getElementById('older');
  const newest = document.getElementById('newest');
  let orders = [], selected = null, before = null, nextBefore = null;
  let loading = false, stopped = false, clockOffset = 0, signature = '';
  const routeIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/><path d="M9 3v15M15 6v15"/></svg>';
  const date = value => value ? new Intl.DateTimeFormat('pt-BR', {dateStyle:'short',timeStyle:'short',timeZone:'America/Sao_Paulo'}).format(new Date(value)) : 'Não informado';
  const money = value => new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(value);
  function node(tag, text, className) {
    const element = document.createElement(tag);
    if (text !== undefined) element.textContent = text;
    if (className) element.className = className;
    return element;
  }
  function estimateNode(order) {
    const p = node('p', estimate(order, Date.now()+clockOffset), 'estimate');
    p.dataset.estimateId = String(order.id);
    return p;
  }
  function openDetails(order) {
    selected = order.id;
    document.getElementById('delivery-title').textContent = `Entrega ${order.code}`;
    details.replaceChildren();
    details.append(node('p', labels[order.status] || 'Status em atualização', 'status'));
    details.append(estimateNode(order));
    if (order.estimateUpdatedAt && !['concluido','cancelado'].includes(order.status)) details.append(node('p', `Previsão informada pela equipe em ${date(order.estimateUpdatedAt)}`, 'muted'));
    if (order.estimatedAt && !['concluido','cancelado'].includes(order.status)) details.append(node('p', `Previsão: ${date(order.estimatedAt)} · horário de Brasília`));
    const timeline = node('ol', undefined, 'delivery-timeline');
    if (order.status === 'cancelado') {
      timeline.append(node('li','Pedido cancelado. A entrega não seguirá para o destino.','cancelled'));
    } else {
      const current = steps.indexOf(order.status);
      steps.forEach((step, index) => {
        const li = node('li', labels[step], index <= current ? 'done' : '');
        if (index === current) li.setAttribute('aria-current','step');
        if (step === 'enviado' && order.dispatchedAt) li.append(node('small',date(order.dispatchedAt)));
        if (step === 'concluido' && order.deliveredAt) li.append(node('small',date(order.deliveredAt)));
        timeline.append(li);
      });
    }
    details.append(timeline);
    details.append(node('h3','Destino da entrega'));
    details.append(node('p',`${order.address} — ${order.region}`));
    const map = node('a','Ver endereço no mapa','map-link');
    map.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(`${order.address}, ${order.region}, Rio Grande do Sul, Brasil`);
    map.target = '_blank'; map.rel = 'noopener noreferrer';
    details.append(map);
    details.append(node('p','O acompanhamento mostra as etapas informadas pela equipe e a previsão de chegada. O mapa exibe o endereço de entrega, sem localização ao vivo do entregador.','muted'));
    if (!dialog.open) dialog.showModal();
  }
  function render() {
    const fragment = document.createDocumentFragment();
    if (!orders.length) fragment.append(node('p',before ? 'Não há mais pedidos nesta página.' : 'Você ainda não tem pedidos vinculados à sua conta. Escolha seus produtos na feira para começar.','empty'));
    for (const order of orders) {
      const card = node('article', undefined,'order-card');
      card.append(node('p',date(order.createdAt),'muted'));
      card.append(node('h2',order.code));
      card.append(node('p',labels[order.status] || 'Status em atualização','status'));
      card.append(estimateNode(order));
      if (order.status === 'enviado') card.append(node('p',order.dispatchedAt ? `Saída confirmada em ${date(order.dispatchedAt)}` : 'A equipe confirmou que sua entrega saiu.', 'muted'));
      if (order.estimatedAt && !['concluido','cancelado'].includes(order.status)) card.append(node('p',`Até ${date(order.estimatedAt)} (Brasília)`,'muted'));
      card.append(node('p',money(order.total),'total'));
      const button = node('button',undefined,'route-button');
      button.type = 'button';
      button.innerHTML = routeIcon;
      button.append(node('span','Acompanhar entrega'));
      button.setAttribute('aria-label',`Acompanhar entrega ${order.code}`);
      button.addEventListener('click',()=>openDetails(order));
      card.append(button); fragment.append(card);
    }
    list.replaceChildren(fragment);
    if (dialog.open) {
      const order = orders.find(order=>order.id === selected);
      if (order) openDetails(order); else dialog.close();
    }
  }
  function tick() {
    document.querySelectorAll('[data-estimate-id]').forEach(element=>{
      const order = orders.find(order=>String(order.id)===element.dataset.estimateId);
      if (order) element.textContent = estimate(order,Date.now()+clockOffset);
    });
  }
  async function load() {
    if (loading || stopped) return;
    loading = true; refresh.disabled = true; older.disabled = true; newest.disabled = true;
    list.setAttribute('aria-busy','true');
    const controller = new AbortController();
    const timeout = setTimeout(()=>controller.abort(),15000);
    try {
      const response = await fetch('api/deliveries.php'+(before ? `?before=${before}` : ''),{credentials:'same-origin',cache:'no-store',signal:controller.signal});
      if (response.status === 401) {
        stopped = true; orders = []; list.replaceChildren(); dialog.close(); details.replaceChildren();
        notice.replaceChildren(node('span','Sua sessão expirou. '));
        const login = node('a','Entrar novamente'); login.href = 'conta.php'; notice.append(login);
        older.hidden = true; newest.hidden = true; return;
      }
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error('Falha na consulta');
      clockOffset = Date.parse(data.serverNow)-Date.now();
      orders = data.orders; nextBefore = data.nextBefore;
      const updated = JSON.stringify(orders);
      if (updated !== signature) { signature = updated; render(); }
      tick();
      older.hidden = !nextBefore; newest.hidden = before === null;
      notice.textContent = `Atualizado às ${new Intl.DateTimeFormat('pt-BR',{timeStyle:'short',timeZone:'America/Sao_Paulo'}).format(new Date(data.serverNow))}. Consulta automática a cada 30 segundos.`;
    } catch (_) {
      notice.textContent = 'Não foi possível atualizar agora. '+(orders.length ? 'Os dados exibidos são da última consulta. ' : '')+'Clique em Atualizar agora para tentar novamente.';
    } finally {
      clearTimeout(timeout); loading = false; refresh.disabled = stopped; older.disabled = false; newest.disabled = false; list.setAttribute('aria-busy','false');
    }
  }
  refresh.addEventListener('click',load);
  older.addEventListener('click',()=>{if(!loading && nextBefore) {before=nextBefore;load();}});
  newest.addEventListener('click',()=>{if(!loading){before=null;load();}});
  document.getElementById('close-dialog').addEventListener('click',()=>dialog.close());
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)load();});
  window.addEventListener('pageshow',load);
  setInterval(()=>{if(!document.hidden){tick();load();}},30000);
  load();
})();
