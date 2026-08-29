import { router } from '../router.js';
import { store } from '../store.js';

const CATEGORY_IMAGES = {
  'CCTV': 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png',
  'Redes': 'https://misdemos.x10.mx/videos/distelecom/switches.png',
  'Control de Acceso': 'https://misdemos.x10.mx/videos/distelecom/terminales_biometricas.png',
  'Telefonía IP': 'https://misdemos.x10.mx/videos/distelecom/telefono_ip.png',
  'Fibra Óptica': 'https://misdemos.x10.mx/videos/distelecom/cables_utp.png',
  'POS': 'https://misdemos.x10.mx/videos/distelecom/punto_de_venta.png'
};

function getProductImage(producto) {
  if (producto.imagen && producto.imagen.startsWith('http')) return producto.imagen;
  return CATEGORY_IMAGES[producto.categoria] || 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png';
}

export function renderProductCard(producto) {
  const formatPrice = (p) => `$${p.toFixed(2)}`;
  const stockClass = producto.stock > 10 ? 'in-stock' : producto.stock > 0 ? 'low-stock' : 'out-stock';
  const stockText = producto.stock > 10 ? 'En Stock' : producto.stock > 0 ? 'Stock Bajo' : 'Agotado';
  const imgSrc = getProductImage(producto);

  const card = document.createElement('div');
  card.className = 'product-card';
  card.innerHTML = `
    <div class="product-card-image">
      <img src="${imgSrc}" alt="${producto.nombre}" loading="lazy" onerror="this.src='${CATEGORY_IMAGES[producto.categoria] || 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png'}'">
      <div class="product-badges">
        ${producto.nuevo ? '<span class="badge badge-new">Nuevo</span>' : ''}
        ${producto.oferta ? '<span class="badge badge-offer">Oferta</span>' : ''}
      </div>
    </div>
    <div class="product-card-body">
      <p class="product-marca">${producto.marca}</p>
      <h3>${producto.nombre}</h3>
      <p class="product-categoria">${producto.categoria}</p>
      <div class="product-card-footer">
        <div>
          <span class="product-price">${formatPrice(producto.precio)}</span>
          ${producto.precioAnterior ? `<span class="product-price-old">${formatPrice(producto.precioAnterior)}</span>` : ''}
        </div>
        <span class="product-stock ${stockClass}">${stockText}</span>
      </div>
    </div>
    <div class="product-card-actions">
      <button class="btn btn-gold view-detail-btn" data-slug="${producto.slug}">
        <i class="fas fa-eye"></i> Detalle
      </button>
      <button class="btn btn-primary add-cart-btn" data-id="${producto.id}">
        <i class="fas fa-cart-plus"></i> Carrito
      </button>
    </div>
  `;

  card.querySelector('.view-detail-btn').addEventListener('click', () => {
    router.navigate(`/producto/${producto.slug}`);
  });

  card.querySelector('.add-cart-btn').addEventListener('click', () => {
    store.addToCart(producto);
    Swal.fire({
      icon: 'success',
      title: 'Agregado',
      text: `${producto.nombre} agregado al carrito`,
      timer: 1500,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    });
  });

  return card;
}

export { CATEGORY_IMAGES, getProductImage };
