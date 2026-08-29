# Distelecom — Tienda Ecommerce SPA

SPA (Single Page Application) de tienda en línea para **Distelecom**, construida con HTML5, CSS3 y **JavaScript ES2023 (Vanilla JS)** — sin frameworks de UI.

![SPA](https://img.shields.io/badge/SPA-Vanilla%20JS-blue) ![Estado](https://img.shields.io/badge/Estado-En%20desarrollo-green)

## Características

- **SPA con router propio** (History API) — navegación sin recargas.
- **Catálogo dinámico**: productos, categorías, ofertas, novedades y servicios cargados desde la API REST.
- **Busqueda global** de productos y novedades.
- **Carrito** persistente en `localStorage` con drawer lateral y página de carrito.
- **Checkout completo**: creación de cliente, dirección, pedido y pago vía API.
- **Seguimiento de pedidos** por número de orden (`GET /api/pedidos/numero/{numero}`).
- **Reseñas de productos** con promedio y formulario de envío.
- **Chatbot** conectado a un webhook de n8n.
- **Botón flotante de WhatsApp**.
- **Diseño UI/UX 2026**: degradados azul, tarjetas con sombras suaves, glassmorphism, microanimaciones y 100% responsive.

---

## Requisitos

- Navegador moderno con soporte para **ES Modules** (Chrome, Firefox, Edge, Safari actuales).
- Un **servidor HTTP** (Apache, Nginx, Node, etc.) — *no funciona bien abriendo `index.html` directo con `file://`*.
- Backend con **API REST** en PHP u otro lenguaje que exponga los endpoints de la sección [API](#api-rest-requerida).

---

---

## Instalación

### 1. Descargar o clonar el proyecto

```bash
git clone <url-del-repositorio> tiendaspadistel
cd tiendaspadistel
```

### 2. Subir al servidor

Sube la carpeta completa a tu servidor web. Ejemplo de rutas de destino:

```
public_html/tiendaspadistel/
├── index.html
└── assets/
```

### 3. Configurar la URL base de la API

Edita **`assets/js/services/apiService.js`** (línea 1):

```js
const API_BASE = 'https://tu-dominio.com/tu-api/public/api/';
```

> Este valor es el único que centraliza todos los endpoints del sitio.

### 5. Configurar el número de WhatsApp (opcional)

Edita **`assets/js/components/WhatsAppButton.js`**:

```js
btn.href = 'https://wa.me/TU_NUMERO';
```
