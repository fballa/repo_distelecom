import { router } from '../router.js';

const SVG_ICONS = {
  shield: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
  cpu: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>`,
  eye: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
  zap: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
  users: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  award: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>`,
  target: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>`,
  compass: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>`,
  headset: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>`,
  settings: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`,
  globe: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`,
  heart: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`,
  tool: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>`,
};

const HERO_IMAGES = {
  main: 'https://misdemos.x10.mx/videos/distelecom/img/hero-networks.png',
  cctv: 'https://misdemos.x10.mx/videos/distelecom/img/hero-cctv.png',
  fiber: 'https://misdemos.x10.mx/videos/distelecom/img/hero-fiber.png',
  datacenter: 'https://misdemos.x10.mx/videos/distelecom/img/hero-datacenter.png',
  telecom: 'https://misdemos.x10.mx/videos/distelecom/img/hero-telecom.png',
};

function getServices() {
  const servicios = window.servicios || [];
  return servicios.slice(0, 6).map(s => ({
    nombre: s.nombre,
    descripcion: s.descripcion ? s.descripcion.substring(0, 100).trim() + '...' : '',
    icono: s.icono || 'fas fa-cog'
  }));
}

function faToSvg(iconClass) {
  const map = {
    'fas fa-video': SVG_ICONS.eye,
    'fas fa-network-wired': SVG_ICONS.globe,
    'fas fa-wifi': SVG_ICONS.zap,
    'fas fa-fingerprint': SVG_ICONS.shield,
    'fas fa-phone-alt': SVG_ICONS.headset,
    'fas fa-cash-register': SVG_ICONS.settings,
    'fas fa-chart-line': SVG_ICONS.compass,
    'fas fa-tools': SVG_ICONS.tool,
    'fas fa-cog': SVG_ICONS.settings,
    'fas fa-calendar-check': SVG_ICONS.award,
    'fas fa-wrench': SVG_ICONS.tool,
  };
  return map[iconClass] || SVG_ICONS.cpu;
}

export function renderAbout() {
  const main = document.getElementById('main-content');
  const servicios = getServices();

  const areasEspecializacion = [
    { titulo: 'CCTV & Videovigilancia', desc: 'Sistemas de seguridad IP y analógicos con monitoreo 24/7.', imagen: HERO_IMAGES.cctv },
    { titulo: 'Redes & Conectividad', desc: 'Infraestructura de redes LAN, WAN y WiFi empresarial.', imagen: HERO_IMAGES.main },
    { titulo: 'Fibra Óptica', desc: 'Tendido, fusión y certificación de redes de alta velocidad.', imagen: HERO_IMAGES.fiber },
    { titulo: 'Data Center', desc: 'Soluciones completas para centros de datos y servidores.', imagen: HERO_IMAGES.datacenter },
    { titulo: 'Telecomunicaciones', desc: 'Telefonía IP, VoIP y comunicaciones unificadas.', imagen: HERO_IMAGES.telecom },
  ];

  const valores = [
    { icono: SVG_ICONS.shield, titulo: 'Confianza', desc: 'Construimos relaciones a largo plazo basadas en la transparencia y el cumplimiento.' },
    { icono: SVG_ICONS.zap, titulo: 'Innovación', desc: 'Estamos a la vanguardia tecnológica para ofrecerte siempre lo mejor del mercado.' },
    { icono: SVG_ICONS.award, titulo: 'Calidad', desc: 'Trabajamos con marcas líderes mundiales y procesos certificados.' },
    { icono: SVG_ICONS.users, titulo: 'Servicio', desc: 'Un equipo de ingenieros certificados listos para apoyarte en cada proyecto.' },
  ];

  const porQueElegirnos = [
    { num: '01', titulo: 'Soluciones Integrales', desc: 'Un solo proveedor para toda tu infraestructura tecnológica. CCTV, redes, fibra, telecomunicaciones y más.' },
    { num: '02', titulo: 'Marcas Certificadas', desc: 'Trabajamos con Hikvision, TP-Link, MikroTik, Ubiquiti, Yealink, ZKTeco y otras marcas líderes.' },
    { num: '03', titulo: 'Asesoría Técnica', desc: 'Ingenieros certificados que diseñan la solución exacta para las necesidades de tu negocio.' },
    { num: '04', titulo: 'Soporte Continuo', desc: 'Instalación profesional, mantenimiento preventivo y soporte técnico especializado.' },
  ];

  main.innerHTML = `
    <section class="about-hero">
      <div class="about-hero-inner">
        <div class="about-hero-text">
          <span class="about-label">NOSOTROS</span>
          <h1 class="about-hero-title">Tecnología y soluciones<br>para conectar, proteger<br>y hacer crecer tu negocio.</h1>
          <p class="about-hero-desc">Distelecom es una empresa nicaragüense especializada en telecomunicaciones, infraestructura tecnológica, redes, seguridad electrónica y sistemas POS. Impulsamos la transformación digital de empresas con soluciones de clase mundial.</p>
          <div class="about-hero-ctas">
            <button class="btn btn-primary" id="about-cta-contact"><i class="fas fa-envelope"></i> Contáctanos</button>
            <button class="btn btn-outline" id="about-cta-products"><i class="fas fa-box"></i> Ver Productos</button>
          </div>
        </div>
        <div class="about-hero-visual">
          <div class="about-hero-image-main">
            <img src="${HERO_IMAGES.main}" alt="Infraestructura de redes Distelecom" loading="lazy">
          </div>
          <div class="about-hero-image-float about-hero-float-1">
            <img src="${HERO_IMAGES.cctv}" alt="CCTV y videovigilancia" loading="lazy">
          </div>
          <div class="about-hero-image-float about-hero-float-2">
            <img src="${HERO_IMAGES.fiber}" alt="Fibra óptica" loading="lazy">
          </div>
          <div class="about-hero-glow"></div>
        </div>
      </div>
    </section>

    <section class="about-mission-section">
      <div class="container">
        <div class="about-mission-grid">
          <div class="about-mission-card">
            <div class="about-mission-icon">${SVG_ICONS.target}</div>
            <h3>Misión</h3>
            <p>Proveer soluciones tecnológicas innovadoras y de calidad que impulsen la productividad y seguridad de las empresas nicaragüenses, ofreciendo productos de clase mundial con soporte técnico especializado y atención personalizada.</p>
          </div>
          <div class="about-mission-card">
            <div class="about-mission-icon">${SVG_ICONS.compass}</div>
            <h3>Visión</h3>
            <p>Ser la empresa líder en soluciones tecnológicas en Nicaragua, reconocida por nuestra calidad, innovación y compromiso con el cliente, contribuyendo al desarrollo digital del país.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="about-services-section">
      <div class="container">
        <div class="about-section-header">
          <span class="about-section-label">LO QUE HACEMOS</span>
          <h2 class="about-section-title">Soluciones especializadas<br>para cada necesidad</h2>
          <p class="about-section-desc">Cobertura completa en infraestructura tecnológica, desde seguridad hasta conectividad.</p>
        </div>
        <div class="about-services-grid">
          ${servicios.map((s, i) => `
            <div class="about-service-card${i === 0 ? ' about-service-featured' : ''}">
              <div class="about-service-icon">${faToSvg(s.icono)}</div>
              <h3>${s.nombre}</h3>
              <p>${s.descripcion}</p>
            </div>
          `).join('')}
        </div>
      </div>
    </section>

    <section class="about-areas-section">
      <div class="container">
        <div class="about-section-header">
          <span class="about-section-label">ESPECIALIZACIÓN</span>
          <h2 class="about-section-title">Nuestras áreas de<br>experiencia</h2>
        </div>
        <div class="about-areas-grid">
          ${areasEspecializacion.map((a, i) => `
            <div class="about-area-card${i === 0 ? ' about-area-featured' : ''}">
              <div class="about-area-image">
                <img src="${a.imagen}" alt="${a.titulo}" loading="lazy">
              </div>
              <div class="about-area-content">
                <h3>${a.titulo}</h3>
                <p>${a.desc}</p>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </section>

    <section class="about-values-section">
      <div class="container">
        <div class="about-section-header">
          <span class="about-section-label">LO QUE NOS MUEVE</span>
          <h2 class="about-section-title">Valores que definen<br>nuestro trabajo</h2>
        </div>
        <div class="about-values-grid">
          ${valores.map(v => `
            <div class="about-value-card">
              <div class="about-value-icon">${v.icono}</div>
              <h3>${v.titulo}</h3>
              <p>${v.desc}</p>
            </div>
          `).join('')}
        </div>
      </div>
    </section>

    <section class="about-why-section">
      <div class="container">
        <div class="about-section-header">
          <span class="about-section-label">¿POR QUÉ ELEGIRNOS?</span>
          <h2 class="about-section-title">La diferencia<br>Distelecom</h2>
        </div>
        <div class="about-why-grid">
          ${porQueElegirnos.map(r => `
            <div class="about-why-card">
              <span class="about-why-num">${r.num}</span>
              <h3>${r.titulo}</h3>
              <p>${r.desc}</p>
            </div>
          `).join('')}
        </div>
      </div>
    </section>

    <section class="about-cta-section">
      <div class="container">
        <div class="about-cta-inner">
          <h2>¿Necesitas una solución tecnológica?</h2>
          <p>Conversemos sobre tu proyecto y encontremos la solución adecuada para tu empresa.</p>
          <div class="about-cta-buttons">
            <button class="btn btn-primary" id="about-final-cta"><i class="fas fa-envelope"></i> Contáctanos</button>
            <button class="btn btn-outline about-cta-outline" id="about-final-products"><i class="fas fa-box"></i> Ver Productos</button>
          </div>
        </div>
      </div>
    </section>
  `;

  document.getElementById('about-cta-contact')?.addEventListener('click', () => router.navigate('/contacto'));
  document.getElementById('about-cta-products')?.addEventListener('click', () => router.navigate('/productos'));
  document.getElementById('about-final-cta')?.addEventListener('click', () => router.navigate('/contacto'));
  document.getElementById('about-final-products')?.addEventListener('click', () => router.navigate('/productos'));
}
