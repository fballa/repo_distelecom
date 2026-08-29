import { fetchPedidoPorNumero } from '../services/apiService.js';

export function renderOrderStatus() {
  const main = document.getElementById('main-content');

  const ESTADO_MAP = {
    'Pendiente': { pos: 1, icono: 'fa-check-circle' },
    'Pagado': { pos: 2, icono: 'fa-credit-card' },
    'En preparaci\u00f3n': { pos: 2, icono: 'fa-cog' },
    'Preparando': { pos: 2, icono: 'fa-cog' },
    'Enviado': { pos: 3, icono: 'fa-truck' },
    'Entregado': { pos: 4, icono: 'fa-home' },
    'Cancelado': { pos: 0, icono: 'fa-times-circle' }
  };

  const ESTADO_COLORS = {
    'Pendiente': '#F59E0B',
    'Pagado': '#10B981',
    'En preparaci\u00f3n': '#3B82F6',
    'Preparando': '#3B82F6',
    'Enviado': '#8B5CF6',
    'Entregado': '#22C55E',
    'Cancelado': '#EF4444'
  };

  const HISTORIAL_ESTADOS = {
    1: 'Pendiente',
    2: 'Pagado',
    3: 'En preparaci\u00f3n',
    4: 'Enviado',
    5: 'Entregado',
    6: 'Cancelado'
  };

  const steps = [
    { label: 'Recibido', icono: 'fa-check-circle', color: '#16a34a', pos: 1 },
    { label: 'En Proceso', icono: 'fa-cog', color: '#2563eb', pos: 2 },
    { label: 'Enviado', icono: 'fa-truck', color: '#f59e0b', pos: 3 },
    { label: 'Entregado', icono: 'fa-home', color: '#16a34a', pos: 4 }
  ];

  const PLACEHOLDER = 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png';

  function fmtFechaHora(f) {
    if (!f) return '';
    const d = new Date(f);
    if (isNaN(d)) return f;
    return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
  }

  function fmtFecha(f) {
    if (!f) return '';
    const d = new Date(f);
    if (isNaN(d)) return f;
    return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
  }

  function fmtPrecio(v) { return `$${(parseFloat(v) || 0).toFixed(2)}`; }

  function colorEstado(e) { return ESTADO_COLORS[e] || '#F59E0B'; }

  function posEstado(e) { return ESTADO_MAP[e]?.pos ?? 1; }

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Estado de Orden</h1>
      <p class="page-subtitle">Consulta el estado de tu pedido</p>
      <div style="max-width:640px;margin:0 auto">
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:32px">
          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
            <input type="text" id="order-search-input" placeholder="N\u00famero de orden (ej. DST-2026-000001)" style="flex:1;min-width:200px;padding:12px 16px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.95rem;outline:none;transition:var(--transition)">
            <button class="btn btn-primary" id="order-search-btn"><i class="fas fa-search"></i> Buscar</button>
          </div>
        </div>
        <div id="order-result"></div>
      </div>
    </div>
  `;

  document.getElementById('order-search-btn').addEventListener('click', buscarOrden);
  document.getElementById('order-search-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') buscarOrden();
  });

  async function buscarOrden() {
    const query = document.getElementById('order-search-input').value.trim();
    const resultDiv = document.getElementById('order-result');

    if (!query) {
      Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Ingresa un n\u00famero de orden.', confirmButtonColor: '#0077b6' });
      return;
    }

    resultDiv.innerHTML = '<div style="text-align:center;padding:40px"><div class="loader-spinner" style="margin:0 auto"></div><p style="margin-top:16px;color:var(--gray-500)">Buscando...</p></div>';

    try {
      const resp = await fetchPedidoPorNumero(query);
      if (resp.success && resp.data) {
        renderOrderFound(resultDiv, resp.data);
      } else {
        renderNotFound(resultDiv);
      }
    } catch {
      renderError(resultDiv);
    }
  }

  function renderNotFound(container) {
    container.innerHTML = `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:48px 32px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <i class="fas fa-search" style="font-size:1.5rem;color:var(--gray-400)"></i>
        </div>
        <h3 style="font-size:1.2rem;color:var(--primary);margin-bottom:8px">Pedido no encontrado</h3>
        <p style="color:var(--gray-500);font-size:0.9rem;margin-bottom:20px">Verifica el n\u00famero ingresado.</p>
        <div style="font-size:0.85rem;color:var(--gray-400)">
          <p>\u00bfNecesitas ayuda? Ll\u00e1manos al <strong>(505) 5888-3346</strong></p>
        </div>
      </div>`;
  }

  function renderError(container) {
    container.innerHTML = `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:48px 32px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <i class="fas fa-exclamation-triangle" style="font-size:1.5rem;color:#dc2626"></i>
        </div>
        <h3 style="font-size:1.2rem;color:var(--primary);margin-bottom:8px">Error de conexi\u00f3n</h3>
        <p style="color:var(--gray-500);font-size:0.9rem;margin-bottom:4px">Intenta nuevamente.</p>
      </div>`;
  }

  function renderOrderFound(container, pedido) {
    const currentPos = posEstado(pedido.estado);
    const estColor = colorEstado(pedido.estado);
    const detalles = Array.isArray(pedido.detalles) ? pedido.detalles : [];
    const pagos = Array.isArray(pedido.pagos) ? pedido.pagos : [];
    const historial = Array.isArray(pedido.historial) ? pedido.historial : [];

    container.innerHTML = `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:8px">
          <div>
            <h3 style="font-size:1.1rem;color:var(--primary)">${pedido.numero}</h3>
            <p style="font-size:0.85rem;color:var(--gray-400)">${fmtFechaHora(pedido.created_at)}</p>
          </div>
          <span style="padding:6px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;background:${estColor}15;color:${estColor}">
            <i class="fas ${ESTADO_MAP[pedido.estado]?.icono || 'fa-check-circle'}"></i> ${pedido.estado}
          </span>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center">
          ${steps.map(s => `
            <div style="flex:1;text-align:center;${currentPos === 0 ? 'opacity:0.4' : ''}">
              <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:0.85rem;${currentPos > 0 && s.pos <= currentPos ? `background:${s.color};color:white` : 'background:var(--gray-100);color:var(--gray-400)'}">
                <i class="fas ${s.icono}"></i>
              </div>
              <span style="font-size:0.7rem;color:${currentPos > 0 && s.pos <= currentPos ? s.color : 'var(--gray-400)'}">${s.label}</span>
            </div>
          `).join('')}
        </div>

        <div style="border-top:1px solid var(--gray-100);padding-top:12px">
          <p style="font-size:0.85rem;color:var(--gray-500);margin-bottom:8px"><strong>Productos</strong></p>
          ${detalles.map(i => `
            <div style="display:flex;gap:12px;padding:8px 0;border-bottom:1px solid var(--gray-50)">
              <img src="${i.imagen_principal || PLACEHOLDER}" alt="${i.producto_nombre || ''}" style="width:48px;height:48px;object-fit:contain;border-radius:var(--radius-sm);background:var(--gray-50);flex-shrink:0" loading="lazy" onerror="this.src='${PLACEHOLDER}'">
              <div style="flex:1;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:4px">
                <div>
                  <p style="font-size:0.85rem;font-weight:500">${i.producto_nombre || ''}</p>
                  <p style="font-size:0.8rem;color:var(--gray-400)">${i.cantidad || 0} x ${fmtPrecio(i.precio)}</p>
                </div>
                <span style="font-weight:600;font-size:0.85rem">${fmtPrecio(i.subtotal)}</span>
              </div>
            </div>
          `).join('')}
          <div style="padding:10px 0 4px">
            <div style="display:flex;justify-content:space-between;font-size:0.9rem;color:var(--gray-500);padding:3px 0"><span>Subtotal</span><span>${fmtPrecio(pedido.subtotal)}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.9rem;color:var(--gray-500);padding:3px 0"><span>Impuestos</span><span>${fmtPrecio(pedido.impuestos)}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:1.2rem;font-weight:700;color:var(--primary);border-top:2px solid var(--gray-200);padding-top:10px;margin-top:6px"><span>Total</span><span>${fmtPrecio(pedido.total)}</span></div>
          </div>
        </div>
      </div>

      ${pagos.length ? `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:16px">
        <p style="font-size:0.9rem;color:var(--gray-500);margin-bottom:12px"><strong>Pagos</strong></p>
        ${pagos.map(p => `
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:8px 0;border-bottom:1px solid var(--gray-50);font-size:0.85rem">
            <div><span style="font-weight:500">${p.metodo || ''}</span> <span style="color:var(--gray-400);font-size:0.8rem">${fmtFecha(p.fecha_pago)}</span></div>
            <div style="display:flex;align-items:center;gap:12px">
              <span style="font-weight:600">${fmtPrecio(p.monto)}</span>
              <span style="padding:2px 10px;border-radius:12px;font-size:0.75rem;font-weight:500;background:${p.estado === 'Completado' || p.estado === 'Aprobado' ? '#dcfce7' : '#fef3c7'};color:${p.estado === 'Completado' || p.estado === 'Aprobado' ? '#16a34a' : '#d97706'}">${p.estado || ''}</span>
            </div>
          </div>
        `).join('')}
      </div>` : ''}

      ${historial.length ? `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:16px">
        <p style="font-size:0.9rem;color:var(--gray-500);margin-bottom:12px"><strong>Historial de estados</strong></p>
        <div style="position:relative;padding-left:24px">
          <div style="position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:var(--gray-200)"></div>
          ${historial.map((h, idx) => {
            const hEst = HISTORIAL_ESTADOS[parseInt(h.estado_id)] || '';
            const hColor = colorEstado(hEst);
            return `
              <div style="position:relative;padding:0 0 20px 16px;${idx === historial.length - 1 ? 'padding-bottom:0' : ''}">
                <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;background:${hColor};border:2px solid var(--white)"></div>
                <p style="font-size:0.85rem;font-weight:500">${hEst || h.comentario || ''}</p>
                <p style="font-size:0.8rem;color:var(--gray-400)">${h.comentario || ''}${h.usuario ? ' — ' + h.usuario : ''}</p>
                <p style="font-size:0.75rem;color:var(--gray-400)">${fmtFechaHora(h.created_at)}</p>
              </div>
            `;
          }).join('')}
        </div>
      </div>` : ''}
    `;
  }
}
