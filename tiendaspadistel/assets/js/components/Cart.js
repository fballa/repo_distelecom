import { store } from '../store.js';
import { router } from '../router.js';

const CAT_FALLBACK = 'https://misdemos.x10.mx/videos/distelecom/cama_sencilla_cctv.png';

export function renderCartSidebar() {
  const sidebar = document.getElementById('cart-sidebar');

  const overlay = document.createElement('div');
  overlay.id = 'cart-overlay';
  overlay.className = 'cart-overlay';
  document.body.appendChild(overlay);

  function lockScroll(lock) {
    document.body.style.overflow = lock ? 'hidden' : '';
  }

  function openCart() {
    sidebar.classList.add('open');
    overlay.classList.add('open');
    lockScroll(true);
  }

  const closeCart = () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    lockScroll(false);
  };

  function onEscKey(e) {
    if (e.key === 'Escape' && sidebar.classList.contains('open')) {
      closeCart();
    }
  }

  overlay.addEventListener('click', closeCart);

  const updateCartUI = () => {
    const items = store.state.cart;
    const IVA_RATE = 0.15;
    const subtotal = store.getCartTotal();
    const iva = subtotal * IVA_RATE;
    const total = subtotal + iva;
    const formatPrice = (p) => `$${p.toFixed(2)}`;

    sidebar.innerHTML = `
      <div class="cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Carrito (${store.getCartCount()})</h3>
        <button class="cart-close" id="cart-close" aria-label="Cerrar carrito"><i class="fas fa-times"></i></button>
      </div>
      <div class="cart-items" id="cart-items">
        ${items.length === 0 ? `
          <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Tu carrito está vacío</p>
            <button class="btn btn-navy" style="margin-top:16px" id="cart-shop-btn">
              <i class="fas fa-box"></i> Ver Productos
            </button>
          </div>
        ` : items.map(item => `
          <div class="cart-item" data-id="${item.id}">
            <img src="${item.imagen || CAT_FALLBACK}" alt="${item.nombre}" loading="lazy" onerror="this.src='${CAT_FALLBACK}'">
            <div class="cart-item-info">
              <h4>${item.nombre}</h4>
              <div class="cart-item-price">${formatPrice(item.precio)}</div>
              <div class="cart-item-actions">
                <button class="cart-qty-minus" data-id="${item.id}">-</button>
                <span>${item.cantidad}</span>
                <button class="cart-qty-plus" data-id="${item.id}">+</button>
                <button class="cart-item-remove" data-id="${item.id}">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
      ${items.length > 0 ? `
        <div class="cart-footer">
          <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.85rem;color:var(--gray-500)">
            <span>Subtotal</span><span>${formatPrice(subtotal)}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:4px 0 12px;font-size:0.85rem;color:var(--gray-500);border-bottom:1px solid var(--gray-100)">
            <span>IVA (15%)</span><span>${formatPrice(iva)}</span>
          </div>
          <div class="cart-total" style="padding-top:12px">
            <span>Total</span>
            <span>${formatPrice(total)}</span>
          </div>
          <button class="btn btn-primary" style="width:100%;justify-content:center" id="cart-checkout-btn">
            <i class="fas fa-credit-card"></i> Ir a Checkout
          </button>
          <button class="btn btn-outline" style="width:100%;justify-content:center;margin-top:8px" id="cart-view-btn">
            <i class="fas fa-eye"></i> Ver Carrito
          </button>
        </div>
      ` : ''}
    `;

    document.getElementById('cart-close')?.addEventListener('click', closeCart);

    document.querySelectorAll('.cart-qty-minus').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id);
        const item = items.find(i => i.id === id);
        if (item && item.cantidad > 1) store.updateCartQuantity(id, item.cantidad - 1);
        else store.removeFromCart(id);
      });
    });

    document.querySelectorAll('.cart-qty-plus').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id);
        const item = items.find(i => i.id === id);
        if (item) store.updateCartQuantity(id, item.cantidad + 1);
      });
    });

    document.querySelectorAll('.cart-item-remove').forEach(btn => {
      btn.addEventListener('click', () => {
        store.removeFromCart(parseInt(btn.dataset.id));
      });
    });

    document.getElementById('cart-checkout-btn')?.addEventListener('click', () => {
      closeCart();
      router.navigate('/checkout');
    });

    document.getElementById('cart-view-btn')?.addEventListener('click', () => {
      closeCart();
      router.navigate('/carrito');
    });

    document.getElementById('cart-shop-btn')?.addEventListener('click', () => {
      closeCart();
      router.navigate('/productos');
    });
  };

  updateCartUI();
  store.subscribe(updateCartUI);

  document.addEventListener('keydown', onEscKey);
  document.addEventListener('open-cart', openCart);
}
