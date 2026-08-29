import { fetchProductos, fetchCategorias, fetchProducto } from './services/apiService.js';

function normalizeProducto(p) {
  const precio = parseFloat(p.precio) || 0;
  const precioOferta = p.precio_oferta ? parseFloat(p.precio_oferta) : null;
  return {
    id: parseInt(p.id),
    uuid: p.uuid,
    categoria_id: parseInt(p.categoria_id) || 0,
    categoria: p.categoria || '',
    marca_id: parseInt(p.marca_id) || 0,
    marca: p.marca || '',
    sku: p.sku || '',
    nombre: p.nombre || '',
    slug: p.slug || '',
    modelo: p.modelo || '',
    descripcion: p.descripcion_larga || p.descripcion || '',
    descripcionCorta: p.descripcion_corta || '',
    precio: precioOferta || precio,
    precioAnterior: precioOferta ? precio : null,
    precioBase: precio,
    stock: parseInt(p.stock) || 0,
    stock_minimo: parseInt(p.stock_minimo) || 0,
    garantia: p.garantia || '',
    imagen: p.imagen_principal || '',
    imagenes: Array.isArray(p.imagenes) ? p.imagenes.map(i => i.imagen || i) : [],
    destacado: p.destacado === '1' || p.destacado === 1 || p.destacado === true,
    nuevo: p.nuevo === '1' || p.nuevo === 1 || p.nuevo === true,
    oferta: p.oferta === '1' || p.oferta === 1 || p.oferta === true,
    estado: p.estado || 'Activo',
    created_at: p.created_at || '',
    ...(p.especificaciones || {})
  };
}

class Store {
  constructor() {
    this.state = {
      productos: [],
      categoriasData: [],
      cart: JSON.parse(localStorage.getItem('distelecom_cart') || '[]'),
      currentRoute: '/',
      searchQuery: '',
      filters: { categoria: '', marca: '', tecnologia: '', precio: '', stock: '' },
      loading: false,
      error: null
    };
    this.listeners = [];
  }

  getState() {
    return this.state;
  }

  setState(partial) {
    this.state = { ...this.state, ...partial };
    this.notify();
  }

  subscribe(fn) {
    this.listeners.push(fn);
    return () => {
      this.listeners = this.listeners.filter(l => l !== fn);
    };
  }

  notify() {
    this.listeners.forEach(fn => fn(this.state));
  }

  async loadProducts() {
    this.setState({ loading: true, error: null });
    try {
      const resp = await fetchProductos({ limite: 100 });
      if (resp.success) {
        const raw = resp.data?.data || [];
        this.setState({ productos: raw.map(normalizeProducto), loading: false });
      } else {
        this.setState({ error: resp.message || 'Error al cargar productos', loading: false });
      }
    } catch (e) {
      console.error('Error loading products:', e);
      this.setState({ error: 'Error de conexión al cargar productos', loading: false });
    }
  }

  async loadCategorias() {
    try {
      const resp = await fetchCategorias({ limite: 100 });
      if (resp.success) {
        this.setState({ categoriasData: resp.data?.data || [] });
      }
    } catch (e) {
      console.error('Error loading categories:', e);
    }
  }

  getProductos() {
    return this.state.productos;
  }

  getProductBySlug(slug) {
    return this.state.productos.find(p => p.slug === slug);
  }

  getProductosByCategoria(cat) {
    return this.state.productos.filter(p => p.categoria.toLowerCase() === cat.toLowerCase());
  }

  getProductosByCategoriaId(catId) {
    return this.state.productos.filter(p => p.categoria_id === catId);
  }

  getDestacados() {
    return this.state.productos.filter(p => p.destacado);
  }

  getOfertas() {
    return this.state.productos.filter(p => p.oferta);
  }

  getNuevos() {
    return this.state.productos.filter(p => p.nuevo);
  }

  getCategorias() {
    if (this.state.categoriasData.length) {
      return this.state.categoriasData;
    }
    const names = [...new Set(this.state.productos.map(p => p.categoria).filter(Boolean))];
    return names.map(n => ({ id: 0, nombre: n, slug: n.toLowerCase().replace(/[\s]+/g, '-'), icono: 'fas fa-box', imagen: '', descripcion: '' }));
  }

  getMarcas() {
    return [...new Set(this.state.productos.map(p => p.marca))].filter(Boolean);
  }

  addToCart(producto, cantidad = 1) {
    const exist = this.state.cart.find(item => item.id === producto.id);
    if (exist) {
      exist.cantidad += cantidad;
    } else {
      this.state.cart.push({ ...producto, cantidad });
    }
    this.persistCart();
    this.notify();
  }

  removeFromCart(productoId) {
    this.state.cart = this.state.cart.filter(item => item.id !== productoId);
    this.persistCart();
    this.notify();
  }

  updateCartQuantity(productoId, cantidad) {
    const item = this.state.cart.find(i => i.id === productoId);
    if (item) {
      item.cantidad = Math.max(1, cantidad);
      this.persistCart();
      this.notify();
    }
  }

  getCartTotal() {
    return this.state.cart.reduce((sum, item) => sum + item.precio * item.cantidad, 0);
  }

  getCartCount() {
    return this.state.cart.reduce((sum, item) => sum + item.cantidad, 0);
  }

  clearCart() {
    this.state.cart = [];
    this.persistCart();
    this.notify();
  }

  persistCart() {
    localStorage.setItem('distelecom_cart', JSON.stringify(this.state.cart));
  }

  searchProductos(query) {
    const q = query.toLowerCase();
    return this.state.productos.filter(p =>
      p.nombre.toLowerCase().includes(q) ||
      p.marca.toLowerCase().includes(q) ||
      p.categoria.toLowerCase().includes(q) ||
      (p.modelo && p.modelo.toLowerCase().includes(q)) ||
      p.sku.toLowerCase().includes(q)
    );
  }

  applyFilters(productos, filters) {
    return productos.filter(p => {
      if (filters.categoria && p.categoria !== filters.categoria) return false;
      if (filters.marca && p.marca !== filters.marca) return false;
      if (filters.stock === 'in' && p.stock <= 0) return false;
      if (filters.stock === 'out' && p.stock > 0) return false;
      return true;
    });
  }
}

export const store = new Store();
