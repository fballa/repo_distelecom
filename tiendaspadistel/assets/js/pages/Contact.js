import { router } from '../router.js';

export function renderContact() {
  const main = document.getElementById('main-content');
  const saved = JSON.parse(localStorage.getItem('distelecom_contact_form') || '{}');
  const cfg = window.appConfig || {};

  const direccion = cfg.direccion || 'Edificio Delta, módulo 4B, Managua, Nicaragua.';
  const telefono = cfg.telefono || '(505) 58883346';
  const whatsapp = cfg.whatsapp || '50558883346';
  const correo = cfg.correo || 'info@distelecom.com';
  const facebook = cfg.facebook || '#';
  const instagram = cfg.instagram || '#';
  const youtube = cfg.youtube || '#';
  const horario = 'Lun - Vie: 8:00 AM - 6:00 PM<br>Sáb: 9:00 AM - 1:00 PM';

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Contacto</h1>
      <p class="page-subtitle">Estamos listos para ayudarte</p>
      <div class="contact-grid">
        <div>
          <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:24px">
            <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:20px"><i class="fas fa-info-circle" style="color:var(--secondary)"></i> Información de Contacto</h3>
            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px">
              <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,180,216,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-map-marker-alt" style="color:var(--secondary);font-size:1.1rem"></i></div>
              <div><h4 style="font-size:0.9rem;color:var(--gray-700);margin-bottom:4px">Dirección</h4><p id="contacto-direccion" style="font-size:0.88rem;color:var(--gray-500)">${direccion}</p></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px">
              <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,180,216,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-phone-alt" style="color:var(--secondary);font-size:1.1rem"></i></div>
              <div><h4 style="font-size:0.9rem;color:var(--gray-700);margin-bottom:4px">Teléfono</h4><p style="font-size:0.88rem;color:var(--gray-500)"><a id="contacto-telefono" href="tel:${telefono.replace(/[^0-9]/g, '')}" style="color:var(--gray-500);text-decoration:none">${telefono}</a></p></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:20px">
              <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,180,216,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-envelope" style="color:var(--secondary);font-size:1.1rem"></i></div>
              <div><h4 style="font-size:0.9rem;color:var(--gray-700);margin-bottom:4px">Correo Electrónico</h4><p style="font-size:0.88rem;color:var(--gray-500)"><a id="contacto-email" href="mailto:${correo}" style="color:var(--gray-500);text-decoration:none">${correo}</a></p></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px">
              <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,180,216,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-clock" style="color:var(--secondary);font-size:1.1rem"></i></div>
              <div><h4 style="font-size:0.9rem;color:var(--gray-700);margin-bottom:4px">Horario de Atención</h4><p id="contacto-horario" style="font-size:0.88rem;color:var(--gray-500)">${horario}</p></div>
            </div>
          </div>

          <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:24px">
            <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:16px"><i class="fas fa-share-alt" style="color:var(--secondary)"></i> Redes Sociales</h3>
            <div style="display:flex;gap:12px">
              <a id="contacto-facebook" href="${facebook}" ${facebook !== '#' ? 'target="_blank" rel="noopener"' : ''} style="width:44px;height:44px;border-radius:12px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.2rem;transition:var(--transition)" class="hover-scale"><i class="fab fa-facebook-f"></i></a>
              <a id="contacto-instagram" href="${instagram}" ${instagram !== '#' ? 'target="_blank" rel="noopener"' : ''} style="width:44px;height:44px;border-radius:12px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.2rem;transition:var(--transition)" class="hover-scale"><i class="fab fa-instagram"></i></a>
              <a id="contacto-youtube" href="${youtube}" ${youtube !== '#' ? 'target="_blank" rel="noopener"' : ''} style="width:44px;height:44px;border-radius:12px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.2rem;transition:var(--transition)" class="hover-scale"><i class="fab fa-youtube"></i></a>
              <a id="contacto-whatsapp" href="https://wa.me/${whatsapp}" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:12px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.2rem;transition:var(--transition)" class="hover-scale"><i class="fab fa-whatsapp"></i></a>
            </div>
          </div>

          <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);height:240px;background:var(--gray-100)">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3900.6!2d-86.2518!3d12.1149!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8f7155c4aef1b5b7%3A0x5f5c5c5c5c5c5c5c!2sManagua!5e0!3m2!1ses!2sni!4v1" width="100%" height="100%" style="border:0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación Distelecom"></iframe>
          </div>
        </div>

        <div>
          <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200)">
            <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:20px"><i class="fas fa-paper-plane" style="color:var(--secondary)"></i> Envíanos un Mensaje</h3>
            <form id="contact-form">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:0">
                <input type="text" id="ct-nombre" placeholder="Nombre completo" value="${saved.nombre || ''}" style="width:100%;padding:14px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;transition:var(--transition);font-family:inherit;margin-bottom:16px" required>
                <input type="text" id="ct-empresa" placeholder="Empresa" value="${saved.empresa || ''}" style="width:100%;padding:14px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;transition:var(--transition);font-family:inherit;margin-bottom:16px">
              </div>
              <input type="email" id="ct-correo" placeholder="Correo electrónico" value="${saved.correo || ''}" style="width:100%;padding:14px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;transition:var(--transition);font-family:inherit;margin-bottom:16px" required>
              <input type="tel" id="ct-telefono" placeholder="Teléfono" value="${saved.telefono || ''}" style="width:100%;padding:14px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;transition:var(--transition);font-family:inherit;margin-bottom:16px" required>
              <textarea id="ct-mensaje" rows="4" placeholder="Escribe tu mensaje..." style="width:100%;padding:14px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;transition:var(--transition);font-family:inherit;margin-bottom:16px;resize:vertical" required>${saved.mensaje || ''}</textarea>
              <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                <i class="fas fa-paper-plane"></i> Enviar Mensaje
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  `;

  function saveForm() {
    const data = {
      nombre: document.getElementById('ct-nombre')?.value || '',
      empresa: document.getElementById('ct-empresa')?.value || '',
      correo: document.getElementById('ct-correo')?.value || '',
      telefono: document.getElementById('ct-telefono')?.value || '',
      mensaje: document.getElementById('ct-mensaje')?.value || ''
    };
    localStorage.setItem('distelecom_contact_form', JSON.stringify(data));
  }

  ['ct-nombre', 'ct-empresa', 'ct-correo', 'ct-telefono', 'ct-mensaje'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', saveForm);
  });

  document.getElementById('contact-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const nombre = document.getElementById('ct-nombre').value.trim();
    const correo = document.getElementById('ct-correo').value.trim();
    const telefono = document.getElementById('ct-telefono').value.trim();
    const mensaje = document.getElementById('ct-mensaje').value.trim();

    if (!nombre || !correo || !telefono || !mensaje) {
      Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Completa todos los campos obligatorios.', confirmButtonColor: '#0077b6' });
      return;
    }

    const msgs = JSON.parse(localStorage.getItem('distelecom_mensajes') || '[]');
    msgs.push({ nombre, correo, telefono, empresa: document.getElementById('ct-empresa').value.trim(), mensaje, fecha: new Date().toISOString() });
    localStorage.setItem('distelecom_mensajes', JSON.stringify(msgs));
    localStorage.removeItem('distelecom_contact_form');

    Swal.fire({
      icon: 'success',
      title: 'Mensaje Enviado',
      text: 'Gracias por contactarnos. Te responderemos a la brevedad posible.',
      confirmButtonColor: '#0077b6'
    });
    e.target.reset();
  });
}
