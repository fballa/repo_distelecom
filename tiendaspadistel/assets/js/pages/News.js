import { store } from '../store.js';
import { renderProductGrid } from '../components/ProductGrid.js';
import { fetchNovedadesPublicadas } from '../services/apiService.js';

export async function renderNews() {
  const main = document.getElementById('main-content');
  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Novedades</h1>
      <p class="page-subtitle">Los productos más recientes en nuestro catálogo</p>
      <div id="news-content"><div style="text-align:center;padding:60px 20px;color:var(--gray-400)"><i class="fas fa-spinner fa-spin" style="font-size:2rem;display:block;margin-bottom:16px"></i><p>Cargando novedades...</p></div></div>
    </div>
  `;
  const container = document.getElementById('news-content');

  try {
    const resp = await fetchNovedadesPublicadas();
    const novedades = resp.success ? (resp.data?.data || []) : [];

    if (!novedades.length) {
      container.innerHTML = `
        <div style="text-align:center;padding:60px 20px;color:var(--gray-400)">
          <i class="fas fa-star" style="font-size:3rem;margin-bottom:16px;display:block"></i>
          <p>No hay novedades disponibles en este momento.</p>
        </div>`;
      return;
    }

    const productosNuevos = novedades.map(novedad => {
      const storeProducto = store.state.productos.find(p => p.id === parseInt(novedad.producto_id));
      if (storeProducto) {
        return { ...storeProducto, nuevo: true };
      }
      const p = novedad.producto || {};
      return {
        id: parseInt(p.id || novedad.producto_id),
        nombre: p.nombre || novedad.titulo || 'Producto',
        slug: p.slug || '',
        imagen: novedad.imagen || p.imagen_principal || '',
        precio: parseFloat(p.precio || 0),
        stock: 10,
        marca: '',
        categoria: '',
        sku: '',
        modelo: '',
        destacado: false,
        nuevo: true,
        oferta: false,
        descripcionCorta: novedad.descripcion || ''
      };
    });

    container.innerHTML = '<div class="product-grid" id="product-grid"></div>';
    renderProductGrid(productosNuevos);
  } catch {
    container.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:var(--gray-400)">
        <i class="fas fa-star" style="font-size:3rem;margin-bottom:16px;display:block"></i>
        <p>No hay novedades disponibles en este momento.</p>
      </div>`;
  }
}
