import { router } from '../router.js';
import { store } from '../store.js';

function applyConfig() {
  const cfg = window.appConfig;
  if (!cfg) return;
  const logo = document.getElementById('header-logo');
  if (logo) logo.src = cfg.logo || logo.src;
  const name = document.getElementById('header-name');
  if (name) name.textContent = cfg.nombre_empresa || name.textContent;
}

export function renderHeader() {
  const header = document.getElementById('main-header');
  const cfg = window.appConfig || {};

  header.innerHTML = `
    <div class="header-inner">
      <div class="logo" data-route="/">
        <img id="header-logo" src="${cfg.logo || 'https://misdemos.x10.mx/videos/distelecom/logodistelcom.png'}" alt="${cfg.nombre_empresa || 'Distelecom'}" loading="lazy">
        <span id="header-name">${cfg.nombre_empresa || 'Distelecom'}</span>
      </div>
      <nav class="nav-menu" id="nav-menu">
        <a class="nav-link" data-route="/">Inicio</a>
        <a class="nav-link" data-route="/productos">Productos</a>
        <a class="nav-link" data-route="/servicios">Servicios</a>
        <a class="nav-link" data-route="/nosotros">Nosotros</a>
        <a class="nav-link" data-route="/ofertas">Ofertas</a>
        <a class="nav-link" data-route="/novedades">Novedades</a>
        <a class="nav-link" data-route="/contacto">Contacto</a>
        <a class="nav-link" data-route="/estado-orden">Estado de Orden</a>
      </nav>
      <div class="header-actions">
        <button class="header-btn" id="search-btn" title="Buscar">
          <i class="fas fa-search"></i>
        </button>
        <button class="header-btn" id="cart-btn" title="Carrito">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-badge" id="cart-badge">0</span>
        </button>
        <button class="quote-btn" id="quote-btn">
          <i class="fas fa-file-invoice"></i> Cotizar
        </button>
        <button class="menu-toggle" id="menu-toggle" aria-label="Menú">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  `;

  applyConfig();

  function closeMobileMenu() {
    const menu = document.getElementById('nav-menu');
    menu.classList.remove('open');
    document.body.style.overflow = '';
  }

  function navigateTo(route) {
    closeMobileMenu();
    router.navigate(route);
  }

  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      navigateTo(link.dataset.route);
    });
  });

  document.querySelector('.logo').addEventListener('click', () => {
    navigateTo('/');
  });

  document.getElementById('menu-toggle').addEventListener('click', () => {
    const menu = document.getElementById('nav-menu');
    menu.classList.toggle('open');
    document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
  });

  document.getElementById('search-btn').addEventListener('click', () => {
    document.getElementById('search-modal').classList.add('open');
    setTimeout(() => document.getElementById('search-input')?.focus(), 100);
  });

  document.getElementById('cart-btn').addEventListener('click', () => {
    document.dispatchEvent(new CustomEvent('open-cart'));
  });

  document.getElementById('quote-btn').addEventListener('click', () => {
    navigateTo('/contacto');
  });

  window.addEventListener('scroll', () => {
    header.classList.toggle('header-scrolled', window.scrollY > 10);
  }, { passive: true });

  store.subscribe(() => {
    const count = store.getCartCount();
    const badge = document.getElementById('cart-badge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    }
  });
}
