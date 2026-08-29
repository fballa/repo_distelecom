import { store } from '../store.js';
import { renderProductGrid } from '../components/ProductGrid.js';
import { fetchOfertasActivas } from '../services/apiService.js';

export async function renderOffers() {
  const main = document.getElementById('main-content');
  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Ofertas</h1>
      <p class="page-subtitle">Aprovecha nuestros precios especiales y descuentos</p>
      <div id="offers-content"><div style="text-align:center;padding:60px 20px;color:var(--gray-400)"><i class="fas fa-spinner fa-spin" style="font-size:2rem;display:block;margin-bottom:16px"></i><p>Cargando ofertas...</p></div></div>
    </div>
  `;
  const container = document.getElementById('offers-content');

  try {
    const resp = await fetchOfertasActivas();
    const ofertas = resp.success ? (resp.data?.data || []) : [];

    if (!ofertas.length) {
      container.innerHTML = `
        <div style="text-align:center;padding:60px 20px;color:var(--gray-400)">
          <i class="fas fa-tags" style="font-size:3rem;margin-bottom:16px;display:block"></i>
          <p>No hay ofertas disponibles en este momento. Vuelve pronto.</p>
        </div>`;
      return;
    }

    const productosOferta = ofertas.map(oferta => {
      const storeProducto = store.state.productos.find(p => p.id === parseInt(oferta.producto_id));
      if (storeProducto) {
        return { ...storeProducto, precio: parseFloat(oferta.precio_oferta), precioAnterior: parseFloat(oferta.producto?.precio || storeProducto.precioBase), oferta: true };
      }
      const p = oferta.producto || {};
      return {
        id: parseInt(p.id || oferta.producto_id),
        nombre: p.nombre || oferta.titulo || 'Producto',
        slug: p.slug || '',
        imagen: p.imagen_principal || '',
        precio: parseFloat(oferta.precio_oferta || p.precio_oferta || 0),
        precioAnterior: parseFloat(p.precio || oferta.precio_oferta || 0),
        stock: 10,
        marca: '',
        categoria: '',
        sku: '',
        modelo: '',
        destacado: false,
        nuevo: false,
        oferta: true,
        descripcionCorta: oferta.descripcion || ''
      };
    });

    container.innerHTML = '<div class="product-grid" id="product-grid"></div>';
    renderProductGrid(productosOferta);
  } catch {
    container.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:var(--gray-400)">
        <i class="fas fa-tags" style="font-size:3rem;margin-bottom:16px;display:block"></i>
        <p>No hay ofertas disponibles en este momento. Vuelve pronto.</p>
      </div>`;
  }
}
