const products = [
    {id:1,name:"Cesta Essencial da Semana",category:"Cestas",price:42.9,unit:"8 itens sazonais",producer:"Rede Raízes do Sul",location:"Nova Santa Rita",image:"img/agrolink-hero.png",imagePosition:"center 62%",badge:"Mais pedida",stock:20},
    {id:2,name:"Morango orgânico",category:"Frutas",price:12.9,unit:"bandeja 250 g",producer:"Família Klein",location:"Bom Princípio",image:"img/morango.jpg",imagePosition:"35% center",badge:"Da estação",stock:40},
    {id:3,name:"Alface crespa",category:"Folhas",price:6.9,unit:"unidade",producer:"Verdes Campos",location:"Viamão",image:"img/alface-crespa.jpg",imagePosition:"25% center",stock:50},
    {id:4,name:"Bergamota montenegrina",category:"Frutas",price:9.8,unit:"pacote 1 kg",producer:"Sítio São Miguel",location:"Montenegro",image:"img/bergamota-montenegrina.webp",imagePosition:"70% center",stock:35},
    {id:5,name:"Tomate italiano",category:"Legumes",price:10.5,unit:"pacote 500 g",producer:"Família Rossi",location:"Feliz",image:"img/tomate.webp",imagePosition:"54% 70%",badge:"Colhido hoje",stock:45},
    {id:6,name:"Couve-manteiga",category:"Folhas",price:5.9,unit:"maço",producer:"Chácara Linha Verde",location:"Guaíba",image:"img/couve-manteiga.webp",imagePosition:"75% center",stock:50}
  ];
  
  const categories = ["Todos","Cestas","Folhas","Frutas","Legumes"];
  const state = {
    category: "Todos",
    search: "",
    cart: JSON.parse(localStorage.getItem("agrolink-cart") || "{}"),
    checkoutStep: "cart",
    receipt: null
  };
  
  const money = value => new Intl.NumberFormat("pt-BR",{style:"currency",currency:"BRL"}).format(value);
  const $ = selector => document.querySelector(selector);
  const $$ = selector => [...document.querySelectorAll(selector)];
  
  function saveCart(){ localStorage.setItem("agrolink-cart", JSON.stringify(state.cart)); }
  function status(message){ $("#status").textContent = message; }
  
  function renderCategories(){
    $("#categories").innerHTML = categories.map(c =>
      `<button class="${state.category===c?"active":""}" type="button" data-category="${c}" aria-pressed="${state.category===c}">${c}</button>`
    ).join("");
  }
  
  function cartCount(){
    return Object.values(state.cart).reduce((sum,n)=>sum+Number(n),0);
  }
  
  function cartItems(){
    return products.filter(p => state.cart[p.id]);
  }
  
  function subtotal(){
    return cartItems().reduce((sum,p)=>sum + p.price * state.cart[p.id], 0);
  }
  
  function renderCartCount(){
    const count = cartCount();
    const badge = $("#bag-count");
    badge.textContent = count;
    badge.hidden = count === 0;
    $("#open-cart").setAttribute("aria-label", `Abrir sacola com ${count} ${count===1?"item":"itens"}`);
  }
  
  function changeCart(id, amount){
    const product = products.find(p=>p.id===id);
    if(!product) return;
    const current = Number(state.cart[id] || 0);
    const next = Math.max(0, Math.min(product.stock, current + amount));
    if(next === 0) delete state.cart[id]; else state.cart[id] = next;
    saveCart();
    state.checkoutStep = "cart";
    state.receipt = null;
    renderProducts();
    renderCartCount();
    if($("#cart-layer").hidden === false) renderCart();
    status(amount > 0 ? `${product.name} foi adicionado à sacola.` : `Quantidade de ${product.name} atualizada.`);
  }
  
  function renderProducts(){
    const query = state.search.trim().toLocaleLowerCase("pt-BR");
    const filtered = products.filter(p => {
      const categoryOk = state.category === "Todos" || p.category === state.category;
      const text = [p.name,p.producer,p.location,p.category].join(" ").toLocaleLowerCase("pt-BR");
      return categoryOk && (!query || text.includes(query));
    });
  
    $("#product-grid").innerHTML = filtered.map(p => {
      const qty = Number(state.cart[p.id] || 0);
      return `
        <article class="product-card">
          <div class="product-image-wrap">
            <img src="${p.image}" alt="" class="product-image" style="object-position:${p.imagePosition}">
            ${p.badge ? `<span class="product-badge">${p.badge}</span>` : ""}
            <button class="favorite-button" type="button" aria-label="Favoritar ${p.name}"><i data-lucide="heart"></i></button>
          </div>
          <div class="product-info">
            <p class="product-category">${p.category}</p>
            <h3>${p.name}</h3>
            <p class="product-origin"><i data-lucide="map-pin"></i> ${p.producer} · ${p.location}</p>
            <div class="product-footer">
              <div><strong>${money(p.price)}</strong><small>${p.unit}</small></div>
              ${qty ? `
                <div class="quantity-control" aria-label="Quantidade de ${p.name}">
                  <button type="button" data-action="minus" data-id="${p.id}" aria-label="Diminuir quantidade"><i data-lucide="minus"></i></button>
                  <span>${qty}</span>
                  <button type="button" data-action="plus" data-id="${p.id}" aria-label="Aumentar quantidade" ${qty>=p.stock?"disabled":""}><i data-lucide="plus"></i></button>
                </div>` :
                `<button class="add-button" type="button" data-action="add" data-id="${p.id}"><i data-lucide="plus"></i> Adicionar</button>`}
            </div>
          </div>
        </article>`;
    }).join("");
  
   
    if(window.lucide) lucide.createIcons();
  }
  
  function openCart(){
    $("#cart-layer").hidden = false;
    renderCart();
    document.body.style.overflow = "hidden"; document.documentElement.style.overflow = "hidden";
  }
  function closeCart(){
    $("#cart-layer").hidden = true;
    document.body.style.overflow = ""; document.documentElement.style.overflow = "";
  }
  
  function renderCart(){
    const items = cartItems();
    const total = subtotal();
    const shipping = total >= 120 ? 0 : 9.9;
    const content = $("#cart-content");
  
    if(state.checkoutStep === "success" && state.receipt){
      $("#cart-kicker").textContent = "Tudo certo";
      $("#cart-title").textContent = "Pedido recebido";
      content.innerHTML = `
        <div class="order-success">
          <span><i data-lucide="check"></i></span>
          <h3>Pedido registrado!</h3>
          <p class="order-code">Código <strong>${state.receipt.code}</strong></p>
          <p>Este site por enquanto não tem servidor, os dados do pedido ficam apenas como demonstração local.</p>
          <div class="order-total"><span>Total do pedido</span><strong>${money(state.receipt.total)}</strong></div>
          <button class="button button-primary" id="back-to-market" type="button">Voltar à feira</button>
        </div>`;
      lucide.createIcons();
      return;
    }
  
    if(state.checkoutStep === "details" && items.length){
      $("#cart-kicker").textContent = "Finalizar reserva";
      $("#cart-title").textContent = "Dados de entrega";
      content.innerHTML = `
        <form class="checkout-form" id="checkout-form">
          <button class="checkout-back" id="checkout-back" type="button"><i data-lucide="arrow-right"></i> Voltar para a sacola</button>
          <div class="checkout-fields">
            <label>Nome completo*<input name="customerName" autocomplete="name" required minlength="2" maxlength="90"></label>
            <label>E-mail<input name="customerEmail" type="email" autocomplete="email" required maxlength="160"></label>
            <label>Telefone com DDD*<input name="customerPhone" type="tel" placeholder="(51) 99999-9999" required maxlength="30"></label>
            <label>Região de entrega*<select name="deliveryRegion" required><option>Porto Alegre</option><option>Canoas</option><option>Viamão</option><option>Guaíba</option></select></label>
            <label>Endereço completo*<textarea name="deliveryAddress" autocomplete="street-address" placeholder="Rua, número, complemento e bairro" required minlength="8" maxlength="220"></textarea></label>
          </div>
          <fieldset class="payment-options">
            <legend>Forma de pagamento</legend>
            <label><input type="radio" name="paymentMethod" value="pix" checked><span><strong>Pix após confirmação</strong><small>Receba as instruções por e-mail</small></span></label>
            <label><input type="radio" name="paymentMethod" value="delivery"><span><strong>Pagamento na entrega</strong><small>Combine no recebimento</small></span></label>
          </fieldset>
          <div class="checkout-total"><span>Total com entrega</span><strong>${money(total+shipping)}</strong></div>
          <p class="checkout-note">Nenhuma cobrança é feita nesta etapa. É apenas uma demonstração.</p>
          <button class="button button-primary checkout-button" type="submit">Reservar pedido <i data-lucide="arrow-right"></i></button>
        </form>`;
      lucide.createIcons();
      return;
    }
  
    $("#cart-kicker").textContent = "Sua compra";
    $("#cart-title").textContent = "Sacola da feira";
  
    if(!items.length){
      content.innerHTML = `<div class="empty-cart"><span><i data-lucide="shopping-bag"></i></span><h3>Sua sacola está vazia</h3><p>Escolha alimentos frescos da feira desta semana.</p><button class="button button-primary" id="see-products" type="button">Ver produtos</button></div>`;
    } else {
      content.innerHTML = `
        <div class="cart-items">
          ${items.map(p => `
            <article class="cart-item">
              <img src="${p.image}" alt="" style="object-position:${p.imagePosition}">
              <div class="cart-item-info">
                <h3>${p.name}</h3><p>${money(p.price)} · ${p.unit}</p>
                <div class="cart-item-actions">
                  <div class="quantity-control">
                    <button type="button" data-cart-minus="${p.id}" aria-label="Diminuir"><i data-lucide="minus"></i></button>
                    <span>${state.cart[p.id]}</span>
                    <button type="button" data-cart-plus="${p.id}" aria-label="Aumentar" ${state.cart[p.id]>=p.stock?"disabled":""}><i data-lucide="plus"></i></button>
                  </div>
                  <button class="remove-item" type="button" data-remove="${p.id}" aria-label="Remover ${p.name}"><i data-lucide="trash-2"></i></button>
                </div>
              </div>
            </article>`).join("")}
        </div>
        <div class="cart-summary">
          <div><span>Subtotal</span><strong>${money(total)}</strong></div>
          <div><span>Entrega refrigerada</span><strong>${shipping===0?"Grátis":money(shipping)}</strong></div>
          <div class="cart-total"><span>Total</span><strong>${money(total+shipping)}</strong></div>
          <p><i data-lucide="package-check"></i> Entrega estimada no próximo sábado</p>
          <button class="button button-primary checkout-button" id="continue-checkout" type="button">Continuar pedido <i data-lucide="arrow-right"></i></button>
        </div>`;
    }
    lucide.createIcons();
  }
  
  function makeCode(){
    const now = new Date();
    const date = now.toISOString().slice(2,10).replaceAll("-","");
    return `AGR-${date}-${Math.random().toString(36).slice(2,8).toUpperCase()}`;
  }
  
  document.addEventListener("click", e => {
    const target = e.target.closest("button, a");
    if(!target) return;
  
    if(target.id === "open-cart") openCart();
    if(target.id === "close-cart" || target.id === "cart-backdrop") closeCart();
    if(target.id === "buy-now" || target.id === "impact-buy"){
      $("#feira").scrollIntoView({behavior:"smooth"});
    }
    if(target.id === "menu-button"){
      const nav = $(".main-nav");
      nav.classList.toggle("is-open");
      const open = nav.classList.contains("is-open");
      target.setAttribute("aria-expanded", open);
      target.innerHTML = `<i data-lucide="${open?"x":"menu"}"></i>`;
      lucide.createIcons();
    }
    if(target.dataset.category){
      state.category = target.dataset.category;
      renderCategories(); renderProducts();
    }
    if(target.dataset.action){
      changeCart(Number(target.dataset.id), target.dataset.action==="minus" ? -1 : 1);
    
      renderCategories(); renderProducts();
    }
    if(target.id === "continue-checkout"){
      state.checkoutStep="details"; renderCart();
    }
    if(target.id === "checkout-back"){
      state.checkoutStep="cart"; renderCart();
    }
    if(target.id === "see-products"){
      closeCart(); $("#feira").scrollIntoView({behavior:"smooth"});
    }
    if(target.id === "back-to-market"){
      state.cart={}; saveCart(); state.checkoutStep="cart"; state.receipt=null;
      renderCart(); renderCartCount();
    }
    if(target.dataset.cartMinus) changeCart(Number(target.dataset.cartMinus), -1);
    if(target.dataset.cartPlus) changeCart(Number(target.dataset.cartPlus), 1);
    if(target.dataset.remove){
      delete state.cart[target.dataset.remove]; saveCart(); renderProducts(); renderCartCount(); renderCart();
    }
  });
  
  $("#search").addEventListener("input", e => {
    state.search = e.target.value;
    renderProducts();
  });
  
  $("#newsletter-form").addEventListener("submit", e => {
    e.preventDefault();
    const email = $("#newsletter-email").value.trim();
    if(!email) return;
    localStorage.setItem("agrolink-newsletter", email);
    e.currentTarget.reset();
    status("Cadastro realizado. Você receberá a próxima feira da semana.");
    const button = e.currentTarget.querySelector("button");
    button.innerHTML = '<i data-lucide="check"></i>';
    lucide.createIcons();
  });
  
  document.addEventListener("submit", e => {
    if(e.target.id !== "checkout-form") return;
    e.preventDefault();
    const form = new FormData(e.target);
    const name = String(form.get("customerName")||"").trim();
    const email = String(form.get("customerEmail")||"").trim();
    const phone = String(form.get("customerPhone")||"").trim();
    const address = String(form.get("deliveryAddress")||"").trim();
    if(name.length < 2 || !email.includes("@") || phone.replace(/\D/g,"").length < 10 || address.length < 8){
      alert("Confira os dados de entrega antes de continuar.");
      return;
    }
    const total = subtotal() + (subtotal() >= 120 ? 0 : 9.9);
    state.receipt = {code:makeCode(), total, paymentMethod:form.get("paymentMethod")};
    state.checkoutStep = "success";
    renderCart();
    status(`Pedido ${state.receipt.code} registrado com sucesso.`);
  });
  
  lucide.createIcons();
  renderCategories();
  renderProducts();
  renderCartCount();
  