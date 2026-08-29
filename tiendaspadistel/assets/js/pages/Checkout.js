import { store } from '../store.js';
import { router } from '../router.js';
import { fetchCliente, fetchClientes, createCliente, fetchDirecciones, createDireccion, createPedido, createPago, updatePedidoEstado, loginCliente } from '../services/apiService.js';

export async function renderCheckout() {
  const main = document.getElementById('main-content');
  const items = store.state.cart;
  const IVA_RATE = 0.15;

  if (!items.length) {
    main.innerHTML = `
      <div class="container page-content" style="text-align:center">
        <i class="fas fa-shopping-cart" style="font-size:3rem;color:var(--gray-400);margin-bottom:16px"></i>
        <h1>Carrito Vac\u00edo</h1>
        <p style="color:var(--gray-500);margin-bottom:24px">Agrega productos al carrito antes de continuar.</p>
        <button class="btn btn-navy" id="back-to-shop"><i class="fas fa-box"></i> Ver Productos</button>
      </div>
    `;
    document.getElementById('back-to-shop').addEventListener('click', () => router.navigate('/productos'));
    return;
  }

  let clienteAutenticado = null;
  let direccionesGuardadas = [];
  const storedId = parseInt(localStorage.getItem('distelecom_cliente_id')) || 0;

  if (storedId) {
    try {
      const resp = await fetchCliente(storedId);
      if (resp.success && resp.data) {
        clienteAutenticado = resp.data;
        const dirResp = await fetchDirecciones(storedId);
        if (dirResp.success) {
          direccionesGuardadas = dirResp.data?.data || [];
        }
      }
    } catch {
      localStorage.removeItem('distelecom_cliente_id');
    }
  }

  const enrichedItems = items.map(item => {
    const p = store.state.productos.find(pr => pr.id === item.id);
    if (p) return { ...p, cantidad: item.cantidad };
    return item;
  });

  const subtotal = enrichedItems.reduce((s, item) => s + item.precio * item.cantidad, 0);
  const iva = subtotal * IVA_RATE;
  const envio = 0;
  const total = subtotal + iva + envio;
  const formatPrice = (p) => `$${p.toFixed(2)}`;

  const saved = JSON.parse(localStorage.getItem('distelecom_checkout_form') || '{}');

  if (clienteAutenticado) {
    if (!saved.nombre) saved.nombre = clienteAutenticado.nombre || '';
    if (!saved.apellido) saved.apellido = clienteAutenticado.apellido || '';
    if (!saved.correo) saved.correo = clienteAutenticado.correo || '';
    if (!saved.telefono) saved.telefono = clienteAutenticado.telefono || '';
    if (!saved.empresa) saved.empresa = clienteAutenticado.empresa || '';
    if (!saved.documento) saved.documento = clienteAutenticado.documento || '';
  }

  const dirPrincipal = direccionesGuardadas.find(d => d.principal) || direccionesGuardadas[0];

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Checkout</h1>
      <p class="page-subtitle">Completa tus datos para finalizar la compra</p>

      ${!clienteAutenticado ? `
        <div class="checkout-section" id="login-section">
          <h3><i class="fas fa-sign-in-alt"></i> \u00bfYa tienes una cuenta?</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Correo Electr\u00f3nico</label>
              <input type="email" id="login-correo" placeholder="tu@correo.com">
            </div>
            <div class="form-group">
              <label>Contrase\u00f1a</label>
              <input type="password" id="login-password" placeholder="Tu contrase\u00f1a">
            </div>
          </div>
          <button class="btn btn-navy" id="login-btn" style="margin-top:4px"><i class="fas fa-sign-in-alt"></i> Iniciar Sesi\u00f3n</button>
          <div id="login-error" style="display:none;margin-top:8px;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius-sm);color:#dc2626;font-size:0.85rem"></div>
        </div>
      ` : `
        <div class="checkout-section">
          <h3><i class="fas fa-user-check"></i> Bienvenido, ${clienteAutenticado.nombre || ''}</h3>
          <p style="color:var(--gray-500);font-size:0.9rem">${clienteAutenticado.correo || ''}</p>
          <button class="btn btn-sm" id="logout-btn" style="margin-top:8px;background:var(--gray-200);color:var(--gray-700)"><i class="fas fa-sign-out-alt"></i> Cerrar Sesi\u00f3n</button>
        </div>
      `}

      <div class="checkout-grid">
        <div>
          <div class="checkout-section" id="checkout-client-section">
            <h3><i class="fas fa-user"></i> Informaci\u00f3n Personal</h3>
            <div class="form-row">
              <div class="form-group">
                <label>Nombre <span style="color:#ef4444">*</span></label>
                <input type="text" id="chk-nombre" placeholder="Tu nombre" value="${saved.nombre || ''}" required>
              </div>
              <div class="form-group">
                <label>Apellido</label>
                <input type="text" id="chk-apellido" placeholder="Tu apellido" value="${saved.apellido || ''}">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Empresa</label>
                <input type="text" id="chk-empresa" placeholder="Nombre de tu empresa" value="${saved.empresa || ''}">
              </div>
              <div class="form-group">
                <label>Documento (C\u00e9dula/RUC)</label>
                <input type="text" id="chk-documento" placeholder="123-456789-0" value="${saved.documento || ''}">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Correo Electr\u00f3nico <span style="color:#ef4444">*</span></label>
                <input type="email" id="chk-correo" placeholder="correo@ejemplo.com" value="${saved.correo || ''}" required>
              </div>
              <div class="form-group">
                <label>Tel\u00e9fono <span style="color:#ef4444">*</span></label>
                <input type="tel" id="chk-telefono" placeholder="+505 8888-8888" value="${saved.telefono || ''}" required>
              </div>
            </div>
            ${!clienteAutenticado ? `
              <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                  <input type="checkbox" id="chk-crear-cuenta"> <span>Crear una cuenta con contrase\u00f1a</span>
                </label>
              </div>
              <div class="form-group" id="password-field-wrap" style="display:none">
                <label>Contrase\u00f1a</label>
                <input type="password" id="chk-password" placeholder="M\u00ednimo 6 caracteres">
              </div>
            ` : ''}
          </div>

          <div class="checkout-section" id="checkout-address-section">
            <h3><i class="fas fa-map-marker-alt"></i> Direcci\u00f3n de Env\u00edo</h3>
            ${direccionesGuardadas.length > 0 ? `
              <div style="margin-bottom:16px" id="saved-addresses-wrap">
                <label style="font-size:0.9rem;font-weight:500;color:var(--gray-700);margin-bottom:8px;display:block">Seleccionar direcci\u00f3n guardada</label>
                ${direccionesGuardadas.map(d => `
                  <label style="display:flex;align-items:flex-start;gap:8px;padding:10px 12px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);margin-bottom:6px;cursor:pointer;${d.principal ? 'background:var(--gray-50)' : ''}">
                    <input type="radio" name="direccion-select" value="${d.id}" ${d.principal ? 'checked' : ''} style="margin-top:3px" class="dir-radio">
                    <div>
                      <span style="font-size:0.85rem">${d.direccion || ''}${d.ciudad ? ', ' + d.ciudad : ''}${d.departamento ? ', ' + d.departamento : ''}${d.pais ? ', ' + d.pais : ''}${d.referencia ? '<br><span style="color:var(--gray-400);font-size:0.8rem">Ref: ' + d.referencia + '</span>' : ''}</span>
                    </div>
                  </label>
                `).join('')}
                <button class="btn btn-sm" id="show-new-address-btn" style="margin-top:4px;background:transparent;color:var(--accent);border:1px dashed var(--accent)"><i class="fas fa-plus"></i> Nueva direcci\u00f3n</button>
              </div>
            ` : ''}
            <div id="address-form-wrap" style="${direccionesGuardadas.length > 0 ? 'display:none' : ''}">
              <div class="form-row">
                <div class="form-group">
                  <label>Pa\u00eds</label>
                  <input type="text" id="chk-pais" placeholder="Nicaragua" value="${saved.pais || 'Nicaragua'}">
                </div>
                <div class="form-group">
                  <label>Departamento</label>
                  <input type="text" id="chk-departamento" placeholder="Managua" value="${saved.departamento || ''}">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Ciudad <span style="color:#ef4444">*</span></label>
                  <input type="text" id="chk-ciudad" placeholder="Managua" value="${saved.ciudad || ''}" required>
                </div>
                <div class="form-group">
                  <label>Direcci\u00f3n <span style="color:#ef4444">*</span></label>
                  <input type="text" id="chk-direccion" placeholder="Direcci\u00f3n completa" value="${saved.direccion || ''}" required>
                </div>
              </div>
              <div class="form-group">
                <label>Referencia</label>
                <textarea id="chk-referencia" rows="2" placeholder="Casa Azul, contiguo a..." style="width:100%;padding:12px 14px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;font-family:inherit;resize:vertical;transition:var(--transition)">${saved.referencia || ''}</textarea>
              </div>
            </div>
          </div>

          <div class="checkout-section">
            <h3><i class="fas fa-credit-card"></i> M\u00e9todo de Pago</h3>
            <div class="form-group">
              <label>Selecciona el m\u00e9todo <span style="color:#ef4444">*</span></label>
              <select id="chk-metodo-pago">
                <option value="">-- Seleccionar --</option>
                <option value="Transferencia">Transferencia Bancaria</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta de Cr\u00e9dito/D\u00e9bito</option>
                <option value="PayPal">PayPal</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="form-group" id="chk-referencia-pago-wrap" style="display:none">
              <label>Referencia de Transferencia <span style="color:#ef4444">*</span></label>
              <input type="text" id="chk-referencia-pago" placeholder="N\u00famero de transferencia">
            </div>
          </div>

          <div class="checkout-section">
            <h3><i class="fas fa-comment"></i> Observaciones</h3>
            <div class="form-group">
              <textarea id="chk-comentarios" rows="3" placeholder="Notas adicionales para tu pedido..." style="width:100%;padding:12px 14px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-size:0.9rem;outline:none;font-family:inherit;resize:vertical;transition:var(--transition)">${saved.comentarios || ''}</textarea>
            </div>
          </div>

          <button class="btn btn-primary" style="width:100%;justify-content:center" id="place-order-btn">
            <i class="fas fa-check-circle"></i> Realizar Pedido
          </button>
          <div id="checkout-error" style="display:none;margin-top:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius-sm);color:#dc2626;font-size:0.9rem"></div>
        </div>

        <div>
          <div class="checkout-section" style="position:sticky;top:100px">
            <h3><i class="fas fa-shopping-bag"></i> Resumen del Pedido</h3>
            <div id="checkout-items">
              ${enrichedItems.map(item => `
                <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--gray-100)">
                  <img src="${item.imagen || 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png'}" alt="${item.nombre}" style="width:56px;height:56px;object-fit:contain;border-radius:var(--radius-sm);background:var(--gray-50)" loading="lazy" onerror="this.src='https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png'">
                  <div style="flex:1">
                    <p style="font-size:0.85rem;font-weight:500">${item.nombre}</p>
                    <p style="font-size:0.8rem;color:var(--gray-400)">Qty: ${item.cantidad} x ${formatPrice(item.precio)}</p>
                  </div>
                  <p style="font-weight:600;font-size:0.85rem">${formatPrice(item.precio * item.cantidad)}</p>
                </div>
              `).join('')}
            </div>
            <div style="padding:16px 0">
              <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:0.9rem;color:var(--gray-500)"><span>Subtotal</span><span>${formatPrice(subtotal)}</span></div>
              <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:0.9rem;color:var(--gray-500)"><span>IVA (15%)</span><span>${formatPrice(iva)}</span></div>
              <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:1.2rem;font-weight:700;color:var(--primary);border-top:2px solid var(--gray-200);margin-top:6px"><span>Total</span><span>${formatPrice(total)}</span></div>
            </div>
            <p style="font-size:0.8rem;color:var(--gray-400);text-align:center;margin-top:8px"><i class="fas fa-info-circle"></i> Un asesor de Distelecom te contactar\u00e1 para confirmar el pedido y coordinar la entrega.</p>
          </div>
        </div>
      </div>
    </div>
  `;

  function saveForm() {
    const ids = ['chk-nombre', 'chk-apellido', 'chk-empresa', 'chk-documento', 'chk-correo', 'chk-telefono', 'chk-pais', 'chk-departamento', 'chk-ciudad', 'chk-direccion', 'chk-referencia', 'chk-comentarios'];
    const data = {};
    ids.forEach(id => { data[id.replace('chk-', '')] = document.getElementById(id)?.value || ''; });
    localStorage.setItem('distelecom_checkout_form', JSON.stringify(data));
  }

  ['chk-nombre', 'chk-apellido', 'chk-empresa', 'chk-documento', 'chk-correo', 'chk-telefono', 'chk-pais', 'chk-departamento', 'chk-ciudad', 'chk-direccion', 'chk-referencia', 'chk-comentarios'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', saveForm);
  });

  const loginBtn = document.getElementById('login-btn');
  if (loginBtn) {
    loginBtn.addEventListener('click', async () => {
      const correo = document.getElementById('login-correo').value.trim();
      const password = document.getElementById('login-password').value;
      const errDiv = document.getElementById('login-error');
      errDiv.style.display = 'none';
      if (!correo || !password) {
        errDiv.textContent = 'Ingresa correo y contrase\u00f1a.';
        errDiv.style.display = 'block';
        return;
      }
      loginBtn.disabled = true;
      loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
      try {
        const resp = await loginCliente({ correo, password });
        if (resp.success && resp.data?.id) {
          localStorage.setItem('distelecom_cliente_id', resp.data.id.toString());
          renderCheckout();
        } else {
          errDiv.textContent = resp.message || 'Credenciales inv\u00e1lidas.';
          errDiv.style.display = 'block';
        }
      } catch {
        errDiv.textContent = 'Error de conexi\u00f3n. Intenta nuevamente.';
        errDiv.style.display = 'block';
      } finally {
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesi\u00f3n';
      }
    });
  }

  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      localStorage.removeItem('distelecom_cliente_id');
      renderCheckout();
    });
  }

  const crearCuentaChk = document.getElementById('chk-crear-cuenta');
  if (crearCuentaChk) {
    crearCuentaChk.addEventListener('change', () => {
      const wrap = document.getElementById('password-field-wrap');
      if (wrap) wrap.style.display = crearCuentaChk.checked ? 'block' : 'none';
    });
  }

  if (direccionesGuardadas.length > 0) {
    document.querySelectorAll('.dir-radio').forEach(r => {
      r.addEventListener('change', () => {
        document.getElementById('address-form-wrap').style.display = 'none';
      });
    });
    const newAddrBtn = document.getElementById('show-new-address-btn');
    if (newAddrBtn) {
      newAddrBtn.addEventListener('click', () => {
        document.getElementById('address-form-wrap').style.display = 'block';
        document.querySelectorAll('.dir-radio').forEach(r => { r.checked = false; });
      });
    }
  }

  const metodoPago = document.getElementById('chk-metodo-pago');
  if (metodoPago) {
    metodoPago.addEventListener('change', () => {
      const wrap = document.getElementById('chk-referencia-pago-wrap');
      if (wrap) wrap.style.display = metodoPago.value === 'Transferencia' ? 'block' : 'none';
    });
  }

  document.getElementById('place-order-btn').addEventListener('click', placeOrder);

  async function placeOrder() {
    const btn = document.getElementById('place-order-btn');
    const errorDiv = document.getElementById('checkout-error');
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';

    const nombre = document.getElementById('chk-nombre').value.trim();
    const apellido = document.getElementById('chk-apellido')?.value.trim() || '';
    const empresa = document.getElementById('chk-empresa')?.value.trim() || '';
    const documento = document.getElementById('chk-documento')?.value.trim() || '';
    const correo = document.getElementById('chk-correo').value.trim();
    const telefono = document.getElementById('chk-telefono').value.trim();

    const metodo = document.getElementById('chk-metodo-pago').value;
    const refPago = document.getElementById('chk-referencia-pago')?.value.trim() || '';

    const errores = [];
    if (!nombre) errores.push('Nombre');
    if (!correo) errores.push('Correo electr\u00f3nico');
    else if (!correo.includes('@') || !correo.includes('.')) errores.push('Correo electr\u00f3nico (formato inv\u00e1lido)');
    if (!telefono) errores.push('Tel\u00e9fono');
    else if (telefono.length < 8) errores.push('Tel\u00e9fono (muy corto)');
    if (!metodo) errores.push('M\u00e9todo de pago');
    if (metodo === 'Transferencia' && !refPago) errores.push('Referencia de transferencia');

    const dirRadio = document.querySelector('.dir-radio:checked');
    const isNewAddress = !dirRadio || document.getElementById('address-form-wrap').style.display !== 'none';

    if (isNewAddress) {
      const direccion = document.getElementById('chk-direccion').value.trim();
      const ciudad = document.getElementById('chk-ciudad').value.trim();
      if (!direccion) errores.push('Direcci\u00f3n de env\u00edo');
      if (!ciudad) errores.push('Ciudad');
    }

    if (errores.length) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: `<p style="margin-bottom:12px">Por favor completa los siguientes campos:</p><div style="text-align:left;display:inline-block">${errores.map(e => `<div style="padding:4px 0"><i class="fas fa-times" style="color:#ef4444;margin-right:8px;font-size:0.8rem"></i>${e}</div>`).join('')}</div>`,
        confirmButtonColor: '#0077b6',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    try {
      let cliente_id = storedId || 0;

      if (!cliente_id) {
        const searchResp = await fetchClientes({ correo, limite: 1 });
        if (searchResp.success && searchResp.data?.data?.length) {
          cliente_id = parseInt(searchResp.data.data[0].id);
        } else {
          const passwordEl = document.getElementById('chk-password');
          const crearCuenta = document.getElementById('chk-crear-cuenta')?.checked || false;
          const clienteData = { nombre, correo, telefono, estado: 'Activo' };
          if (apellido) clienteData.apellido = apellido;
          if (empresa) clienteData.empresa = empresa;
          if (documento) clienteData.documento = documento;
          if (crearCuenta && passwordEl?.value) clienteData.password = passwordEl.value;
          const createResp = await createCliente(clienteData);
          if (!createResp.success) throw new Error(createResp.message || 'Error al crear cliente');
          cliente_id = createResp.data?.id;
          if (!cliente_id) throw new Error('No se pudo crear el cliente');
        }
        localStorage.setItem('distelecom_cliente_id', cliente_id.toString());
      }

      let direccion_id = 0;

      if (dirRadio && !isNewAddress) {
        direccion_id = parseInt(dirRadio.value);
      } else {
        const ciudad = document.getElementById('chk-ciudad').value.trim();
        const direccion = document.getElementById('chk-direccion').value.trim();
        const pais = document.getElementById('chk-pais')?.value.trim() || 'Nicaragua';
        const departamento = document.getElementById('chk-departamento')?.value.trim() || '';
        const referencia = document.getElementById('chk-referencia')?.value.trim() || '';
        const dirResp = await createDireccion(cliente_id, { pais, departamento, ciudad, direccion, referencia, principal: direccionesGuardadas.length === 0 ? 1 : 0 });
        if (!dirResp.success) throw new Error(dirResp.message || 'Error al guardar direcci\u00f3n');
        direccion_id = dirResp.data?.id;
        if (!direccion_id) throw new Error('No se pudo crear la direcci\u00f3n');
      }

      const detalles = enrichedItems.map(item => ({
        producto_id: item.id,
        nombre_producto: item.nombre || 'Producto',
        cantidad: Math.max(1, parseInt(item.cantidad) || 1),
        precio_unitario: parseFloat(item.precio) || 0
      }));

      const observaciones = document.getElementById('chk-comentarios')?.value.trim() || '';

      const pedidoData = {
        cliente_id,
        direccion_id,
        subtotal: parseFloat(subtotal.toFixed(2)),
        impuestos: parseFloat(iva.toFixed(2)),
        total: parseFloat(total.toFixed(2)),
        estado_id: 1,
        observaciones,
        detalles,
        pagos: [{
          metodo,
          monto: parseFloat(total.toFixed(2)),
          referencia: refPago || null,
          estado: 'Pendiente',
          fecha_pago: new Date().toISOString().split('T')[0]
        }]
      };

      const pedidoResp = await createPedido(pedidoData);
      if (!pedidoResp.success) throw new Error(pedidoResp.message || 'Error al crear pedido');
      const pedido = pedidoResp.data;

      localStorage.removeItem('distelecom_checkout_form');
      store.clearCart();

      Swal.fire({
        icon: 'success',
        title: '\u00a1Pedido Realizado!',
        html: `<p>Gracias por tu compra, ${nombre}.</p><p style="font-size:0.85rem;color:var(--gray-500);margin-top:8px">N\u00famero de orden: <strong>${pedido.numero}</strong></p><p style="font-size:0.9rem;margin-top:12px">Un asesor de Distelecom se comunicar\u00e1 contigo a la brevedad para confirmar los detalles.</p>`,
        confirmButtonColor: '#0077b6',
        confirmButtonText: 'OK'
      }).then(() => router.navigate('/'));
    } catch (e) {
      errorDiv.style.display = 'block';
      errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${e.message || 'Error al procesar el pedido. Intenta nuevamente.'}`;
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-check-circle"></i> Realizar Pedido';
    }
  }
}
