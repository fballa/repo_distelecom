import { router } from '../router.js';

export function renderFooter() {
  const footer = document.getElementById('main-footer');
  const cfg = window.appConfig || {};

  const nombre = cfg.nombre_empresa || 'Distelecom';
  const direccion = cfg.direccion || 'Edificio Delta, módulo 4B, Managua, Nicaragua.';
  const telefono = cfg.telefono || '(505) 58883346';
  const whatsapp = cfg.whatsapp || '50558883346';
  const correo = cfg.correo || 'info@distelecom.com';
  const slogan = cfg.slogan || '';
  const logo = cfg.logo || 'https://misdemos.x10.mx/videos/distelecom/logodistelcom.png';
  const facebook = cfg.facebook || '#';
  const instagram = cfg.instagram || '#';
  const youtube = cfg.youtube || '#';

  const fbAttrs = facebook === '#' ? '' : ' target="_blank" rel="noopener"';
  const igAttrs = instagram === '#' ? '' : ' target="_blank" rel="noopener"';
  const ytAttrs = youtube === '#' ? '' : ' target="_blank" rel="noopener"';

  footer.innerHTML = `
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo" style="cursor:pointer" data-route="/">
            <img src="${logo}" alt="${nombre}" loading="lazy">
            <span>${nombre}</span>
          </div>
          <p>${slogan || 'Soluciones integrales en telecomunicaciones, infraestructura tecnol\u00f3gica, redes, seguridad electr\u00f3nica y sistemas POS para empresas en Nicaragua.'}</p>
          <div class="footer-contact-item"><i class="fas fa-map-marker-alt"></i><span>${direccion}</span></div>
          <div class="footer-contact-item"><i class="fas fa-phone-alt"></i><span>${telefono}</span></div>
          <div class="footer-contact-item"><i class="fas fa-envelope"></i><span>${correo}</span></div>
          <div class="footer-social">
            <a href="${facebook}" aria-label="Facebook" id="footer-facebook"${fbAttrs}><i class="fab fa-facebook-f"></i></a>
            <a href="${instagram}" aria-label="Instagram" id="footer-instagram"${igAttrs}><i class="fab fa-instagram"></i></a>
            <a href="${youtube}" aria-label="YouTube" id="footer-youtube"${ytAttrs}><i class="fab fa-youtube"></i></a>
            <a href="https://wa.me/${whatsapp}" aria-label="WhatsApp" id="footer-whatsapp" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h4>Enlaces R\u00e1pidos</h4>
          <ul>
            <li><a data-route="/">Inicio</a></li>
            <li><a data-route="/productos">Productos</a></li>
            <li><a data-route="/servicios">Servicios</a></li>
            <li><a data-route="/nosotros">Nosotros</a></li>
            <li><a data-route="/contacto">Contacto</a></li>
            <li><a data-route="/estado-orden">Estado de Orden</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Categor\u00edas</h4>
          <ul>
            <li><a data-route="/categorias/cctv">CCTV</a></li>
            <li><a data-route="/categorias/redes">Redes</a></li>
            <li><a data-route="/categorias/control-de-acceso">Control de Acceso</a></li>
            <li><a data-route="/categorias/telefonia-ip">Telefon\u00eda IP</a></li>
            <li><a data-route="/categorias/fibra-optica">Fibra \u00d3ptica</a></li>
            <li><a data-route="/categorias/pos">POS</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Horario de Atenci\u00f3n</h4>
          <p style="font-size:0.9rem;color:var(--gray-400);line-height:1.8">Lun - Vie: 8:00 AM - 6:00 PM<br>S\u00e1b: 9:00 AM - 1:00 PM</p>
          <h4 style="margin-top:24px">Servicios</h4>
          <ul>
            <li><a data-route="/servicios">Instalaci\u00f3n</a></li>
            <li><a data-route="/servicios">Mantenimiento</a></li>
            <li><a data-route="/servicios">Soporte T\u00e9cnico</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; ${new Date().getFullYear()} ${nombre}. Todos los derechos reservados.</p>
      </div>
    </div>
  `;

  document.querySelectorAll('#main-footer [data-route]').forEach(el => {
    el.addEventListener('click', () => router.navigate(el.dataset.route));
  });
}
