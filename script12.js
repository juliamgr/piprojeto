let products = [];

const categories = ["Todos", "Cestas", "Folhas", "Frutas", "Legumes"];

const state = {
  category: "Todos",
  search: "",
  cart: JSON.parse(localStorage.getItem("agrolink-cart") || "{}"),
  checkoutStep: "cart",
  receipt: null
};

const money = value =>
  new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(value);

const $ = selector => document.querySelector(selector);

const escapeHtml = value =>
  String(value ?? "").replace(
    /[&<>"']/g,
    character => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;"
    })[character]
  );

function saveCart() {
  localStorage.setItem("agrolink-cart", JSON.stringify(state.cart));
}

function status(message) {
  $("#status").textContent = message;
}

function renderCategories() {
  $("#categories").innerHTML = categories.map(category => `
    <button
      class="${state.category === category ? "active" : ""}"
      type="button"
      data-category="${category}"
      aria-pressed="${state.category === category}"
    >${category}</button>
  `).join("");
}

function cartCount() {
  return Object.values(state.cart).reduce(
    (sum, quantity) => sum + Number(quantity),
    0
  );
}

function cartItems() {
  return products.filter(product => state.cart[product.id]);
}

function subtotal() {
  return cartItems().reduce(
    (sum, product) => sum + product.price * state.cart[product.id],
    0
  );
}

function renderCartCount() {
  const count = cartCount();
  const badge = $("#bag-count");

  badge.textContent = count;
  badge.hidden = count === 0;

  $("#open-cart").setAttribute(
    "aria-label",
    `Abrir sacola com ${count} ${count === 1 ? "item" : "itens"}`
  );
}

function changeCart(id, amount) {
  const product = products.find(item => item.id === id);
  if (!product) return;

  const current = Number(state.cart[id] || 0);
  const next = Math.max(0, Math.min(product.stock, current + amount));

  if (next === 0) {
    delete state.cart[id];
  } else {
    state.cart[id] = next;
  }

  saveCart();
  state.checkoutStep = "cart";
  state.receipt = null;

  renderProducts();
  renderCartCount();

  if (!$("#cart-layer").hidden) renderCart();

  status(
    amount > 0
      ? `${product.name} foi adicionado à sacola.`
      : `Quantidade de ${product.name} atualizada.`
  );
}

function renderProducts() {
  const query = state.search.trim().toLocaleLowerCase("pt-BR");

  const filtered = products.filter(product => {
    const categoryOk =
      state.category === "Todos" ||
      product.category === state.category;

    const text = [
      product.name,
      product.producer,
      product.location,
      product.category
    ].join(" ").toLocaleLowerCase("pt-BR");

    return categoryOk && (!query || text.includes(query));
  });

  $("#product-grid").innerHTML = filtered.length
    ? filtered.map(product => {
        const quantity = Number(state.cart[product.id] || 0);

        return `
          <article class="product-card">
            <div class="product-image-wrap">
              <img
                src="${escapeHtml(product.image)}"
                alt=""
                class="product-image"
              >

              ${product.badge
                ? `<span class="product-badge">${escapeHtml(product.badge)}</span>`
                : ""
              }

              <button
                class="favorite-button"
                type="button"
                aria-label="Favoritar ${escapeHtml(product.name)}"
              >
                <i data-lucide="heart"></i>
              </button>
            </div>

            <div class="product-info">
              <p class="product-category">
                ${escapeHtml(product.category)}
              </p>

              <h3>${escapeHtml(product.name)}</h3>

              <p class="product-origin">
                <i data-lucide="map-pin"></i>
                ${escapeHtml(product.producer)} · ${escapeHtml(product.location)}
              </p>

              <div class="product-footer">
                <div>
                  <strong>${money(product.price)}</strong>
                  <small>${escapeHtml(product.unit)}</small>
                </div>

                ${quantity
                  ? `
                    <div
                      class="quantity-control"
                      aria-label="Quantidade de ${escapeHtml(product.name)}"
                    >
                      <button
                        type="button"
                        data-action="minus"
                        data-id="${product.id}"
                        aria-label="Diminuir quantidade"
                      >
                        <i data-lucide="minus"></i>
                      </button>

                      <span>${quantity}</span>

                      <button
                        type="button"
                        data-action="plus"
                        data-id="${product.id}"
                        aria-label="Aumentar quantidade"
                        ${quantity >= product.stock ? "disabled" : ""}
                      >
                        <i data-lucide="plus"></i>
                      </button>
                    </div>
                  `
                  : `
                    <button
                      class="add-button"
                      type="button"
                      data-action="add"
                      data-id="${product.id}"
                    >
                      <i data-lucide="plus"></i> Adicionar
                    </button>
                  `
                }
              </div>
            </div>
          </article>
        `;
      }).join("")
    : `<p class="catalog-message">Nenhum produto encontrado.</p>`;

  if (window.lucide) lucide.createIcons();
}

function openCart() {
  $("#cart-layer").hidden = false;
  renderCart();

  document.body.style.overflow = "hidden";
  document.documentElement.style.overflow = "hidden";
}

function closeCart() {
  $("#cart-layer").hidden = true;

  document.body.style.overflow = "";
  document.documentElement.style.overflow = "";
}

function renderCart() {
  const items = cartItems();
  const total = subtotal();
  const shipping = total >= 120 ? 0 : 9.9;
  const content = $("#cart-content");

  if (state.checkoutStep === "success" && state.receipt) {
    $("#cart-kicker").textContent = "Tudo certo";
    $("#cart-title").textContent = "Pedido recebido";

    content.innerHTML = `
      <div class="order-success">
        <span><i data-lucide="check"></i></span>
        <h3>Pedido registrado!</h3>

        <p class="order-code">
          Código <strong>${escapeHtml(state.receipt.code)}</strong>
        </p>

        <p>
          Seu pedido foi salvo com segurança. A equipe Agrolink
          entrará em contato para confirmar a entrega.
        </p>

        <div class="order-total">
          <span>Total do pedido</span>
          <strong>${money(state.receipt.total)}</strong>
        </div>

        <button
          class="button button-primary"
          id="back-to-market"
          type="button"
        >
          Voltar à feira
        </button>
      </div>
    `;

    if (window.lucide) lucide.createIcons();
    return;
  }

  if (state.checkoutStep === "details" && items.length) {
    $("#cart-kicker").textContent = "Finalizar reserva";
    $("#cart-title").textContent = "Dados de entrega";

    content.innerHTML = `
      <form class="checkout-form" id="checkout-form">
        <button
          class="checkout-back"
          id="checkout-back"
          type="button"
        >
          <i data-lucide="arrow-right"></i> Voltar para a sacola
        </button>

        <div class="checkout-fields">
          <label>
            Nome completo*
            <input
              name="customerName"
              autocomplete="name"
              required
              minlength="2"
              maxlength="90"
            >
          </label>

          <label>
            E-mail
            <input
              name="customerEmail"
              type="email"
              autocomplete="email"
              required
              maxlength="160"
            >
          </label>

          <label>
            Telefone com DDD*
            <input
              name="customerPhone"
              type="tel"
              placeholder="(51) 99999-9999"
              required
              maxlength="30"
            >
          </label>

          <label>
            Região de entrega*
            <select name="deliveryRegion" required>
              <option>Porto Alegre</option>
              <option>Canoas</option>
              <option>Viamão</option>
              <option>Guaíba</option>
            </select>
          </label>

          <label>
            Endereço completo*
            <textarea
              name="deliveryAddress"
              autocomplete="street-address"
              placeholder="Rua, número, complemento e bairro"
              required
              minlength="8"
              maxlength="220"
            ></textarea>
          </label>
        </div>

        <fieldset class="payment-options">
          <legend>Forma de pagamento</legend>

          <label>
            <input
              type="radio"
              name="paymentMethod"
              value="pix"
              checked
            >
            <span>
              <strong>Pix após confirmação</strong>
              <small>Receba as instruções por e-mail</small>
            </span>
          </label>

          <label>
            <input
              type="radio"
              name="paymentMethod"
              value="delivery"
            >
            <span>
              <strong>Pagamento na entrega</strong>
              <small>Combine no recebimento</small>
            </span>
          </label>
        </fieldset>

        <div class="checkout-total">
          <span>Total com entrega</span>
          <strong>${money(total + shipping)}</strong>
        </div>

        <p class="checkout-note">
          Nenhuma cobrança é feita nesta etapa. É apenas uma demonstração.
        </p>

        <button
          class="button button-primary checkout-button"
          type="submit"
        >
          Reservar pedido <i data-lucide="arrow-right"></i>
        </button>
      </form>
    `;

    if (window.lucide) lucide.createIcons();
    return;
  }

  $("#cart-title").textContent = "Sacola da feira";

  if (!items.length) {
    content.innerHTML = `
      <div class="empty-cart">
        <span><i data-lucide="shopping-bag"></i></span>
        <h3>Sua sacola está vazia</h3>
        <p>Escolha alimentos frescos da feira desta semana.</p>

        <button
          class="button button-primary"
          id="see-products"
          type="button"
        >
          Ver produtos
        </button>
      </div>
    `;
  } else {
    content.innerHTML = `
      <div class="cart-items">
        ${items.map(product => `
          <article class="cart-item">
            <img src="${escapeHtml(product.image)}" alt="">

            <div class="cart-item-info">
              <h3>${escapeHtml(product.name)}</h3>

              <p>
                ${money(product.price)} · ${escapeHtml(product.unit)}
              </p>

              <div class="cart-item-actions">
                <div class="quantity-control">
                  <button
                    type="button"
                    data-cart-minus="${product.id}"
                    aria-label="Diminuir"
                  >
                    <i data-lucide="minus"></i>
                  </button>

                  <span>${state.cart[product.id]}</span>

                  <button
                    type="button"
                    data-cart-plus="${product.id}"
                    aria-label="Aumentar"
                    ${state.cart[product.id] >= product.stock ? "disabled" : ""}
                  >
                    <i data-lucide="plus"></i>
                  </button>
                </div>

                <button
                  class="remove-item"
                  type="button"
                  data-remove="${product.id}"
                  aria-label="Remover ${escapeHtml(product.name)}"
                >
                  <i data-lucide="trash-2"></i>
                </button>
              </div>
            </div>
          </article>
        `).join("")}
      </div>

      <div class="cart-summary">
        <div>
          <span>Subtotal</span>
          <strong>${money(total)}</strong>
        </div>

        <div>
          <span>Entrega refrigerada</span>
          <strong>${shipping === 0 ? "Grátis" : money(shipping)}</strong>
        </div>

        <div class="cart-total">
          <span>Total</span>
          <strong>${money(total + shipping)}</strong>
        </div>

        <p>
          <i data-lucide="package-check"></i>
          Entrega estimada no próximo sábado
        </p>

        <button
          class="button button-primary checkout-button"
          id="continue-checkout"
          type="button"
        >
          Continuar pedido <i data-lucide="arrow-right"></i>
        </button>
      </div>
    `;
  }

  if (window.lucide) lucide.createIcons();
}

async function api(url, options = {}) {
  const response = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    ...options
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(
      data.message || "Não foi possível concluir a solicitação."
    );
  }

  return data;
}

async function loadProducts() {
  $("#product-grid").innerHTML =
    `<p class="catalog-message">Carregando a feira...</p>`;

  try {
    const data = await api("api/products.php");
    products = data.products;

    for (const id of Object.keys(state.cart)) {
      const product = products.find(
        item => item.id === Number(id)
      );

      if (!product) {
        delete state.cart[id];
      } else {
        state.cart[id] = Math.min(
          Number(state.cart[id]) || 0,
          product.stock
        );

        if (!state.cart[id]) delete state.cart[id];
      }
    }

    saveCart();
    renderCartCount();
    renderProducts();
  } catch (error) {
    $("#product-grid").textContent =
      `${error.message} Abra o projeto pelo Apache do XAMPP.`;
  }
}

document.addEventListener("click", event => {
  const target = event.target.closest("button, a");
  if (!target) return;

  if (target.id === "open-cart") openCart();

  if (
    target.id === "close-cart" ||
    target.id === "cart-backdrop"
  ) {
    closeCart();
  }

  if (
    target.id === "buy-now" ||
    target.id === "impact-buy"
  ) {
    $("#feira").scrollIntoView({ behavior: "smooth" });
  }

  if (target.id === "menu-button") {
    const nav = $(".main-nav");
    nav.classList.toggle("is-open");

    const open = nav.classList.contains("is-open");
    target.setAttribute("aria-expanded", open);

    target.innerHTML =
      `<i data-lucide="${open ? "x" : "menu"}"></i>`;

    if (window.lucide) lucide.createIcons();
  }

  if (target.dataset.category) {
    state.category = target.dataset.category;
    renderCategories();
    renderProducts();
  }

  if (target.dataset.action) {
    changeCart(
      Number(target.dataset.id),
      target.dataset.action === "minus" ? -1 : 1
    );
  }

  if (target.id === "continue-checkout") {
    state.checkoutStep = "details";
    renderCart();
  }

  if (target.id === "checkout-back") {
    state.checkoutStep = "cart";
    renderCart();
  }

  if (target.id === "see-products") {
    closeCart();
    $("#feira").scrollIntoView({ behavior: "smooth" });
  }

  if (target.id === "back-to-market") {
    state.cart = {};
    saveCart();

    state.checkoutStep = "cart";
    state.receipt = null;

    renderCart();
    renderCartCount();
  }

  if (target.dataset.cartMinus) {
    changeCart(Number(target.dataset.cartMinus), -1);
  }

  if (target.dataset.cartPlus) {
    changeCart(Number(target.dataset.cartPlus), 1);
  }

  if (target.dataset.remove) {
    delete state.cart[target.dataset.remove];

    saveCart();
    renderProducts();
    renderCartCount();
    renderCart();
  }
});

$("#search").addEventListener("input", event => {
  state.search = event.target.value;
  renderProducts();
});

$("#newsletter-form").addEventListener("submit", async event => {
  event.preventDefault();

  const email = $("#newsletter-email").value.trim();
  if (!email) return;

  const button = event.currentTarget.querySelector("button");
  button.disabled = true;

  try {
    const data = await api("api/newsletter.php", {
      method: "POST",
      body: JSON.stringify({
        email,
        company: event.currentTarget.company.value
      })
    });

    event.currentTarget.reset();
    status(data.message);
    button.innerHTML = '<i data-lucide="check"></i>';
  } catch (error) {
    status(error.message);
    alert(error.message);
  } finally {
    button.disabled = false;
    if (window.lucide) lucide.createIcons();
  }
});

document.addEventListener("submit", async event => {
  if (event.target.id !== "checkout-form") return;

  event.preventDefault();

  const form = new FormData(event.target);
  const name = String(form.get("customerName") || "").trim();
  const email = String(form.get("customerEmail") || "").trim();
  const phone = String(form.get("customerPhone") || "").trim();
  const address = String(form.get("deliveryAddress") || "").trim();

  if (
    name.length < 2 ||
    !email.includes("@") ||
    phone.replace(/\D/g, "").length < 10 ||
    address.length < 8
  ) {
    alert("Confira os dados de entrega antes de continuar.");
    return;
  }

  const submitButton = event.target.querySelector(
    '[type="submit"]'
  );

  submitButton.disabled = true;
  submitButton.textContent = "Salvando pedido...";

  try {
    const data = await api("api/orders.php", {
      method: "POST",
      body: JSON.stringify({
        customer: {
          name,
          email,
          phone,
          region: form.get("deliveryRegion"),
          address
        },
        paymentMethod: form.get("paymentMethod"),
        items: cartItems().map(product => ({
          productId: product.id,
          quantity: Number(state.cart[product.id])
        }))
      })
    });

    state.receipt = data.order;
    state.checkoutStep = "success";

    renderCart();
    status(
      `Pedido ${state.receipt.code} registrado com sucesso.`
    );
  } catch (error) {
    alert(error.message);
    status(error.message);

    submitButton.disabled = false;
    submitButton.textContent = "Reservar pedido";
  }
});

if (window.lucide) lucide.createIcons();

renderCategories();
loadProducts();
renderCartCount();