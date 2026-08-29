const STORAGE_KEY = 'distelecom_chat_history';
const WEBHOOK_URL = 'https://nicatoolagente.app.n8n.cloud/webhook/agenteiadistelecom';
const TIMEOUT_MS = 20000;

export async function getPublicIP() {
  try {
    const resp = await axios.get('https://api.ipify.org/?format=json', { timeout: 5000 });
    return resp.data?.ip || '0.0.0.0';
  } catch {
    return '0.0.0.0';
  }
}

export async function sendMessage(mensaje, ip) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), TIMEOUT_MS);

  try {
    const resp = await axios.post(WEBHOOK_URL, { ip, mensaje }, {
      headers: { 'Content-Type': 'application/json' },
      signal: controller.signal,
      timeout: TIMEOUT_MS
    });
    clearTimeout(timeoutId);
    return resp.data?.output || null;
  } catch (err) {
    clearTimeout(timeoutId);
    if (err.code === 'ERR_CANCELED' || err.message?.includes('aborted')) {
      return { error: 'timeout' };
    }
    return { error: 'network' };
  }
}

export function saveHistory(history) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
  } catch { /* ignore storage errors */ }
}

export function loadHistory() {
  try {
    const data = localStorage.getItem(STORAGE_KEY);
    return data ? JSON.parse(data) : [];
  } catch {
    return [];
  }
}
