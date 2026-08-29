import { store } from './store.js';
import { router } from './router.js';
import { renderHeader } from './components/Header.js';
import { renderFooter } from './components/Footer.js';
import { renderCartSidebar } from './components/Cart.js';
import { renderSearchModal } from './components/SearchBar.js';
import { renderWhatsAppButton } from './components/WhatsAppButton.js';

import { renderHome } from './pages/Home.js';
import { renderProducts } from './pages/Products.js';
import { renderProductDetail } from './pages/ProductDetail.js';
import { renderCategories, renderCategoryDetail } from './pages/Categories.js';
import { renderServices } from './pages/Services.js';
import { renderAbout } from './pages/About.js';
import { renderContact } from './pages/Contact.js';
import { renderOffers } from './pages/Offers.js';
import { renderNews } from './pages/News.js';
import { renderOrderStatus } from './pages/OrderStatus.js';
import { renderCartPage } from './pages/Cart.js';
import { renderCheckout } from './pages/Checkout.js';
import { fetchConfig, fetchMarcasActivas, fetchServiciosActivos } from './services/apiService.js';

async function loadConfig() {
  try {
    const resp = await fetchConfig();
    if (resp.success) {
      window.appConfig = resp.data;
      localStorage.setItem('distelecom_config', JSON.stringify(resp.data));
    }
  } catch {
    const cached = localStorage.getItem('distelecom_config');
    if (cached) window.appConfig = JSON.parse(cached);
  }
}

async function loadMarcas() {
  try {
    const cached = localStorage.getItem('marcas');
    if (cached) {
      window.marcas = JSON.parse(cached);
      return;
    }
    const resp = await fetchMarcasActivas();
    if (resp.success) {
      const marcas = resp.data?.data || [];
      window.marcas = marcas;
      localStorage.setItem('marcas', JSON.stringify(marcas));
    }
  } catch {
    const cached = localStorage.getItem('marcas');
    if (cached) window.marcas = JSON.parse(cached);
  }
}

async function loadServicios() {
  try {
    const cached = localStorage.getItem('servicios');
    if (cached) {
      window.servicios = JSON.parse(cached);
      return;
    }
    const resp = await fetchServiciosActivos();
    if (resp.success) {
      const servicios = resp.data?.data || [];
      window.servicios = servicios;
      localStorage.setItem('servicios', JSON.stringify(servicios));
    }
  } catch {
    const cached = localStorage.getItem('servicios');
    if (cached) window.servicios = JSON.parse(cached);
  }
}

function showLoader() {
  const loader = document.getElementById('loader');
  loader.innerHTML = '<div class="loader-overlay" id="loader-overlay"><div class="loader-spinner"></div></div>';
}

function hideLoader() {
  const overlay = document.getElementById('loader-overlay');
  if (overlay) {
    overlay.classList.add('hidden');
    setTimeout(() => overlay.remove(), 500);
  }
}

async function initApp() {
  showLoader();

  renderHeader();
  renderCartSidebar();
  renderSearchModal();
  renderWhatsAppButton();

  document.body.insertAdjacentHTML('beforeend', '<footer id="main-footer"></footer>');

  router.addRoute('/', () => renderHome());
  router.addRoute('/productos', () => renderProducts());
  router.addRoute('/producto/:slug', (params) => renderProductDetail(params));
  router.addRoute('/categorias', () => renderCategories());
  router.addRoute('/categorias/:slug', (params) => renderCategoryDetail(params));
  router.addRoute('/servicios', () => renderServices());
  router.addRoute('/nosotros', () => renderAbout());
  router.addRoute('/contacto', () => renderContact());
  router.addRoute('/ofertas', () => renderOffers());
  router.addRoute('/novedades', () => renderNews());
  router.addRoute('/estado-orden', () => renderOrderStatus());
  router.addRoute('/carrito', () => renderCartPage());
  router.addRoute('/checkout', () => renderCheckout());
  router.addRoute('/404', () => {
    document.getElementById('main-content').innerHTML = `
      <div class="container page-content" style="text-align:center">
        <i class="fas fa-exclamation-triangle" style="font-size:4rem;color:var(--gray-400);margin-bottom:16px"></i>
        <h1 style="font-size:3rem;color:var(--primary);margin-bottom:8px">404</h1>
        <p style="color:var(--gray-500);font-size:1.1rem;margin-bottom:24px">Página no encontrada</p>
        <a class="btn btn-primary" data-route="/"><i class="fas fa-home"></i> Volver al Inicio</a>
      </div>
    `;
    document.querySelector('[data-route="/"]')?.addEventListener('click', (e) => {
      e.preventDefault();
      router.navigate('/');
    });
  });

  await Promise.all([store.loadProducts(), store.loadCategorias(), loadConfig(), loadMarcas(), loadServicios()]);

  renderFooter();

  if (window.appConfig) {
    document.title = window.appConfig.nombre_empresa || 'Distelecom';
    const metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc && window.appConfig.slogan) metaDesc.content = window.appConfig.slogan;
    const favicon = document.querySelector('link[rel="icon"]');
    if (favicon && window.appConfig.favicon) favicon.href = window.appConfig.favicon;
  }

  hideLoader();
  router.init();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initApp);
} else {
  initApp();
}
