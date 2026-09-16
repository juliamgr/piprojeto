const pickupPoints = [
  {
    id: 1,
    name: "Feira dos Agricultores Ecologistas",
    shortName: "FAE — Bom Fim",
    neighborhood: "Bom Fim",
    region: "Centro",
    address: "Av. José Bonifácio, 675 — quadra 1",
    day: "sábado",
    schedule: "Sábado, 7h às 13h",
    lat: -30.03382,
    lng: -51.21319
  },
  {
    id: 2,
    name: "Feira Ecológica do Bom Fim",
    shortName: "Ecológica do Bom Fim",
    neighborhood: "Bom Fim",
    region: "Centro",
    address: "Av. José Bonifácio, 675 — quadra 2",
    day: "sábado",
    schedule: "Sábado, 7h às 13h",
    lat: -30.03415,
    lng: -51.21155
  },
  {
    id: 3,
    name: "Feira Ecológica da Tristeza",
    shortName: "Ecológica da Tristeza",
    neighborhood: "Tristeza",
    region: "Sul",
    address: "Av. Otto Niemeyer × Av. Wenceslau Escobar",
    day: "sábado",
    schedule: "Sábado, 7h às 12h",
    lat: -30.10906,
    lng: -51.24619
  },
  {
    id: 4,
    name: "Feira Ecológica Três Figueiras",
    shortName: "Ecológica Três Figueiras",
    neighborhood: "Três Figueiras",
    region: "Leste",
    address: "Praça Des. La Hire Guerra — R. Cel. Armando Assis",
    day: "sábado",
    schedule: "Sábado, 7h às 12h30",
    lat: -30.03152,
    lng: -51.17476
  },
  {
    id: 5,
    name: "Feira Ecológica Praça André Forster",
    shortName: "Praça André Forster",
    neighborhood: "Bom Fim",
    region: "Centro",
    address: "Praça André Forster — R. Rômulo Telles Pessoa",
    day: "sábado",
    schedule: "Sábado, 7h às 13h",
    lat: -30.03833,
    lng: -51.20712
  },
  {
    id: 6,
    name: "Feira Ecológica Lindóia",
    shortName: "Ecológica Lindóia",
    neighborhood: "Jardim Lindóia",
    region: "Noroeste",
    address: "R. Catamarca — Praça Ponaim",
    day: "sábado",
    schedule: "Sábado, 7h às 12h",
    lat: -30.00569,
    lng: -51.14387
  },
  {
    id: 7,
    name: "Feira Ecológica Auxiliadora",
    shortName: "Ecológica Auxiliadora",
    neighborhood: "Auxiliadora",
    region: "Centro",
    address: "Travessa Lanceiros Negros",
    day: "terça",
    schedule: "Terça, 7h às 12h",
    lat: -30.02612,
    lng: -51.19723
  },
  {
    id: 8,
    name: "Feira do Centro Administrativo Municipal",
    shortName: "Centro Administrativo",
    neighborhood: "Centro Histórico",
    region: "Centro",
    address: "R. General João Manoel, 157",
    day: "quinta",
    schedule: "Quinta, 7h30 às 15h",
    lat: -30.03195,
    lng: -51.23018
  },
  {
    id: 9,
    name: "Feira Modelo Epatur",
    shortName: "Feira Modelo Epatur",
    neighborhood: "Cidade Baixa",
    region: "Centro",
    address: "Largo Zumbi dos Palmares",
    day: "terça",
    schedule: "Terça, 15h30 às 20h30",
    lat: -30.0382046,
    lng: -51.2263062
  },
  {
    id: 10,
    name: "Feira Modelo General João Telles",
    shortName: "Feira Gen. João Telles",
    neighborhood: "Bom Fim",
    region: "Centro",
    address: "R. General João Telles, entre Osvaldo Aranha e Henrique Dias",
    day: "terça",
    schedule: "Terça, 7h às 12h",
    lat: -30.0305695,
    lng: -51.2121381
  },
  {
    id: 11,
    name: "Feira Modelo Praça Florida",
    shortName: "Feira da Praça Florida",
    neighborhood: "Floresta",
    region: "Centro",
    address: "R. São Carlos × R. Comendador Azevedo",
    day: "terça",
    schedule: "Terça, 15h30 às 20h30",
    lat: -30.0206212,
    lng: -51.2103979
  },
  {
    id: 12,
    name: "Feira Encol",
    shortName: "Feira da Encol",
    neighborhood: "Bela Vista",
    region: "Centro",
    address: "Praça Carlos Simão Arnt, entre Nilópolis e Passo da Pátria",
    day: "quarta",
    schedule: "Quarta, 15h às 20h",
    lat: -30.0361919,
    lng: -51.1874856
  },
  {
    id: 13,
    name: "Feira Modelo Germânia",
    shortName: "Feira Germânia",
    neighborhood: "Jardim Europa",
    region: "Norte",
    address: "Av. Ferdinand Kisslinger, junto ao Parque Germânia",
    day: "quinta",
    schedule: "Quinta, 14h às 20h",
    lat: -30.0272652,
    lng: -51.1552056
  },
  {
    id: 14,
    name: "Feira Modelo Menino Deus",
    shortName: "Feira Menino Deus",
    neighborhood: "Menino Deus",
    region: "Centro",
    address: "Praça Israel — R. Vicente Lopes Santos",
    day: "quinta",
    schedule: "Quinta, 15h30 às 20h30",
    lat: -30.0528892,
    lng: -51.2262949
  },
  {
    id: 15,
    name: "Feira Modelo Jardim Botânico",
    shortName: "Feira Jardim Botânico",
    neighborhood: "Jardim Botânico",
    region: "Leste",
    address: "R. Felizardo Furtado, ao lado da ESEF",
    day: "sexta",
    schedule: "Sexta, 15h30 às 20h30",
    lat: -30.0495823,
    lng: -51.1838723
  },
  {
    id: 16,
    name: "Feira Modelo Vila Nova",
    shortName: "Feira Vila Nova",
    neighborhood: "Vila Nova",
    region: "Sul",
    address: "R. Coronel Otaviano Pinto Soares × R. Joaquim de Carvalho",
    day: "sábado",
    schedule: "Sábado, 7h30 às 12h30",
    lat: -30.1151304,
    lng: -51.2196464
  },
  {
    id: 17,
    name: "Mercadão Moinhos de Vento",
    shortName: "Mercadão Moinhos de Vento",
    neighborhood: "Moinhos de Vento",
    region: "Centro",
    address: "R. Comendador Caminha, 313 — Largo José Antonio Daudt",
    day: "quinta",
    schedule: "Quinta, 15h30 às 20h30",
    lat: -30.0262322,
    lng: -51.2012349
  },
  {
    id: 18,
    name: "Mercadão Cidade Baixa",
    shortName: "Mercadão Cidade Baixa",
    neighborhood: "Cidade Baixa",
    region: "Centro",
    address: "Av. Loureiro da Silva — Largo Zumbi dos Palmares",
    day: "sábado",
    schedule: "Sábado, 7h às 12h",
    lat: -30.0382046,
    lng: -51.2263062
  }
];

const state = {
  day: "todos",
  query: "",
  selectedId: 1,
  userLocation: null
};

const portoAlegreBounds = [
  [-30.135, -51.295],
  [-29.960, -51.105]
];

const listElement = document.querySelector("#location-list");
const countElement = document.querySelector("#results-count");
const emptyElement = document.querySelector("#empty-state");
const clearButton = document.querySelector("#clear-filters");
const searchInput = document.querySelector("#location-search");
const statusElement = document.querySelector("#status");
const nearMeButton = document.querySelector("#near-me");
const menuButton = document.querySelector("#menu-button");
const mainNav = document.querySelector("#main-nav");

let map;
let markerLayer;
let userMarker;
const markers = new Map();

function normalizeText(value) {
  return value
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim();
}

function escapeHTML(value) {
  const span = document.createElement("span");
  span.textContent = value;
  return span.innerHTML;
}

function getFilteredPoints() {
  const query = normalizeText(state.query);

  return pickupPoints.filter((point) => {
    const matchesDay = state.day === "todos" || point.day === state.day;
    const haystack = normalizeText([
      point.name,
      point.shortName,
      point.neighborhood,
      point.region,
      point.address
    ].join(" "));
    const matchesQuery = !query || haystack.includes(query);
    return matchesDay && matchesQuery;
  });
}

function distanceInKm(origin, destination) {
  const earthRadius = 6371;
  const toRadians = (degrees) => degrees * Math.PI / 180;
  const latDifference = toRadians(destination.lat - origin.lat);
  const lngDifference = toRadians(destination.lng - origin.lng);
  const a = Math.sin(latDifference / 2) ** 2
    + Math.cos(toRadians(origin.lat))
    * Math.cos(toRadians(destination.lat))
    * Math.sin(lngDifference / 2) ** 2;

  return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatDistance(distance) {
  if (distance < 1) return `${Math.round(distance * 1000)} m`;
  return `${distance.toFixed(1).replace(".", ",")} km`;
}

function createLocationCard(point) {
  const card = document.createElement("button");
  const distance = state.userLocation ? distanceInKm(state.userLocation, point) : null;
  card.className = `location-card${point.id === state.selectedId ? " is-selected" : ""}`;
  card.type = "button";
  card.dataset.id = point.id;
  card.setAttribute("aria-pressed", String(point.id === state.selectedId));
  card.setAttribute("aria-label", `${point.name}, ${point.schedule}, ${point.address}`);
  card.innerHTML = `
    <span class="location-number" aria-hidden="true"><span>${String(point.id).padStart(2, "0")}</span></span>
    <span>
      <h3>${escapeHTML(point.shortName)}</h3>
      <p>${escapeHTML(point.address)}</p>
      <span class="location-meta">
        <span>${escapeHTML(point.schedule)}</span>
        <span>${escapeHTML(point.region)}</span>
      </span>
    </span>
    ${distance === null ? "" : `<span class="location-distance">${formatDistance(distance)}</span>`}
  `;

  card.addEventListener("click", () => selectPoint(point.id, true));
  return card;
}

function markerHTML(point, selected = false) {
  return `<div class="custom-marker${selected ? " is-selected" : ""}"><span>${String(point.id).padStart(2, "0")}</span></div>`;
}

function createMarkerIcon(point, selected = false) {
  return L.divIcon({
    className: "marker-shell",
    html: markerHTML(point, selected),
    iconSize: [38, 38],
    iconAnchor: [19, 38],
    popupAnchor: [0, -40]
  });
}

function mapLink(point) {
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${point.address}, Porto Alegre, RS`)}`;
}

function popupHTML(point) {
  return `
    <div class="map-popup">
      <span class="popup-kicker">Ponto ${String(point.id).padStart(2, "0")} · ${escapeHTML(point.region)}</span>
      <h3>${escapeHTML(point.shortName)}</h3>
      <p>${escapeHTML(point.address)}</p>
      <p><strong>${escapeHTML(point.schedule)}</strong></p>
      <a href="${mapLink(point)}" target="_blank" rel="noopener noreferrer">Abrir rota no Google Maps</a>
    </div>
  `;
}

function initializeMap() {
  const mapElement = document.querySelector("#map");

  if (typeof L === "undefined") {
    mapElement.innerHTML = '<p class="map-fallback">Não foi possível carregar o mapa agora. Use a lista ao lado para consultar endereços e horários.</p>';
    document.querySelector("#map-reset").hidden = true;
    return;
  }

  map = L.map("map", {
    zoomControl: true,
    scrollWheelZoom: false,
    minZoom: 10,
    maxZoom: 18
  });

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  markerLayer = L.layerGroup().addTo(map);
  renderMapMarkers(pickupPoints);
  fitMapToPoints(pickupPoints);
}

function renderMapMarkers(points) {
  if (!map || !markerLayer) return;

  markerLayer.clearLayers();
  markers.clear();

  points.forEach((point) => {
    const marker = L.marker([point.lat, point.lng], {
      icon: createMarkerIcon(point, point.id === state.selectedId),
      title: point.name,
      alt: point.name,
      keyboard: true
    })
      .bindPopup(popupHTML(point))
      .on("click", () => selectPoint(point.id, false))
      .addTo(markerLayer);

    markers.set(point.id, marker);
  });
}

function fitMapToPoints(points) {
  if (!map) return;

  if (!points.length) {
    map.fitBounds(portoAlegreBounds, { padding: [30, 30] });
    return;
  }

  if (points.length === 1) {
    map.setView([points[0].lat, points[0].lng], 15);
    return;
  }

  const bounds = L.latLngBounds(points.map((point) => [point.lat, point.lng]));
  map.fitBounds(bounds, { padding: [44, 44], maxZoom: 14 });
}

function renderLocations({ fitMap = false } = {}) {
  let points = getFilteredPoints();

  if (state.userLocation) {
    points = [...points].sort((a, b) => (
      distanceInKm(state.userLocation, a) - distanceInKm(state.userLocation, b)
    ));
  }

  if (!points.some((point) => point.id === state.selectedId)) {
    state.selectedId = points[0]?.id ?? null;
  }

  listElement.replaceChildren(...points.map(createLocationCard));
  countElement.textContent = `${points.length} ${points.length === 1 ? "ponto encontrado" : "pontos encontrados"}`;
  emptyElement.hidden = points.length > 0;
  listElement.hidden = points.length === 0;
  clearButton.hidden = state.day === "todos" && !state.query;

  renderMapMarkers(points);
  if (fitMap) fitMapToPoints(points);
}

function selectPoint(id, moveMap) {
  const point = pickupPoints.find((item) => item.id === id);
  if (!point) return;

  state.selectedId = id;
  document.querySelectorAll(".location-card").forEach((card) => {
    const selected = Number(card.dataset.id) === id;
    card.classList.toggle("is-selected", selected);
    card.setAttribute("aria-pressed", String(selected));
  });

  markers.forEach((marker, markerId) => {
    const markerPoint = pickupPoints.find((item) => item.id === markerId);
    marker.setIcon(createMarkerIcon(markerPoint, markerId === id));
  });

  const marker = markers.get(id);
  if (map && marker) {
    if (moveMap) map.flyTo([point.lat, point.lng], 15, { duration: 0.55 });
    marker.openPopup();
  }

  statusElement.textContent = `${point.name} selecionada. ${point.schedule}.`;
}

function clearFilters() {
  state.day = "todos";
  state.query = "";
  searchInput.value = "";
  document.querySelectorAll(".filter-button").forEach((button) => {
    const active = button.dataset.day === "todos";
    button.classList.toggle("is-active", active);
    button.setAttribute("aria-pressed", String(active));
  });
  renderLocations({ fitMap: true });
  statusElement.textContent = "Filtros removidos. Todos os pontos estão visíveis.";
}

document.querySelector("#day-filters").addEventListener("click", (event) => {
  const button = event.target.closest(".filter-button");
  if (!button) return;

  state.day = button.dataset.day;
  document.querySelectorAll(".filter-button").forEach((item) => {
    const active = item === button;
    item.classList.toggle("is-active", active);
    item.setAttribute("aria-pressed", String(active));
  });
  renderLocations({ fitMap: true });
});

searchInput.addEventListener("input", () => {
  state.query = searchInput.value;
  renderLocations({ fitMap: true });
});

clearButton.addEventListener("click", clearFilters);
document.querySelector("#empty-clear").addEventListener("click", clearFilters);
document.querySelector("#map-reset").addEventListener("click", () => fitMapToPoints(getFilteredPoints()));

nearMeButton.addEventListener("click", () => {
  if (!navigator.geolocation) {
    statusElement.textContent = "A localização não está disponível neste navegador.";
    return;
  }

  nearMeButton.classList.add("is-loading");
  nearMeButton.innerHTML = '<i data-lucide="loader-circle" aria-hidden="true"></i> Localizando…';
  if (window.lucide) lucide.createIcons();

  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      state.userLocation = { lat: coords.latitude, lng: coords.longitude };
      nearMeButton.classList.remove("is-loading");
      nearMeButton.innerHTML = '<i data-lucide="locate-fixed" aria-hidden="true"></i> Mais próximos';
      if (window.lucide) lucide.createIcons();

      if (map) {
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([coords.latitude, coords.longitude], {
          icon: L.divIcon({ className: "user-marker-shell", html: '<div class="user-marker"></div>', iconSize: [18, 18], iconAnchor: [9, 9] })
        }).bindPopup("Você está aqui").addTo(map);
      }

      renderLocations();
      const closest = [...pickupPoints].sort((a, b) => (
        distanceInKm(state.userLocation, a) - distanceInKm(state.userLocation, b)
      ))[0];
      selectPoint(closest.id, true);
      document.querySelector(".location-card")?.scrollIntoView({ behavior: "smooth", block: "nearest" });
      statusElement.textContent = `O ponto mais próximo é ${closest.name}, a ${formatDistance(distanceInKm(state.userLocation, closest))} em linha reta.`;
    },
    () => {
      nearMeButton.classList.remove("is-loading");
      nearMeButton.innerHTML = '<i data-lucide="locate-fixed" aria-hidden="true"></i> Perto de mim';
      if (window.lucide) lucide.createIcons();
      statusElement.textContent = "Não foi possível acessar sua localização. Você ainda pode buscar pelo bairro.";
    },
    { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
  );
});

menuButton?.addEventListener("click", () => {
  const isOpen = mainNav.classList.toggle("is-open");
  menuButton.setAttribute("aria-expanded", String(isOpen));
  menuButton.setAttribute("aria-label", isOpen ? "Fechar menu" : "Abrir menu");
  menuButton.innerHTML = `<i data-lucide="${isOpen ? "x" : "menu"}" aria-hidden="true"></i>`;
  if (window.lucide) lucide.createIcons();
});

mainNav.addEventListener("click", (event) => {
  if (!event.target.closest("a")) return;
  mainNav.classList.remove("is-open");
  menuButton.setAttribute("aria-expanded", "false");
  menuButton.setAttribute("aria-label", "Abrir menu");
  menuButton.innerHTML = '<i data-lucide="menu" aria-hidden="true"></i>';
  if (window.lucide) lucide.createIcons();
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && mainNav.classList.contains("is-open")) {
    mainNav.classList.remove("is-open");
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.focus();
  }
});

initializeMap();
renderLocations();
if (window.lucide) lucide.createIcons();
(() => {
  function iniciarCarrosselFeiras() {
    const carrossel = document.querySelector(".agro-galeria");
    if (!carrossel || carrossel.dataset.iniciado) return;

    const fotos = [...carrossel.querySelectorAll(".agro-foto")];
    if (!fotos.length) return;

    carrossel.dataset.iniciado = "true";

    const controles = carrossel.querySelector(".agro-galeria-controles");
    const anterior = carrossel.querySelector("[data-agro-anterior]");
    const proxima = carrossel.querySelector("[data-agro-proxima]");
    const pausa = carrossel.querySelector("[data-agro-pausa]");
    const contagem = carrossel.querySelector(".agro-galeria-contagem");
    const status = carrossel.querySelector("[data-agro-status]");
    const movimentoReduzido = window.matchMedia(
      "(prefers-reduced-motion: reduce)"
    );

    let atual = 0;
    let temporizador;
    let pausado = movimentoReduzido.matches;
    let mouseDentro = false;

    const formatar = (numero) => String(numero).padStart(2, "0");

    function mostrarFoto(indice, anunciar = false) {
      atual = (indice + fotos.length) % fotos.length;

      fotos.forEach((foto, i) => {
        const ativa = i === atual;

        foto.classList.toggle("is-active", ativa);
        foto.setAttribute("aria-hidden", String(!ativa));
        foto.setAttribute("aria-label", `${i + 1} de ${fotos.length}`);
      });

      contagem.textContent =
        `${formatar(atual + 1)} / ${formatar(fotos.length)}`;

      if (anunciar) {
        const legenda = fotos[atual].querySelector("figcaption strong");
        status.textContent =
          `Foto ${atual + 1} de ${fotos.length}. ${legenda?.textContent ?? ""}`;
      }
    }

    function atualizarReproducao() {
      window.clearInterval(temporizador);

      pausa.textContent = pausado ? "Reproduzir" : "Pausar";
      pausa.setAttribute(
        "aria-label",
        pausado ? "Reproduzir carrossel" : "Pausar carrossel"
      );

      if (
        fotos.length < 2 ||
        pausado ||
        mouseDentro ||
        document.hidden ||
        carrossel.contains(document.activeElement)
      ) return;

      temporizador = window.setInterval(() => {
        mostrarFoto(atual + 1);
      }, 5000);
    }

    function navegar(direcao) {
      mostrarFoto(atual + direcao, true);
      atualizarReproducao();
    }

    anterior.addEventListener("click", () => navegar(-1));
    proxima.addEventListener("click", () => navegar(1));

    pausa.addEventListener("click", () => {
      pausado = !pausado;
      atualizarReproducao();
    });

    carrossel.addEventListener("mouseenter", () => {
      mouseDentro = true;
      atualizarReproducao();
    });

    carrossel.addEventListener("mouseleave", () => {
      mouseDentro = false;
      atualizarReproducao();
    });

    carrossel.addEventListener("focusin", atualizarReproducao);
    carrossel.addEventListener("focusout", () => {
      window.setTimeout(atualizarReproducao, 0);
    });

    carrossel.addEventListener("keydown", (evento) => {
      if (evento.key === "ArrowLeft" || evento.key === "ArrowRight") {
        evento.preventDefault();
        navegar(evento.key === "ArrowLeft" ? -1 : 1);
      }
    });

    document.addEventListener("visibilitychange", atualizarReproducao);

    movimentoReduzido.addEventListener("change", (evento) => {
      pausado = evento.matches;
      atualizarReproducao();
    });

    controles.hidden = fotos.length < 2;
    mostrarFoto(0);
    atualizarReproducao();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciarCarrosselFeiras);
  } else {
    iniciarCarrosselFeiras();
  }
})();