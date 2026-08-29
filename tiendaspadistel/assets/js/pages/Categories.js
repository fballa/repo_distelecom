import { store } from '../store.js';
import { renderProductGrid } from '../components/ProductGrid.js';
import { router } from '../router.js';

export function renderCategories() {
  const main = document.getElementById('main-content');
  const categorias = store.getCategorias();

  const catCards = Array.isArray(categorias) && categorias.length
    ? categorias.map(c => {
        const count = store.state.productos.filter(p => p.categoria_id === parseInt(c.id) || p.categoria === c.nombre).length;
        return `
          <div class="category-card" data-id="${c.id}" data-slug="${c.slug || ''}">
            <div class="category-card-body">
              <h3>${c.nombre}</h3>
              <p>${count} productos</p>
              <button class="btn btn-navy"><i class="fas fa-box"></i> Ver Productos</button>
            </div>
          </div>
        `;
      }).join('')
    : '';

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Categorías</h1>
      <p class="page-subtitle">Explora nuestras categorías de productos</p>
      <div class="categories-grid">
        ${catCards || '<p style="text-align:center;padding:60px 20px;color:var(--gray-400)">No hay categorías disponibles.</p>'}
      </div>
    </div>
  `;

  document.querySelectorAll('.category-card').forEach(card => {
    card.addEventListener('click', () => {
      const slug = card.dataset.slug;
      if (slug) {
        router.navigate(`/categorias/${slug}`);
      } else {
        router.navigate('/productos');
      }
    });
  });
}

export function renderCategoryDetail(params) {
  const main = document.getElementById('main-content');
  const categorias = store.getCategorias();
  const cat = Array.isArray(categorias) ? categorias.find(c => c.slug === params.slug) : null;
  const catNombre = cat ? cat.nombre : (params.slug || '');
  const catId = cat ? parseInt(cat.id) : null;

  let productos = [];
  if (catId) {
    productos = store.getProductosByCategoriaId(catId);
  }
  if (!productos.length) {
    productos = store.getProductosByCategoria(catNombre);
  }

  main.innerHTML = `
    <div class="container page-content">
      <div style="margin-bottom:24px">
        <a style="color:var(--secondary);cursor:pointer;font-size:0.9rem" id="back-cat">
          <i class="fas fa-arrow-left"></i> Todas las Categorías
        </a>
      </div>
      <h1 class="page-title">${catNombre}</h1>
      <p class="page-subtitle">${productos.length} productos disponibles</p>
      <div class="product-grid" id="product-grid"></div>
    </div>
  `;

  document.getElementById('back-cat').addEventListener('click', () => router.navigate('/productos'));
  renderProductGrid(productos);
}
