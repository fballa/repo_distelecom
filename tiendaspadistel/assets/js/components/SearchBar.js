import { store } from '../store.js';
import { router } from '../router.js';
import { getProductImage } from './ProductCard.js';

export function renderSearchModal() {
  const modal = document.getElementById('search-modal');

  modal.innerHTML = `
    <div class="search-overlay" id="search-overlay"></div>
    <div class="search-modal-content">
      <input type="text" id="search-input" placeholder="Buscar por nombre, marca, modelo, categoría o SKU..." autocomplete="off">
      <div class="search-results" id="search-results"></div>
    </div>
  `;

  const input = document.getElementById('search-input');
  const results = document.getElementById('search-results');
  const overlay = document.getElementById('search-overlay');

  const closeSearch = () => {
    modal.classList.remove('open');
    input.value = '';
    results.innerHTML = '';
  };

  overlay.addEventListener('click', closeSearch);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSearch();
  });

  input.addEventListener('input', () => {
    const query = input.value.trim().toLowerCase();
    if (!query) { results.innerHTML = ''; return; }

    const found = store.state.productos.filter(p =>
      p.nombre.toLowerCase().includes(query) ||
      p.marca.toLowerCase().includes(query) ||
      (p.modelo && p.modelo.toLowerCase().includes(query)) ||
      p.categoria.toLowerCase().includes(query) ||
      p.sku.toLowerCase().includes(query)
    ).slice(0, 8);

    if (!found.length) {
      results.innerHTML = '<p style="text-align:center;padding:24px;color:var(--gray-400)">No se encontraron resultados</p>';
      return;
    }

    results.innerHTML = found.map(p => {
      const img = getProductImage(p);
      return `
      <div class="search-result-item" data-slug="${p.slug}">
        <img src="${img}" alt="${p.nombre}" loading="lazy" onerror="this.style.display='none'">
        <div class="result-info">
          <h4>${p.nombre}</h4>
          <span>${p.categoria} | ${p.marca} | ${p.sku}</span>
        </div>
      </div>`;
    }).join('');

    results.querySelectorAll('.search-result-item').forEach(el => {
      el.addEventListener('click', () => {
        closeSearch();
        router.navigate(`/producto/${el.dataset.slug}`);
      });
    });
  });
}
