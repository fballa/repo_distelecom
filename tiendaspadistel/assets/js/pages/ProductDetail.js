import { store } from '../store.js';
import { router } from '../router.js';
import { renderProductCard, CATEGORY_IMAGES } from '../components/ProductCard.js';
import { fetchProducto, fetchProductos, fetchReviewsPromedio, fetchReviews, createReview } from '../services/apiService.js';

const LABEL_MAP = {
  resolucion: 'Resoluci\u00f3n',
  tecnologia: 'Tecnolog\u00eda',
  vision_nocturna: 'Visi\u00f3n Nocturna',
  alcance_ir: 'Alcance IR',
  audio: 'Audio',
  proteccion_ip: 'Protecci\u00f3n IP',
  proteccion_ik: 'Protecci\u00f3n IK',
  material: 'Material',
  wifi: 'WiFi',
  poe: 'PoE',
  puertos: 'Puertos',
  color: 'Color',
  capacidad: 'Capacidad',
  canales: 'Canales',
  velocidad: 'Velocidad',
  alimentacion: 'Alimentaci\u00f3n',
  angulo: '\u00c1ngulo de Visi\u00f3n',
  categoria_cable: 'Categor\u00eda de Cable',
  compatibilidad: 'Compatibilidad',
  otros: 'Otros'
};

function formatDate(fecha) {
  if (!fecha) return '';
  const d = new Date(fecha);
  const dd = String(d.getDate()).padStart(2, '0');
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

function starsHtml(n) {
  return '<i class="fas fa-star" style="color:#f0ad4e"></i>'.repeat(n);
}

export async function renderProductDetail(params) {
  const main = document.getElementById('main-content');
  let producto = store.getProductBySlug(params.slug);
  let productoId = producto ? producto.id : null;

  main.innerHTML = '<div class="container page-content" style="text-align:center;padding:80px"><div class="loader-spinner" style="margin:0 auto"></div></div>';

  if (!productoId) {
    const idFromSlug = parseInt(params.slug);
    if (!isNaN(idFromSlug) && idFromSlug > 0) productoId = idFromSlug;
  }

  if (!productoId) {
    main.innerHTML = `
      <div class="container page-content" style="text-align:center">
        <i class="fas fa-exclamation-circle" style="font-size:3rem;color:var(--gray-400);margin-bottom:16px"></i>
        <h1>Producto no encontrado</h1>
        <p style="color:var(--gray-500);margin-bottom:24px">El producto que buscas no existe o ha sido eliminado.</p>
        <button class="btn btn-primary" id="back-shop"><i class="fas fa-arrow-left"></i> Volver a Productos</button>
      </div>
    `;
    document.getElementById('back-shop')?.addEventListener('click', () => router.navigate('/productos'));
    return;
  }

  try {
    const resp = await fetchProducto(productoId);
    if (!resp || !resp.success || !resp.data) throw new Error('Producto no encontrado');
    const d = resp.data;
    const categoriaId = parseInt(d.categoria_id) || 0;
    const specs = buildSpecs(d.especificaciones || {});
    const precio = parseFloat(d.precio) || 0;
    const precioOferta = d.precio_oferta ? parseFloat(d.precio_oferta) : null;
    const precioActual = (precioOferta !== null && precioOferta < precio) ? precioOferta : precio;
    const precioAnterior = (precioOferta !== null && precioOferta < precio) ? precio : null;
    const stock = parseInt(d.stock) || 0;
    const isNew = d.nuevo === '1' || d.nuevo === 1 || d.nuevo === true;
    const isOffer = d.oferta === '1' || d.oferta === 1 || d.oferta === true;
    const isFeatured = d.destacado === '1' || d.destacado === 1 || d.destacado === true;
    const img = d.imagen_principal || '';
    const thumbs = Array.isArray(d.imagenes) ? d.imagenes.filter(i => i.imagen).sort((a, b) => (a.orden || 0) - (b.orden || 0)) : [];
    const formatPrice = (p) => `$${p.toFixed(2)}`;

    let stockText, stockClass;
    if (stock === 0) { stockText = 'Agotado'; stockClass = 'out-stock'; }
    else if (stock < 5) { stockText = '\u00a1\u00daltimas unidades!'; stockClass = 'low-stock'; }
    else { stockText = 'Disponible'; stockClass = 'in-stock'; }

    const categorias = Array.isArray(store.getCategorias()) ? store.getCategorias() : [];
    const catSlug = categorias.find(c => c.nombre === d.categoria)?.slug || (d.categoria || '').toLowerCase().replace(/\s+/g, '-');

    main.innerHTML = `
      <div class="container product-detail">
        <nav style="display:flex;gap:8px;font-size:0.85rem;color:var(--gray-400);margin-bottom:32px">
          <a style="color:var(--secondary);cursor:pointer" id="breadcrumb-home">Inicio</a>
          <span>/</span>
          <a style="color:var(--secondary);cursor:pointer" id="breadcrumb-cat" data-cat="${catSlug || ''}">${d.categoria || ''}</a>
          <span>/</span>
          <span style="color:var(--gray-600)">${d.nombre || ''}</span>
        </nav>
        <div class="product-detail-grid">
          <div class="product-detail-images">
            <div class="product-detail-main-image">
              <img src="${img}" alt="${d.nombre || ''}" id="main-image" onerror="this.src='${CATEGORY_IMAGES[d.categoria] || 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png'}'">
            </div>
            ${thumbs.length > 1 ? `
            <div class="product-detail-thumbs">
              ${thumbs.map((t, i) => `
                <div class="product-detail-thumb ${i === 0 ? 'active' : ''}" data-img="${t.imagen}">
                  <img src="${t.imagen}" alt="" loading="lazy">
                </div>
              `).join('')}
            </div>` : ''}
            <div style="margin-top:24px">
              <button class="btn btn-outline" style="width:100%;justify-content:center" id="quote-detail-btn">
                <i class="fas fa-file-invoice"></i> Solicitar Cotizaci\u00f3n
              </button>
            </div>
          </div>
          <div class="product-detail-info">
            <div class="product-badges" style="margin-bottom:12px">
              ${isNew ? '<span class="badge badge-new">Nuevo</span>' : ''}
              ${isOffer ? '<span class="badge badge-offer">Oferta</span>' : ''}
              ${isFeatured ? '<span class="badge badge-destacado" style="background:var(--warning,#f0ad4e);color:var(--white);padding:4px 10px;border-radius:4px;font-size:0.75rem;font-weight:600;text-transform:uppercase">Destacado</span>' : ''}
            </div>
            <p style="font-size:0.8rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px">${d.marca || ''}</p>
            <h1 style="font-size:1.8rem;color:var(--primary);margin-bottom:8px">${d.nombre || ''}</h1>
            <p style="font-size:0.85rem;color:var(--gray-400);margin-bottom:16px">SKU: ${d.sku || ''} ${d.modelo ? '| Modelo: ' + d.modelo : ''}</p>
            <div class="product-detail-price">
              ${formatPrice(precioActual)}
              ${precioAnterior !== null ? `<span class="product-detail-price-old" style="text-decoration:line-through;color:var(--gray-400);font-size:1.1rem;margin-left:8px">${formatPrice(precioAnterior)}</span>` : ''}
            </div>
            <div class="product-detail-stock ${stockClass}" style="margin:16px 0">
              <i class="fas ${stock > 0 ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${stockText}
            </div>
            <p style="font-size:0.95rem;line-height:1.6;color:var(--gray-600);margin-bottom:20px">${d.descripcion_corta || ''}</p>
            <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
              <div style="display:flex;align-items:center;gap:8px">
                <button style="width:36px;height:36px;border:1px solid var(--gray-200);background:var(--white);border-radius:4px;cursor:pointer;font-size:1.1rem" id="qty-minus">-</button>
                <span style="font-size:1.1rem;font-weight:600;min-width:32px;text-align:center" id="detail-qty-display">1</span>
                <button style="width:36px;height:36px;border:1px solid var(--gray-200);background:var(--white);border-radius:4px;cursor:pointer;font-size:1.1rem" id="qty-plus">+</button>
              </div>
              <button class="btn btn-primary" id="add-to-cart-detail" ${stock === 0 ? 'disabled' : ''}>
                <i class="fas fa-cart-plus"></i> Agregar al Carrito
              </button>
              <button class="btn btn-outline" id="buy-now-btn" ${stock === 0 ? 'disabled' : ''}>
                <i class="fas fa-bolt"></i> Comprar Ahora
              </button>
            </div>
            <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:0.85rem;color:var(--gray-500);margin-bottom:24px;padding:16px 0;border-top:1px solid var(--gray-100);border-bottom:1px solid var(--gray-100)">
              <span><i class="fas fa-shield-alt" style="color:var(--secondary);margin-right:6px"></i> Garant\u00eda: ${d.garantia || 'No especificada'}</span>
              <span><i class="fas fa-truck" style="color:var(--secondary);margin-right:6px"></i> Env\u00edo a todo Nicaragua</span>
              <span><i class="fas fa-headset" style="color:var(--secondary);margin-right:6px"></i> Soporte t\u00e9cnico</span>
            </div>
            <div style="margin-bottom:24px">
              <h3 style="font-size:1rem;color:var(--primary);margin-bottom:8px">Descripci\u00f3n</h3>
              <p style="font-size:0.9rem;line-height:1.7;color:var(--gray-600)">${d.descripcion_larga || d.descripcion || ''}</p>
            </div>
            ${specs.length ? `
            <div class="product-specs">
              <h3>Especificaciones T\u00e9cnicas</h3>
              <table class="specs-table">
                ${specs.map(s => `<tr><td>${s.label}</td><td>${s.value}</td></tr>`).join('')}
              </table>
            </div>` : ''}
          </div>
        </div>
      </div>

      <section class="reviews-section">
        <div class="container">
          <h2 class="section-title">Opiniones de los clientes</h2>
          <div id="reviews-promedio" style="text-align:center;padding:30px 0"><div class="loader-spinner" style="margin:0 auto"></div></div>
          <div id="reviews-lista" style="margin-bottom:32px"></div>
          <div class="review-form-wrap">
            <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:16px">Escribe tu opini\u00f3n</h3>
            <form id="review-form">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px" class="review-row">
                <input type="text" id="review-nombre" class="filter-search" placeholder="Tu nombre" required style="width:100%">
                <input type="email" id="review-correo" class="filter-search" placeholder="Tu correo (opcional)" style="width:100%">
              </div>
              <div style="display:flex;gap:16px;margin-bottom:16px;align-items:center;flex-wrap:wrap">
                <select id="review-calificacion" class="filter-select" required style="min-width:160px">
                  <option value="">Calificaci\u00f3n</option>
                  <option value="1">\u2605</option>
                  <option value="2">\u2605\u2605</option>
                  <option value="3">\u2605\u2605\u2605</option>
                  <option value="4">\u2605\u2605\u2605\u2605</option>
                  <option value="5">\u2605\u2605\u2605\u2605\u2605</option>
                </select>
              </div>
              <textarea id="review-comentario" class="filter-search" placeholder="Escribe tu opini\u00f3n..." required style="width:100%;min-height:100px;resize:vertical;margin-bottom:16px"></textarea>
              <button type="submit" class="btn btn-primary" style="justify-content:center">
                <i class="fas fa-paper-plane"></i> Enviar rese\u00f1a
              </button>
            </form>
          </div>
        </div>
      </section>

      <section class="related-section" style="background:var(--gray-50)">
        <div class="container">
          <h2 class="section-title">Productos relacionados</h2>
          <p class="section-subtitle" style="margin-bottom:32px">Otros productos en ${d.categoria || ''}</p>
          <div class="product-grid" id="related-products-grid">
            <div style="text-align:center;padding:40px"><div class="loader-spinner" style="margin:0 auto"></div></div>
          </div>
        </div>
      </section>
    `;

    bindProductEvents(d, stock, precioActual, precioAnterior, img, catSlug);

    // Fetch reviews + related in parallel
    Promise.all([
      categoriaId ? fetchRelatedProducts(categoriaId, parseInt(d.id)) : Promise.resolve([]),
      fetchReviewsPromedio(productoId),
      fetchReviews(productoId)
    ]).then(([relacionados, promData, revData]) => {
      renderRelated(relacionados);
      renderPromedio(promData);
      renderReviews(revData);
    }).catch(() => {
      document.getElementById('reviews-promedio') && (document.getElementById('reviews-promedio').innerHTML = '<p style="color:var(--gray-400)">No se pudieron cargar las rese\u00f1as.</p>');
      document.getElementById('reviews-lista') && (document.getElementById('reviews-lista').innerHTML = '');
      document.getElementById('related-products-grid') && (document.getElementById('related-products-grid').innerHTML = '<p style="color:var(--gray-400);text-align:center;padding:40px">No se pudieron cargar productos relacionados.</p>');
    });

    bindReviewForm(productoId);

    if (typeof AOS !== 'undefined') AOS.refresh();
  } catch {
    main.innerHTML = `
      <div class="container page-content" style="text-align:center">
        <i class="fas fa-exclamation-circle" style="font-size:3rem;color:var(--gray-400);margin-bottom:16px"></i>
        <h1>Error al cargar el producto</h1>
        <p style="color:var(--gray-500);margin-bottom:24px">No se pudo cargar la informaci\u00f3n del producto. Intenta nuevamente.</p>
        <button class="btn btn-primary" id="back-shop"><i class="fas fa-arrow-left"></i> Volver a Productos</button>
      </div>
    `;
    document.getElementById('back-shop')?.addEventListener('click', () => router.navigate('/productos'));
  }
}

// --- helpers ---

function buildSpecs(espec) {
  if (typeof espec !== 'object' || !espec) return [];
  return Object.entries(espec)
    .filter(([, v]) => v !== null && v !== '' && v !== undefined)
    .map(([k, v]) => ({
      label: LABEL_MAP[k] || k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
      value: v
    }));
}

function bindProductEvents(d, stock, precioActual, precioAnterior, img, catSlug) {
  const qtyDisplay = document.getElementById('detail-qty-display');
  document.getElementById('qty-plus')?.addEventListener('click', () => {
    let q = parseInt(qtyDisplay.textContent) || 1;
    if (q < (stock || 99)) qtyDisplay.textContent = q + 1;
  });
  document.getElementById('qty-minus')?.addEventListener('click', () => {
    let q = parseInt(qtyDisplay.textContent) || 1;
    if (q > 1) qtyDisplay.textContent = q - 1;
  });
  document.getElementById('breadcrumb-home')?.addEventListener('click', () => router.navigate('/'));
  document.getElementById('breadcrumb-cat')?.addEventListener('click', function () {
    if (this.dataset.cat) router.navigate('/categorias/' + this.dataset.cat);
  });
  document.querySelectorAll('.product-detail-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      document.querySelectorAll('.product-detail-thumb').forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      document.getElementById('main-image').src = thumb.dataset.img;
    });
  });
  const addToCart = (qty) => {
    const cartProduct = store.state.productos.find(p => p.id === parseInt(d.id));
    if (cartProduct) store.addToCart(cartProduct, qty);
    else store.addToCart({ id: parseInt(d.id), nombre: d.nombre, precio: precioActual, precioAnterior, imagen: img, slug: d.slug, marca: d.marca, stock }, qty);
  };
  document.getElementById('add-to-cart-detail')?.addEventListener('click', () => {
    const qty = parseInt(qtyDisplay.textContent) || 1;
    addToCart(qty);
    Swal.fire({ icon: 'success', title: 'Agregado al carrito', text: `${qty} x ${d.nombre}`, timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
  });
  document.getElementById('buy-now-btn')?.addEventListener('click', () => {
    const qty = parseInt(qtyDisplay.textContent) || 1;
    addToCart(qty);
    router.navigate('/checkout');
  });
  document.getElementById('quote-detail-btn')?.addEventListener('click', () => router.navigate('/contacto'));
}

async function fetchRelatedProducts(categoriaId, excludeId) {
  try {
    const resp = await fetchProductos({ categoria_id: categoriaId, limite: 4 });
    if (resp.success && Array.isArray(resp.data?.data)) {
      return resp.data.data.filter(p => parseInt(p.id) !== excludeId).slice(0, 3);
    }
  } catch {}
  return [];
}

function renderRelated(productos) {
  const grid = document.getElementById('related-products-grid');
  if (!grid) return;
  if (!productos || productos.length === 0) {
    grid.innerHTML = '<p style="color:var(--gray-400);text-align:center;padding:40px">No hay productos relacionados en esta categor\u00eda.</p>';
    return;
  }
  grid.innerHTML = '';
  productos.forEach(p => {
    const pNormalized = {
      id: parseInt(p.id),
      nombre: p.nombre || '',
      slug: p.slug || '',
      precio: (p.precio_oferta ? parseFloat(p.precio_oferta) : parseFloat(p.precio)) || 0,
      precioAnterior: p.precio_oferta ? parseFloat(p.precio) : null,
      imagen: p.imagen_principal || '',
      marca: p.marca || '',
      stock: parseInt(p.stock) || 0,
      categoria: p.categoria || '',
      descripcion: p.descripcion || ''
    };
    grid.appendChild(renderProductCard(pNormalized));
  });
}

function renderPromedio(data) {
  const el = document.getElementById('reviews-promedio');
  if (!el) return;
  if (!data || !data.success) {
    el.innerHTML = '<p style="color:var(--gray-400)">No se pudieron cargar las rese\u00f1as.</p>';
    return;
  }
  const { promedio, total_resenas } = data.data || {};
  if (!total_resenas || total_resenas === 0) {
    el.innerHTML = '<p style="font-size:1rem;color:var(--gray-500);padding:20px 0">Sin rese\u00f1as a\u00fan</p>';
    return;
  }
  const fullStars = Math.round(promedio);
  el.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;padding:20px 0;flex-wrap:wrap">
      <span style="font-size:1.6rem">${starsHtml(fullStars)}</span>
      <span style="font-size:1.4rem;font-weight:700;color:var(--primary)">${(promedio || 0).toFixed(1)}</span>
      <span style="font-size:0.95rem;color:var(--gray-500)">(${total_resenas} rese\u00f1a${total_resenas !== 1 ? 's' : ''})</span>
    </div>
  `;
}

function renderReviews(data) {
  const el = document.getElementById('reviews-lista');
  if (!el) return;
  let reviews = [];
  if (data && data.success && Array.isArray(data.data?.data)) {
    reviews = data.data.data;
  }
  if (!reviews.length) {
    el.innerHTML = '<p style="color:var(--gray-500);padding:20px 0;text-align:center">No hay rese\u00f1as a\u00fan. \u00a1S\u00e9 el primero en opinar!</p>';
    return;
  }
  el.innerHTML = reviews.map(r => `
    <div style="padding:20px 0;border-bottom:1px solid var(--gray-100)">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap">
        <strong style="font-size:0.95rem;color:var(--primary)">${r.nombre || ''}</strong>
        <span style="font-size:0.9rem">${starsHtml(r.calificacion || 0)}</span>
        <span style="font-size:0.8rem;color:var(--gray-400);margin-left:auto">${formatDate(r.created_at)}</span>
      </div>
      <p style="font-size:0.9rem;color:var(--gray-600);line-height:1.6;margin:0">${r.comentario || ''}</p>
    </div>
  `).join('');
}

function bindReviewForm(productoId) {
  const form = document.getElementById('review-form');
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre = document.getElementById('review-nombre').value.trim();
    const calificacion = document.getElementById('review-calificacion').value;
    const comentario = document.getElementById('review-comentario').value.trim();
    if (!nombre || !calificacion || !comentario) {
      Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Completa todos los campos obligatorios.', confirmButtonColor: '#0077b6' });
      return;
    }
    const correo = document.getElementById('review-correo').value.trim() || null;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    const body = { producto_id: productoId, nombre, calificacion: parseInt(calificacion), comentario, estado: 'Publicado' };
    if (correo) body.correo = correo;
    try {
      const resp = await createReview(body);
      if (resp.success) {
        Swal.fire({ icon: 'success', title: '\u00a1Gracias!', text: 'Tu rese\u00f1a fue publicada correctamente.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
        form.reset();
        recargarResenas(productoId);
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo enviar la rese\u00f1a.', confirmButtonColor: '#0077b6' });
      }
    } catch {
      Swal.fire({ icon: 'error', title: 'Error de conexi\u00f3n', text: 'Intenta nuevamente.', confirmButtonColor: '#0077b6' });
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar rese\u00f1a';
    }
  });
}

async function recargarResenas(productoId) {
  const listaEl = document.getElementById('reviews-lista');
  const promEl = document.getElementById('reviews-promedio');
  if (listaEl) listaEl.innerHTML = '<p style="text-align:center;color:var(--gray-400);padding:20px 0"><i class="fas fa-spinner fa-spin"></i> Cargando rese\u00f1as...</p>';
  if (promEl) promEl.innerHTML = '<p style="text-align:center;color:var(--gray-400);padding:10px 0"><i class="fas fa-spinner fa-spin"></i></p>';
  try {
    const [promData, revData] = await Promise.all([
      fetchReviewsPromedio(productoId),
      fetchReviews(productoId)
    ]);
    renderPromedio(promData);
    renderReviews(revData);
  } catch {
    if (listaEl) listaEl.innerHTML = '<p style="color:var(--gray-400);text-align:center;padding:20px 0">Error al cargar las rese\u00f1as.</p>';
  }
}
