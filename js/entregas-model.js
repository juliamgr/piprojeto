/* Shared presentation rules: elapsed time never changes the order status. */
(function (root) {
  'use strict';
  const labels = { recebido: 'Aguardando confirmação', confirmado: 'Confirmado', separacao: 'Em preparação', enviado: 'Saiu para entrega', concluido: 'Entregue', cancelado: 'Cancelado' };
  const steps = ['recebido', 'confirmado', 'separacao', 'enviado', 'concluido'];
  function estimate(order, now) {
    if (order.status === 'cancelado') return 'Entrega cancelada';
    if (order.status === 'concluido') return 'Entrega concluída';
    const at = Date.parse(order.estimatedAt);
    if (!Number.isFinite(at)) return 'A equipe ainda não informou a previsão';
    const minutes = Math.ceil((at - now) / 60000);
    if (minutes <= 0) return 'Prazo estimado ultrapassado. Aguardando atualização da equipe.';
    if (minutes < 60) return `Chegada estimada em ${minutes} min`;
    const hours = Math.floor(minutes / 60), rest = minutes % 60;
    return `Chegada estimada em ${hours} h${rest ? ` e ${rest} min` : ''}`;
  }
  const model = { labels, steps, estimate };
  if (typeof module !== 'undefined' && module.exports) module.exports = model;
  else root.DeliveryModel = model;
})(typeof window === 'undefined' ? globalThis : window);
