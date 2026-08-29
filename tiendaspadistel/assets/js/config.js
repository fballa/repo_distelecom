const scriptUrl = new URL(import.meta.url);
const basePath = scriptUrl.pathname.substring(0, scriptUrl.pathname.indexOf('/assets/js/config.js'));

export const BASE_URL = basePath || '';
