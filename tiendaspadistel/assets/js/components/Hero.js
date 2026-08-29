import { router } from '../router.js';

const SLIDES = [
  {
    img: 'https://misdemos.x10.mx/videos/distelecom/img/hero-cctv.png',
    tag: 'VIDEOVIGILANCIA',
    title: 'Soluciones de videovigilancia para proteger lo que más importa',
    desc: 'Cámaras IP, sistemas de seguridad y soluciones profesionales para tu empresa o negocio.',
    cta: 'VER SOLUCIONES CCTV',
    ctaRoute: '/categorias/cctv',
    alt: 'Soluciones de videovigilancia CCTV',
    icon: 'camera'
  },
  {
    img: 'https://misdemos.x10.mx/videos/distelecom/img/hero-datacenter.png',
    tag: 'DATACENTER',
    title: 'Infraestructura preparada para tu operación',
    desc: 'Gabinetes, UPS, cableado estructurado y soluciones completas para centros de datos.',
    cta: 'EXPLORAR DATACENTER',
    ctaRoute: '/productos',
    alt: 'Infraestructura de data center',
    icon: 'server'
  },
  {
    img: 'https://misdemos.x10.mx/videos/distelecom/img/hero-networks.png',
    tag: 'REDES',
    title: 'Conecta tu empresa con tecnología confiable',
    desc: 'Switches, routers, access points y todo lo que necesitas para tu infraestructura de red.',
    cta: 'VER SOLUCIONES DE REDES',
    ctaRoute: '/categorias/redes',
    alt: 'Soluciones de redes empresariales',
    icon: 'network'
  },
  {
    img: 'https://misdemos.x10.mx/videos/distelecom/img/hero-fiber.png',
    tag: 'FIBRA ÓPTICA',
    title: 'Conectividad de alto rendimiento',
    desc: 'Cableado, conectores y equipos para redes de fibra óptica de alta velocidad.',
    cta: 'EXPLORAR FIBRA ÓPTICA',
    ctaRoute: '/categorias/fibra-optica',
    alt: 'Soluciones de fibra óptica',
    icon: 'wifi'
  },
  {
    img: 'https://misdemos.x10.mx/videos/distelecom/img/hero-telecom.png',
    tag: 'TELECOMUNICACIONES',
    title: 'Tecnología para mantenerte siempre conectado',
    desc: 'Telefonía IP, centrales y soluciones de comunicación para tu empresa.',
    cta: 'VER SOLUCIONES TELECOM',
    ctaRoute: '/categorias/telefonia-ip',
    alt: 'Soluciones de telecomunicaciones',
    icon: 'phone'
  }
];

const SVG_ICONS = {
  camera: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="12" r="4"/></svg>`,
  server: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>`,
  network: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="1" width="22" height="22" rx="2" ry="2"/><path d="M7 7l10 10"/><path d="M17 7l-10 10"/></svg>`,
  wifi: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>`,
  phone: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`
};

const SVG_PREV = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>`;
const SVG_NEXT = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>`;

export function renderHero() {
  const heroSection = document.getElementById('hero-section');
  if (!heroSection) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isMobile = window.innerWidth < 768;

  let currentIndex = 0;
  let autoplayTimer = null;
  let isPaused = false;
  let mouseX = 0;
  let mouseY = 0;
  let rafId = null;

  heroSection.innerHTML = `
    <section class="hero-xero" role="region" aria-label="Carrusel de presentación" tabindex="0">
      <!-- Fondo: Arco de luz + Grid -->
      <div class="hero-xero-bg" aria-hidden="true"></div>
      <div class="hero-xero-grid" aria-hidden="true"></div>

      <!-- Carrusel Principal -->
      <div class="hero-xero-carousel">
        <div class="hero-xero-viewport">
          ${SLIDES.map((s, i) => `
            <div class="hero-xero-slide${i === 0 ? ' hero-xero-active' : ''}" data-index="${i}" aria-hidden="${i !== 0}">
              <div class="hero-xero-image-wrapper">
                <img src="${s.img}" alt="${s.alt}" class="hero-xero-image" ${i === 0 ? '' : 'loading="lazy"'}>
              </div>
              <div class="hero-xero-content">
                <span class="hero-xero-tag">${s.tag}</span>
                <h1 class="hero-xero-title">${s.title}</h1>
                <p class="hero-xero-desc">${s.desc}</p>
                <div class="hero-xero-actions">
                  <a class="btn btn-primary hero-xero-cta" data-route="${s.ctaRoute}">
                    <i class="fas fa-arrow-right"></i> ${s.cta}
                  </a>
                </div>
              </div>
            </div>
          `).join('')}
        </div>

        <!-- Indicadores -->
        <div class="hero-xero-indicators" role="tablist" aria-label="Slides del carrusel">
          ${SLIDES.map((_, i) => `
            <button class="hero-xero-dot${i === 0 ? ' hero-xero-dot-active' : ''}"
                    role="tab"
                    aria-selected="${i === 0}"
                    aria-label="Ir al slide ${i + 1}: ${SLIDES[i].tag}"
                    data-slide="${i}"></button>
          `).join('')}
        </div>

        <!-- Controles -->
        <button class="hero-xero-nav hero-xero-prev" aria-label="Slide anterior">${SVG_PREV}</button>
        <button class="hero-xero-nav hero-xero-next" aria-label="Siguiente slide">${SVG_NEXT}</button>
      </div>

      <!-- Nodos y Beam decorativos (solo desktop) -->
      <div class="hero-xero-nodes" aria-hidden="true">
        <div class="hero-xero-node hero-xero-node-left">
          <div class="hero-xero-node-core"></div>
          <div class="hero-xero-node-ring"></div>
          <div class="hero-xero-node-icon">${SVG_ICONS.camera}</div>
        </div>
        <div class="hero-xero-node hero-xero-node-center">
          <div class="hero-xero-node-core"></div>
          <div class="hero-xero-node-ring"></div>
          <div class="hero-xero-node-icon">${SVG_ICONS.network}</div>
        </div>
        <div class="hero-xero-node hero-xero-node-right">
          <div class="hero-xero-node-core"></div>
          <div class="hero-xero-node-ring"></div>
          <div class="hero-xero-node-icon">${SVG_ICONS.server}</div>
        </div>
        <div class="hero-xero-beam"></div>
      </div>
    </section>
  `;

  const container = heroSection.querySelector('.hero-xero');
  const carousel = heroSection.querySelector('.hero-xero-carousel');
  const slides = heroSection.querySelectorAll('.hero-xero-slide');
  const dots = heroSection.querySelectorAll('.hero-xero-dot');
  const prevBtn = heroSection.querySelector('.hero-xero-prev');
  const nextBtn = heroSection.querySelector('.hero-xero-next');
  const nodes = heroSection.querySelectorAll('.hero-xero-node');
  const beam = heroSection.querySelector('.hero-xero-beam');

  function goToSlide(index, direction) {
    if (index === currentIndex) return;
    const prevIndex = currentIndex;
    currentIndex = index;

    slides.forEach((slide, i) => {
      slide.classList.remove('hero-xero-active', 'hero-xero-exit-left', 'hero-xero-exit-right', 'hero-xero-enter-left', 'hero-xero-enter-right');
      slide.setAttribute('aria-hidden', 'true');

      if (i === prevIndex) {
        slide.classList.add(direction === 'next' ? 'hero-xero-exit-left' : 'hero-xero-exit-right');
      } else if (i === currentIndex) {
        slide.classList.add(direction === 'next' ? 'hero-xero-enter-right' : 'hero-xero-enter-left', 'hero-xero-active');
        slide.setAttribute('aria-hidden', 'false');
      }
    });

    dots.forEach((dot, i) => {
      dot.classList.toggle('hero-xero-dot-active', i === currentIndex);
      dot.setAttribute('aria-selected', i === currentIndex);
    });

    updateNodes();
    resetAutoplay();
  }

  function nextSlide() {
    goToSlide((currentIndex + 1) % SLIDES.length, 'next');
  }

  function prevSlide() {
    goToSlide((currentIndex - 1 + SLIDES.length) % SLIDES.length, 'prev');
  }

  function startAutoplay() {
    if (isPaused || prefersReducedMotion) return;
    clearInterval(autoplayTimer);
    autoplayTimer = setInterval(nextSlide, 5500);
  }

  function resetAutoplay() {
    clearInterval(autoplayTimer);
    startAutoplay();
  }

  function pauseAutoplay() {
    isPaused = true;
    clearInterval(autoplayTimer);
  }

  function resumeAutoplay() {
    isPaused = false;
    startAutoplay();
  }

  function updateNodes() {
    const slideIcons = ['camera', 'server', 'network', 'wifi', 'phone'];
    const iconName = slideIcons[currentIndex];
    const icons = [SVG_ICONS.camera, SVG_ICONS.server, SVG_ICONS.network, SVG_ICONS.wifi, SVG_ICONS.phone];

    nodes.forEach((node, i) => {
      const targetIcon = icons[(currentIndex + i - 1 + 5) % 5];
      const iconEl = node.querySelector('.hero-xero-node-icon');
      if (iconEl) {
        iconEl.style.opacity = '0';
        setTimeout(() => {
          iconEl.innerHTML = targetIcon;
          iconEl.style.opacity = '1';
        }, 150);
      }
    });

    updateBeam();
  }

  function updateBeam() {
    if (!beam || nodes.length < 2) return;
    const leftNode = nodes[0];
    const rightNode = nodes[2];
    if (!leftNode || !rightNode) return;

    const leftRect = leftNode.getBoundingClientRect();
    const rightRect = rightNode.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();

    const x1 = leftRect.left + leftRect.width / 2 - containerRect.left;
    const y1 = leftRect.top + leftRect.height / 2 - containerRect.top;
    const x2 = rightRect.left + rightRect.width / 2 - containerRect.left;
    const y2 = rightRect.top + rightRect.height / 2 - containerRect.top;

    const angle = Math.atan2(y2 - y1, x2 - x1) * 180 / Math.PI;
    const length = Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2);

    beam.style.width = `${length}px`;
    beam.style.left = `${x1}px`;
    beam.style.top = `${y1}px`;
    beam.style.transform = `rotate(${angle}deg)`;
  }

  function handleMouseMove(e) {
    if (prefersReducedMotion || isMobile) return;
    const rect = container.getBoundingClientRect();
    mouseX = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
    mouseY = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
  }

  function applyParallax() {
    if (prefersReducedMotion || isMobile) return;
    const activeSlide = heroSection.querySelector('.hero-xero-active');
    if (!activeSlide) return;

    const imgWrapper = activeSlide.querySelector('.hero-xero-image-wrapper');
    const content = activeSlide.querySelector('.hero-xero-content');

    if (imgWrapper) {
      const rotateY = mouseX * 5;
      const rotateX = -mouseY * 3;
      imgWrapper.style.transform = `perspective(1200px) translateZ(20px) rotateY(${rotateY}deg) rotateX(${rotateX}deg)`;
    }

    if (content) {
      const translateX = mouseX * 12;
      const translateY = mouseY * 8;
      content.style.transform = `translateZ(60px) translateX(${translateX}px) translateY(${translateY}px)`;
    }

    rafId = requestAnimationFrame(applyParallax);
  }

  function resetParallax() {
    const activeSlide = heroSection.querySelector('.hero-xero-active');
    if (!activeSlide) return;
    const imgWrapper = activeSlide.querySelector('.hero-xero-image-wrapper');
    const content = activeSlide.querySelector('.hero-xero-content');
    if (imgWrapper) imgWrapper.style.transform = '';
    if (content) content.style.transform = '';
  }

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      const idx = parseInt(dot.dataset.slide);
      goToSlide(idx, idx > currentIndex ? 'next' : 'prev');
    });
  });

  prevBtn.addEventListener('click', prevSlide);
  nextBtn.addEventListener('click', nextSlide);

  container.addEventListener('mouseenter', () => {
    pauseAutoplay();
    if (!prefersReducedMotion && !isMobile) {
      rafId = requestAnimationFrame(applyParallax);
    }
  });

  container.addEventListener('mouseleave', () => {
    resumeAutoplay();
    if (rafId) cancelAnimationFrame(rafId);
    resetParallax();
  });

  container.addEventListener('mousemove', handleMouseMove);

  container.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prevSlide();
    else if (e.key === 'ArrowRight') nextSlide();
  });

  heroSection.querySelectorAll('[data-route]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      router.navigate(el.dataset.route);
    });
  });

  window.addEventListener('resize', updateNodes);
  updateNodes();
  startAutoplay();
}