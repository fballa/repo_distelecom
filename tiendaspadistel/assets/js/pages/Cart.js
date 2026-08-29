import { store } from '../store.js';
import { router } from '../router.js';

const CART_FALLBACK = 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png';

export function renderCartPage() {
  const main = document.getElementById('main-content');
  const IVA_RATE = 0.15;

  function renderUI() {
    const items = store.state.cart;
    const subtotal = store.getCartTotal();
    const iva = subtotal * IVA_RATE;
    const total = subtotal + iva;
    const formatPrice = (p) => `$${p.toFixed(2)}`;

    main.innerHTML = `
      <div class="container page-content">
        <h1 class="page-title">Carrito de Compras</h1>
        <p class="page-subtitle">Revisa los productos antes de continuar</p>
        ${!items.length ? `
          <div style="text-align:center;padding:80px 20px;color:var(--gray-400)">
            <i class="fas fa-shopping-cart" style="font-size:4rem;margin-bottom:20px;display:block"></i>
            <p style="font-size:1.1rem;margin-bottom:24px">Tu carrito está vacío</p>
            <button class="btn btn-navy" id="empty-shop-btn"><i class="fas fa-box"></i> Ver Productos</button>
          </div>
        ` : `
        <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start">
          <div>
            <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);overflow:hidden">
              <div style="padding:16px 24px;background:var(--gray-50);border-bottom:1px solid var(--gray-200);display:grid;grid-template-columns:80px 1fr 100px 80px 100px;gap:12px;font-size:0.8rem;font-weight:600;color:var(--gray-500);text-transform:uppercase">
                <span></span><span>Producto</span><span>Precio</span><span>Cant</span><span>Subtotal</span>
              </div>
              <div id="cart-items-list">
                ${items.map(item => `
                  <div class="cart-item" data-id="${item.id}" style="display:grid;grid-template-columns:80px 1fr 100px 80px 100px;gap:12px;align-items:center;padding:16px 24px;border-bottom:1px solid var(--gray-100)">
                    <img src="${item.imagen || CART_FALLBACK}" alt="${item.nombre}" style="width:60px;height:60px;object-fit:contain;background:var(--gray-50);border-radius:var(--radius-sm)" loading="lazy" onerror="this.src='${CART_FALLBACK}'">
                    <div>
                      <h4 style="font-size:0.9rem;margin-bottom:2px">${item.nombre}</h4>
                      <p style="font-size:0.75rem;color:var(--gray-400)">${item.marca} | ${item.sku}</p>
                    </div>
                    <span style="font-weight:600;font-size:0.9rem">${formatPrice(item.precio)}</span>
                    <div style="display:flex;align-items:center;gap:4px">
                      <button class="qty-btn" data-id="${item.id}" data-action="minus" style="width:28px;height:28px;border:1px solid var(--gray-200);background:var(--white);border-radius:4px;cursor:pointer">-</button>
                      <span style="font-weight:600;font-size:0.9rem;min-width:24px;text-align:center">${item.cantidad}</span>
                      <button class="qty-btn" data-id="${item.id}" data-action="plus" style="width:28px;height:28px;border:1px solid var(--gray-200);background:var(--white);border-radius:4px;cursor:pointer">+</button>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                      <span style="font-weight:600;font-size:0.9rem;color:var(--primary)">${formatPrice(item.precio * item.cantidad)}</span>
                      <button class="remove-btn" data-id="${item.id}" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:0.85rem"><i class="fas fa-trash-alt"></i></button>
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
              <button class="btn btn-outline" id="clear-cart-btn"><i class="fas fa-trash"></i> Vaciar Carrito</button>
              <button class="btn btn-outline" id="continue-shop-btn"><i class="fas fa-arrow-left"></i> Continuar Comprando</button>
            </div>
          </div>
          <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);padding:24px;position:sticky;top:100px">
            <h3 style="font-size:1rem;color:var(--primary);margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--gray-100)">Resumen del Pedido</h3>
            <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:0.9rem;color:var(--gray-500)">
              <span>Subtotal</span><span>${formatPrice(subtotal)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:0.9rem;color:var(--gray-500)">
              <span>IVA (15%)</span><span>${formatPrice(iva)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 0;font-size:1.2rem;font-weight:700;color:var(--primary);border-top:2px solid var(--gray-200);margin-top:8px">
              <span>Total</span><span>${formatPrice(total)}</span>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px" id="checkout-from-cart-btn">
              <i class="fas fa-credit-card"></i> Proceder al Checkout
            </button>
          </div>
        </div>
        `}
      </div>
    `;

    document.querySelectorAll('.qty-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id);
        const item = store.state.cart.find(i => i.id === id);
        if (!item) return;
        if (btn.dataset.action === 'plus') store.updateCartQuantity(id, item.cantidad + 1);
        else if (item.cantidad > 1) store.updateCartQuantity(id, item.cantidad - 1);
        else store.removeFromCart(id);
        renderUI();
      });
    });

    document.querySelectorAll('.remove-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        store.removeFromCart(parseInt(btn.dataset.id));
        renderUI();
      });
    });

    document.getElementById('empty-shop-btn')?.addEventListener('click', () => router.navigate('/productos'));
    document.getElementById('clear-cart-btn')?.addEventListener('click', () => {
      Swal.fire({ title: '¿Vaciar carrito?', text: 'Esta acción eliminará todos los productos.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, vaciar', cancelButtonText: 'Cancelar' }).then(r => { if (r.isConfirmed) { store.clearCart(); renderUI(); } });
    });
    document.getElementById('continue-shop-btn')?.addEventListener('click', () => router.navigate('/productos'));
    document.getElementById('checkout-from-cart-btn')?.addEventListener('click', () => router.navigate('/checkout'));
  }

  renderUI();
}
