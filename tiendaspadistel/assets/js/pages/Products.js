import { store } from '../store.js';
import { renderProductGrid } from '../components/ProductGrid.js';

export function renderProducts() {
  const main = document.getElementById('main-content');
  const categoriasList = store.getCategorias();

  const isCatArr = Array.isArray(categoriasList);

  main.innerHTML = `
    <div class="container page-content">
      <h1 class="page-title">Productos</h1>
      <p class="page-subtitle">Explora nuestro catálogo completo de soluciones tecnológicas</p>
      <div class="filters-bar">
        <select class="filter-select" id="filter-categoria">
          <option value="">Todas las Categorías</option>
          ${isCatArr ? categoriasList.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('') : ''}
        </select>
        <select class="filter-select" id="filter-marca">
          <option value="">Todas las Marcas</option>
          ${(window.marcas || []).map(m => `<option value="${m.id}">${m.nombre}</option>`).join('')}
        </select>
        <select class="filter-select" id="filter-precio">
          <option value="">Cualquier Precio</option>
          <option value="0-50">Hasta $50</option>
          <option value="50-100">$50 - $100</option>
          <option value="100-200">$100 - $200</option>
          <option value="200-500">$200 - $500</option>
          <option value="500-99999">Más de $500</option>
        </select>
        <select class="filter-select" id="filter-stock">
          <option value="">Disponibilidad</option>
          <option value="in">En Stock</option>
          <option value="out">Agotados</option>
        </select>
        <input type="text" class="filter-search" id="filter-search" placeholder="Buscar productos...">
        <button class="btn btn-outline" id="clear-filters-btn" style="padding:10px 16px;font-size:0.85rem">
          <i class="fas fa-times"></i> Limpiar
        </button>
        <span class="filter-results-count" id="results-count">0 productos</span>
      </div>
      <div class="product-grid" id="product-grid"></div>
      <div class="pagination" id="pagination"></div>
    </div>
  `;

  const ITEMS_PER_PAGE = 12;
  let currentPage = 1;
  let totalPages = 1;

  function applyFiltersAndRender() {
    const categoria = document.getElementById('filter-categoria').value;
    const marca = document.getElementById('filter-marca').value;
    const precio = document.getElementById('filter-precio').value;
    const stock = document.getElementById('filter-stock').value;
    const query = document.getElementById('filter-search').value.toLowerCase();

    let filtered = store.state.productos;

    if (query) {
      filtered = filtered.filter(p =>
        p.nombre.toLowerCase().includes(query) ||
        p.marca.toLowerCase().includes(query) ||
        p.categoria.toLowerCase().includes(query) ||
        (p.modelo && p.modelo.toLowerCase().includes(query)) ||
        p.sku.toLowerCase().includes(query)
      );
    }

    if (categoria) {
      const catId = parseInt(categoria);
      filtered = filtered.filter(p => p.categoria_id === catId);
    }
    if (marca) filtered = filtered.filter(p => p.marca_id === parseInt(marca));
    if (precio) {
      const [min, max] = precio.split('-').map(Number);
      filtered = filtered.filter(p => p.precio >= min && p.precio <= max);
    }
    if (stock === 'in') filtered = filtered.filter(p => p.stock > 0);
    if (stock === 'out') filtered = filtered.filter(p => p.stock <= 0);

    document.getElementById('results-count').textContent = `${filtered.length} productos`;

    totalPages = Math.max(1, Math.ceil(filtered.length / ITEMS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    const pageItems = filtered.slice(start, start + ITEMS_PER_PAGE);

    renderProductGrid(pageItems);
    renderPagination(filtered.length);
  }

  function renderPagination(total) {
    const container = document.getElementById('pagination');
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = '<div class="pagination-controls">';

    html += `<button class="page-btn" data-page="${currentPage - 1}" ${currentPage <= 1 ? 'disabled' : ''}>
      <i class="fas fa-chevron-left"></i> Anterior
    </button>`;

    const maxVisible = 5;
    let startP = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endP = Math.min(totalPages, startP + maxVisible - 1);
    if (endP - startP + 1 < maxVisible) startP = Math.max(1, endP - maxVisible + 1);

    if (startP > 1) {
      html += `<button class="page-btn" data-page="1">1</button>`;
      if (startP > 2) html += `<span class="page-ellipsis">...</span>`;
    }
    for (let i = startP; i <= endP; i++) {
      html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
    if (endP < totalPages) {
      if (endP < totalPages - 1) html += `<span class="page-ellipsis">...</span>`;
      html += `<button class="page-btn" data-page="${totalPages}">${totalPages}</button>`;
    }

    html += `<button class="page-btn" data-page="${currentPage + 1}" ${currentPage >= totalPages ? 'disabled' : ''}>
      Siguiente <i class="fas fa-chevron-right"></i>
    </button>`;

    html += '</div>';
    container.innerHTML = html;

    container.querySelectorAll('.page-btn:not(:disabled)').forEach(btn => {
      btn.addEventListener('click', () => {
        currentPage = parseInt(btn.dataset.page);
        applyFiltersAndRender();
        window.scrollTo({ top: document.querySelector('.filters-bar').offsetTop - 100, behavior: 'smooth' });
      });
    });
  }

  function clearFilters() {
    document.getElementById('filter-categoria').value = '';
    document.getElementById('filter-marca').value = '';
    document.getElementById('filter-precio').value = '';
    document.getElementById('filter-stock').value = '';
    document.getElementById('filter-search').value = '';
    currentPage = 1;
    applyFiltersAndRender();
  }

  ['filter-categoria', 'filter-marca', 'filter-precio', 'filter-stock'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => { currentPage = 1; applyFiltersAndRender(); });
  });
  document.getElementById('filter-search').addEventListener('input', () => { currentPage = 1; applyFiltersAndRender(); });
  document.getElementById('clear-filters-btn').addEventListener('click', clearFilters);

  applyFiltersAndRender();
}
