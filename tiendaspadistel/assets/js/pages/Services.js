import { router } from '../router.js';

function iconCls(icono) {
  return icono ? (icono.includes(' ') ? icono : `fas ${icono}`) : 'fas fa-cog';
}

function formatearDescripcion(texto) {
  if (!texto) return '';
  return texto.replace(/\n/g, '<br>');
}

export function renderServices() {
  const main = document.getElementById('main-content');
  const servicios = window.servicios || [];

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Servicios</h1>
      <p class="page-subtitle">Soluciones integrales en tecnología y telecomunicaciones para tu empresa</p>
      <div id="services-container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px">
        ${servicios.length ? servicios.map(s => `
          <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);transition:var(--transition)" class="hover-lift">
            ${s.imagen ? `<img src="${s.imagen}" alt="${s.nombre}" style="width:100%;height:160px;object-fit:cover" loading="lazy">` : `<div style="height:100px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center"><i class="${iconCls(s.icono)}" style="font-size:3rem;color:var(--white)"></i></div>`}
            <div style="padding:24px">
              <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,180,216,0.1);display:flex;align-items:center;justify-content:center">
                  <i class="${iconCls(s.icono)}" style="color:var(--secondary);font-size:1rem"></i>
                </div>
                <h3 style="font-size:1.05rem;color:var(--primary)">${s.nombre}</h3>
              </div>
              <p style="font-size:0.88rem;color:var(--gray-500);line-height:1.6;margin-bottom:16px">${formatearDescripcion(s.descripcion)}</p>
              <button class="btn btn-blue service-info-btn" style="width:100%;justify-content:center;font-size:0.85rem">
                <i class="fas fa-info-circle"></i> Solicitar Información
              </button>
            </div>
          </div>
        `).join('') : '<div style="text-align:center;padding:60px;color:var(--gray-400)"><i class="fas fa-exclamation-circle" style="font-size:2rem;margin-bottom:12px;display:block"></i><p>No se pudieron cargar los servicios.</p></div>'}
      </div>
    </div>
  `;

  const container = document.getElementById('services-container');
  container.querySelectorAll('.service-info-btn').forEach(btn => {
    btn.addEventListener('click', () => router.navigate('/contacto'));
  });

  if (typeof AOS !== 'undefined') AOS.refresh();
}
