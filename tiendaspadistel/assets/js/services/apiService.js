const API_BASE = 'URL DE API AQUI';

async function apiFetch(endpoint, params = {}) {
  const url = new URL(endpoint, API_BASE);
  Object.entries(params).forEach(([k, v]) => { if (v !== undefined && v !== null && v !== '') url.searchParams.set(k, v); });
  const resp = await fetch(url.toString());
  if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
  return resp.json();
}

export async function fetchCategorias(params = {}) {
  return apiFetch('categorias', { estado: 'Activo', limite: 100, ...params });
}

export async function fetchProductos(params = {}) {
  return apiFetch('productos', { estado: 'Activo', ...params });
}

export async function fetchProducto(idOrSlug) {
  return apiFetch(`productos/${idOrSlug}`);
}

export async function fetchOfertasActivas(params = {}) {
  return apiFetch('ofertas/activas', { limite: 12, ...params });
}

export async function fetchNovedadesPublicadas(params = {}) {
  return apiFetch('novedades/publicadas', { limite: 12, ...params });
}

export async function fetchPedidos(params = {}) {
  return apiFetch('pedidos', params);
}

export async function fetchPedido(id) {
  return apiFetch(`pedidos/${id}`);
}

export async function fetchPedidoPorNumero(numero) {
  return apiFetch(`pedidos/numero/${numero}`);
}

export async function fetchCliente(id) {
  return apiFetch(`clientes/${id}`);
}

export async function fetchClientes(params = {}) {
  return apiFetch('clientes', params);
}

export async function createCliente(data) {
  const resp = await fetch(new URL('clientes', API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function loginCliente(data) {
  const resp = await fetch(new URL('clientes/login', API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function fetchDirecciones(clienteId) {
  return apiFetch(`clientes/${clienteId}/direcciones`);
}

export async function createDireccion(clienteId, data) {
  const resp = await fetch(new URL(`clientes/${clienteId}/direcciones`, API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function createPedido(data) {
  const resp = await fetch(new URL('pedidos', API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function createPago(pedidoId, data) {
  const resp = await fetch(new URL(`pedidos/${pedidoId}/pagos`, API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function updatePedidoEstado(pedidoId, data) {
  const resp = await fetch(new URL(`pedidos/${pedidoId}/estado`, API_BASE), { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}

export async function fetchConfig() {
  return apiFetch('configuracion');
}

export async function fetchMarcasActivas() {
  return apiFetch('marcas/activas');
}

export async function fetchServiciosActivos() {
  return apiFetch('servicios/activos');
}

export async function fetchReviewsPromedio(productoId) {
  return apiFetch(`reviews/producto/${productoId}/promedio`);
}

export async function fetchReviews(productoId, pagina = 1, limite = 10) {
  return apiFetch(`reviews/producto/${productoId}`, { pagina, limite });
}

export async function createReview(data) {
  const resp = await fetch(new URL('reviews', API_BASE), { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return resp.json();
}
