import { store } from '../store.js';
import { renderHero } from '../components/Hero.js';
import { renderProductCard } from '../components/ProductCard.js';
import { router } from '../router.js';

function extraerResumen(descripcion) {
  if (!descripcion) return '';
  const match = descripcion.match(/\*\*Descripción Resumida:\*\* (.*?)(?:\n|$)/);
  return match ? match[1].trim() : descripcion.substring(0, 120).trim() + '...';
}

export function renderHome() {
  const main = document.getElementById('main-content');
  const categorias = store.getCategorias();

  const catCards = Array.isArray(categorias) && categorias.length
    ? categorias.slice(0, 6).map(c => `
      <div class="category-card" data-id="${c.id || ''}" data-slug="${c.slug || ''}">
        <img src="${c.imagen || 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png'}" alt="${c.nombre}" loading="lazy">
        <div class="category-card-body">
          <i class="${c.icono || 'fas fa-box'}"></i>
          <h3>${c.nombre}</h3>
          <p>${c.descripcion || ''}</p>
          <button class="btn btn-navy">
            <i class="fas fa-box"></i> Ver Productos
          </button>
        </div>
      </div>
    `).join('')
    : '';

  const serviciosData = window.servicios || [];
  const servicios = serviciosData.slice(0, 4).map(s => ({
    icono: s.icono ? (s.icono.includes(' ') ? s.icono : `fas ${s.icono}`) : 'fas fa-cog',
    titulo: s.nombre,
    desc: extraerResumen(s.descripcion)
  }));

  const marcasApi = window.marcas || [];

  const beneficios = [
    { icono: 'fas fa-truck', titulo: 'Envío Rápido', desc: 'Entrega en todo Nicaragua en 24-48 horas.' },
    { icono: 'fas fa-headset', titulo: 'Soporte Técnico', desc: 'Asistencia especializada pre y post venta.' },
    { icono: 'fas fa-award', titulo: 'Garantía', desc: 'Productos con garantía de hasta 5 años.' },
    { icono: 'fas fa-tools', titulo: 'Instalación', desc: 'Servicio de instalación y configuración profesional.' }
  ];

  const destacados = store.getDestacados().slice(0, 8);

  main.innerHTML = `
    <section id="hero-section"></section>

    ${catCards ? `
    <section class="section" style="background:var(--gray-50)" data-aos="fade-up">
      <div class="container">
        <h2 class="section-title">Categorías</h2>
        <p class="section-subtitle">Explora nuestras soluciones tecnológicas</p>
        <div class="categories-grid">
          ${catCards}
        </div>
      </div>
    </section>` : ''}

    <section class="section" data-aos="fade-up">
      <div class="container">
        <h2 class="section-title">Productos Destacados</h2>
        <p class="section-subtitle">Lo más vendido y recomendado por nuestros clientes</p>
        <div class="product-grid" id="home-products"></div>
        <div style="text-align:center;margin-top:40px">
          <a class="btn btn-primary" data-route="/productos">
            <i class="fas fa-box"></i> Ver Todos los Productos
          </a>
        </div>
      </div>
    </section>

    <section class="section" style="background:var(--gray-50)" data-aos="fade-up">
      <div class="container">
        <h2 class="section-title">Servicios</h2>
        <p class="section-subtitle">Soluciones integrales para tu empresa</p>
        <div class="services-grid">
          ${servicios.map(s => `
            <div class="service-card">
              <i class="${s.icono}"></i>
              <h3>${s.titulo}</h3>
              <p>${s.desc}</p>
            </div>
          `).join('')}
        </div>
        <div style="text-align:center;margin-top:40px">
          <a class="btn btn-primary" data-route="/servicios">
            <i class="fas fa-info-circle"></i> Ver Todos los Servicios
          </a>
        </div>
      </div>
    </section>

    <section class="brands-section" data-aos="fade-up">
      <div class="container">
        <h2 class="section-title">Marcas que Trabajamos</h2>
        <p class="section-subtitle">Las mejores marcas del mercado tecnológico</p>
        <div class="brands-grid">
          ${marcasApi.length ? marcasApi.map(m => `
            <span class="brand-item">${m.logo ? `<img src="${m.logo}" alt="${m.nombre}" style="max-height:40px;vertical-align:middle;margin-right:8px">` : ''}${m.nombre}</span>
          `).join('') : '<p style="color:var(--gray-400);text-align:center">Cargando marcas...</p>'}
        </div>
      </div>
    </section>

    <section class="section" data-aos="fade-up">
      <div class="container">
        <h2 class="section-title">¿Por qué elegir Distelecom?</h2>
        <p class="section-subtitle">Más de 10 años brindando soluciones tecnológicas en Nicaragua</p>
        <div class="benefits-grid">
          ${beneficios.map(b => `
            <div class="benefit-item">
              <i class="${b.icono}"></i>
              <h4>${b.titulo}</h4>
              <p>${b.desc}</p>
            </div>
          `).join('')}
        </div>
      </div>
    </section>

    <section class="cta-section" data-aos="fade-up">
      <div class="container">
        <h2>¿Listo para transformar tu infraestructura tecnológica?</h2>
        <p>Contáctanos hoy y descubre cómo podemos ayudarte a llevar tu empresa al siguiente nivel.</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
          <a class="btn btn-primary" style="background:var(--white);color:var(--primary)" data-route="/contacto">
            <i class="fas fa-envelope"></i> Contáctanos
          </a>
          <a class="btn btn-navy" data-route="/productos">
            <i class="fas fa-box"></i> Ver Productos
          </a>
        </div>
      </div>
    </section>
  `;

  renderHero();

  const grid = document.getElementById('home-products');
  if (destacados.length) {
    destacados.forEach(p => grid.appendChild(renderProductCard(p)));
  }

  document.querySelectorAll('[data-route]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      router.navigate(el.dataset.route);
    });
  });

  document.querySelectorAll('.category-card').forEach(card => {
    card.addEventListener('click', () => {
      const slug = card.dataset.slug;
      if (slug) {
        router.navigate(`/categorias/${slug}`);
      } else {
        router.navigate('/productos');
      }
    });
  });

  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 600, once: true, offset: 100 });
  }
}
