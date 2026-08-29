import { renderProductCard } from './ProductCard.js';

export function renderProductGrid(productos, containerId = 'product-grid') {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = '';

  if (!productos.length) {
    container.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:var(--gray-400)">
        <i class="fas fa-box-open" style="font-size:3rem;margin-bottom:16px;display:block"></i>
        <p>No se encontraron productos.</p>
      </div>
    `;
    return;
  }

  productos.forEach(producto => {
    container.appendChild(renderProductCard(producto));
  });
}
