import { store } from './store.js';
import { BASE_URL } from './config.js';

class Router {
  constructor() {
    this.routes = {};
    this.currentRoute = null;
    window.addEventListener('popstate', () => this.handleRoute());
  }

  addRoute(path, handler) {
    this.routes[path] = handler;
  }

  navigate(path, data = {}) {
    const fullPath = path.startsWith('/') ? path : `/${path}`;
    const url = `${BASE_URL}${fullPath}`;
    history.pushState(data, '', url);
    this.handleRoute();
  }

  handleRoute() {
    const path = window.location.pathname;
    const mainContent = document.getElementById('main-content');

    const routePath = this.stripBase(path);

    mainContent.classList.remove('page-enter');
    void mainContent.offsetWidth;

    for (const [pattern, handler] of Object.entries(this.routes)) {
      const params = this.matchRoute(pattern, routePath);
      if (params !== null) {
        this.currentRoute = pattern;
        handler(params);
        this.updateMeta(routePath);
        mainContent.classList.add('page-enter');
        window.scrollTo(0, 0);
        this.updateActiveNav(routePath);
        store.setState({ currentRoute: routePath });
        return;
      }
    }

    this.routes['/404']?.();
    mainContent.classList.add('page-enter');
  }

  stripBase(path) {
    if (BASE_URL && path.startsWith(BASE_URL)) {
      const stripped = path.slice(BASE_URL.length);
      return stripped || '/';
    }
    return path;
  }

  matchRoute(pattern, path) {
    const patternParts = pattern.split('/');
    const pathParts = path.split('/');

    if (patternParts.length !== pathParts.length) return null;

    const params = {};
    for (let i = 0; i < patternParts.length; i++) {
      if (patternParts[i].startsWith(':')) {
        params[patternParts[i].slice(1)] = pathParts[i];
      } else if (patternParts[i] !== pathParts[i]) {
        return null;
      }
    }
    return params;
  }

  updateMeta(routePath) {
    const titles = {
      '/': 'Distelecom - Telecomunicaciones e Infraestructura Tecnológica',
      '/productos': 'Productos - Distelecom',
      '/servicios': 'Servicios - Distelecom',
      '/nosotros': 'Nosotros - Distelecom',
      '/ofertas': 'Ofertas - Distelecom',
      '/novedades': 'Novedades - Distelecom',
      '/contacto': 'Contacto - Distelecom',
      '/carrito': 'Carrito de Compras - Distelecom',
      '/checkout': 'Checkout - Distelecom',
      '/estado-orden': 'Estado de Orden - Distelecom',
    };

    let title = titles[routePath];
    if (!title) {
      if (routePath.startsWith('/producto/')) title = 'Producto - Distelecom';
      else if (routePath.startsWith('/categorias/')) title = 'Categoría - Distelecom';
      else title = 'Distelecom';
    }
    document.title = title;
    document.querySelector('meta[name="description"]').content = title;
    document.querySelector('meta[property="og:title"]').content = title;
  }

  updateActiveNav(routePath) {
    document.querySelectorAll('.nav-link').forEach(link => {
      const route = link.dataset.route;
      const isActive = route === routePath ||
        (routePath.startsWith('/producto/') && route === '/productos') ||
        (routePath.startsWith('/categorias/') && route === '/productos') ||
        (routePath === '/carrito' && route === '/carrito') ||
        (routePath === '/checkout' && route === '/carrito');
      link.classList.toggle('active', isActive);
    });
  }

  init() {
    this.handleRoute();
  }
}

export const router = new Router();
